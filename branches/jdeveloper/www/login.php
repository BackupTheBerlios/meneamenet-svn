<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

if($_GET["op"] === 'logout') {
	$current_user->Logout(preg_replace('/ /', '', $_REQUEST['return']));
}

// We need it because we modify headers
ob_start();

do_header("login");
do_navbar("login");

echo '<div id="genericform-contents">'."\n";
echo '<div id="genericform">'."\n";

if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
	do_recover();
} else {
	do_login();
}

echo '</div>'."\n";
echo '</div>'."\n";

do_footer();


function do_login() {
	global $current_user;

	echo '<div class="recoverpass" align="center"><h4><a href="login.php?op=recover">'._('¿Has olvidado la contraseña?').'</a></h4></div>';
	
	echo '<form action="login.php" id="thisform" method="post">'."\n";

	echo '<fieldset>'."\n";
	echo '<legend><span class="sign">'._("login").'</span></legend>'."\n";

	if($_POST["processlogin"] == 1) {
		$username = trim($_POST['username']);
		$password = trim($_POST['password']);
		$persistent = $_POST['persistent'];
		if($current_user->Authenticate($username, $password, $persistent) == false) {
			recover_error(_('usuario inexistente, sin validar, o clave incorrecta'));
			//echo '<p><span class="error">'._('ERROR - Usuario o clave incorrecta').'</span></p>';
		} else {
			if(strlen(preg_replace('/ /', '', $_REQUEST['return'])) > 1) {
				header('Location: '.$_REQUEST['return']);
			} else {
				header('Location: ./');
			}
			die;
		}
	}

	echo '<p class="l-top"><label for="name">'._('usuario').':</label><br />'."\n";
	echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.$username.'" /></p>'."\n";
	echo '<p class="l-mid"><label for="password">'._('clave').':</label><br />'."\n";
	echo '<input type="password" name="password" id="password" size="25" tabindex="2"/></p>'."\n";
	echo '<p class="l-mid"><label for="remember">'._('recuérdame').': </label><input type="checkbox" name="persistent" id="remember" tabindex="3"/></p>'."\n";
	echo '<p class="l-bot"><input type="submit" value="login" class="genericsubmit" tabindex="4" />'."\n";
	echo '<input type="hidden" name="processlogin" value="1"/></p>'."\n";
	echo '<input type="hidden" name="return" value="'.htmlspecialchars(preg_replace('/ /', '', $_REQUEST['return'])).'"/>'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
}

function do_recover() {
	global $site_key, $globals;
	require_once(mnminclude.'ts.php');

	echo '<fieldset>'."\n";
	echo '<legend><span class="sign">'._("recuperación de contraseñas").'</span></legend>'."\n";

	if(!empty($_POST['recover'])) {
		if (!ts_is_human()) {
			recover_error(_('El código de seguridad no es correcto!'));
		} else {
			require_once(mnminclude.'user.php');
			$user=new User();
			$user->username=$_POST['username'];
			if(!$user->read()) {
				recover_error(_('el usuario no existe'));
				return false;
			}
			if($user->level == 'disabled') {
				recover_error(_('cuenta deshabilitada'));
				return false;
			}
			require_once(mnminclude.'mail.php');
			$sent = send_recover_mail($user);
		}
	}
	if (!$sent) {
		echo '<form action="login.php" id="thisform-recover" method="post">'."\n";
		echo '<label for="name">'._('usuario').':</label><br />'."\n";
		echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.$username.'" />'."\n";
		echo '<p class="nobold">'._('(recibirás un e-mail para cambiar la contraseña)').'</p>';
		echo '<input type="hidden" name="recover" value="1"/>'."\n";
		echo '<input type="hidden" name="return" value="'.htmlspecialchars(preg_replace('/ /', '', $_REQUEST['return'])).'"/>'."\n";
		ts_print_form();
		echo '<br /><input type="submit" value="'._('recibir e-mail').'" class="genericsubmit" />'."\n";
		echo '</form>'."\n";
	}
	echo '</fieldset>'."\n";
}

function recover_error($message) {
	echo '<div class="form-error">';
	echo "<p>$message</p>";
	echo "</div>\n";
}

?>
