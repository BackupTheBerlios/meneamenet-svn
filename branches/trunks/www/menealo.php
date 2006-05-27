<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'link.php');

header('Content-Type: text/plain; charset=UTF-8');

if(!($id=check_integer('id'))) {
	error(_('Falta el ID del artículo'));
}

if(empty($_REQUEST['user']) && $_REQUEST['user'] !== '0' ) {
	error(_('Falta el código de usuario'));
}

if (empty($_REQUEST['md5'])) {
	error(_('Falta la clave de control'));
}

$link = new Link;
$link->id=$id;
if(!$link->read_basic()) {
	error(_('Artículo inexistente'). $current_user->user_id . '-'. $_REQUEST['user']);
}

if(!$link->is_votable()) {
	error(_('¡tranquilo cowboy!'));
}

if ($current_user->user_id == 0) {
	if (! $anonnymous_vote) {
		error(_('Los votos anónimos están temporalmente deshabilitados'));
	} else {
		// Check that there are not too much annonymous votes in 1 hour
		$from = time() - 3600;
		$anon_votes = $db->get_var("select count(*) from votes where vote_type = 'links' and vote_link_id = $id and vote_user_id = 0 and vote_date > from_unixtime($from)");
		if ($anon_votes > $anon_to_user_votes) {
			$user_votes = $anon_to_user_votes * $db->get_var("select count(*) from votes where vote_type = 'links' and vote_link_id = $id and vote_user_id > 0 and vote_date > from_unixtime($from)");
			if ($anon_votes > $user_votes) {
				// start anti spam measure: assing 1 to previous anonnymous votes
				$db->query("update votes set vote_value = 1 where vote_type = 'links' and vote_link_id = $id and vote_user_id = 0 and vote_date > from_unixtime($from)");
				error(_('Demasiados votos anónimos para esta noticia, inténtelo más tarde'));
			}
		}
	}
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('Usuario incorrecto'). $current_user->user_id . '-'. htmlspecialchars($_REQUEST['user']));
}

$md5=md5($site_key.$_REQUEST['user'].$id.$link->randkey.$globals['user_ip']);
if($md5 !== $_REQUEST['md5']){
	error(_('clave de control incorrecta'));
}

$votes_freq = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id and vote_date > from_unixtime(unix_timestamp(now())-30) and vote_ip = '".$globals['user_ip']."'");

if ($current_user->user_id > 0) $freq = 3;
else $freq = 2;

if ($votes_freq > $freq) {
	warn(_('¡tranquilo cowboy!'));
}

if (!$link->insert_vote($current_user->user_id)) {
	error(_('ya ha votado antes'));
}

// TODO
if ($link->status == 'discard') {
	$sum = $db->get_var("select sum(vote_value) from votes where vote_type='links' and vote_link_id = $link->id and vote_user_id > 0");
	if ($sum > 0 ) {
		$link->read();
		$link->status = 'queued';
		$link->store();
	}
}
	
$count=$link->votes;
echo "$count meneos~--~".$_REQUEST['id'];

function error($mess) {
	echo "ERROR: $mess";
	die;
}

function warn($mess) {
	echo "WARN: $mess";
	die;
}
?>
