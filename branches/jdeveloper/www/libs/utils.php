<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


//require_once(mnminclude.'check_behind_proxy.php');
//$globals['user_ip'] = check_ip_behind_proxy();
$globals['user_ip'] = $_SERVER["REMOTE_ADDR"];

$negative_votes_values = Array ( -1 => _('irrelevante'), -2 => _('antigua'), -3 => _('spam'), -4 => _('duplicada'), -5 => _('provocación'), -6 => _('errónea') );

function get_negative_vote($value) {
	global $negative_votes_values;
	return $negative_votes_values[$value];
}

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

function url_clean($url) {
	$array = explode('#', $url, 1);
	return $array[0];
}

function check_username($name) {
	return (preg_match('/^[a-z0-9_\-\.çÇñÑ·]+$/i', $name) && strlen($name) <= 24);
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

function txt_shorter($string, $len=70) {
	if (strlen($string) > $len)
		$string = substr($string, 0, $len-3) . "...";
	return $string;
}

function clean_text($string) {
	return htmlspecialchars(strip_tags(preg_replace('/[\n\t\r]+/s', ' ', trim($string))));
}

function save_text_to_html($string) {
	//$string = strip_tags(trim($string));
	//$string= htmlspecialchars(trim($string));
	$string= text_to_html($string);
	$string = preg_replace("/\r\n|\r|\n/", "\n<br />\n", $string);
	return $string;
}

function text_to_html($string) {
	return preg_replace('/([ \n\r]|^)([hf][tps]{2,4}:\/\/[^ \t\n\r\]\(\)]+[^ .\t,\n\r\(\)\"\'\]\?])/', '$1<a href="$2" rel="nofollow">$2</a>', $string);
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
		} elseif (preg_match('/^date:/', $words)) {
			$_REQUEST['date'] = 'true';
			$words=preg_replace('/^date: */', '', $words);
		}
		if ($_REQUEST['tag'] == 'true') {
			$where .= "MATCH (link_tags) AGAINST ('$words' $mode) ";
		} else {
			$where = "MATCH (link_url, link_url_title, link_title, link_content, link_tags) AGAINST ('$words' $mode) ";
		}
		if (!empty($_REQUEST['from'])) {
			$where .=  " AND link_date > from_unixtime(".intval($_REQUEST['from']).") ";
		}
		// To aovid showing news still in "limbo"
		$where .=  " AND link_votes > 0 ";
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

function get_gravatar_url($email, $size, $alt=true) {
		get_avatar_url(1, 1, $email, $size, $alt);
}

function get_avatar_url($user, $avatar, $email, $size, $alt=true) {
	global $globals; 
	if ($avatar > 0 && !empty($globals['avatars_dir'])) {
		$file = $globals['avatars_dir'] . '/'. intval($user/$globals['avatars_files_per_dir']) . '/' . $user . "-$size.jpg";
		$file_path = mnmpath.'/'.$file;
		if (is_readable($file_path)) {
			return $globals['base_url'] . $file;
		} else {
			return $globals['base_url'] . "backend/get_avatar.php?id=$user&amp;size=$size";
		}
	} else return check_gravatar_url($email, $size, $alt);
}

function check_gravatar_url($email, $size, $alt=true) {
	global $globals; 
	if ($globals['do_gravatars']) {
		if ($alt) $default = '&amp;default=http%3A%2F%2F'.get_server_name().$globals['base_url'].'img%2Fcommon%2Fno-gravatar-2-'.$size.'.jpg';
		return 'http://www.gravatar.com/avatar.php?gravatar_id='.md5($email).'&amp;rating=PG&amp;size='.$size.$default;
	} else {
		return $globals['base_url'].'img/common/no-gravatar-2-'.$size.'.jpg';
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
