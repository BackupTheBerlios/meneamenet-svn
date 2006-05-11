<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
}

global $db;

if (!isset($_GET['id'])) die;
$link_id = intval($_GET['id']);
if (!isset($_GET['p'])) $votes_page = 1;
else $votes_page = intval($_GET['p']);

$votes_page_size = 20;
$votes_offset=($votes_page-1)*$votes_page_size;

$votes = $db->get_results("SELECT vote_user_id, vote_ip, user_email, user_login, date_format(vote_date,'%d/%m %T') as vote_date FROM votes, users WHERE vote_type='links' and vote_link_id=".$link_id." AND vote_user_id > 0 AND vote_value > 0 AND user_id = vote_user_id ORDER BY vote_date LIMIT $votes_offset,$votes_page_size");
if (!$votes) die;

$votes_users = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$link_id." AND vote_user_id!=0 AND vote_value > 0");
$votes_anon = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$link_id." AND vote_user_id=0 AND vote_value > 0");
$votes_negatives = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$link_id." AND vote_value < 0");

echo '<div id="voters-list">';
foreach ( $votes as $vote ){
	echo '<div class="item">';
	echo '<a href="user.php?login='.$vote->user_login.'" title="'.$vote->vote_date.'">';
	echo '<img src="'.get_gravatar_url($vote->user_email, 20).'" width="20" height="20" alt="'.$vote->user_login.'"/>';
	echo $vote->user_login.'</a>';
	echo '</div>';
}
echo "</div>\n";

echo '<div class="news-details">';
echo _('votos usuarios'). ': '.$votes_users.',&nbsp;&nbsp;';
echo _('votos anónimos'). ': '.$votes_anon.',&nbsp;&nbsp;';
echo  _('votos negativos').': '. $votes_negatives;
echo '</div>';


do_shakers_pages($votes_users, $votes_page, $votes_page_size);

function do_shakers_pages($total, $current, $page_size=20) {
	global $db, $link_id;

	$index_limit = 10;

	$total_pages=ceil($total/$page_size);
	$start=max($current-intval($index_limit/2), 1);
	$end=$start+$index_limit-1;
	
	echo '<div class="pages">';
	if($start>1) {
		$i = 1;
		echo '<a href="javascript:get_votes('.$i.','.$link_id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
		echo '<span>...</span>';
	}
	for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
		if($i==$current) echo '<span class="current">';
		echo '<a href="javascript:get_votes('.$i.','.$link_id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
		if($i==$current) echo '</span>';
	}
	if($total_pages>$end) {
		$i = $total_pages;
		echo '<span>...</span>';
		echo '<a href="javascript:get_votes('.$i.','.$link_id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
	}
	echo "</div>\n";

}

?>
