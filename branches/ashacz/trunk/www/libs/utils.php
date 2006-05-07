<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once(mnminclude.'check_behind_proxy.php');
$globals['user_ip'] = check_ip_behind_proxy();

function user_exists($username) {
	global $db;
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_login='$username'");
	if ($res>0) return true;
	return false;
}

function email_exists($email) {
	global $db;
	$res=$db->get_var("SELECT count(*) FROM users WHERE user_email='$email'");
	if ($res>0) return $res;
	return false;
}

function check_email($email) {
	return preg_match('/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{2,4}$/', $email);
}

function txt_time_diff($from, $now=0){
	$txt = '';
	if($now==0) $now = time();
	$diff=$now-$from;
	$days=intval($diff/86400);
	$diff=$diff%86400;
	$hours=intval($diff/3600);
	$diff=$diff%3600;
	$minutes=intval($diff/60);

	if($days>1) $txt  .= " $days "._('días');
	else if ($days==1) $txt  .= " $days "._('día');

	if($hours>1) $txt .= " $hours "._('horas');
	else if ($hours==1) $txt  .= " $hours "._('hora');

	if($minutes>1) $txt .= " $minutes "._('minutos');
	else if ($minutes==1) $txt  .= " $minutes "._('minuto');

	if($txt=='') $txt = ' '. _('pocos segundos') . ' ';
	return $txt;
}

function txt_shorter($string, $len=80) {
	if (strlen($string) > $len)
		$string = substr($string, 0, $len-3) . "...";
	return $string;
}

function clean_text($string) {
	return htmlspecialchars(strip_tags(trim($string)));
}

function save_text_to_html($string) {
	//$string = strip_tags(trim($string));
	//$string= htmlspecialchars(trim($string));
	$string= text_to_html($string);
	$string = preg_replace("/\r\n|\r|\n/", "\n<br />\n", $string);
	return $string;
}

function text_to_html($string) {
	return preg_replace('/([hf][tps]{2,4}:\/\/[^ \t\n\r\]\(\)]+[^ .\t,\n\r\(\)\"\'\]\?])/', '<a href="$1" rel="nofollow">$1</a>', $string);
}

function check_integer($which) {
	if (is_numeric($_REQUEST[$which])) {
		return intval($_REQUEST[$which]);
	} else {
		return false;
	}
}

function get_current_page() {
	if(($var=check_integer('page'))) {
		return $var;
	} else {
		return 1;
	}
    // return $_GET['page']>0 ? $_GET['page'] : 1;
}

function get_search_clause($option='') {
	global $db;
	if($option == 'boolean') {
		$mode = 'IN BOOLEAN MODE';
	}
	if(!empty($_REQUEST['search'])) {
		$words = $db->escape(strip_tags(trim($_REQUEST['search'])));
		if (preg_match('/^tag:/', $words)) {
			$_REQUEST['tag'] = 'true';
			$words=preg_replace('/^tag: */', '', $words);
		}
		if ($_REQUEST['tag'] == 'true') {
			$where .= "MATCH (link_tags) AGAINST ('$words' $mode) ";
		} else {
			$where = "MATCH (link_url, link_url_title, link_title, link_content, link_tags) AGAINST ('$words' $mode) ";
		}
		if (!empty($_REQUEST['from'])) {
			$where .=  " AND link_date > from_unixtime(".intval($_REQUEST['from']).") ";
		}
		return $where;
	} else {
		return false;
	}
}

function get_date($epoch) {
    return date("Y-m-d", $epoch);
}

function get_date_time($epoch) {
	    return date("Y-m-d H:i", $epoch);
}

function get_server_name() {
	global $server_name;
	if(empty($server_name)) 
		return $_SERVER['SERVER_NAME'];
	else
		return $server_name;
}

function get_permalink($id) {
	return "http://".get_server_name()."/story.php?id=$id";
}

function get_trackback($id) {
	return "http://".get_server_name()."/trackback.php?id=$id";
}

function get_gravatar_url($email, $size, $alt=true) {
	global $globals; 
 	if ($globals['do_gravatars']) {
		if ($alt) $default = '&amp;default=http%3A%2F%2F'.get_server_name().'%2Fimg%2Fcommon%2Fno-gravatar-2-'.$size.'.jpg';
		return 'http://www.gravatar.com/avatar.php?gravatar_id='.md5($email).'&amp;rating=PG&amp;size='.$size.$default;
	} else {
		return 'http://'.get_server_name().'/img/common/no-gravatar-2-'.$size.'.jpg';
	}
}

function utf8_substr($str,$start)
{
	preg_match_all("/./su", $str, $ar);
 
	if(func_num_args() >= 3) {
		$end = func_get_arg(2);
		return join("",array_slice($ar[0],$start,$end));
	} else {
		return join("",array_slice($ar[0],$start));
	}
}
?>
