<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


@include('ads-credits-functions.php');

function do_navbar($where) {
	if ($where != '') $where = '&#187; '.$where; // benjami: change &#187 order
	echo '<div id="nav-string"><div>&#187; <a href="./"><strong>'.$_SERVER['SERVER_NAME'].'</strong></a>' . $where . '</div></div>' . "\n";
	do_banner_top();
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals;

	header("Content-type: text/html; charset=utf-8");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
	echo '<head>' . "\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo "<title>"._($title)." // " . $globals['site'] . "</title>\n";
	echo '<meta name="generator" content="'. $globals['site'] .'" />' . "\n";
	echo '<meta name="keywords" content="'.$globals['tags'].'" />' . "\n";
	echo '<style type="text/css" media="screen">@import "/css/es/mnm10.css";</style>' . "\n";
	//echo '<style type="text/css" media="all">@import "./css/es/meneame.css";</style>' . "\n";
	//echo '<style type="text/css" media="all">@import "./css/es/toolbar.css";</style>' . "\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('publicadas').'" href="http://'.get_server_name().'/rss2.php" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('pendientes').'" href="http://'.get_server_name().'/rss2.php?status=queued" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('todas').'" href="http://'.get_server_name().'/rss2.php?status=all" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('comentarios').'" href="http://'.get_server_name().'/comments_rss2.php" />'."\n";

	echo '<link rel="icon" href="/favicon.ico" type="image/x-icon" />' . "\n";
	echo '<script src="./js/xmlhttp03.js" type="text/javascript"></script>' . "\n";
	if ( $title == _('administración de categorías') )
	  echo '<script src="./js/categories.js" type="text/javascript"></script>' . "\n";
	echo '</head>' . "\n";
	echo "<body id=\"$id\" ". $globals['body-args']. ">\n";
	echo '<div id="container">' . "\n";
	echo '<div id="logo">'  . "\n";
	// sneake logo
	// if (substr($title, 0, 3) == "fis")
	//	echo '<a href="./"><img src="img/es/logo01-sneak.png" alt="logo" /></a>';
	// normal logo
	// else
		echo '<a href="./"><img src="img/es/logo01.png" alt="meneame, noticias colaborativas" /></a>';
	echo '</div>'  . "\n";

	echo '<div id="header">' . "\n";
	// benjami: later - echo '<h1><a href="/">men&eacute;ame</a></h1>' . "\n";
	echo '<ul>' . "\n";
	echo '<li><a href="telnet.php">'. _('¡el jefe!') . '</a></li>' . "\n";
// 	echo '<li><a href="./archives/meneame-src.tgz">' . _('código fuente') . '</a></li>' . "\n";
	echo '<li><a href="./faq-'.$dblang.'.php">' . _('acerca de menéame').'</a></li>' . "\n";
	if ($title != "login") {
		if($current_user->authenticated) {
	  		echo '<li><a href="./login.php?op=logout&amp;return='.urlencode($_SERVER['REQUEST_URI']).'">' . _('cerrar sesión') . '</a></li>' . "\n";
  			echo '<li><a href="./user.php">' . _('perfil de') . ' ' . $current_user->user_login . '</a></li>' . "\n";
			echo (in_array($current_user->user_level, array('admin', 'god'))) ? '<li><a href="./categories.php">' . _('categorías') . '</a></li>' . "\n" : "";
		} else {
  			echo '<li><a href="./register.php">' . _('registrarse') . '</a></li>' . "\n";
  			echo '<li><a href="./login.php?return='.urlencode($_SERVER['REQUEST_URI']).'">' . _('login') . '</a></li>' . "\n";
		}
	}
	echo '<li>' . "\n";
		echo '<form action="./" method="get" id="thisform-search">' . "\n";
		echo '<label for="search" accesskey="100" class="inside">'._('buscar').'</label>' . "\n";
		if (!empty($_REQUEST['search'])) {
			echo '<input type="text" name="search" id="search" value="'.htmlspecialchars(strip_tags($_REQUEST['search'])).'" />' . "\n";
		} else {
		// benjami: onblur and onfocus to this	
			echo '<input name="search" id="search" value="'._('buscar...').'" type="text" onblur="if(this.value==\'\') this.value=\''._('buscar...').'\';" onfocus="if(this.value==\''._('buscar...').'\') this.value=\'\';"/>' . "\n";
		}
		echo '</form>' . "\n";
	echo '</li>' . "\n";
	echo '</ul>' . "\n";
	echo '</div>' . "\n";
}

