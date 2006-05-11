<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'sneak.php');


#$globals['ads'] = true;
$globals['body-args'] = 'onload="start()"';

// Create temporary table for chat if it does not exist
$db->query('CREATE TABLE IF NOT EXISTS `chats` ( `chat_time` INTEGER UNSIGNED NOT NULL DEFAULT 0 , `chat_uid` INTEGER UNSIGNED NOT NULL DEFAULT 0, `chat_user` CHAR( 32 ) NOT NULL , `chat_md5` CHAR( 32 ), `chat_text` CHAR( 200 ) NOT NULL , INDEX USING BTREE ( `chat_time` ) ) ENGINE = MEMORY MAX_ROWS = 1000');
$db->query('CREATE TABLE IF NOT EXISTS `sneakers` ( `sneaker_id` CHAR(24) NOT NULL, `sneaker_time` INTEGER UNSIGNED NOT NULL DEFAULT 0, UNIQUE ( `sneaker_id` ) ) ENGINE = MEMORY MAX_ROWS = 1000');

// Check number of users if it's annonymous
if ($current_user->user_id == 0) {
	$nusers= $db->get_var("select count(*) from sneakers");
	if ($nusers > 80) {
		header('Location: http://' . get_server_name(). '/toomuch.html');
		die;
	}
}

// Delete all connections from the same IP, just to avoid stupid cheating
$db->query("delete from sneakers where sneaker_id like '".$globals['user_ip']."%'");

// Start html
header("Content-type: text/html; charset=utf-8");
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
 "\n";
//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
echo '<head>' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
echo "<title>"._('fisgón')." // men&eacute;ame</title>\n";
echo '<meta name="generator" content="meneame" />' . "\n";
echo '<style type="text/css" media="screen">@import "/css/es/telnet.css";</style>' . "\n";
echo '<link rel="icon" href="/img/common/konsole.png" type="image/x-icon" />' . "\n";
echo '<script src="./js/xmlhttp02.js" type="text/javascript"></script>' . "\n";
echo '</head>' . "\n";
echo "<body onload=\"start()\">\n";

?>
<script type="text/javascript">
//<![CDATA[
var my_version = <? echo $sneak_version; ?>;
var ts=<? echo (time()-3600); ?>; // just due a freaking IE cache problem
var server_name = '<? echo get_server_name(); ?>';
var base_url = 'http://'+'<? echo get_server_name();?>'+'/sneaker.php';
var mykey = <? echo rand(100,999); ?>;

var do_animation=false;

function to_html(data) {
	var tstamp=new Date(data.ts*1000);
	var timeStr;

	var hours = tstamp.getHours();
	var minutes = tstamp.getMinutes();
	var seconds = tstamp.getSeconds();

	timeStr  = ((hours < 10) ? "0" : "") + hours;
	timeStr  += ((minutes < 10) ? ":0" : ":") + minutes;
	timeStr  += ((seconds < 10) ? ":0" : ":") + seconds;

	html = '<div class="sneaker-ts">'+timeStr+'</div>';

	/* If it's a comment */
	if (data.type == 'chat') {
		html += '<div class="sneaker-type">T</div>';
		html += '<div class="sneaker-votes">&nbsp;</div>';
		html += '<div class="sneaker-chat">'+data.title+'</div>';
		html += '<div class="sneaker-who">';
		html += '<a href="user.php?login='+data.who+'">'+data.who+'</a></div>';
		html += '<div class="sneaker-status">'+data.status+'</div>';
		return html;
	}

	/* All the others */
	if (data.type == 'vote')
		html += '<div class="sneaker-type">+</div>';
	else if (data.type == 'problem')
		html += '<div class="sneaker-type">-</div>';
	else if (data.type == 'comment')
		html += '<div class="sneaker-type">C</div>';
	else if (data.type == 'new')
		html += '<div class="sneaker-type">-&gt;</div>';
	else if (data.type == 'published')
		html += '<div class="sneaker-type">&lt;-</div>';
	else
		html += '<div class="sneaker-type">'+data.type+'</div>';

	html += '<div class="sneaker-votes">'+data.votes+'</div>';
	if ("undefined" != typeof(data.cid) && data.cid > 0) anchor='#c-'+data.cid;
	else anchor='';

	html += '<div class="sneaker-story"><a href="story.php?id='+data.link+anchor+'">'+data.title+'</a></div>';
	if (data.type == 'problem')
		html += '<div class="sneaker-who"><span class="sneaker-problem">'+data.who+'</span></div>';
	else if (data.uid > 0)  {
		html += '<div class="sneaker-who">';
		html += '<a href="user.php?login='+data.who+'">'+data.who+'</a></div>';
	} else 
		html += '<div class="sneaker-who">'+data.who+'</div>';
	if (data.status == '<? echo _('publicada');?>')
		html += '<div class="sneaker-status"><a href="./"><span class="sneaker-published">'+data.status+'</span></a></div>';
	else if (data.status == '<? echo _('descartada');?>')
		html += '<div class="sneaker-status"><a href="shakeit.php?view=discarded"><span class="sneaker-discarded">'+data.status+'</span></a></div>';
	else 
		html += '<div class="sneaker-status"><a href="shakeit.php">'+data.status+'</a></div>';
	return html;
}


