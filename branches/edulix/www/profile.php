<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'user.php');


$user_levels = array ('disabled', 'normal', 'special', 'admin', 'god');

// User recovering her password
if (!empty($_GET['login']) && !empty($_GET['t']) && !empty($_GET['k'])) {
	$time = intval($_GET['t']);
	$key = $_GET['k'];

	$user=new User();
	$user->username=preg_replace('/ /', '_', $_GET['login']);
	if($user->read()) {
		$now = time();
		$key2 = md5($user->id.$user->pass.$time.$site_key.get_server_name());
		//echo "$now, $time; $key == $key2\n";
		if ($time > $now - 7200 && $time < $now && $key == $key2) {
			$db->query("update users set user_validated_date = now() where user_id = $user->id and user_validated_date is null");
			$current_user->Authenticate($user->username, $user->pass);
		}
	}
}
//// End recovery

if ($current_user->user_id > 0 && $current_user->authenticated && empty($_REQUEST['login'])) {
		$login=$current_user->user_login;
} elseif (!empty($_REQUEST['login']) && $current_user->user_level == 'god') {
	$login=$db->escape($_REQUEST['login']);
	$admin_mode = true;
} else {
	header("Location: ./login.php");
	die;
}

$user=new User();
$user->username = $login;
if(!$user->read()) {
	echo $login;
	echo "error 2";
	die;
}

do_header(_('edición del perfil del usuario'). ': ' . $login);
do_navbar('<a href="/topusers.php">'._('usuarios') . '</a> &#187; <a href="/user.php">' . $user->username .'</a> &#187; ' . _('editar'));

show_profile();

do_footer();


function show_profile() {
	global $user, $admin_mode, $user_levels;


	save_profile();
	echo '<div id="genericform-contents"><div id="genericform"><fieldset><legend>';
	if (!$admin_mode)
		echo '<span class="sign">'._('modifica tu perfil')." ($user->username: $user->level)</span></legend>";
	else 
		echo '<span class="sign">'."$user->username: $user->level</span></legend>";

	echo '<form action="profile.php" method="post" id="thisform">';
	echo '<input type="hidden" name="process" value="1">';
	echo '<input type="hidden" name="user_id" value="'.$user->id.'">';
	if ($admin_mode)
		echo '<input type="hidden" name="login" value="'.$user->username.'">';

	echo '<p class="l-top"><label for="name" accesskey="1">'._('nombre real').':</label><br/>';
	echo '<input type="text" name="names" id="names" tabindex="1" value="'.$user->names.'">';
	echo '</p>';

	echo '<p class="l-mid"><label for="name" accesskey="1">'._('correo electrónico').':</label><br/>';
	echo '<input type="text" name="email" id="email" tabindex="2" value="'.$user->email.'">';
	echo '</p>';

	echo '<p class="l-mid"><label for="name" accesskey="1">'._('página web').':</label><br/>';
	echo '<input type="text" name="url" id="url" tabindex="3" value="'.$user->url.'">';
	echo '</p>';

	
	echo '<p>'._('Introduce la nueva clave para cambiarla -no se cambiará si la dejas en blanco-:').'</p>';

	echo '<p class="l-mid"><label for="password">' . _("clave") . ':</label><br />' . "\n";
	echo '<input type="password" id="password" name="password" size="25" tabindex="4"/></p>' . "\n";

	echo '<p class="l-mid"><label for="verify">' . _("repite la clave") . ': </label><br />' . "\n";
	echo '<input type="password" id="verify" name="password2" size="25" tabindex="5"/></p>' . "\n";

	if ($admin_mode) {
		echo '<p class="l-mid"><label for="verify">' . _("estado") . ': </label><br />' . "\n";
		echo '<select name="user_level">';
		foreach ($user_levels as $level) {
			echo '<option value="'.$level.'"';
			if ($user->level == $level) echo ' selected="selected"';
			echo '>'.$level.'</option>';
		}
		echo '</select>';
	}
	
	echo '<p class="l-bottom"><input type="submit" name="save_profile" value="'._('actualizar').'" class="genericsubmit"></p>';
	echo "</form></fieldset></div></div>\n";
	
}


function save_profile() {
	global $db, $user, $current_user, $globals, $admin_mode;
	$errors = 0; // benjami: control added (2005-12-22)
	
	if(!isset($_POST['save_profile']) || !isset($_POST['process']) || 
		($_POST['user_id'] != $current_user->user_id && !$admin_mode) ) return;
	if(!check_email(trim($_POST['email']))) {
		echo '<p class="form-error">'._('El correo electrónico no es correcto').'</p>';
		$errors = 1;
	} else {
		$user->email=trim($_POST['email']);
	}
	$user->url=htmlspecialchars(trim($_POST['url']));
	$user->names=trim($_POST['names']);
	if(!empty($_POST['password']) || !empty($_POST['password2'])) {
		if($_POST['password'] !== $_POST['password2']) {
			echo '<p class="form-error">'._('Las claves no son iguales, no se ha modificado').'</p>';
			$errors = 1;
		} else {
			$user->pass=trim($_POST['password']);
			echo '<p>'._('La clave se ha cambiado').'</p>';
		}
	}
	if ($admin_mode && !empty($_POST['user_level'])) {
		$user->level=$db->escape($_POST['user_level']);
	}
	if (!$errors) { // benjami: "if" added (2005-12-22)
		if (empty($user->ip)) {
			$user->ip=$globals['user_ip'];
		}
		$user->store();
		$user->read();
		if (!$admin_mode)
			$current_user->Authenticate($user->username, $user->pass);
		echo '<p class="form-act">'._('Datos actualizados').'</p>';
	}
}

?>
