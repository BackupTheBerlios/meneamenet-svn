<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');

$link = new Link;
$id=intval($_REQUEST['id']);
$user_id=intval($_REQUEST['user']);



$value = round($_REQUEST['value']);
if ($value < -10 || $value > -1)
	error(_('Voto incorrecto') . " $value");

$link->id=$id;
$link->read();

if(!$link->is_votable()) {
	error(_('¡tranquilo cowboy!'));
}

if($link->status == 'published') {
	error(_('no se puede votar negativo a las publicadas'));
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

if ($current_user->user_karma < 5) {
	error(_('No tiene el karma suficiente para votar negativo'));
}

$votes_freq = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') and vote_ip = '".$globals['user_ip']."' and vote_value < 0"); 
if ($current_user->user_id > 0) $freq = 2;
else $freq = 2;

if ($votes_freq > $freq && $current_user->user_karma > 4) {
	// Typical "negative votes" attack, decrease karma
	require_once(mnminclude.'user.php');
	$user = new User;
	$user->id = $current_user->user_id;
	$user->read();
	$user->karma = $user->karma - 1.0;
	$user->store();
	error(_('¡tranquilo cowboy!, tu karma ha bajado: ') . $user->karma);
}

if (!$link->insert_vote($current_user->user_id, $value)) {
	error(_('error insertando voto'));
}

echo _('será tomado en cuenta, gracias');

function error($mess) {
	echo "ERROR:$mess";
	die;
}
?>
