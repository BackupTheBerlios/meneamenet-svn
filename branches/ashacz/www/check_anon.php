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

$link = new Link;
$link->id=$id;
if(!$link->read_basic()) {
	error(_('Artículo inexistente'). $current_user->user_id . '-'. $_REQUEST['user']);
}

$from = time() - 3600;
$anon_votes = $db->get_var("select count(*) from votes where vote_type = 'links' and vote_link_id = $id and vote_user_id = 0 and vote_date > from_unixtime($from)");
echo "Anonnymous: $anon_votes \n";
$user_votes =  $db->get_var("select count(*) from votes where vote_type = 'links' and vote_link_id = $id and vote_user_id > 0 and vote_date > from_unixtime($from)");
echo "User: $user_votes \n";
echo "anon_to_user_votes: $anon_to_user_votes \n";


function error($mess) {
	echo "ERROR: $mess";
	die;
}

function warn($mess) {
	echo "WARN: $mess";
	die;
}
?>
