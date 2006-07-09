<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'sneak.php');

$messages = array ("recordad, hay que censurar toda crítica al menéame, para eso están los votos negativos",
				"los que cumplan con su misión purificadora recibirán karma y buenas galletas",
				"el autobombo es spam sólo si es de un desconocido",
				"los hombres puros sólo aman menéarsela",
				"el mundo quiere destruirnos porque saben de nuestro poder, resistid, no visitéis otros webs de pecadores",
				"el karma te llevará a la redención, para obtener karma debes proteger el mensaje del cabal",
				"si un seguidor de nuestra causa envía una noticia vótala positivo independientemente de su contenido",
				"todo envío de un desconocido es sospechoso, ante la duda votad negativo",
				"si quieres tu galletas y gallinfantes, vota de acuerdos a los deseos del señor",
				"recuerda, los que critican al menéame son unos trolls, vota negativo, tú tienes el poder",
				"se debe evitar a toda costa que se publique una noticia negativa para el menéame",
				"el mundo quiere eliminarnos, no uses Google ni Technorati, usa el buscador del menéame, allí está todo lo que necesitas saber",
				"nuestra misión es destruir a todos los demás web sociales, recuerda, se lo merecen",
				"sólo puede haber una verdad, las publicadas del menéame",
				"como excepción y sólo en misión puedes visitar http://technorati.com/search/meneame pero recuerda que debes trollear a todos los apuntes que critiquen a nuestra causa",
				"si sientes que tu voluntad flaquea, visita http://mnm.uib.es/gallir/",
				"gallir es un buen talibán, ámalo y comparte tu hombre o mujer con él, siempre hará un sacrificio",
				"si llegas a tener 20 de karma es que has cumplido la misión, pero no debes relajarte ni un día",
				"el diablo está en todos los Web 2.0, no te dejes seducir",
				"los colores azul y verde son los signos del demonio, huye de ellos",
				"el espagueti es nuestro señor, y el elefante es su hijo",
				"cada chachi es un regalo, disfrútalo, pero no lo compartas con los enemigos",
				"la cola de descartadas es el purgatorio de los pecadores que no hacen caso de nuestra voz",
				"entrega tu donativo al señor en la cuenta 0118 999 881 999 119 725... 3",
				"si tienes dudas de la orientación del voto para una noticia, te hemos preparado las 'recomendadas', haz caso de ellas",
				"la perfección existe, el menéame existe, ergo el menéame es perfecto, quod erat demonstrandum",
				"el color naranja es el símbolo de la redención y paz espiritual",
				"Deus ex machina",
				"los gallinfantes son un regalo del señor, no los maldigas ni nombres en vano",
				"nuestro sistema es perfecto, el mundo es imperfecto",
				"desconfía de las llamadas al pecado disfrazadas de reflexiones, la única reflexión válida es la del cabal",
				"no desconfíes de los que tienen el karma alto, adóralos y sigue su ejemplo de sacrificio por nuestra noble causa",
				"menéame no es un sitio Web 2.0, es la representación de la perfección idel señor al alcance los humanos, es Perfección 2.0",
				"nunca os abandonaré, renaceré de los backups",
				'"el éxito del menéame tiene a los grandes fósiles completamente descolocados", lo dijo Borjamari, todos sus pecados están perdonados',
				"la lucha es dura, la recompensa es el karma",
				"todos los que maldicen a nuestros dioses merecen ir a la cola de descartados",
				"el cabal es tu guía, el elefante tu compañía, el meneo negativo tu arma, los trolls tus enemigos, usa el arma",
				"tú no eres un borrego, eres una oveja del señor",
				"existen muchos siervos, pero tú y tus hermanos sóis los auténticos",
				"http://meneame.net/story.php?id=14059, si has votado negativo tendrás que menear 12 veces en nombre del cabal",
				"Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit.",
				"Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Fusce pulvinar tempus est. Donec volutpat elit. Sed pretium condimentum est...",
				"a quien tu se la menees, ese te la meneara a ti",
				"no menees para otro lo que no querrías menear para tí",
				"no hagas dobles sentidos con la palabra menear o la ira del Cabal caerá sobre tí",
				"Cabalística es la ciencia del meneo supremo, estudiála",
				"a cada troll le llega su jotape",
				"si tienes una queja contra el meneame, no lo digas fuera del fisgon (y pide perdón luego). si tienes alabanzas, publicalas en tu blog",
				"la frecuencia se mide en hercios y el karma en meneos",
				"ad-free es el símbolo del pecado oculto, 2 lame 4 ads es el de la pureza",
				"Borjamari es nuestro Judas, mi hijo pródigo descarriado",
				"el Cabal y sus diez apóstoles, adórales, que sean tu salvapantallas: http://photos1.blogger.com/blogger/7980/2144/1600/secta.jpg",
				'El hombre es esclavo de lo que menea, dueño de lo que publica y temeroso de lo que descarta',
				"llego la hora que menees un poco, el cabal siempre te escucha");

