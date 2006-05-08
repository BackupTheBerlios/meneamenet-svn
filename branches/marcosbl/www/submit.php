<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');
include(mnminclude.'link.php');
include(mnminclude.'tags.php');

force_authentication();

do_header(_("enviar noticia"), "post");

if(isset($_POST["phase"])) {
	switch ($_POST["phase"]) {
		case 1:
			do_submit1();
			break;
		case 2:
			do_submit2();
			break;
		case 3:
			do_submit3();
			break;
	}
} else {
	do_submit0();
//	do_sidebar();
}
do_footer();
exit;

function print_empty_submit_form() {
	if (!empty($_GET['url'])) {
		$url = trim(preg_replace('/ /', '+', $_GET['url']));
	} else {
		$url = 'http://';
	}
	echo '<div id="genericform">';
	echo '<fieldset><legend><span class="sign">'._('dirección de la noticia').'</span></legend>';
	echo '<form action="/submit.php" method="post" id="thisform">';
	echo '<p class="l-top"><label for="url">'._('url').':</label><br />';
	echo '<input type="text" name="url" id="url" value="'.$url.'" class="form-full" /></p>';
	echo '<input type="hidden" name="phase" value=1 />';
	echo '<input type="hidden" name="randkey" value="'.rand(10000,10000000).'" />';
	echo '<input type="hidden" name="id" value="c_1" />';
	echo '<p class="l-bottom"><input class="genericsubmit" type="submit" value="'._('continuar &#187;').'" /></p>';
	echo '</form>';
	echo '</fieldset>';
	echo '</div>';
}

function do_submit0() {
	do_navbar(_('enviar noticia') . ' &#187; '. _('paso 1: la dirección'));
	echo '<div id="genericform-contents">'."\n";
	echo '<h2>'._('envío de una nueva noticia: paso 1 de 3').'</h2>';
	echo '<div class="instruction">';
	echo '<h3>'._('por favor, respeta estas las instrucciones para mejorar la calidad:').'</h3>';
	echo '<ul class="instruction-list">';
	echo '<li><strong>'._('Contenido interesante').':</strong> '._('¿Está relacionado con la tecnología, Internet, o la cultura digital?').'</li>';
	echo '<li><strong>'._('Enlaza la fuente original').':</strong> '._('No hagas perder tiempo a los lectores.').'</li>';
	echo '<li><strong>'._('Busca antes').':</strong> '._('Evita duplicar noticias.').'</li>';
	echo '<li><strong>'._('Sé descriptivo').':</strong> '._('Explica la noticia lo mejor que puedas y porqué es interesante').'.</li>';
	echo '<li><strong>'._('Repetimos, por las dudas... ¡enlaza la fuente original!').':</strong> '._('No hagas perder tiempo a los lectores, no enlaces a un blog que no ofrece información ni comentarios adicionales.').'</li>';
	echo '</ul></div>'."\n";
	print_empty_submit_form();
	echo '</div>';
}

