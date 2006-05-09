<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');

do_header(_("registro"), "post");
do_navbar(_("registro"));

echo '<div id="genericform-contents">'."\n";
echo '<div id="genericform">'."\n";

if(isset($_POST["process"])) {
	switch (intval($_POST["process"])) {
		case 1:
			do_register1();
			break;
		case 2:
			do_register2();
			break;
	}
} else {
	do_register0();
}

echo '</div>' . "\n";
echo '</div>' . "\n";
do_footer();
exit;

function do_register0() {
	//echo '<div id="formz">' . "\n";

	echo '<div class="recoverpass" align="center"><h4><a href="login.php?op=recover">'._('¿Has olvidado la contraseña?').'</a></h4></div>';

	echo '<form action="/register.php" method="post" id="thisform">' . "\n";
	echo '<fieldset>' . "\n";
	echo '<legend><span class="sign">' . _("registro") . '</span></legend>' . "\n";
	echo '<p class="l-top"><label for="name">' . _("nombre de usuario") . ':</label><br />' . "\n";

	echo '<input type="text" name="username" id="name" value="" onkeyup="enablebutton(this.form.checkbutton1, this.form.submit, this)" size="25" tabindex="1"/>' . "\n";
	echo '<span id="checkit"><input type="button" name="checkbutton1" id="checkbutton1" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'username\', this.form, this.form.username)"/></span>' . "\n";
	echo '<br/><span id="usernamecheckitvalue"></span></p>' . "\n";

	echo '<p class="l-mid"><label for="email">email:</label><br />' . "\n";
	echo _('es importante que sea correcta, recibirás un correo para validar la cuenta').' <br />';
	echo '<input type="text" id="email" name="email" value=""  onkeyup="enablebutton(this.form.checkbutton2, this.form.submit, this)" size="25" tabindex="2"/>' . "\n";
		echo '<input type="button" name="checkbutton2" id="checkbutton3" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'email\', this.form, this.form.email)"/>' . "\n";
	echo '<br/><span id="emailcheckitvalue"></span></p>' . "\n";

	echo '<p class="l-mid"><label for="password">' . _("clave") . ':</label><br />' . "\n";
	echo _('al menos cinco caracteres').' <br />';
	echo '<input type="password" id="password" name="password" size="25" tabindex="3"/></p>' . "\n";

	echo '<p class="l-mid"><label for="verify">' . _("verificación de clave") . ': </label><br />' . "\n";
	echo '<input type="password" id="verify" name="password2" size="25" tabindex="4"/></p>' . "\n";


	echo '<p class="l-bot"><input type="submit" disabled="disabled" name="submit" value="'._('crear usuario').'" class="log2" tabindex="6" /></p>' . "\n";
	echo '<input type="hidden" name="process" value="1"/>' . "\n";

	echo '</fieldset>' . "\n";
	echo '</form>' . "\n";
	//echo '</div>' . "\n";
}

function do_register1() {
	global $db, $globals;

	$error = false;

	if(!isset($_POST["username"]) || strlen($_POST["username"]) < 3) {
		register_error(_("Nombre de usuario erróneo, debe ser de 3 o más caracteres alfanuméricos"));
		$error=true;
	}
	if(!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $_POST["username"])) {
		register_error(_("Nombre de usuario erróneo, caracteres no admitidos"));
		$error=true;
	}
	if(user_exists(trim($_POST["username"])) ) {
		register_error(_("El usuario ya existe"));
		$error=true;
	}
	if(!check_email(trim($_POST["email"]))) {
		register_error(_("El correo electrónico no es correcto"));
		$error=true;
	}
	if(email_exists(trim($_POST["email"])) ) {
		register_error(_("Ya existe otro usuario con esa dirección de correo"));
		$error=true;
	}
	if(preg_match('/[ \']/', $_POST["password"]) || preg_match('/[ \']/', $_POST["password2"]) ) {
		register_error(_("Caracteres inválidos en la clave"));
		$error=true;
	}
	if(strlen($_POST["password"]) < 5 ) {
		register_error(_("Clave demasiado corta, debe ser de 5 o más caracteres"));
		$error=true;
	}
	if($_POST["password"] !== $_POST["password2"] ) {
		register_error(_("Las claves no coinciden"));
		$error=true;
	}
	$user_ip = $globals['user_ip'];
	$from = time() - 86400*2;
	$last_register = $db->get_var("select count(*) from users where user_date > from_unixtime($from) and user_ip = '$user_ip'");
	if($last_register > 0) {
		register_error(_("Para registrar otro usuario desde la misma dirección debes esperar 48 horas."));
		$error=true;
	}
	if ($error) return;
	echo '<br style="clear:both" />';
