<?
include('../libs/sneak.php');
header('Content-Type: text/javascript; charset=UTF-8');
header('Cache-Control: max-age=3600');
?>

var items = Array();
var new_items = 0;
var max_items = <? echo $max_items; ?>;
var request_timer;
var min_update = 20000;
var next_update = 5000;
var xmlhttp = new myXMLHttpRequest ();
var requests = 0;
var max_requests = 3000;
var comment = '';
var last_comment_sent=0;
var comment_period = 10; //seconds
var ccnt = 0; 	// Connected counter

var show_vote = true;
var show_problem = true;
var show_comment = true;
var show_new = true;
var show_published = true;
var show_chat = true;

function start() {
	for (i=0; i<max_items; i++) {
		items[i] = document.getElementById('sneaker-'+i);
	}
	get_data();
}

function is_busy() {
    switch (xmlhttp.readyState) {
        case 1:
        case 2:
        case 3:
            return true;
        break;
        // Case 4 and 0
        default:
            return false;
        break;
    }
}

function abort_request () {
	clearTimeout(timer);
	clearTimeout(request_timer);
	if (is_busy()) {
		xmlhttp.abort();
		// Bug in konqueror, it forces to create a new object after the abort
		xmlhttp = new myXMLHttpRequest();
		//alert("timeout");
	}
}

function handle_timeout () {
	abort_request();
	//alert("handle_timeout");
	timer = setTimeout('get_data()', next_update/2);
}

function get_data() {
	if (is_busy()) {
		handle_timeout();
		return false;
	}
	url=sneak_base_url+'?k='+mykey+'&time='+ts+'&v='+my_version+'&r='+requests;
	if (show_chat == false) url += '&nochat=1';
	if (show_vote == false) url += '&novote=1';
	if (show_problem == false) url += '&noproblem=1';
	if (show_comment == false) url += '&nocomment=1';
	if (show_new == false) url += '&nonew=1';
	if (show_published == false) url += '&nopublished=1';
	if(comment.length > 0) {
		var content = 'chat='+encodeURIComponent(comment);
		xmlhttp.open ("POST", url, true);
		xmlhttp.setRequestHeader ('Content-Type', 'application/x-www-form-urlencoded');
		xmlhttp.onreadystatechange=received_data;
		xmlhttp.send (content);
		comment = '';
	} else {
		xmlhttp.open("GET",url,true);
		xmlhttp.onreadystatechange=received_data;
		xmlhttp.send(null);
	}
	request_timer = setTimeout('handle_timeout()', 10000);  // wait for 10 seconds
	requests++;
	return false;
}

function received_data() {
	if (xmlhttp.readyState != 4) return;
	if (xmlhttp.status == 200 && xmlhttp.responseText.length > 10) {
		clearTimeout(request_timer);
		// We get new_data array
		var new_data = Array();
		eval (xmlhttp.responseText);
		target=document.getElementById("ccnt");
		if(target) target.innerHTML = ccnt;
		new_items= new_data.length;
		if(new_items > 0) {
			if (do_animation) clearInterval(animation_timer);
			next_update = Math.round(0.5*next_update + 0.5*min_update/(new_items*2));
			shift_items(new_items);
			for (i=0; i<new_items && i<max_items; i++) {
				items[i].innerHTML = to_html(new_data[i]);
				if (do_animation) set_initial_color(i);
			}
			if (do_animation) {
				animation_timer = setInterval('animate_background()', 100);
				animating = true;
			}
		} else next_update = Math.round(next_update*1.25);
	}
	if (next_update < 5000) next_update = 5000;
	if (next_update > min_update) next_update = min_update;
	if (requests > max_requests) {
		if ( !confirm('<? echo _('Fisgón: ¿desea continuar conectado?');?>') ) {
			mnm_banner_reload = 0;
			return;
		}
		requests = 0;
		next_update = 100;
	}
	timer = setTimeout('get_data()', next_update);
}

function shift_items(n) {
	//for (i=n;i<max_items;i++) {
	for (i=max_items-1;i>=n;i--) {
		items[i].innerHTML = items[i-n].innerHTML;
		//items.shift();
	}
}

function clear_items() {
	for (i=0;i<max_items;i++) {
		items[i].innerHTML = '&nbsp;';
	}
}

function send_chat(form) {
	var currentTime = new Date();
	if(show_chat == false) {
		alert("<? echo _('tiene deshabilitado los comentarios'); ?>");
		return false;
	}
	if(form.comment.value.length < 4) {
		alert("<? echo _('mensaje demasiado corto'); ?>");
		return false;
	}
	if( currentTime.getTime() < last_comment_sent + (comment_period*1000)) {
		alert("<? echo _('sólo se puede enviar un mensaje cada');?> " + comment_period + " <? echo _('segundos');?>");
		return false;
	}
	abort_request();
	comment=form.comment.value;
	last_comment_sent = currentTime.getTime();
	form.comment.value='';
	if (do_animation && animating) {
		timer = setTimeout('get_data()', 500)
	} else {
		get_data();
	}
	return false;
}

function check_control(what) {
	var status = document.getElementById(what+'-status');
	if (status.checked) {
		eval('show_'+what+' = true');
		return true;
	} else {
		eval('show_'+what+' = false');
		return false;
	}
}

function toggle_control(what) {
	abort_request();
	check_control(what);
	clear_items();
	ts-=3600;
	timer = setTimeout('get_data()', 100)
	return false;
}