function do_footer($credits = true) {
	if($credits) @do_credits();
	echo "</div><!--#container closed-->\n";
	@include('ads/statcounter.inc');
	@include('ads/analytics-01.inc');
	//echo '<script type="text/javascript" src="http://embed.technorati.com/embed/4wkm6tqw3.js"></script>';
	echo "</body></html><!--html1:do_footer-->\n";
}

function do_sidebar() {
	global $db, $dblang, $globals;
	echo '<div id="sidebar">';
	
	if(!empty($_REQUEST['id'])) {
		$doing_story=true;
		do_trackbacks();
		echo '<ul class="main-menu">' . "\n";
	} else {
		$doing_story=false;
		echo '<ul class="main-menu">' . "\n";
		echo '<li>' . "\n";
		echo '<div class="note-temp">' . "\n";
		echo '<strong>' . _("menéame"). '</strong>' . "\n";
		echo _("es un sistema de promoción colaborativa de blogs...").' <a href="/faq-'.$dblang.'.php">'. _("leer más") . '</a>.' . "\n";
		echo '</div>' . "\n";
		echo '</li>' . "\n";
	}


	// Menear box
	echo '<li class="main-mnm"><a href="/shakeit.php">'._("menear pendientes").'</a></li>' . "\n";
	if ($globals['do_vote_queue']) {
		echo '<li class="main-mnm-moretext"><a href="/shakeit.php?category='.$globals['category_id'].'">'._("menear pendientes de la categoría").' <strong>'.$globals['category_name'].'</strong></a></li>' . "\n";
	}

	if(empty($_REQUEST['id'])) {
		// submit box
		echo '<li id="main-submit"><a href="/submit.php">'. _("enviar una historia") . '</a></li>' . "\n";
		echo '<li class="main-sneak"><a href="/sneak.php">'._("fisgón").'</a></li>' . "\n";

		do_categories('index', $_REQUEST['category']);
//		do_tags_box();
		do_standard_links();
		//do_banner_right_a(); // right side banner
	}
	echo '<li><div class="mnu-bugs"><a href="http://meneame.wikispaces.com/Bugs">'._("reportar un bug").'</a></div></li>' . "\n";
	do_rss_box();
	echo '</ul></div><!--html1:do_sidebar-->' . "\n";
}

function do_standard_links () {
	//	echo '<li><a href="/lastshaked.php">'._("últimos meneados").'</a></li>' . "\n";
		echo '<li><div class="mnu-us"><a href="/cloud.php">'._("etiquetas").'</a></div></li>' . "\n";
		echo '<li><div class="mnu-us"><a href="/topstories.php">'._("más meneadas").'</a></div></li>' . "\n";
		echo '<li><div class="mnu-top"><a href="/blogscloud.php">'._("blogs").'</a></div></li>' . "\n";
		echo '<li><div class="mnu-top"><a href="/topusers.php">'._("usuarios").'</a></div></li>' . "\n";
}