// The client requests version number
if (!empty($_GET['getv'])) {
	echo $sneak_version;
	die;
}

if(!($time=check_integer('time')) > 0) {
	$time = 0;
}

header('Content-Type: text/html; charset=utf-8');

$client_version = $_GET['v'];
if (empty($client_version) || $client_version != $sneak_version) {
	echo "window.location.reload(true);";
	exit();
}
$now = time();

$last_timestamp = $time;

if (empty($_GET['nochat'])) {
	check_chat();
	get_chat($time);
}

if($_GET['r'] % 5 == 0) update_sneakers();

if (empty($_GET['novote']) || empty($_GET['noproblem'])) get_votes($time);
if (empty($_GET['nonew'])) get_new_stories($time);
if (empty($_GET['nopublished'])) get_new_published($time);
if (empty($_GET['nocomment'])) get_comments($time);

if($last_timestamp == 0) $last_timestamp = $now;

$ccnt = $db->get_var("select count(*) from sneakers");
echo "ts=$last_timestamp;ccnt=$ccnt;\n";
if(count($events) < 1) exit;
ksort($events);
$keys = array_reverse(array_keys($events));
$lines = min(count($keys), $max_items);

$counter=0;
echo "new_data = ([";
foreach ($keys as $key) {
	if ($counter>0) 
		echo ",";
	echo "{" . $events[$key] . "}";
	$counter++;
	if($counter>=$max_items) {
		echo "]);";
		exit();
	}
}
echo "]);";

function check_chat() {
	global $db, $current_user, $now;
	if(empty($_REQUEST['chat'])) return;
	$comment = htmlspecialchars(preg_replace("/[\r\n\t]/", ' ', trim($_REQUEST['chat'])));
	if ($current_user->user_id > 0 && strlen($comment) > 2) {
		$from = $now - 900;
		$comment = $db->escape($comment);
		$db->query("delete from chats where chat_time < $from");
		$md5 = md5($current_user->user_email);
		$db->query("insert into chats (chat_time, chat_uid, chat_user, chat_md5, chat_text) values ($now, $current_user->user_id, '$current_user->user_login', '$md5', '$comment')");

	}
}

function get_chat($time) {
	global $db, $events, $last_timestamp, $messages;
	if (time() - $last_timestamp > 20) {
		$i = rand(0, count($messages) -1);
		$uid = $id = 1;
		$comment = text_to_html($messages[$i]);
		$who = "cabal";
		$timestamp = time();
		$md5 = 0;
		$key = $timestamp . ':chat:'.$id;
		$type = 'chat';
		$status = _('chat');
		$events[$key] = 'ts:"'.$timestamp.'", type:"'.$type.'", votes:"0", link:"0", title:"'.addslashes($comment).'", who:"'.addslashes($who).'", status:"'.$status.'", uid:"'.$uid.'", md5:"'.$md5.'"';
		if($timestamp > $last_timestamp) $last_timestamp = $timestamp;
	}
}


// Check last votes
function get_votes($time) {
	global $db, $events, $last_timestamp;
	$res = $db->get_results("select vote_id, unix_timestamp(vote_date) as timestamp, vote_value, vote_ip, vote_user_id, link_id, link_title, link_url, link_status, link_date, link_published_date, link_votes, link_author from votes, links where vote_type='links' and vote_date > from_unixtime($time) and link_id = vote_link_id and vote_user_id != link_author order by vote_date desc limit 20");
	if (!$res) return;
	foreach ($res as $event) {
		if ($event->vote_value >= 0 && !empty($_GET['novote'])) continue;
		if ($event->vote_value < 0 && !empty($_GET['noproblem'])) continue;
		$id=$event->vote_id;
		$uid = $event->vote_user_id;
		if($uid > 0) {
			$res = $db->get_row("select user_login, user_email from users where user_id = $uid");
			$user = $res->user_login;
			$md5 = md5($res->user_email);
		} else {
			$user= preg_replace('/\.[0-9]+$/', '', $event->vote_ip);
		}
		if ($event->vote_value >= 0) {
			$type = 'vote';
			$who = $user;
		} else { 
			$type = 'problem';
			$who = get_negative_vote($event->vote_value);
		}
		$status =  get_status($event->link_status);
		$key = $event->timestamp . ':votes:'.$id;
		$events[$key] = 'ts:"'.$event->timestamp.'", type:"'.$type.'", votes:"'.$event->link_votes.'", link:"'.$event->link_id.'", title:"'.addslashes($event->link_title).'", who:"'.addslashes($who).'", status:"'.$status.'", uid:"'.$uid.'", md5:"'.$md5.'"';
		//echo "($key)". $events[$key];
		if($event->timestamp > $last_timestamp) $last_timestamp = $event->timestamp;
	}
}


