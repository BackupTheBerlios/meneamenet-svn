<?php
includeonce('../config.php');

?>
function myXMLHttpRequest ()
{
	var xmlhttplocal;
	try {
		xmlhttplocal = new ActiveXObject ("Msxml2.XMLHTTP")}
	catch (e) {
		try {
			xmlhttplocal = new ActiveXObject ("Microsoft.XMLHTTP")
		}
		catch (E) {
			xmlhttplocal = false;
		}
  	}
	if (!xmlhttplocal && typeof XMLHttpRequest != 'undefined') {
		try {
			var xmlhttplocal = new XMLHttpRequest ();
		}
		catch (e) {
	  		var xmlhttplocal = false;
			alert ('couldn\'t create xmlhttp object');
		}
	}
	return (xmlhttplocal);
}

var mnmxmlhttp = Array ();
<?php if ( $globals['interface'] == "monouser" ) { ?>
var mnmxmlhttp2 = Array ();
var mnmxmlhttp3 = Array ();
<?php } ?>
var mnmString = Array ();
var mnmPrevColor = Array ();
var responsestring = Array ();
var myxmlhttp = Array ();
var responseString = new String;
var xmlhttp = new myXMLHttpRequest ();
var update_voters = false;


function menealo (user, id, htmlid, md5)
{
  	if (xmlhttp) {
		url = "menealo.php";
		content = "id=" + id + "&user=" + user + "&md5=" + md5;
		mnmxmlhttp[htmlid] = new myXMLHttpRequest ();
		if (mnmxmlhttp[htmlid]) {
		/*
			mnmxmlhttp[htmlid].open ("POST", url, true);
			mnmxmlhttp[htmlid].setRequestHeader ('Content-Type',
					   'application/x-www-form-urlencoded');
			mnmxmlhttp[htmlid].send (content);
		*/
			url = url + "?" + content;
			mnmxmlhttp[htmlid].open ("GET", url, true);
			mnmxmlhttp[htmlid].send (null);


			warnmatch = new RegExp ("^WARN:");
			errormatch = new RegExp ("^ERROR:");
			target1 = document.getElementById ('mnms-' + htmlid);
			target2 = document.getElementById ('mnmlink-' + htmlid);
			mnmPrevColor[htmlid] = target2.style.backgroundColor;
			//target1.style.background = '#c00';
			target2.style.backgroundColor = '#FF9400';
			mnmxmlhttp[htmlid].onreadystatechange = function () {
				if (mnmxmlhttp[htmlid].readyState == 4) {
					mnmString[htmlid] = mnmxmlhttp[htmlid].responseText;
					if (mnmString[htmlid].match (errormatch)) {
						mnmString[htmlid] = mnmString[htmlid].substring (6, mnmString[htmlid].length);
						// myclearTimeout(row);
						// resetrowfull(row);
						alert (mnmString[htmlid]);
						changemnmvalues (htmlid, true);
						updateVoters(id);
					} else {
						// Just a warning, do nothing
						if (mnmString[htmlid].match (warnmatch)) {
							alert(mnmString[htmlid]);
						} else {
							changemnmvalues (htmlid, false);
							updateVoters(id);
						}
					}
					<?php if ( $globals['interface'] == "monouser" ) { ?>
					apila_noticias(htmlid, id);
					<?php } ?>
				}
			}
		} else {
			alert('Couldn\'t create XmlHttpRequest');
		}
	}
}

<?php if ( $globals['interface'] == "monouser" ) { ?>

function apila_noticias(htmlid, id) {
	mnmxmlhttp2[htmlid] = new myXMLHttpRequest ();
	if (mnmxmlhttp2[htmlid]) {
		promote = "scripts/promote2.php";
		mnmxmlhttp2[htmlid].open ("GET", promote, true);
		mnmxmlhttp2[htmlid].send (null);
		mnmxmlhttp2[htmlid].onreadystatechange = function () {
			if (mnmxmlhttp2[htmlid].readyState == 4) {
				count = 1;
				prev_id = id;
				while (parseInt(id) - count >= parseInt(document.getElementById('end').innerHTML)) {
					while (!document.getElementById('news-' + (parseInt(id) - count))) {
						count++;
					}
					buffer = document.getElementById('news-' + (parseInt(id) - count)).innerHTML;
					document.getElementById('news-' + prev_id).innerHTML = buffer;
					document.getElementById('news-' + prev_id).id = 'news-' + (parseInt(id) - count);
					prev_id = parseInt(id) - count;
					count++;
				}
			}
		}
		ultima_noticia(htmlid);
	}
}

function ultima_noticia(htmlid) {
	mnmxmlhttp3[htmlid] = new myXMLHttpRequest ();
	if (mnmxmlhttp3[htmlid]) {
		url = "/libs/next.php?id=" + document.getElementById('end').innerHTML;
		mnmxmlhttp3[htmlid].open ("GET", url, true);
		mnmxmlhttp3[htmlid].send (null);
		mnmxmlhttp3[htmlid].onreadystatechange = function () {
			if (mnmxmlhttp3[htmlid].readyState == 4) {
				eval (mnmxmlhttp3[htmlid].responseText);
				window.alert(link_title);
				tags = link_tags.split(", ");
				html = '<div class="news-body"><ul class="news-shakeit"><li class="mnm-queued" id="main' + link_id + '"><a id="mnms-' + link_id + '" href="story.php?id=' + link_id + '">0 meneos</a></li>';
				html += '<li class="menealo" id="mnmlink-' + link_id + '"><a href="javascript:menealo(' + userid + ',' + link_id + ',' + link_id + ',\'' + randkey + '\')" title="vota si te agrada">menéalo</a></li></ul>';
				html += '<h3 id="title' + link_id + '"><a href="' + link_url + '">' + link_title + '</a></h3><div class="news-submitted"><strong>' + link_url + '</strong><br>';
				html += 'enviado hace </div><div class="news-body-text">' + link_content + '</div><div class="news-tags"><strong><a href="cloud.php" title="nube">etiquetas</a></strong>: ';
				for (i=0; i<tags.length; i++) {
					if ( i != 0 )
						html += ', ';
					html += '<a href="index.php?search=tag:' + tags[i] + '">' + tags[i] + '</a>';
				}
				html += '</div><div class="news-details"><span class="tool">categoría: <a href="./index.php?category=' + category_id + '" title="categoría">' + link_category + '</a></span> <span class="tool"><a href="editlink.php?id=' + link_id + '">editar</a></span> ';
				html += '<form class="tool" action="" id="problem-' + link_id + '"><a href="javascript:report_problem(document.getElementById(\'problem-' + link_id + '\'), ' + userid + ', ' + link_id + ', \'' + randkey + '\')">Descartar noticia</a><input name="ratings" value="-1" type="hidden"></form>';
				html += '</div></div>';
				prev_id = document.getElementById('end').innerHTML;
				document.getElementById('news-' + prev_id).innerHTML = html;
				document.getElementById('news-' + prev_id).id = 'news-' + link_id;
				window.alert(document.getElementById('end').innerHTML);
				document.getElementById('end').innerHTML = link_id;
				window.alert(document.getElementById('end').innerHTML);
			}
		}
	}
}

<?php } ?>

