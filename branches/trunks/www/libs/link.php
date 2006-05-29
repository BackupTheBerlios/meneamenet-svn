<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Link {
	var $id = 0;
	var $author = -1;
	var $blog = 0;
	var $username = false;
	var $randkey = 0;
	var $karma = 0;
	var $valid = false;
	var $date = false;
	var $published_date = 0;
	var $modified = 0;
	var $url = false;
	var $url_title = false;
	var $encoding = false;
	var $status = 'discard';
	var $type = '';
	var $category = 0;
	var $votes = 0;
	var $title = '';
	var $tags = '';
	var $content = '';
	var $html = false;
	var $trackback = false;
	var $read = false;
	var $fullread = false;
	var $voted = false;
	var $votes_enabled = true;

	function print_html() {
		echo "Valid: " . $this->valid . "<br>\n";
		echo "Url: " . $this->url . "<br>\n";
		echo "Title: " . $this->url_title . "<br>\n";
		echo "encoding: " . $this->encoding . "<br>\n";
	}

	function get($url) {
		$url=trim($url);
		//$context = array('user_agent' => 'Mozilla/5.0 (compatible; Meneame; +http://meneame.net/)'); // for PHP5
		if(!preg_match('/^http[s]*:/', $url) || !($this->html = file_get_contents($url))) {
			return;
		}
		$this->valid = true;
		$this->url=$url;
		if(preg_match('/charset=([a-zA-Z0-9-_]+)/i', $this->html, $matches)) {
			$this->encoding=trim($matches[1]);
			if(strcasecmp($this->encoding, 'utf-8') != 0) {
				$this->html=iconv($this->encoding, 'UTF-8//IGNORE', $this->html);
			}
		}
		if(preg_match('/<title>([^<>]*)<\/title>/i', $this->html, $matches)) {
			$this->url_title=trim($matches[1]);
		}
		require_once(mnminclude.'blog.php');
		$blog = new Blog();
		$blog->analyze_html($this->url, $this->html);
		if(!$blog->read('key')) {
			$blog->store();
		}
		$this->blog=$blog->id;
		$this->type=$blog->type;

		// Detect trackbacks
		if (!empty($_POST['trackback'])) {
			$this->trackback=trim($_POST['trackback']);
		} elseif (preg_match('/trackback:ping="([^"]+)"/i', $this->html, $matches) ||
			preg_match('/trackback:ping +rdf:resource="([^>]+)"/i', $this->html, $matches) || 
			preg_match('/<trackback:ping>([^<>]+)/i', $this->html, $matches)) {
			$this->trackback=trim($matches[1]);
		} elseif (preg_match('/<a[^>]+rel="trackback"[^>]*>/i', $this->html, $matches)) {
			if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
				$this->trackback=trim($matches2[1]);
			}
		} elseif (preg_match('/<a[^>]+href=[^>]+>trackback<\/a>/i', $this->html, $matches)) {
			if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
				$this->trackback=trim($matches2[1]);
			}
		}  
		
	}
	function type() {
		if (empty($this->type)) {
			if ($this->blog > 0) {
				require_once(mnminclude.'blog.php');
				$blog = new Blog();
				$blog->id = $this->blog;
				if($blog->read()) {
					$this->type=$blog->type;
					return $this->type;
				}
			}
			return 'normal';
		}
		return $this->type;
	}

	function store() {
		global $db, $current_user;

		$this->store_basic();
		$link_url = $db->escape($this->url);
		$link_url_title = $db->escape($this->url_title);
		$link_title = $db->escape($this->title);
		$link_tags = $db->escape($this->tags);
		$link_content = $db->escape($this->content);
		$db->query("UPDATE links set link_url='$link_url', link_url_title='$link_url_title', link_title='$link_title', link_content='$link_content', link_tags='$link_tags' WHERE link_id=$this->id");
		
	}

	function store_basic() {
		global $db, $current_user;

		if(!$this->date) $this->date=time();
		$link_author = $this->author;
		$link_blog = $this->blog;
		$link_status = $this->status;
		$link_votes = $this->votes;
		$link_karma = $this->karma;
		$link_randkey = $this->randkey;
		$link_category = $this->category;
		$link_date = $this->date;
		$link_published_date = $this->published_date;
		if($this->id===0) {
			$db->query("INSERT INTO links (link_author, link_blog, link_status, link_randkey, link_category, link_date, link_published_date, link_votes, link_karma) VALUES ($link_author, $link_blog, '$link_status', $link_randkey, $link_category, FROM_UNIXTIME($link_date), FROM_UNIXTIME($link_published_date), $link_votes, $link_karma)");
			$this->id = $db->insert_id;
		} else {
		// update
			$db->query("UPDATE links set link_author=$link_author, link_blog=$link_blog, link_status='$link_status', link_randkey=$link_randkey, link_category=$link_category, link_modified=NULL, link_date=FROM_UNIXTIME($link_date), link_published_date=FROM_UNIXTIME($link_published_date), link_votes=$link_votes, link_karma=$link_karma WHERE link_id=$this->id");
		}
	}
	
	function read() {
		global $db, $current_user;
		$id = $this->id;
		if(($link = $db->get_row("SELECT links.*, users.user_login, users.user_email FROM links, users WHERE link_id = $id AND user_id=link_author"))) {
			$this->author=$link->link_author;
			$this->username=$link->user_login;
			$this->email=$link->user_email;
			$this->blog=$link->link_blog;
			$this->status=$link->link_status;
			$this->votes=$link->link_votes;
			$this->karma=$link->link_karma;
			$this->randkey=$link->link_randkey;
			$this->category=$link->link_category;
			$this->url= $link->link_url;
			$this->url_title=$link->link_url_title;
			$this->title=$link->link_title;
			$this->tags=$link->link_tags;
			$this->content=$link->link_content;
			$date=$link->link_date;
			$this->date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$date=$link->link_published_date;
			$this->published_date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$date=$link->link_modified;
			$this->modified=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->fullread = $this->read = true;
			return true;
		}
		$this->fullread = $this->read = false;
		return false;
	}

	function read_basic() {
		global $db, $current_user;
		$this->username = false;
		$this->fullread = false;
		$id = $this->id;
		if(($link = $db->get_row("SELECT link_author, link_blog, link_status, link_randkey, link_category, link_date, link_votes, link_karma, link_published_date FROM links WHERE link_id = $id"))) {
			$this->author=$link->link_author;
			$this->blog=$link->link_blog;
			$this->votes=$link->link_votes;
			$this->karma=$link->link_karma;
			$this->status=$link->link_status;
			$this->randkey=$link->link_randkey;
			$this->category=$link->link_category;
			$date=$link->link_date;
			$this->date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$date=$link->link_published_date;
			$this->published_date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function duplicates($url) {
		global $db;
		$link_url=$db->escape($url);
		$n = $db->get_var("SELECT count(*) FROM links WHERE link_url = '$link_url' AND (link_status != 'discard' OR link_votes>0)");
		return $n;
	}

	function print_summary($type='full') {
		global $current_user, $current_user, $globals;

		if(!$this->read) return;
		if($this->is_votable()) {
			$this->voted = $this->vote_exists($current_user->user_id);
		}

		$url = $this->url;
		$title_short = wordwrap($this->title, 36, " ", 1);

		echo '<div class="news-summary" id="news-'.$this->id.'">';
		echo '<div class="news-body">';
		if ($type != 'preview' && !empty($this->title) && !empty($this->content)) {
			$this->print_shake_box($votes_enabled);
		}
		echo '<h3 id="title'.$this->id.'">';
		echo '<a href="'.htmlspecialchars($url).'">'. $title_short. '</a>';
		echo '</h3>';
		echo '<div class="news-submitted">';
		if ($type != 'short')
			echo '<a href="user.php?login='.$this->username.'" title="'.$this->username.'"><img src="'.get_gravatar_url($this->email, 25).'" width="25" height="25" alt="icon gravatar.com" /></a>';
		echo '<strong>'.htmlentities(preg_replace('/^http:\/\//', '', txt_shorter($this->url))).'</strong>'."<br />\n";
		echo _('enviado por').' <a href="user.php?login='.$this->username.'&amp;view=history"><strong>'.$this->username.'</strong></a> '._('hace').txt_time_diff($this->date);
		if($this->status == 'published')
			echo ', '  ._('publicado hace').txt_time_diff($this->published_date);
		echo "</div>\n";
		if($type=='full' || $type=='preview') {
			echo '<div class="news-body-text">'.text_to_html($this->content).'</div>';
		}
		if (!empty($this->tags)) {
			echo '<div class="news-tags">';
			echo '<strong><a href="cloud.php" title="'._('nube').'">'._('etiquetas').'</a></strong>:';
			$tags_array = explode(",", $this->tags);
			$tags_counter = 0;
			foreach ($tags_array as $tag_item) {
				$tag_item=trim($tag_item);
				$tag_url = urlencode($tag_item);
				if ($tags_counter > 0) echo ',';
				echo ' <a href="index.php?search=tag:'.$tag_url.'">'.$tag_item.'</a>';
				$tags_counter++;
			}
			echo '</div>';
		}

		echo '<div class="news-details">';
		if ($globals['comments']) {
			$ncomments = $this->comments();
			if($ncomments > 0) {
				$comments_mess = $ncomments . ' ' . _('comentarios');
				$comment_class = "comments";
			} else  {
				$comments_mess = _('sin comentarios');
				$comment_class = "comments_no";
			}
			if(empty($globals['link_id']))
				echo '<a href="story.php?id='.$this->id.'" class="tool '.$comment_class.'">'.$comments_mess. '</a>';
			else
				echo '<span class="tool comments">'.$comments_mess. '</span>';
		}

		/*
		if (!empty($this->tags)) {
			echo '<span class="tool"><a href="cloud.php" title="'._('nube').'">'._('etiquetas').'</a>:';
			$tags_array = explode(",", $this->tags);
			$tags_counter = 0;
			foreach ($tags_array as $tag_item) {
				$tag_item=trim($tag_item);
				$tag_url = urlencode($tag_item);
				if ($tags_counter > 0) echo ',';
				echo ' <a href="index.php?search=tag:'.$tag_url.'">'.$tag_item.'</a>';
				$tags_counter++;
			}
			echo '</span>';
		}
		*/
		echo '<span class="tool">'._('categoría'). ': <a href="./index.php?category='.$this->category.'" title="'._('categoría').'">'.$this->category_name().'</a></span>';

		// Allow to modify it
		if ($type != 'preview' && $this->is_editable()) {
			echo ' <span  class="tool"><a href="editlink.php?id='.$this->id.'">'._('editar').'</a></span> ';
		}

		if($current_user->user_id > 0 && $this->status!='published' && $this->votes > 0 && $type != 'preview' &&
				$current_user->user_karma > 5 && $this->votes_enabled /*&& $this->author != $current_user->user_id*/) {
			$this->print_problem_form();
		}

		echo '</div>'."\n";
		echo '</div></div>'."\n";

	}
	
	function print_shake_box() {
		global $current_user, $anonnymous_vote, $site_key, $globals;
		
		switch ($this->status) {
			case 'queued': // another color box for not-published
				$box_class = 'mnm-queued';
				break;
			case 'discard': // another color box for discarded
				$box_class = 'mnm-discarded';
				break;
			case 'published': // default for published
			default:
				$box_class = 'mnm-published';
				break;
		}
		echo '<ul class="news-shakeit">';
		echo '<li class="'.$box_class.'" id="main'.$this->id.'">';
		if (!empty($globals['link_id'])) $anchor='#voters';
		else $anchor = '';
		echo '<a id="mnms-'.$this->id.'" href="story.php?id='.$this->id.$anchor.'">'.$this->votes.' '._('meneos').'</a></li>';
		echo '<li class="menealo" id="mnmlink-'.$this->id.'">';

		if ($this->votes_enabled == false) {
			echo '<span>'._('cerrado').'</span>';
			echo '</ul>'."\n";
			return;
		}

		if( !$this->voted) {
			echo '<a href="javascript:menealo('."$current_user->user_id,$this->id,$this->id,"."'".md5($site_key.$current_user->user_id.$this->id.$this->randkey.$globals['user_ip'])."'".')" title="'._('vota si te agrada').'">'._('menéalo').'</a></li>';
		} else {
			if ($this->voted > 0) $mess = _('&#161;chachi!');
			else $mess = ':-(';
			echo '<span>'.$mess.'</span>';
		}
		echo '</ul>'."\n";
	}

	function print_problem_form() {
		global $current_user, $db, $anon_karma, $anonnymous_vote, $globals, $site_key;
		require_once(mnminclude.'votes.php');

		$vote = new Vote;
		$vote->link=$this->id;
		$vote->type='links';
		$vote->user=$current_user->user_id;
		if(/*!$current_user->user_id > 0 || */ (!$anonnymous_vote && $current_user->user_id == 0 ) || $this->voted) {
			// don't show it for now
			return;
			$status='disabled="disabled"';
		}
		$pvalue = -1;
		//echo '<span class="tool-right">';
		echo '<form class="tool" action="" id="problem-'.$this->id.'">';
		echo '<select '.$status.' name="ratings"  onchange="';
		echo 'report_problem(this.form,'."$current_user->user_id, $this->id, "."'".md5($site_key.$current_user->user_id.$this->randkey.$globals['user_ip'])."'".')';
		echo '">';
		echo '<option value="0" selected="selected">¿problema?</option>';
		echo '<option value="'.$pvalue.'">'._('irrelevante').'</option>'; $pvalue--;
		echo '<option value="'.$pvalue.'">'._('antigua').'</option>'; $pvalue--;
		echo '<option value="'.$pvalue.'">'._('spam').'</option>'; $pvalue--;
		echo '<option value="'.$pvalue.'">'._('duplicada').'</option>'; $pvalue--;
		echo '<option value="'.$pvalue.'">'._('provocación').'</option>'; $pvalue--;
		echo '<option value="'.$pvalue.'">'._('errónea').'</option>'; $pvalue--;
		echo '</select>';
//		echo '<input type="hidden" name="return" value="" disabled />';
		echo '</form>';
	}

	function vote_exists($user) {
		require_once(mnminclude.'votes.php');
		$vote = new Vote;
		$vote->user=$user;
		$vote->link=$this->id;
		return $vote->exists();	
	}
	
	function votes($user) {
		require_once(mnminclude.'votes.php');

		$vote = new Vote;
		$vote->user=$user;
		$vote->link=$this->id;
		return $vote->count();
	}

	function insert_vote($user=0) {
		global $anon_karma;
		require_once(mnminclude.'votes.php');

		$vote = new Vote;
		$vote->user=$user;
		$vote->link=$this->id;
		if ($vote->exists()) return false;
		$vote->value=$anon_karma;
		if($user>0) {
			require_once(mnminclude.'user.php');
			$dbuser = new User($user);
			if($dbuser->id>0) {
				$vote->value = round($dbuser->karma);
			}
		}
		if($vote->insert()) {
			$vote->user=-1;
			$this->votes=$vote->count();
			$this->store_basic();
			return true;
		}
		return false;
	}

	function category_name() {
		global $db, $dblang;
		return $db->get_var("SELECT category_name FROM categories WHERE category_lang='$dblang' AND category_id=$this->category");
	}

	function publish() {
		if(!$this->read) $this->read_basic();
		$this->published_date = time();
		$this->status = 'published';
		$this->store_basic();
	}

	function comments() {
		global $db;
		return $db->get_var("SELECT count(*) FROM comments WHERE comment_link_id = $this->id");
	}

	function is_editable() {
		global $current_user, $db;

		if($current_user->user_id > 0 && (
			($this->author == $current_user->user_id && $this->status != 'published'  && time() - $this->date < 3600) ||
			$current_user->user_level != 'normal'))
			return true;
		return false;
	}

	function is_votable() {
		global $globals;

		if($globals['time_enabled_votes'] > 0 && $this->date < time() - $globals['time_enabled_votes'])  {
			$this->votes_enabled = false;
		} else {
			$this->votes_enabled = true;
		}
		return $this->votes_enabled;
	}
}
