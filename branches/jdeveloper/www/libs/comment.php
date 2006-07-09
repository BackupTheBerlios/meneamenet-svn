<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Comment {
	var $id = 0;
	var $randkey = 0;
	var $author = 0;
	var $link = 0;
	var $date = false;
	var $karma = 0;
	var $content = '';
	var $read = false;

	function store() {
		global $db, $current_user;

		if(!$this->date) $this->date=time();
		$comment_author = $this->author;
		$comment_link = $this->link;
		$comment_karma = $this->karma;
		$comment_date = $this->date;
		$comment_randkey = $this->randkey;
		$comment_content = $db->escape($this->content);
		if($this->id===0) {
			$db->query("INSERT INTO comments (comment_user_id, comment_link_id, comment_karma, comment_date, comment_randkey, comment_content) VALUES ($comment_author, $comment_link, $comment_karma, FROM_UNIXTIME($comment_date), $comment_randkey, '$comment_content')");
			$this->id = $db->insert_id;
		} else {
			$db->query("UPDATE comments set comment_user_id=$comment_author, comment_link_id=$comment_link, comment_karma=$comment_karma, comment_date=FROM_UNIXTIME($comment_date), comment_randkey=$comment_randkey, comment_content='$comment_content' WHERE comment_id=$this->id");
		}
	}
	
	function read() {
		global $db, $current_user;
		$id = $this->id;
		if(($link = $db->get_row("SELECT comments.*, users.user_login, users.user_avatar, users.user_email FROM comments, users WHERE comment_id = $id and user_id = comment_user_id"))) {
			$this->author=$link->comment_user_id;
			$this->username=$link->user_login;
			$this->email=$link->user_email;
			$this->randkey=$link->comment_randkey;
			$this->link=$link->comment_link_id;
			$this->karma=$link->comment_karma;
			$this->avatar=$link->user_avatar;
			$this->content=$link->comment_content;
			$date=$link->comment_date;
			$this->date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function print_summary($link, $length = 0) {
		global $current_user, $globals;
		static $comment_counter = 0;

		if(!$this->read) return;
		$comment_counter++;
		echo '<li id="ccontainer-'.$this->id.'">';
		echo '<div class="comment-body" id="comment-'.$comment_counter.'"><a href="'.$link->get_relative_permalink().'#comment-'.$comment_counter.'"><strong>#'.$comment_counter.'</strong></a>';
		echo '&nbsp;&nbsp;&nbsp;<span id="c-'.$this->id.'">'. "\n";
		//if($globals['external_ads']) echo "<!-- google_ad_section_start -->\n";
		$this->print_text($length);
		//if($globals['external_ads']) echo "<!-- google_ad_section_end -->\n";
		echo '</span></div>';
		echo '<div class="comment-info">';
		echo _('escrito por'). ' <a href="'.$globals['base_url'].'user.php?login='.urlencode($this->username).'">'.$this->username.'</a> '._('hace').' '.txt_time_diff($this->date);
		echo '<img src="'.get_avatar_url($this->author, $this->avatar, $this->email, 20).'" width="20" height="20" alt="'.$this->username.'" title="avatar" /></div>';
		echo "</li>\n";
	}

	function print_text($length = 0) {
		global $current_user, $globals;

		if ($this->author == $current_user->user_id &&
			time() - $this->date < $globals['comment_edit_time']) {
			$expand = '<br /><br />&#187;&nbsp;' . 
				'<a href="javascript:get_votes(\'comment_edit.php\',\'edit_comment\',\'ccontainer-'.$this->id.'\',0,'.$this->id.')" title="'._('editar').'">'._('editar comentario').'</a>';

		} elseif ($length>0 && strlen($this->content) > $length + $length/2) {
			$this->content = substr($this->content, 0 , $length);
			$expand = '...<br /><br />&#187;&nbsp;' .
				'<a href="javascript:get_votes(\'get_comment.php\',\'comment\',\'c-'.$this->id.'\',0,'.$this->id.')" title="'._('resto del comentario').'">'._('ver todo el comentario').'</a>';
		}
		echo $this->put_smileys(save_text_to_html($this->content)) . $expand;
	}

	function username() {
		global $db;
//TODO
		$this->username = $db->get_var("SELECT user_login FROM users WHERE user_id = $this->author");
		return $this->username;
	}

	function put_smileys($str) {
		global $globals;
		$str=preg_replace('/:-{0,1}\)/i', '<img src="'.$globals['base_url'].'img/smileys/smiley.gif" alt=":-)" title=":-)"/>', $str);
		$str=preg_replace('/[^t];-{0,1}\)/i', '<img src="'.$globals['base_url'].'img/smileys/wink.gif" alt=";)" title=";)" />', $str);
		$str=preg_replace('/:-{0,1}&gt;/i', '<img src="'.$globals['base_url'].'img/smileys/cheesy.gif" alt=":->" title=":->" />', $str);
		$str=preg_replace('/:-D|:grin:/i', '<img src="'.$globals['base_url'].'img/smileys/grin.gif" alt=":-D" title=":-D"/>', $str);
		$str=preg_replace('/:oops:|&lt;:\(/i', '<img src="'.$globals['base_url'].'img/smileys/embarassed.gif" alt="&lt;&#58;(" title="&#58;oops&#58; &lt;&#58;(" />', $str);
		$str=preg_replace('/&gt;:-{0,1}\(/i', '<img src="'.$globals['base_url'].'img/smileys/angry.gif" alt="&gt;&#58;-(" title="&gt;&#58;-(" />', $str);
		$str=preg_replace('/\?(:-){0,1}\(/i', '<img src="'.$globals['base_url'].'img/smileys/huh.gif" alt="?(" title="?(" />', $str);
		$str=preg_replace('/:-{0,1}\(/i', '<img src="'.$globals['base_url'].'img/smileys/sad.gif" alt=":-(" title=":-(" />', $str);
		$str=preg_replace('/:-O/', '<img src="'.$globals['base_url'].'img/smileys/shocked.gif" alt=":-O" title=":-O" />', $str);
		$str=preg_replace('/ 8-{0,1}[D\)]|:cool:/', ' <img src="'.$globals['base_url'].'img/smileys/cool.gif" alt="8-D" title=":cool: 8-D"/>', $str);
		$str=preg_replace('/:roll:/i', '<img src="'.$globals['base_url'].'img/smileys/rolleyes.gif" alt=":roll:" title=":roll:" />', $str);
		$str=preg_replace('/:-P/i', '<img src="'.$globals['base_url'].'img/smileys/tongue.gif" alt=":-P" title=":-P" />', $str);
		$str=preg_replace('/:-x/i', '<img src="'.$globals['base_url'].'img/smileys/lipsrsealed.gif" alt=":-x" title=":-x" />', $str);
		$str=preg_replace('/([^ps]):-{0,1}\//i', '$1<img src="'.$globals['base_url'].'img/smileys/undecided.gif" alt=":-/" title=":-/ :/" />', $str);
		$str=preg_replace('/:\'\(|:cry:/i', '<img src="'.$globals['base_url'].'img/smileys/cry.gif" alt=":\'(" title=":cry: :\'(" />', $str);
		$str=preg_replace('/([^a-zA-Z]|^)[xX]D+|:lol:/', ' <img src="'.$globals['base_url'].'img/smileys/laugh.gif" alt="xD" title=":lol: xD" />', $str);
		$str=preg_replace('/ :-{0,1}S/i', ' <img src="'.$globals['base_url'].'img/smileys/confused.gif" alt=":-S" title=":-S :S"/>', $str);
		$str=preg_replace('/:-{0,1}\|/i', '<img src="'.$globals['base_url'].'img/smileys/blank.gif" alt=":-|" title=":-| :|"/>', $str);
		$str=preg_replace('/:-{0,1}\*/i', '<img src="'.$globals['base_url'].'img/smileys/kiss.gif" alt=":-*" title=":-* :*"/>', $str);
		return $str;
	}
}
