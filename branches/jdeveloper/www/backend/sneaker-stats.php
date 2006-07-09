<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function check_stats($string) {
	global $globals;
	if (preg_match('/^!statsu/', $string)) return do_statsu($string);
	if (preg_match('/^!stats2/', $string)) return do_stats2($string);
	if (preg_match('/^!stats1{0,1}/', $string)) return do_stats1($string);
	if (preg_match('/^!help/', $string)) return _('comandos') . ': <a href="http://meneame.wikispaces.com/Comandos">meneame.wikispaces.com/Comandos</a>';
	if (preg_match('/^!cabal/', $string)) return do_cabal($string);
	if (preg_match('/^!wiki/', $string)) return 'wiki: <a href="http://meneame.wikispaces.com/">meneame.wikispaces.com</a>';
	if (preg_match('/^!promote/', $string)) return '<a href="http://' . get_server_name().$globals['base_url']. 'archives/promote.html">promote.html</a>';
	return '';
}

function do_stats1($string) {
	global $db;
	$comment = '<strong>'._('Estadísticas globales'). '</strong>. ';
	$comment .= _('usuarios') . ':&nbsp;' . $db->get_var('select count(*) from users') . ', ';
	$comment .= _('votos') . ':&nbsp;' . $db->get_var('select count(*) from votes where vote_type="links"') . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $db->get_var('select count(*) from links') . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="published"') . ', ';
	$comment .= _('pendientes') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="queued"') . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard"') . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $db->get_var('select count(*) from comments');
	return $comment;
}

function do_stats2($string) {
	global $db;
	$comment = '<strong>'._('Estadísticas 24 horas'). '</strong>. ';
	$comment .= _('votos') . ':&nbsp;' . $db->get_var('select count(*) from votes where vote_type="links" and vote_date > date_sub(now(), interval 24 hour)') . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $db->get_var('select count(*) from links where link_date > date_sub(now(), interval 24 hour)') . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="published" and link_published_date > date_sub(now(), interval 24 hour)') . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard" and link_date > date_sub(now(), interval 24 hour)') . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $db->get_var('select count(*) from comments where  comment_date > date_sub(now(), interval 24 hour)');
	return $comment;
}

function do_statsu($string) {
	global $db, $current_user;
	require_once(mnminclude.'user.php');
	$array = preg_split('/\s+/', $string);
	if (count($array) >= 2) {
		$user_login = $db->escape($array[1]);
		$user_id = $db->get_var("select user_id from users where user_login='$user_login'");
	}
	if (!$user_id > 0) { 
		$user_id = $current_user->user_id;
		$user_login = $current_user->user_login;
	}
	$user = new User();
	$user->id = $user_id;
	$user->read();
	$user->all_stats();
	
	$comment = '<strong>'._('Estadísticas de'). ' ' . $user_login. '</strong>. ';
	$comment .= _('karma') . ':&nbsp;' . $user->karma . ', ';
	if ($user->total_links > 1) {
		$comment .= _('entropía') . ':&nbsp;' . intval(($user->blogs() - 1) / ($user->total_links - 1) * 100) . '%, ';
	}
	$comment .= _('votos') . ':&nbsp;' . $user->total_votes . ', ';
	$comment .= _('artículos') . ':&nbsp;' . $user->total_links . ', ';
	$comment .= _('publicados') . ':&nbsp;' . $user->published_links . ', ';
	$comment .= _('pendientes') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="queued" and link_author='.$user_id) . ', ';
	$comment .= _('descartados') . ':&nbsp;' . $db->get_var('select count(*) from links where link_status="discard" and link_author='.$user_id) . ', ';
	$comment .= _('comentarios') . ':&nbsp;' . $user->total_comments;
	return $comment;
}

function do_cabal($string) {
	require_once('../libs/cabal.php');
	$i = rand(0, count($cabal_messages) -1);
	$comment = '<b>'. _('el cabal dice'). '</b>: <i>' . text_to_html($cabal_messages[$i]) . '</i>';
	return $comment;
}

?>