/********* TODO
function do_tags_box () {
	global $db, $dblang;

	$from_time=time()-86400;
	$res=$db->get_results("select tag_words, count(*) as count FROM tags WHERE tag_lang='$dblang' and tag_date > FROM_UNIXTIME($from_time)  GROUP BY tag_words order by tag_words asc limit 20");
	if ($res) {
		echo '<li><div class="mnu-tags"><aa href="/cloud.php">';
		foreach ($res as $item) {
	        $words = $item->tag_words;
	        $count = $item->count;
	        $size = intval(9+$count);
			echo '<span style="font-size: '.$size.'pt;">'.$words.'</span>&nbsp; ';
			//echo $words.'&nbsp; ';
		}
		echo '</a></div></li>' . "\n";
	}
}
***********/

function do_rss_box() {
	global $globals;

	echo '<li>' . "\n"; // It was class="side-boxed"
	echo '<ul class="rss-list">' . "\n";
	echo '<li class="rss-retol">'._('suscripciones').'</li>' . "\n";

	if(!empty($_REQUEST['search'])) {
		$search =  htmlspecialchars($_REQUEST['search']);
		echo '<li>';
		echo '<a href="/rss2.php?search='.$search.'" rel="rss">'._("búsqueda").': <strong>'. htmlspecialchars($_REQUEST['search'])."</strong></a>\n";
		echo '</li>';

	}

	if(!empty($globals['category_name'])) {
		echo '<li>';
		echo '<a href="/rss2.php?status=all&amp;category='.$globals['category_id'].'" rel="rss">'._("categoría").': <strong>'.$globals['category_name']."</strong></a>\n";
		echo '</li>';

	}

	echo '<li>';
	echo '<a href="/rss2.php" rel="rss">'._('publicadas').'</a>';
	echo '</li>' . "\n";
	
	echo '<li>';
	echo '<a href="/rss2.php?status=queued" rel="rss">'._('en cola').'</a>';
	echo '</li>' . "\n";

	echo '<li>';
	echo '<a href="/rss2.php?status=all" rel="rss">'._('todas').'</a>';
	echo '</li>' . "\n";

	if(!empty($globals['link_id'])) {
		echo '<li>';
		echo '<a href="/comments_rss2.php?id='.$globals['link_id'].'" rel="rss">'._('comentarios noticia').'</a>';
		echo '</li>' . "\n";
	}

	echo '<li>';
	echo '<a href="/comments_rss2.php" rel="rss">'._('comentarios (todos)').'</a>';
	echo '</li>' . "\n";

	echo '</ul>' . "\n";
	echo '<br style="clear: both;" />' . "\n";
	echo '</li> <!--html1:do_rss_box()-->' . "\n";

}

function force_authentication() {
	global $current_user;

	if(!$current_user->authenticated) {
		//echo '<div class="instruction"><h2>'._('ERROR: debes autentificarte antes').'. <a href="login.php">'._('Login').'</a>.</h2></div>'."\n";
		header("Location: ./login.php?return=".$_SERVER['REQUEST_URI']);
		die;
	}
	return true;
}

function do_pages($total, $page_size=25) {
	global $db;

	$index_limit = 10;

	$query=preg_replace('/page=[0-9]+/', '', $_SERVER['QUERY_STRING']);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}
	
	$current = get_current_page();
	$total_pages=ceil($total/$page_size);
	$start=max($current-intval($index_limit/2), 1);
	$end=$start+$index_limit-1;
	
	echo '<div class="pages">';

	if($current==1) {
		echo '<span class="nextprev">&#171; '._('anterior'). '</span>';
	} else {
		$i = $current-1;
		echo '<a href="?page='.$i.$query.'">&#171; '._('anterior').'</a>';
	}

	if($start>1) {
		$i = 1;
		echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
		echo '<span>...</span>';
	}
	for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
		if($i==$current) {
			echo '<span class="current">'.$i.'</span>';
		} else {
			echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
		}
	}
	if($total_pages>$end) {
		$i = $total_pages;
		echo '<span>...</span>';
		echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
	}
	if($current<$total_pages) {
		$i = $current+1;
		echo '<a href="?page='.$i.$query.'">&#187; '._('siguiente').'</a>';
	} else {
		echo '<span class="nextprev">&#187; '._('siguiente'). '</span>';
	}
	echo "</div><!--html1:do_pages-->\n";

}