function do_submit1() {
	global $db, $dblang, $current_user;

	do_navbar(_('enviar noticia ') . '&#187;'. _(' paso 2: detalles'));
	echo '<div id="genericform-contents">'."\n";

	// avoid spams, an extra security check
	$from = time() - 3600;
	$same_user = $db->get_var("select count(*) from links where link_date > from_unixtime($from) and link_author=$current_user->user_id");
	if ($same_user > 4) {
		echo '<p class="error"><strong>'._('debes esperar').  '</strong></p>';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}
	
	$url = trim(preg_replace('/ /', '+', $_POST['url']));
	$linkres=new Link;

	$edit = false;
	$linkres->get($url);
	$trackback=$linkres->trackback;
	$linkres->randkey = intval($_POST['randkey']);
	if(!$linkres->valid) {
		echo '<p class="error"><strong>'._('url no válido').':</strong> ('.$url.')</p>';
		echo '<p>'._('prueba con otra dirección').'</p>';
		print_empty_submit_form();
		return;
	}

	// avoid auto-promotion (autobombo)
	$hours = 4;
	$from = time() - 3600*$hours;
	$same_blog = $db->get_var("select count(*) from links where link_date > from_unixtime($from) and link_author=$current_user->user_id and link_blog=$linkres->blog and link_votes > 0");
	if ($same_blog > 0 && $current_user->user_karma < 12) {
		echo '<p class="error"><strong>'._('ya has enviado un enlace al mismo sitio hace poco tiempo').'</strong></p> ';
		echo '<p class="error-text">'._('debes esperar'). " $hours " . _(' horas entre cada envío al mismo sitio. Es para evitar "spams" y "autobombo"') . ', ';
		echo '<a href="faq-'.$dblang.'.php">'._('lee el FAQ').'</a></p>';
		echo '<br style="clear: both;" />' . "\n";
		echo '</div>'. "\n";
		return;
	}
	
	// check that the user also votes, not only sends links
	if ($current_user->user_karma < 10) {
		$from = time() - 3600*24;
		$user_votes = $db->get_var("select count(*) from votes where vote_type='links' and vote_date > from_unixtime($from) and vote_user_id=$current_user->user_id");
		$user_links = 1 + $db->get_var("select count(*) from links where link_author=$current_user->user_id and link_date > from_unixtime($from) and link_status != 'discard'");
		$total_links = $db->get_var("select count(*) from links where  link_date > from_unixtime($from) and link_status = 'queued'");
		$min_votes = intval($total_links/20) * $user_links;
		if ($user_votes < $min_votes) {
			$needed = $min_votes - $user_votes;
			echo '<p class="error"><strong>'._('no tienes el mínimo de votos necesarios para enviar una nueva historia').'</strong></p> ';
			echo '<p class="error-text">'._('necesitas votar como mínimo a'). " $needed " . _('noticias') . ', ';
			echo '<a href="shakeit.php" target="_blank">'._('haz clic aquí para ir a votar').'</a></p>';
			echo '<br style="clear: both;" />' . "\n";
			echo '</div>'. "\n";
			return;
		}
	}
	
	if($linkres->duplicates($url) > 0) {
		echo '<p class="error"><strong>'._('noticia repetida!').'</strong></p> ';
		echo '<p class="error-text">'._('lo sentimos').'</p>';
		echo '<p class="error-text"><a href="index.php?search='.htmlentities($url).'">'._('haz clic aquí para votar o comentar la noticia que enviaron antes').'</a>';
		echo '<br style="clear: both;" /><br style="clear: both;" />' . "\n";
		echo '<form id="genericform">';
		echo '<input class="genericsubmit" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'" />';
		echo '</form>'. "\n";
		echo '</div>'. "\n";
		return;
	}
	$linkres->status='discard';
	$linkres->author=$current_user->user_id;

	/***** Currently commented out until we find if it makes sense here
	// First delete last drafts, just in case to avoid triggering the anti spam measure
	$from = time() - 1800;
	$db->query("delete from links where link_date > from_unixtime($from) and link_author=$current_user->user_id and link_status='discard' and link_votes = 0");
	*****/

	// Now stores new draft
	$linkres->store();
	
	echo '<h2>'._('envío de una nueva noticia: paso 2 de 3').'</h2>'."\n";


	echo '<div id="genericform">'."\n";
	echo '<form action="/submit.php" method="post" id="thisform">'."\n";

	echo '<input type="hidden" name="url" id="url" value="'.$url.'" />'."\n";
	echo '<input type="hidden" name="phase" value="2" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";

	echo '<fieldset><legend><span class="sign">'._('info de la noticia').'</span></legend>'."\n";
	echo '<p class="genericformtxt"><label for="url_title" accesskey="1">'._('título de la página').': </label> '."\n";
	echo $linkres->url_title;
	if($linkres->type() === 'blog') {
		echo '<br /> ('._('parece ser un blog').')</p>'."\n";
	} else {
		echo "</p>\n";
	}
	echo '</fieldset>'."\n";

	echo '<fieldset><legend><span class="sign">'._('detalles de la noticia').'</span></legend>'."\n";

	echo '<label for="title" accesskey="2">'._('título de la noticia').':</label>'."\n";
	echo '<p><span class="genericformnote">'._('título de la noticia. máximo: 120 caracteres').'</span>'."\n";

	echo '<br/><input type="text" id="title" name="title" value="'.$link_title.'" size="60" maxlength="120" /></p>'."\n";

	echo '<label for="tags" accesskey="4">'._('etiquetas').':</label>'."\n";
	echo '<p><span class="genericformnote"><strong>'._('pocas palabras, genéricas, cortas y separadas por "," (coma)').'</strong> Ejemplo: <em>web, programación, software libre</em></span>'."\n";
	echo '<br/><input type="text" id="tags" name="tags" value="'.$link_tags.'" size="40" maxlength="40" /></p>'."\n";

	echo '<p><label for="bodytext" accesskey="3">'._('descripción de la noticia').':</label>'."\n";
	echo '<br /><span class="genericformnote">'._('describe la noticia con tus palabras. entre dos y cinco frases es suficiente. sé cuidadoso.').'</span>'."\n";
	echo '<br/><textarea name="bodytext"  rows="10" cols="60" id="bodytext" >'.$link_content.'</textarea></p>'."\n";
	echo '<p><label accesskey="5">'._('categoría').':</label><br />'."\n";
	echo '<span class="genericformnote">'._('selecciona la categoría más apropiada').'</span></p>'."\n";
	echo '<div class="column-list">'."\n";
	echo '<div class="categorylist">'."\n";
	echo '<ul>'."\n";
	$categories = $db->get_results("SELECT category_id, category_name FROM categories WHERE category_lang='$dblang' ORDER BY category_name ASC");
	foreach ($categories as $category) {
	 	echo '<li><input name="category" type="radio" value="'.$category->category_id.'"/>'._($category->category_name).'</li>'."\n";
	}
	// TODO: no standard
	echo '<br style="clear: both;" />' . "\n";
	echo '</ul></div></div>'."\n";
	echo '<p><label for="trackback">'._('trackback').':</label><br />'."\n";
	echo '<span class="genericformnote">'._('puedes agregar o cambiar el trackback si ha sido detectado automáticamente').'</span>'."\n";
	echo '<input type="text" name="trackback" id="trackback" value="'.$trackback.'" class="form-full" /></p>'."\n";
	echo '<input class="genericsubmit" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'">&nbsp;&nbsp;'."\n";
	echo '<input class="genericsubmit" type="submit" value="'._('continuar &#187;').'" />'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo '</div>'."\n";
	echo '</div>'."\n";
}


