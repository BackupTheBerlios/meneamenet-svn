<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'link.php');
include(mnminclude.'votes.php');

/*
echo $_SERVER['REQUEST_URI'];
exit;
*/


$link = new Link;
$id=intval($_REQUEST['id']);
$user_id=intval($_REQUEST['user']);



$value = intval($_REQUEST['value']);
if ($value < -10 || $value > -1)
	error(_('Voto incorrecto') . " $value");

$link->id=$id;
$link->read_basic();

if(!$link->is_votable()) {
	error(_('¡tranquilo cowboy!'));
}

if ($current_user->user_id == 0 && ! $anonnymous_vote) {
	error(_('Los votos anónimos están temporalmente deshabilitados'));
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('Usuario incorrecto'). $current_user->user_id . '-'. $_REQUEST['user']);
}

$md5=md5($site_key.$_REQUEST['user'].$link->randkey.$globals['user_ip']);
if($md5 !== $_REQUEST['md5']){
	error(_('Clave de control incorrecta'));
}

if ($globals['interface'] == "monouser" ) {
	if ( $current_user->user_level == 'god' ) {
		$db->query("update links set link_status='discard' where link_id = " . $link->id);
		die;
	}
}

$vote = new Vote;
$vote->link=$link->id;
$vote->type='links';
$vote->user=$user_id;

if($vote->exists()) {
	error(_('Ya ha votado antes'));
}

$votes_freq = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id and vote_date > from_unixtime(unix_timestamp(now())-30) and vote_ip = '".$globals['user_ip']."'"); 
if ( $globals['interface'] == "digg" ) {
	if ($current_user->user_id > 0) $freq = 2;
	else $freq = 2;
} elseif ($globals['interface'] == "monouser" ) {
	$freq = 1000;
}

if ($votes_freq > $freq) {
	error(_('¡tranquilo cowboy!'));
}
	
$vote->value = $value;
if($link->status == 'published' || !$vote->insert()) {
	error(_('Error insertando voto'));
}

echo _('Será tomado en cuenta, gracias');

function error($mess) {
	echo "ERROR:$mess";
	die;
}
?>
