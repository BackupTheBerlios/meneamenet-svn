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

$offset=(get_current_page()-1)*$page_size;


$login = $_REQUEST['login'];
if(empty($login)){
	if ($current_user->user_id > 0) {
		$login=$current_user->user_login;
	} else {
		header('Location: ./');
		die;
	}
}
$user=new User();
$user->username = preg_replace('/ /', '_', $login);
if(!$user->read()) {
	echo "error 2";
	die;
}

$view = preg_replace('/ /', '_', $_REQUEST['view']);
if(empty($view)) $view = 'profile';
do_header(_('perfil de usuario'). ': ' . $login);
do_navbar('<a href="/topusers.php">'._('usuarios') . '</a> &#187; ' . $user->username);
echo '<div id="genericform-contents">'."\n";

// Tabbed navigation
if (strlen($user->names) > 0) {
	$display_name = $user->names;
}
else {
	$display_name = $user->username;
}
echo '<h2>'.$display_name.'</h2>'."\n";
echo '<div class="sub-nav">'."\n";
echo '<ul>'."\n";

switch ($view) {

	case 'history':
		echo '<li><a href="user.php?login='.$login.'&amp;view=profile">'._('datos personales'). '</a></li>';
		echo '<li class="active"><span>'._('enviadas').'</span></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=published">'._('publicadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=commented">'._('comentadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=shaken">'._('votadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=preferred">'._('autores preferidos'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=voters">'._('votado por'). '</a></li>';
		echo '</ul><br /></div>';
		do_history();
		break;
	case 'published':
		echo '<li><a href="user.php?login='.$login.'&amp;view=profile">'._('datos personales'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=history">'._('enviadas'). '</a></li>';
		echo '<li class="active"><span>'._('publicadas').'</span></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=commented">'._('comentadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=shaken">'._('votadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=preferred">'._('autores preferidos'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=voters">'._('votado por'). '</a></li>';
		echo '</ul><br /></div>';
		do_published();
		break;
	case 'commented':
		echo '<li><a href="user.php?login='.$login.'&amp;view=profile">'._('datos personales'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=history">'._('enviadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=published">'._('publicadas'). '</a></li>';
		echo '<li class="active"><span>'._('comentadas').'</span></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=shaken">'._('votadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=preferred">'._('autores preferidos'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=voters">'._('votado por'). '</a></li>';
		echo '</ul><br /></div>';
		do_commented();
		break;
	case 'shaken':
		echo '<li><a href="user.php?login='.$login.'&amp;view=profile">'._('datos personales'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=history">'._('enviadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=published">'._('publicadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=commented">'._('comentadas'). '</a></li>';
		echo '<li class="active"><span>'._('votadas').'</span></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=preferred">'._('autores preferidos'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=voters">'._('votado por'). '</a></li>';
		echo '</ul><br /></div>';
		do_shaken();
		break;
	case 'preferred':
		echo '<li><a href="user.php?login='.$login.'&amp;view=profile">'._('datos personales'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=history">'._('enviadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=published">'._('publicadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=commented">'._('comentadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=shaken">'._('votadas'). '</a></li>';
		echo '<li class="active"><span>'._('autores preferidos').'</span></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=voters">'._('votado por'). '</a></li>';
		echo '</ul><br /></div>';
		do_preferred();
		break;
	case 'voters':
		echo '<li><a href="user.php?login='.$login.'&amp;view=profile">'._('datos personales'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=history">'._('enviadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=published">'._('publicadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=commented">'._('comentadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=shaken">'._('votadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=preferred">'._('autores preferidos'). '</a></li>';
		echo '<li class="active"><span>'._('votado por').'</span></li>';
		echo '</ul><br /></div>';
		do_voters();
		break;
	case 'profile':
	default:
		echo '<li class="active"><span>'._('datos personales').'</span></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=history">'._('enviadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=published">'._('publicadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=commented">'._('comentadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=shaken">'._('votadas'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=preferred">'._('autores preferidos'). '</a></li>';
		echo '<li><a href="user.php?login='.$login.'&amp;view=voters">'._('votado por'). '</a></li>';
		echo '</ul><br /></div>';
		do_profile();
		break;
}

do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();

//echo '<div id="contents">';
//echo '</div>';



function do_profile() {
	global $user, $current_user, $login;


// 	echo '<div id="contents-wide">';
	echo '<fieldset><legend>';
	echo _('información personal');
	if($login===$current_user->user_login) {
		echo ' (<a href="profile.php">'._('modificar').'</a>)';
	} elseif ($current_user->user_level == 'god') {
		echo ' (<a href="profile.php?login='.$login.'">'._('modificar').'</a>)';
	}
	echo '</legend>';
	echo '<img class="sub-nav-img" src="'.get_gravatar_url($user->email, 80, false).'" width="80" height="80" alt="'.$user->username.'" title="gravatar.com" />';

	echo '<dl>';	
	if(!empty($user->username)) {
		echo '<dt>'._('usuario').':</dt><dd>'.$user->username;
		if ($login===$current_user->user_login || $current_user->user_level == 'god') 
			echo " (<em>$user->level</em>)";
		echo '</dd>';
	}
	if(!empty($user->names))
		echo '<dt>'._('nombre').':</dt><dd>'.$user->names.'</dd>';
	if(!empty($user->url))
		echo '<dt>'._('sitio web').':</dt><dd><a href="'.$user->url.'">'.$user->url.'</a></dd>';
	echo '<dt>'._('desde').':</dt><dd>'.get_date($user->date).'</dd>';
	if(!empty($user->karma))
		echo '<dt>'._('karma').':</dt><dd>'.$user->karma.'</dd>';
	echo '</dl></fieldset>';

	$user->all_stats();
	echo '<fieldset><legend>'._('estadísticas de meneos').'</legend><dl>';

        echo '<dt>'._('noticias enviadas').':</dt><dd>'.$user->total_links.'</dd>';
        echo '<dt>'._('noticias publicadas').':</dt><dd>'.$user->published_links.'</dd>';
        echo '<dt>'._('comentarios').':</dt><dd>'.$user->total_comments.'</dd>';
        echo '<dt>'._('número de votos').':</dt><dd>'.$user->total_votes.'</dd>';
        echo '<dt>'._('votos de publicadas').':</dt><dd>'.$user->published_votes.'</dd>';

	echo '</dl></fieldset>';
// 	echo '</div>';
}


function do_history () {
	global $db, $rows, $user, $offset, $page_size;

	$link = new Link;
// 	echo '<div id="contents-wide">';
	echo '<h2>'._('noticias enviadas').'</h2>';
	//$rows = $db->get_var("SELECT count(*) FROM links WHERE link_author=$user->id AND link_status!='discard'");
	//$links = $db->get_col("SELECT link_id FROM links WHERE link_author=$user->id AND link_status!='discard' ORDER BY link_date DESC LIMIT $offset,$page_size");
	$rows = $db->get_var("SELECT count(*) FROM links WHERE link_author=$user->id AND link_votes > 0");
	$links = $db->get_col("SELECT link_id FROM links WHERE link_author=$user->id AND link_votes > 0 ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
// 	echo '</div>';
}

function do_published () {
	global $db, $rows, $user, $offset, $page_size;

	$link = new Link;
// 	echo '<div id="contents-wide">';
	echo '<h2>'._('noticias publicadas').'</h2>';
	$rows = $db->get_var("SELECT count(*) FROM links WHERE link_author=$user->id AND link_status='published'");
	$links = $db->get_col("SELECT link_id FROM links WHERE link_author=$user->id AND link_status='published'  ORDER BY link_published_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
// 	echo '</div>';
}

function do_shaken () {
	global $db, $rows, $user, $offset, $page_size;

	$link = new Link;
// 	echo '<div id="contents-wide">';
	echo '<h2>'._('noticias votadas').'</h2>';
	$rows = $db->get_var("SELECT count(*) FROM links, votes WHERE vote_type='links' and vote_user_id=$user->id AND vote_link_id=link_id and vote_value > 0");
	$links = $db->get_col("SELECT link_id FROM links, votes WHERE vote_type='links' and vote_user_id=$user->id AND vote_link_id=link_id  and vote_value > 0 ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
// 	echo '</div>';
}


function do_commented () {
	global $db, $rows, $user, $offset, $page_size;

	$link = new Link;
	echo '<h2>'._('noticias comentadas').'</h2>';
	$rows = $db->get_var("SELECT count(distinct comment_link_id) FROM comments WHERE comment_user_id=$user->id");
	$links = $db->get_col("SELECT DISTINCT link_id FROM links, comments WHERE comment_user_id=$user->id AND comment_link_id=link_id  ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
}

function do_preferred () {
	global $db, $user;

	$friend = new User;
	echo '<h2>'._('autores preferidos').'</h2>';
	echo '<div class="friends-list">';
	echo "<ol>\n";
	$dbusers = $db->get_results("SELECT friend_to, friend_value FROM friends WHERE friend_type='affiliate' AND friend_from=$user->id AND friend_to !=0 ORDER BY friend_value DESC LIMIT 50");
	if ($dbusers) {
		foreach($dbusers as $dbuser) {
			$friend->id=$dbuser->friend_to;
			$value = $dbuser->friend_value * 100;
			$value = sprintf("%6.2f", $value);
			$friend->read();
			echo '<li><a href="user.php?login='.$friend->username.'">'.$friend->username."</a> ($value %)</li>\n";
		}
	}
	echo '</ol>';
	echo "</div>\n";
}


function do_voters () {
	global $db, $user;

	$friend = new User;
	echo '<h2>'._('los que votan').'</h2>';
	echo '<div class="friends-list">';
	echo "<ol>\n";
	$dbusers = $db->get_results("SELECT friend_from, friend_value FROM friends WHERE friend_type='affiliate' AND friend_to=$user->id AND friend_from !=0 ORDER BY friend_value DESC LIMIT 50");
	if ($dbusers) {
		foreach($dbusers as $dbuser) {
			$friend->id=$dbuser->friend_from;
			$value = $dbuser->friend_value * 100;
			$value = sprintf("%6.2f", $value);
			$friend->read();
			echo '<li><a href="user.php?login='.$friend->username.'">'.$friend->username."</a> ($value %)</li>\n";
		}
	}
	echo '</ol>';
	echo "</div>\n";
}

?>
