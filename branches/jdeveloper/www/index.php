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

$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$search = get_search_clause();
$search_txt = htmlspecialchars(strip_tags($_REQUEST['search']));
// Search all if it's a search
$cat=check_integer('category');
if($search)  {
	$from_where = "FROM links WHERE ";
	if($cat) {
		$from_where .= " link_category=$cat AND ";
	}
} else {
	$from_where = "FROM links WHERE link_status='published' ";
	if($cat) {
		$from_where .= " AND link_category=$cat ";
	}
}

if($search) {
	do_header(_('búsqueda de'). '"'.$search_txt.'"');
	do_navbar(_('búsqueda'));
	echo '<div id="contents">'; // benjami: repetit, no m'agrada, arreglar depres
	echo '<h2>'._('resultados de la búsqueda'). ' "'.$search_txt.'" </h2>';
	$from_where .= $search;
	if ($_REQUEST['tag'] == 'true' || $_REQUEST['date']  == 'true' ) {
		$order_by = ' ORDER BY link_date DESC ';
	} else {
		$order_by = '';
	}
} else {
	do_header(_('últimas publicadas'));
	do_navbar('');
	echo '<div id="contents">'; // benjami: repetit, no m'agrada, arreglar despres
	echo '<h2>'._('últimas noticias').'</h2>';
	$order_by = " ORDER BY link_published_date DESC ";
}

$link = new Link;
$rows = $db->get_var("SELECT count(*) $from_where $order_by");
$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary();
	}
}

do_pages($rows, $page_size);
echo '</div> <!--index.php-->';
do_sidebar();
do_footer();
?>