function disable_problem_form(id) {
	target = document.getElementById ('problem-' + id);
	if (target) {
		target.ratings.disabled=true;
		target.innerHTML = "";
	}
}

function disable_vote_link(id, mess) {
	target = document.getElementById ('mnmlink-' + id);
	if (target) {
		target.style.backgroundColor = mnmPrevColor[id];
		target.innerHTML = "<span>"+mess+"</span>";
	}
}

function changemnmvalues (id, error)
{
	split = new RegExp ("~--~");
	b = mnmString[id].split (split);
	target1 = document.getElementById ('mnms-' + id);
	target2 = document.getElementById ('mnmlink-' + id);
	if (error) {
		disable_vote_link(id, "grr...");
		disable_problem_form(id);
		//target2.innerHTML = "<span>grrr...</span>";
		return false;
	}
	if (b.length <= 3) {
		target1.innerHTML = b[0];
		target2.style.backgroundColor = mnmPrevColor[id];
		//target2.innerHTML = "<span>¡chachi!</span>";
		disable_vote_link(id, "¡chachi!");
		disable_problem_form(id);
	}
	return false;
}


function enablebutton (button, button2, target)
{
	var string = target.value;
	button2.disabled = false;
	if (string.length > 0) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}
}

function checkfield (type, form, field)
{
	url = 'checkfield.php?type='+type+'&name=' + field.value;
	checkitxmlhttp = new myXMLHttpRequest ();
	checkitxmlhttp.open ("GET", url, true);
	checkitxmlhttp.onreadystatechange = function () {
		if (checkitxmlhttp.readyState == 4) {
		responsestring = checkitxmlhttp.responseText;
			if (responsestring == 'OK') {
				document.getElementById (type+'checkitvalue').innerHTML = '<span style="color:black">"' + field.value + 
						'": ' + responsestring + '</span>';
				form.submit.disabled = '';
			} else {
				document.getElementById (type+'checkitvalue').innerHTML = '<span style="color:red">"' + field.value + '": ' +
				responsestring + '</span>';
				form.submit.disabled = 'disabled';
			}
		}
	}
  //  xmlhttp.setRequestHeader('Accept','message/x-formresult');
  checkitxmlhttp.send (null);
  return false;
}

function report_problem(frm, user, id, md5 /*id, code*/) {
	if (frm.ratings.value == 0)
		return;
	if (! confirm("¿Seguro que desea continuar?") ) {
		frm.ratings.selectedIndex=0;
		return false;
	}
	content = "id=" + id + "&user=" + user + "&md5=" + md5 + '&value=' +frm.ratings.value;
	url="problem.php?" + content;
	xmlhttp.open("GET",url,true);
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			errormatch = new RegExp ("^ERROR:");
			response = xmlhttp.responseText;
			if (response.match(errormatch)) {
				response = response.substring (6, response.length);
				alert (response);
			} else {
				disable_vote_link(id, ":-(");
				disable_problem_form(id);
				//frm.ratings.disabled=true;
				/*alert(xmlhttp.responseText);*/
				updateVoters(id);
			}
		}
  	}
	xmlhttp.send(null);
	return false;
}

function updateVoters(id) {
	if (update_voters) {
		get_votes(1, id);
	}
}

// Get voters by Beldar <beldar.cat at gmail dot com>
function get_votes(page,id) {
	var url = 'libs/meneos.php?id='+id+'&p='+page;
	xmlhttp.open('get', url, true);
	xmlhttp.onreadystatechange = votes_resp;
	xmlhttp.send(null);
}

function votes_resp(){
	if(xmlhttp.readyState == 4){
		response = xmlhttp.responseText;
		if (response.length > 10) 
			document.getElementById('voters-container').innerHTML = response;
	} /*else{
		document.getElementById('voters-container').innerHTML = 'Loading...';
	}*/
}