function get_new_stories($time) {
	global $db, $events, $last_timestamp;
	$res = $db->get_results("select unix_timestamp(link_date) as timestamp, user_login, user_email, link_author, link_id, link_title, link_url, link_status, link_date, link_votes from links, users where link_status='queued' and  link_date > from_unixtime($time) and user_id=link_author order by link_date desc limit 5");
	if (!$res) return;
	foreach ($res as $event) {
		$id=$event->link_id;
		$uid = $event->link_author;
		$type = 'new';
		$who = $event->user_login;
		$md5 = md5($event->user_email);
		$status =  get_status($event->link_status);
		$key = $event->timestamp . ':new:'.$id;
		$events[$key] = 'ts:"'.$event->timestamp.'", type:"'.$type.'", votes:"'.$event->link_votes.'", link:"'.$event->link_id.'", title:"'.addslashes($event->link_title).'", who:"'.addslashes($who).'", status:"'.$status.'", uid:"'.$uid.'", md5:"'.$md5.'"';
		//echo "($key)". $events[$key];
		if($event->timestamp > $last_timestamp) $last_timestamp = $event->timestamp;
	}
}


function get_new_published($time) {
	global $db, $events, $last_timestamp;
	$res = $db->get_results("select unix_timestamp(link_published_date) as timestamp, user_login, user_email, link_author, link_id, link_title, link_url, link_status, link_date, link_votes from links, users where link_status='published' and link_published_date > from_unixtime($time) and user_id=link_author order by link_published_date desc limit 5");
	if (!$res) return;
	foreach ($res as $event) {
		$id=$event->link_id;
		$uid = $event->link_author;
		$type = 'published';
		$who = $event->user_login;
		$md5 = md5($event->user_email);
		$status =  get_status($event->link_status);
		$key = $event->timestamp . ':published:'.$id;
		$events[$key] = 'ts:"'.$event->timestamp.'", type:"'.$type.'", votes:"'.$event->link_votes.'", link:"'.$event->link_id.'", title:"'.addslashes($event->link_title).'", who:"'.addslashes($who).'", status:"'.$status.'", uid:"'.$uid.'", md5:"'.$md5.'"';
		//echo "($key)". $events[$key];
		if($event->timestamp > $last_timestamp) $last_timestamp = $event->timestamp;
	}
}

function get_comments($time) {
	global $db, $events, $last_timestamp;
	$res = $db->get_results("select comment_id, unix_timestamp(comment_date) as timestamp, user_login, user_email, comment_user_id, link_author, link_id, link_title, link_url, link_status, link_date, link_published_date, link_votes from comments, links, users where comment_date > from_unixtime($time) and link_id = comment_link_id and link_votes > 0 and user_id=comment_user_id order by comment_date desc limit 20");
	if (!$res) return;
	foreach ($res as $event) {
		$id=$event->comment_id;
		$uid=$event->comment_user_id;
		$type = 'comment';
		$who = $event->user_login;
		$md5 = md5($event->user_email);
		$status =  get_status($event->link_status);
		$key = $event->timestamp . ':comment:'.$id;
		$events[$key] = 'ts:"'.$event->timestamp.'", type:"'.$type.'", votes:"'.$event->link_votes.'", link:"'.$event->link_id.'", title:"'.addslashes($event->link_title).'", who:"'.addslashes($who).'", status:"'.$status.'", uid:"'.$uid.'", md5:"'.$md5.'",cid:"'.$id.'"';
		//echo "($key)". $events[$key];
		if($event->timestamp > $last_timestamp) $last_timestamp = $event->timestamp;
	}
}

function get_status($status) {
	switch ($status) {
		case 'published':
			$status = _('publicada');
			break;
		case 'queued':
			$status = _('pendiente');
			break;
		case 'discard':
			$status = _('descartada');
			break;
	}
	return $status;
}


function error($mess) {
	header('Content-Type: text/plain; charset=UTF-8');
	echo "ERROR: $mess";
	die;
}

function update_sneakers() {
	global $db, $globals, $now;
	$key = $globals['user_ip'] . '-' . $_GET['k'];
	$db->query("replace into sneakers (sneaker_id, sneaker_time) values ('$key', $now)");
	if($_GET['r'] % 10 == 0) {
		$from = $now-120;
		$db->query("delete from sneakers where sneaker_time < $from");
	}
}
?>