//]]>
</script>
<script type="text/javascript" src="http://<? echo get_server_name(); ?>/js/sneak01.js.php"></script>
<?

echo '<div class="sneaker">';
echo '<div class="sneaker-legend">';
echo '<form action="" class="sneaker-control" id="sneaker-control" name="sneaker-control">';
echo _('voto: ').'<input type="checkbox" checked="checked" name="sneak-vote" id="vote-status" onclick="toggle_control(\'vote\')" /> [+]&nbsp;';
echo _('problema: ').'<input type="checkbox" checked="checked" name="sneak-problem" id="problem-status" onclick="toggle_control(\'problem\')" /> [-]&nbsp;';
echo _('comentario: ').'<input type="checkbox" checked="checked" name="sneak-comment" id="comment-status" onclick="toggle_control(\'comment\')" /> [C]&nbsp;';
echo _('nueva: ').'<input type="checkbox" checked="checked" name="sneak-new" id="new-status" onclick="toggle_control(\'new\')" /> [-&gt;]&nbsp;';
echo _('publicada: ').'<input type="checkbox" checked="checked" name="sneak-published" id="published-status" onclick="toggle_control(\'published\')" /> [&lt;-]&nbsp;';
echo _('mensaje: ').'<input type="checkbox" checked="checked" name="sneak-chat" id="chat-status" onclick="toggle_control(\'chat\')" /> [T]&nbsp;';
echo _('fisgones').  ': <strong><span style="font-size: 140%;" id="ccnt"> </span></strong>';
echo "</form>\n";
if ($current_user->user_id > 0) {
	echo '<form name="chat_form" onsubmit="return send_chat(this);">';
	echo _('mensaje') . ': <input type="text" name="comment" value="" size="90" maxlength="195" />&nbsp;<input type="submit" value="'._('enviar').'" class="sendmessage"/>';
	echo '</form>';
}
echo '</div>' . "\n";
echo '<div class="sneaker-item">';
echo '<div class="sneaker-ts"><strong>'._('hora').'</strong></div>';
echo '<div class="sneaker-type"><strong>'._('acción').'</strong></div>';
echo '<div class="sneaker-votes"><strong>'._('meneos').'</strong></div>';
echo '<div class="sneaker-story"><strong>'._('noticia').'</strong></div>';
echo '<div class="sneaker-who"><strong>'._('quién/qué').'</strong></div>';
echo '<div class="sneaker-status"><strong>'._('estado').'</strong></div>';
echo "</div>\n";


for ($i=0; $i<$max_items;$i++) {
	echo '<div id="sneaker-'.$i.'" class="sneaker-item">&nbsp;';
	echo "</div>\n";


}

echo '</div>';
//echo "</div>\n"; // air-with-footer
?>
<script type="text/javascript">
var chat_status = document.getElementById('chat-status');
//<![CDATA[
check_control('vote');
check_control('problem');
check_control('comment');
check_control('new');
check_control('published');
check_control('chat');
//]]>
</script>
<?
echo "</body></html>\n";
?>