function do_submit2() {
	global $db, $dblang;

	$linkres=new Link;
	$linkres->id=$link_id = intval($_POST['id']);
	$linkres->read();
	$linkres->category=$_POST['category'];
	$linkres->title = clean_text($_POST['title']);
	$linkres->tags = tags_normalize_string(clean_text($_POST['tags']));
	$linkres->content = clean_text($_POST['bodytext']);
	if (link_errors($linkres)) {
		echo '<form id="genericform">'."\n";
		echo '<p><input class="genericsubmit" type=button onclick="window.history.go(-1)" value="'._('&#171; retroceder').'"></p>'."\n";
		echo '</form>'."\n";
		echo '</div>'."\n"; // opened in print_form_submit_error
		return;
	}

	$linkres->store();
	tags_insert_string($linkres->id, $dblang, $linkres->tags);
	$linkres->read();
	$edit = true;
	$link_title = $linkres->title;
	$link_content = $linkres->content;
	do_navbar(_('enviar noticia ') . '&#187;'. _(' paso 3: control final'));
	echo '<div id="genericform-contents">'."\n";
	
	echo '<h2>'._('envío de una nueva noticia: paso 3 de 3').'</h2>'."\n";

	echo '<form action="./submit.php" method="post" id="genericform">'."\n";
	echo '<fieldset><legend><span class="sign">'._('detalles de la noticia').'</span></legend>'."\n";

	echo '<div class="genericformtxt"><label>'._('ATENCIÓN: esto es sólo es una muestra!').'</label>&nbsp;&nbsp;<br/>'._('Ahora puedes 1) ').'<label>'._('retroceder').'</label>'._(' o 2)  ').'<label>'._('enviar a la cola y finalizar').'</label>'._('. Cualquier otro clic convertirá tu noticia en comida para <del>gatos</del> elefantes (o no).').'</div>';	

	echo '<div class="formnotice">'."\n";
	$linkres->print_summary('preview');
	echo '</div>'."\n";

	echo '<input type="hidden" name="phase" value="3" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.intval($_POST['randkey']).'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$linkres->id.'" />'."\n";
	echo '<input type="hidden" name="trackback" value="'.htmlspecialchars(trim($_POST['trackback'])).'" />'."\n";

	echo '<br style="clear: both;" /><br style="clear: both;" />'."\n";
	echo '<input class="genericsubmit" type="button" onclick="window.history.go(-1)" value="'._('&#171; retroceder').'">&nbsp;&nbsp;'."\n";
	echo '<input class="genericsubmit" type="submit" value="'._('enviar a la cola y finalizar &#187;').'" />'."\n";
	echo '</form>'."\n";
	echo '</fieldset>'."\n";
	echo '</div>'."\n";
}

