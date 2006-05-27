<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Javier Carranza <javier at al dot quimia dot net>
//     based on profile.php memeame source
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'user.php');


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

if ($current_user->user_id > 0 && $current_user->authenticated && in_array($current_user->user_level, array('admin', 'god'))) {
		$login=$current_user->user_login;
} else {
	header("Location: ./login.php");
	die;
}

$user=new User();
$user->username = $login;
if(!$user->read()) {
	echo "error 2";
	die;
}

do_header(_('administración de categorías'));
do_navbar(_('administración de categorías'));

show_category_form();

do_footer();


function show_category_form() {
	global $user;

	change_categories();

	echo '<div id="genericform-contents"><div id="genericform"><fieldset><legend>';
	echo '<span class="sign">'._('administración de categorías').'</span></legend>';

	echo '<div class="column-list">'."\n";
	echo '<div class="categorylist">'."\n";
	echo '<form action="categories.php" method="post" id="thisform">' . "\n";
	echo '<input type="hidden" name="process" value="1" />' . "\n";
	echo '<input type="hidden" name="user_id" value="'.$user->id.'" />' . "\n";

	show_categories();

	echo '<br style="clear: both;" />' . "\n";
	echo '</div></div>'."\n";

	echo '<br />' . "\n";
	echo '<p>'._('Introduce una nueva categoría o edita las existentes:').'</p>';

	echo '<p class="l-mid" id="insert-p"><label for="new_cat">' . _("nueva categoría") . ':</label> ' . "\n";
	echo '<input type="text" id="new_cat" name="new_cat" tabindex="1" size="25" /> ' . "\n";
	echo '<label for="new_parent">' . _("hija de") . ':</label> ' . "\n";
	echo '<input type="text" id="new_parent" name="new_parent" tabindex="1" size="1" value="0" />' . "\n";
	echo '<label for="new_feed">' . _("url feed") . ':</label> ' . "\n";
	echo '<input type="text" id="new_feed" name="new_feed" tabindex="1" size="25" /><br />' . "\n";

	echo '<span class="genericformnote"><strong>pocas palabras, genéricas, cortas y separadas por "," (coma)</strong> Ejemplo: <em>web, programación, software libre</em></span><br />';
	echo '<label for="new_tags">' . _("etiquetas por defecto") . ':</label> ' . "\n";
	echo '<input type="text" id="new_tags" name="new_tags" tabindex="1" size="25" /></p>' . "\n";

	echo '<p class="l-bottom" id="submit-p"><input type="submit" name="new" value="'._('enviar').'" class="genericsubmit"></p>';
	echo "</form></fieldset></div></div>\n";
}

function show_categories() {
	global $db, $dblang;

	$categories = $db->get_results("SELECT category_id, category_parent, category_name, category_feed FROM categories WHERE category_lang='$dblang' ORDER BY category_parent ASC, category_id ASC");

	foreach ($categories as $category) {
		if (isset($tree[$category->category_parent])) {
			$tree[$category->category_parent] .= "," . $category->category_id;
		} else {
			$tree[$category->category_parent] = $category->category_id;
		}
		$category_data[$category->category_id]->category_parent = $category->category_parent;
		$category_data[$category->category_id]->category_name = $category->category_name;
		$category_data[$category->category_id]->category_feed = $category->category_feed;
	}

	$elems = split(",", $tree[0]);
	recursive($elems, $tree, $category_data, 0);

}

function recursive($elems, $tree, $data, $level) {
	foreach ($elems as $item) {
		if (isset($tree[$item])) {
			$elem = split(",", $tree[$item]);
			for ($i=$level; $i>0; $i--)
				echo '<blockquote>';

			print_category($item, $data[$item]->category_parent, $data[$item]->category_name, $data[$item]->category_feed);

			for ($i=$level; $i>0; $i--)
				echo '</blockquote>';
			recursive($elem, $tree, $data, $level+1) . "\n";
		} else {
			for ($i=$level; $i>0; $i--)
				echo '<blockquote>';

			print_category($item, $data[$item]->category_parent, $data[$item]->category_name, $data[$item]->category_feed);

			for ($i=$level; $i>0; $i--)
				echo '</blockquote>';
		}
	}
}

