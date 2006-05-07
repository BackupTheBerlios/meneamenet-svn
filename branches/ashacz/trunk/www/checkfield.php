<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

	include('config.php');
	header('Content-Type: text/plain; charset=UTF-8');
	$type=$_REQUEST['type'];
	$name=$_GET["name"];
	#echo "$type, $name...";
	switch ($type) {
		case 'username':
			if (strlen($name)<3) {
				echo _('nombre demasiado corto');
				return;
			}
			if (!preg_match('/^[a-z0-9_\-\.]+$/i', $name)) {
				echo _('caracteres inválidos');
				return;
			}
			if(user_exists($name)) {
				echo _('el usuario ya existe');
				return;
			}
			echo "OK";
			break;
		case 'email':
			if (!check_email($name)) {
				echo _('dirección de correo no válida');
				return;
			}
			if(email_exists($name)) {
				echo _('ya existe otro usuario con esa dirección de correo');
				return;
			}
			echo "OK";
			break;
			default:
				echo "KO $type";
	}
?>
