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
		url = base_url + "backend/menealo.php";
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
				}
			}
		} else {
			alert('Couldn\'t create XmlHttpRequest');
		}
	}
}

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
	if (button2 != null) {
		button2.disabled = false;
	}
	if (string.length > 0) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}
}

function checkfield (type, form, field)
{
	url = base_url + 'backend/checkfield.php?type='+type+'&name=' + field.value;
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
	if (! confirm("¿Seguro que desea reportarlo?") ) {
		frm.ratings.selectedIndex=0;
		return false;
	}
	content = "id=" + id + "&user=" + user + "&md5=" + md5 + '&value=' +frm.ratings.value;
	url=base_url + "backend/problem.php?" + content;
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
		get_votes('meneos.php', 'voters', 'voters-container',1, id);
	}
}

// Get voters by Beldar <beldar.cat at gmail dot com>
// Generalized for other uses (gallir at gmail dot com)
function get_votes(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type;
	xmlhttp.open('get', url, true);
	xmlhttp.onreadystatechange = function () {
		if(xmlhttp.readyState == 4){
			response = xmlhttp.responseText;
			if (response.length > 10) {
				document.getElementById(container).innerHTML = response;
			}
		}
	}
	xmlhttp.send(null);
}