function print_category ($id, $parent, $name, $feed) {
	echo '<p class="l-top" id="l-top-edit[' . $id . ']">['. $id . 
		'] <label for="edit[' . $id . ']" accesskey="' . $id . 
		'" id="label1-edit[' . $id .']">' . _($name) .'</label> ';

	echo '<input type="button" name="edit[' . $id . ']" id="edit[' . $id . 
		']" tabindex="1" value="' . _('editar') . 
		' " onclick="javascript:edit_category(this);" />' . "\n";

	echo '<input type="hidden"  name="parent-edit[' . $id . ']" id="parent-edit[' 
		. $id . ']" value="' . $parent . '" />';

	echo '<input type="hidden"  name="feed-edit[' . $id . ']" id="feed-edit[' 
		. $id . ']" value="' . $feed . '" />';

	echo '<input type="submit" name="delete[' . $id . ']" id="delete[' . $id .
		 ']" tabindex="1" value="' . _('eliminar') . ' " />' . "\n";
}

function change_categories() {
	global $user, $current_user, $globals;
	$errors = 0;
	$ncat = 0;
	
	if( !(isset($_POST['new']) || isset($_POST['delete'])) || !isset($_POST['process']) || $_POST['user_id'] != $current_user->user_id ) return;

	if (isset($_POST['delete'])) {
		delete_category(array_keys($_POST['delete']));
	} elseif (isset($_POST['new_cat'])) {
		insert_category(trim($_POST['new_cat']), trim($_POST['new_parent']), trim($_POST['new_feed']), trim($_POST['new_tags']));
	} else {
		foreach (array_keys($_POST['new_cat-edit']) as $key) {
			$category->id[$ncat]=trim($key);
			$ncat++;
		}
		$ncat=0;
		foreach ($_POST['new_cat-edit'] as $catitem) {
			$category->name[$ncat]=trim($catitem);
			$ncat++;
		}
		$ncat=0;
		foreach ($_POST['new_parent-edit'] as $catparent) {
			$category->parent[$ncat]=intval($catparent);
			$ncat++;
		}
		$ncat=0;
		foreach ($_POST['new_feed-edit'] as $catfeed) {
			$category->feed[$ncat]=htmlspecialchars(trim($catfeed));
			$ncat++;
		}
		$ncat=0;
		foreach ($_POST['new_tags-edit'] as $cattags) {
			$category->tags[$ncat]=trim($cattags);
			$ncat++;
		}
		if ( !(empty($_POST['new_cat-edit']) || empty($_POST['new-cat'])) ) {
			$errors = 1;
			echo '<p class="form-error">'._('No ha introducido ninguna categoría nueva').'</p>';
		}
		if ( empty($_POST['new_tags-edit']) ) {
			$errors = 1;
			echo '<p class="form-error">'._('No ha introducido ninguna etiqueta').'</p>';
		}
		if (!$errors) {
			save_categories($category);
			echo '<p class="form-act">'._('Datos actualizados').'</p>';
		}
	}
}

function save_categories($category) {
	global $db, $dblang;

	$ncat=0;
	foreach ($category->id as $key) {
		$db->query("update categories set category_name=\"" . $category->name[$ncat] . "\", category_parent=\"" . $category->parent[$ncat] . "\", category_feed=\"" . $category->feed[$ncat] . "\" where category_id=\"$key\" and category_lang=\"$dblang\"");
		$db->query("update feed_data set tags='" . $category->tags[$ncat] . "\" where id=\"$key\"");
		$ncat++;
	}
}

function delete_category($key) {
	global $db, $dblang;

	if (is_int($key[0]))
		$db->query("delete from categories where category_id=\"" . $key[0] . "\" and category_lang=\"$dblang\"");
		$db->query("delete from feed_data where id=\"" . $key[0] . "\"");
}

function insert_category($key, $parent, $feed, $tags) {
	global $db, $dblang;

	$id=$db->get_results("select max(category_id) max from categories");
	if ($feed != "") {
		$uri = parse_url($feed);
		$link = $uri['scheme'] . "://" . $uri['host'];
		$db->query("insert into feed_data (id, link, tags) values(" . (intval($id[0]->max) + 1) .", '". $link  ."', '" . $tags . "')");
		$db->query("insert into categories (category_id, category_parent, category_name, category_feed, category_lang) values (" . (intval($id[0]->max) + 1) . ", $parent, \"$key\", \"$feed\", \"$dblang\")");
	} else {
		$db->query("insert into categories (category_id, category_parent, category_name, category_lang) values (" . (intval($id[0]->max) + 1) . ", $parent, \"$key\", \"$dblang\")");
	}
}

?>
