<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');


$globals['ads'] = true;

if(is_numeric($_REQUEST['id'])) {
	$link = new Link;
	$link->id=intval($_REQUEST['id']);
	if (! $link->read() ) {
		header('Location: http://'.get_server_name());
		die;
	}
	if ($_POST['process']=='newcomment') {
		insert_comment();
	}
	// Set globals
	$globals['link_id']=$link->id;
	$globals['category_id']=$link->category;
	$globals['category_name']=$link->category_name();
	if ($link->status != 'published') 
		$globals['do_vote_queue']=true;
	if (!empty($link->tags))
		$globals['tags']=$link->tags;

	do_header($link->title, 'post');
	do_navbar('<a href="./index.php?category='.$link->category.'">'. $globals['category_name'] . '</a> &#187; '. $link->title);
	echo '<div id="contents">';
	$link->print_summary();
	// AdSense
	do_banner_story();

	echo '<div id="comments">';
   	echo '<h2>'._('comentarios').'</h2>';

	$comments = $db->get_col("SELECT comment_id FROM comments WHERE comment_link_id=$link->id ORDER BY comment_date");
	if ($comments) {
		echo '<ol id="comments-list">';
		require_once(mnminclude.'comment.php');
		$comment = new Comment;
		foreach($comments as $comment_id) {
			$comment->id=$comment_id;
			$comment->read();
			$comment->print_summary($link);
		}
		echo "</ol>\n";
	}


	
	if($link->date < time()-604800) { // older than 7 days
		//echo '<br />'."\n";
		echo '<div class="commentform" align="center" >'."\n";
		echo _('comentarios cerrados')."\n";
		echo '</div>'."\n";
	} elseif ($current_user->authenticated && ($current_user->user_karma > $globals['min_karma_for_comments'] || $current_user->user_id == $link->author)) {
		print_comment_form();
	} else {
		echo '<br/>'."\n";
		echo '<div class="commentform" align="center" >'."\n";
		if ($current_user->authenticated && $current_user->user_karma <= $globals['min_karma_for_comments']) 
			echo _('No tienes el mínimo karma requerido')." (" . $globals['min_karma_for_comments'] . ") ". _('para comentar'). ": ".$current_user->user_karma ."\n";

		else
			echo '<a href="/login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Autentifícate si deseas escribir').'</a> '._('comentarios').'. '._('O regístrate'). ' <a href="/register.php">aquí</a>.'."\n";
		echo '</div>'."\n";
	}

	echo '</div>' . "\n";
	
	echo '<div class="air-with-footer">'."\n";

	// Show voters
	echo '<div id="voters">';
	echo '<h2>'._('¿Quién ha meneado esto?').'</h2>';
	echo '<div id="voters-container">';
	include('libs/meneos.php');
	echo '</div><br />';
	echo '</div>';
	echo '<script type="text/javascript">var update_voters=true;</script>';

	echo '</div>'."\n"; // <div class="air-with-footer">
		
	echo '</div>';
	do_sidebar();
	do_footer();
}

function print_comment_form() {
	global $link, $current_user;

	echo '<div id="commentform" align="left">'."\n";
	echo '<form action="" method="post" id="thisform" style="display:inline;">'."\n";
	echo '<fieldset><legend><span class="sign">'._('envía un comentario').'</span></legend>'."\n";
	//echo '<p>'."\n";
	echo _('Sé respetuoso, no insultes, exprésate con el voto. Si la noticia o un comentario te parece fuera de lugar, explícalo de forma breve y objetiva. Se agradece el buen humor.');
	echo '<label for="comment" accesskey="2" style="float:left">'._('texto del comentario / no se admiten etiquetas HTML').'</label>'."\n";
	//echo '</p>';
	echo '<p class="l-top-s"><br/>'."\n";
	echo $foo;
	echo '<textarea name="comment_content" id="comment" rows="3" cols="76"></textarea><br/>'."\n";
	echo '<input class="submitcomment" type="submit" name="submit" value="'._('enviar el comentario').'" />'."\n";
	echo '<input type="hidden" name="process" value="newcomment" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.rand(1000000,100000000).'" />'."\n";
	echo '<input type="hidden" name="link_id" value="'.$link->id.'" />'."\n";
	echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />'."\n";
	echo '</p>'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo "</div>\n";
}

function insert_comment () {
	global $link, $db, $current_user;
	// Check if is a POST of a comment
	if(intval($_POST['link_id']) == $link->id && $current_user->authenticated && intval($_POST['user_id']) == $current_user->user_id &&
		intval($_POST['randkey']) > 0 && strlen(trim($_POST['comment_content'])) > 2 ) {
		require_once(mnminclude.'comment.php');
		$comment = new Comment;
		$comment->link=$link->id;
		$comment->randkey=intval($_POST['randkey']);
		$comment->author=intval($_POST['user_id']);
		$comment->content=trim(htmlspecialchars(strip_tags(trim($_POST['comment_content']))));
		if (strlen($comment->content) > 0 )
			$comment->store();
		header('Location: '.$_SERVER['REQUEST_URI']);
		die;
	}
}

?>
