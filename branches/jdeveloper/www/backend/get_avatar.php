<?php
include_once('../config.php');
include_once(mnmpath.'/libs/avatars.php');

$id = intval($_GET['id']);
if (! $id > 0) die;
$size = intval($_GET['size']);
if (!$size > 0) $size = 80;

$img=avatar_get_from_file($id, $size);
if (!($img=avatar_get_from_file($id, $size))) {
	$img=avatar_get_from_db($id, $size);
}

if (!$img) {
	require_once(mnmpath.'/libs/user.php');
	$user = new User();
	$user->id = $id;
	if($user->read()) {
		header('Location: ' . check_gravatar_url($user->email, $size) );
		die;
	}
}  

header("Content-type: image/jpg");
//header('Cache-Control: max-age=7200');
echo $img;
?>