function do_submit3() {
	global $db, $current_user;

	$linkres=new Link;

	$linkres->id=$link_id = intval($_POST['id']);
	$linkres->read();
	// Check it is not in the queue already
	if($linkres->votes == 0 && $linkres->status != 'queued') {
		$linkres->status='queued';
		$linkres->date=time();
		$linkres->store_basic();
		$linkres->insert_vote($current_user->user_id);
		$db->query("delete from links where link_author = $linkres->author and link_status='discard' and link_votes=0");
		if(!empty($_POST['trackback'])) {
			require_once(mnminclude.'trackback.php');
			$trackres = new Trackback;
			$trackres->url=preg_replace('/ /', '+', trim($_POST['trackback']));
			$trackres->link=$linkres->id;
			$trackres->title=$linkres->title;
			$trackres->author=$linkres->author;
			$trackres->content=$linkres->content;
			$res = $trackres->send();
		}
	}

	header("Location: ./shakeit.php");
	die;
	
}

function link_errors($linkres)
{
	$error = false;
	// Errors
	if(intval($_POST['randkey']) != $linkres->randkey) {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("Clave incorrecta"));
		$error = true;
	}
	if($linkres->status != 'discard') {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("La historia ya está en cola").": $linkres->status");
		$error = true;
	}
	if(strlen($linkres->title) < 10  || strlen($linkres->content) < 30 ) {
		print_form_submit_error(_("Título o texto incompletos"));
		$error = true;
	}
	if(strlen($linkres->title) > 120  || strlen($linkres->content) > 550 ) {
		print_form_submit_error(_("Título o texto demasiado largos"));
		$error = true;
	}
	if(strlen($linkres->tags) < 3 ) {
		print_form_submit_error(_("No has puesto etiquetas"));
		$error = true;
	}

	if(preg_match('/.*http:\//', $linkres->title)) {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("Por favor, no pongas URLs en el título, no ofrece información"));
		$error = true;
	}
	if(!$linkres->category > 0) {
		//echo '<br style="clear: both;" />';
		print_form_submit_error(_("Categoría no seleccionada"));
		$error = true;
	}
	return $error;
}

function print_form_submit_error($mess) {
	static $previous_error=false;
	
	if (!$previous_error) {
		do_navbar(_('enviar noticia') . ' &#187; '. _('ooops!'));
		echo '<div id="genericform-contents">'."\n"; // this div MUST be closed after function call!
		echo '<h2>'._('ooops!').'</h2>'."\n";
		$previous_error = true;
	}
	echo '<div class="form-error-submit">&nbsp;&nbsp;'._($mess).'</div>'."\n";
}
	

?>