function do_trackbacks() {
	global $db, $globals;

	echo '<div id="trackback">';
	echo '<h2><a href="'.get_trackback($globals['link_id']).'" title="'._('URI para trackbacks').'">trackbacks</a></h2>';
	$id=$globals['link_id'];
	$trackbacks = $db->get_col("SELECT trackback_id FROM trackbacks WHERE trackback_link_id=$id AND trackback_type='in' ORDER BY trackback_date DESC");
	echo '<ul>';
	if ($trackbacks) {
		require_once(mnminclude.'trackback.php');
		$trackback = new Trackback;
		foreach($trackbacks as $trackback_id) {
			$trackback->id=$trackback_id;
			$trackback->read();
			echo '<li><a href="'.$trackback->url.'" title="'.$trackback->content.'">'.$trackback->title.'</a></li>';
		}
	}
	else {
		echo '<li>'._('(sin trackbacks)').'</li>';
	}
	echo '<li><img src="img/favicons/technorati.png" alt="'._('enlaces technorati').'" width="16" height="16"/>&nbsp;<a href="http://technorati.com/search/'.urlencode(get_permalink($globals['link_id'])).'">'._('según Technorati').'</a></li>';
	echo "</ul>\n";
	echo '</div><!--html1:do_trackbacks-->';
}

function do_categories($what_cat_type, $what_cat_id) {
	
	// $what_cat_type:
	//	index: from index.php
	// 	shakeit: from shakeit.php

	global $db, $dblang, $globals;

	// Categories Box
	echo '<li>' . "\n"; // It was class="side-boxed"

	// change class id for shakeit page
	if ($what_cat_type == 'shakeit') $categorylist_class = 'column-one-list';
		else $categorylist_class = 'column-list';
	echo '<div class="'.$categorylist_class.'">' . "\n";
	
	echo '<ul>' . "\n";

	// database query
	if ($what_cat_type == 'shakeit') {
		$queued_count = $db->get_var("SELECT count(*) FROM links WHERE link_status = 'queued'");
		$categories = $db->get_results("select category_id, category_name,  count(*) as count from links, categories where category_lang='$dblang' and category_id=link_category AND link_status = 'queued' group by link_category ORDER BY category_name ASC");
	}
	else {
		$categories = $db->get_results("SELECT category_id, category_name FROM categories WHERE category_lang='$dblang' ORDER BY category_name ASC");
	}

	$query=preg_replace('/category=[0-9]*/', '', $_SERVER['QUERY_STRING']);
	// Always return to page 1
	$query=preg_replace('/page=[0-9]*/', '', $query);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}

	// draw first category: all categories
	if (empty($what_cat_id)) $thiscat = ' class="thiscat"';
		else $thiscat = '';
	echo '<li'.$thiscat.'><a href="'.$_SERVER[PHP_SELF].'?'.$query.'">'._('_todas');
	if ($what_cat_type == 'shakeit') echo '&nbsp;('.$queued_count.')';
	echo '</a></li>' . "\n";

	// draw categories
	foreach ($categories as $category) {

		if($category->category_id == $what_cat_id) {
			$globals['category_id'] = $category->category_id;
			$globals['category_name'] = $category->category_name;
			$thiscat = ' class="thiscat"';
		}
		else {
			$thiscat = '';
		}


		echo '<li'.$thiscat.'><a href="'.$_SERVER[PHP_SELF].'?category='.$category->category_id.$query.'">';
		echo _($category->category_name);
		if ($what_cat_type == 'shakeit') echo '&nbsp;('.$category->count.')';
		echo "</a></li>\n";

		}

	echo '</ul>';
	echo '<br style="clear: both;" />' . "\n";
	echo '</div></li><!--html1:do_categories-->' . "\n";

}
?>
