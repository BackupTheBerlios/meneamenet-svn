<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Javier Carranza <javier at al dot quimia dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
global $current_user, $site_key, $globals;

$login = $_REQUEST['login'];
if(empty($login)){
        if ($current_user->user_id > 0) {
                $login=$current_user->user_login;
        } else {
                header('Location: ./login.php');
                die;
        }
}

header('Content-Type: text/html; charset=utf-8');

$id = (int) trim($_REQUEST['id']);

$result = $db->get_results("SELECT link_id, link_title, link_content, link_url, link_tags, link_randkey,
	category_name, category_id from links LEFT JOIN categories ON link_category = category_id WHERE
	link_id < " . $id . " AND link_status='queued' ORDER BY link_id DESC LIMIT 1");

$randkey = md5($site_key.$current_user->user_id.$result[0]->link_id.$result[0]->link_randkey.$globals['user_ip']);

echo 'link_id=' . $result[0]->link_id . ';link_title=\'' . htmlspecialchars($result[0]->link_title) .
	'\';link_content=\'' . substr(preg_replace('/\n/','', htmlspecialchars($result[0]->link_content)), 0, 254) .
	'\';link_url=\'' . $result[0]->link_url . '\';link_tags=\'' . $result[0]->link_tags . 
	'\';link_category=\'' . $result[0]->category_name . '\';category_id=' . $result[0]->category_id .
	';randkey=\'' . $randkey . '\';userid=' . $current_user->user_id . ';';

?>