// 	echo '<div id="contents-wide">' . "\n";
	//echo '<div id="capform">' . "\n";


	echo '<form action="/register.php" method="post" id="thisform">' . "\n";
	echo '<fieldset><legend><span class="sign">'._(validación).'</span></legend>'."\n";
	ts_print_form();
	/*
	echo _("introduzca el código que ve en la imagen:")."<br/><br/>\n";
	echo '<table><tr><td>';
	$ts_random=rand();
	echo '<input type="hidden" name="ts_random" value="'.$ts_random.'" />';
	echo '<img src="/images/code.php?ts_random='.$ts_random.'" class="ch2" /></td>';
	echo '<tr><td><input type="text" size="20" name="ts_code" /></td></tr></table><br/>'."\n";
	*/
	echo '<input type="submit" name="submit" value="'._('continuar').'" />';
	echo '<input type="hidden" name="process" value="2" />';
	echo '<input type="hidden" name="email" value="'.preg_replace('/ /', '_', $_POST["email"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="username" value="'.preg_replace('/ /', '_', $_POST["username"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="password" value="'.preg_replace('/ /', '_', $_POST["password"]).'" />'; // extra sanity, in fact not needed
	echo '</fieldset></form>'."\n";
	//echo '</div>';
// 	echo '</div>'."\n";
}

function do_register2() {
	global $db, $current_user, $globals;
	if ( !ts_is_human()) {
		register_error(_('El código de seguridad no es correcto.'));
		return;
	}
	$username=preg_replace('/ /', '_', trim($_POST['username'])); // sanity check
	$password=md5(trim($_POST['password']));
	$email=preg_replace('/ /', '_', trim($_POST['email'])); // sanity check
	$user_ip = $globals['user_ip'];
	if (!user_exists($username)) {
		if ($db->query("INSERT INTO users (user_login, user_email, user_pass, user_date, user_ip) VALUES ('$username', '$email', '$password', now(), '$user_ip')")) {
			//register_error(_("Usuario creado").'.<a href="login.php">'._(Login).'</a>');
/***
			if($current_user->Authenticate($username, $password, false) == false) {
				register_error(_("Error insertando usuario en la base de datos"));
			} else {
****/
			echo '<fieldset>'."\n";
			echo '<legend><span class="sign">'._("registro de usuario").'</span></legend>'."\n";
			require_once(mnminclude.'user.php');
			$user=new User();
			$user->username=$username;
			if(!$user->read()) {
				register_error(_('Error insertando usuario en la base de datos'));
			} else {
				require_once(mnminclude.'mail.php');
				$sent = send_recover_mail($user);
			}
			//header('Location: ./user.php?login='.$username);
			echo '</fieldset>'."\n";
		} else {
			register_error(_("Error insertando usuario en la base de datos"));
		}
	} else {
		register_error(_("El usuario ya existe"));
	}
}

function register_error($message) {
	echo '<div class="form-error">';
	//echo '<h3>'._("Noticia").'</h3>';
	echo "<p>$message</p>";
	echo "</div>\n";
}

?>
