<?
include('../config.php');
include(mnminclude.'link.php');

header("Content-Type: text/plain");
ob_end_flush();

define(MAX, 1.10);
define (MIN, 1.0);

$now = time();
echo "BEGIN: ".get_date_time($now)."\n";
if(!empty($_GET['period']))
	$period = intval($_GET['period']);
else $period = 200;

echo "Period (h): $period\n";

$from_time = $now - $period*3600;
#$from_where = "FROM votes, links WHERE  


$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(link_published_date)) from links WHERE link_status='published'");
if (!$last_published) $last_published = $now - 24*3600*30;
//$history_from = $last_published - $period*3600;
$history_from = $last_published - 200*3600;

$diff = $now - $last_published;

$d = min(MAX, MAX - ($diff/3600)*(MAX-MIN) );
$d = max(0.8, $d);
print "Last published at: " . get_date_time($last_published) ."\n";
echo "Decay: $d\n";

$continue = true;
$i=0;

$past_karma = $db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_published_date > FROM_UNIXTIME($history_from) and link_status='published'");
//$past_karma = $db->get_var("SELECT avg(link_karma) from links WHERE link_status='published' ORDER BY link_published_date DESC LIMIT 30");
echo "Past karma: $past_karma\n";
while ($continue) {
	$continue = false;
//////////////

	if ( $globals['interface'] == "digg" ) {
		$min_karma = round(max($past_karma * $d, 20));
		$min_votes = 5;
	} elseif ($globals['interface'] == "monouser" ) {
	        $min_karma = 0;
	        $min_votes = 1;
	}

/////////////
	
	echo "Current MIN karma: $min_karma    MIN votes: $min_votes\n";
	$where = "vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=link_id AND link_status = 'queued' AND link_votes>=$min_votes and user_id = link_author and user_level != 'disabled'";
	$group =  "GROUP BY vote_link_id";
	$sort = "ORDER BY karma DESC";


	//$votes = $db->get_var("SELECT count(*) from links, votes where $where");
	//$karma_total = $db->get_var("SELECT sum(vote_value) from links, votes where $where");

	$links = $db->get_results("SELECT SQL_NO_CACHE link_id, sum(vote_value) as karma, count(*) as votes from links, votes, users where $where $group $sort LIMIT 30");
	$rows = $db->num_rows;
	if (!$rows) {
		echo "There is no articles\n";
		echo "--------------------------\n";
		die;
	}
	
	//$karma_avg = $karma_total / $rows;
	//echo "Votes: $votes Karma_total: $karma_total Media: $karma_avg\n";

	$max_karma_found = 0;
	$best_link = 0;
	$best_karma = 0;
	
	if ($links) {
//		$dblink = current($links);
		printf ("\n%6s %6s %8s    %s\n", "id", "votes", "karma", "title");
		foreach($links as $dblink) {
			$link = new Link;
			$link->id=$dblink->link_id;
			$link->read();
			//$karma = $dblink->karma/sqrt($period);

			// Aged karma
			$diff = max(0, $now - ($link->date + 18*3600)); // 1 hour without decreasing
			$oldd = 1 - $diff/(3600*144);
			$oldd = max(0.5, $oldd);
			$oldd = min(1, $oldd);
			//echo "Oldness: $oldd ($diff)\n";
			$aged_karma = $dblink->karma * $oldd;

			$dblink->karma=$aged_karma;

			$max_karma_found = max($max_karma_found, $dblink->karma);
			printf ("%6d %6d %8.2f    %s\n", $link->id, $dblink->votes, $dblink->karma, $link->title);
			//echo "$link->id:  $dblink->votes, $dblink->karma, '" . $link->title; echo "'\n";
			if ($max_karma_found == $dblink->karma)	{
				$best_title = $link->title;
			}
			
			if ($dblink->votes >= $min_votes && $dblink->karma >= $min_karma &&
				$dblink->karma > ($max_karma_found - 0.1) ) {
				$best_link = $link->id;
				$best_karma = $dblink->karma;
				$best_title = $link->title;
				echo "     Better found:  '$best_title'\n";
			}
		}

		//////////
		echo "\n";
		echo "Current best karma: $max_karma_found '$best_title'\n";
		if ($best_link > 0) {
			$i++;
			$link->id = $best_link;
			$link->read();
			$link->karma=$best_karma;
			$link->status='published';
			$link->published_date=time();
			echo "Best found: $link->id, $link->karma\n";
			$link->store();
			echo "$i Published: $link->title \n";
			echo "$i -------------\n";
			if ($i < 3 && $d > 1.01) $continue = true;
		} 
		/****else {
			$future = $past_karma * 3600 / $blink->karma;
			echo "Estimated: " . get_date_time(time()+$future) ."\n";
		}
		**********/
	}  
	echo "--------------------------\n";
}
?>
