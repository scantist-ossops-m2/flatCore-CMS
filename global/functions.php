<?php

/**
 * global functions
 * are used in frontend and backend
 * 
 */

include_once 'functions.posts.php';


/**
 * get all categories
 * order by cat_sort
 */

function fc_get_categories() {
	global $db_content;
	$categories = $db_content->select("fc_categories", "*",
	[
		"ORDER" => ["cat_sort" => "DESC"]
	]);	
	return $categories;
}


/**
 * get all comments
 * $filter = array()
 * $filter['type'] -> p|b|c
 * $filter['status'] -> all|1|2
 */

function fc_get_comments($start=0,$limit=100,$filter) {
	
	global $db_content;
	
	$filter_type = $filter['type'];
	if($filter_type == 'all') {
		$filter_type = ["p","b","c"];
	}
	

	if($filter['status'] == 'all') {
		$comment_status = ["1","2"];
	} else if($filter['status'] == '1') {
		$comment_status = "1";
	} else {
		$comment_status = 2;
	}
	
	
	$filter_relation_id = $filter['relation_id'];
	
	if($filter_relation_id == 'all') {

		$comments = $db_content->select("fc_comments", "*",[
				"AND" => [
				"comment_type" => $filter_type,
				"comment_status" => $comment_status
			],
				"LIMIT" => [$start,$limit],
				"ORDER" => ["comment_time" => "DESC"]
		]);

	} else {

		$comments = $db_content->select("fc_comments", "*",[
				"AND" => [
				"comment_type" => $filter_type,
				"comment_relation_id" => $filter_relation_id,
				"comment_status" => $comment_status
			],
				"LIMIT" => [$start,$limit],
				"ORDER" => ["comment_time" => "ASC"]
		]);		
		
	}
	
	return $comments;
}



/**
 * $comments array() from comments table
 * $sorting array() for sorting by id and parent_id
 */

function fc_list_comments_thread($comments, $sorting, $tpl, $root=0, $level=0) {

	global $lang;

  if(isset($sorting[$root])) {
  	foreach($sorting[$root] as $key => $comment_id) {
	     
	    $padding = (int) (20*$level);
	    if(!is_numeric($padding)) {
		  	$padding = 0;
	    }
	    	    
      $comment_time = date('d.m.Y H:i',$comments[$key]['comment_time']);
      $comment_avatar = '/styles/default/images/avatar.jpg';
      $comment_avatar_img = '<img src="'.$comment_avatar.'" class="img-avatar img-fluid rounded-circle" alt="" title="'.$comments[$key]['comment_author'].'">';
			$this_comment = $tpl;
			
			$this_comment = str_replace('{comment_author}', $comments[$key]['comment_author'], $this_comment);
			$this_comment = str_replace('{comment_text}', $comments[$key]['comment_text'], $this_comment);
			$this_comment = str_replace('{comment_time}', $comment_time, $this_comment);
			$this_comment = str_replace('{comment_avatar}', $comment_avatar_img, $this_comment);
			$this_comment = str_replace('{comment_id}', $comments[$key]['comment_id'], $this_comment);
			$a_url = '?cid='.$comments[$key]['comment_id'].'#comment-form';
			$this_comment = str_replace('{url_answer_comment}', $a_url, $this_comment);
			$this_comment = str_replace('{level}', $level, $this_comment);
						
			$entry_str .= '<div class="comment-level comment-level-'.$level.'">';
			$entry_str .=  $this_comment;
           
      $entry_str .= fc_list_comments_thread($comments, $sorting, $tpl, $comment_id, $level+1);
      $entry_str .= '</div>';

     }
  }
  
  $entry_str = str_replace('{lang_answer}', $lang['btn_send_answer'], $entry_str);
  
  return $entry_str;
  
}


function fc_write_comment($data) {
	
	global $db_content;
	global $lang;
	global $prefs_comments_mode;
	
	if($data['input_name'] != '' && $data['input_mail'] != '' && $data['input_comment'] != '') {
	
		foreach($data as $key => $val) {
			$$key = htmlspecialchars(strip_tags($val)); 
		}
		
		$type = 'p';
		$comment_status = 2;
		
		if($prefs_comments_mode == 1) {
			$comment_status = 1;
		}
		
		$comment_time = time();
		
		if(is_numeric($data['page_id'])) {
			$type = 'p';
			$relation_id = (int) $data['page_id'];
		}
		
		if(is_numeric($data['post_id'])) {
			$type = 'b';
			$relation_id = (int) $data['post_id'];
		}
	
		if(strlen($input_name) > 30) {
			$input_name = substr($input_name, 0,30);
		}
		
		if(strlen($input_mail) > 50) {
			$input_mail = substr($input_mail, 0,50);
		}
			
		if(strlen($input_comment) > 500) {
			$input_comment = substr($input_comment, 0,500);
		}
		
		if(is_numeric($data['parent_id'])) {
			$parent_id = (int) $data['parent_id'];
		}
		
		
		$input_comment = nl2br($input_comment);
		
		
		$db_content->insert("fc_comments", [
			"comment_type" =>  $type,
			"comment_relation_id" =>  $relation_id,
			"comment_parent_id" =>  $parent_id,
			"comment_status" =>  $comment_status,
			"comment_time" =>  $comment_time,
			"comment_author" =>  $input_name,
			"comment_author_mail" =>  $input_mail,
			"comment_text" =>  $input_comment
		]);
		
		$insert_id=$db_content->id();
		
		return $insert_id;
		
	}
}




/**
 * sending e-mails
 * $recipient -> array() 'name' and 'mail'
 * $subject -> string()
 * $message -> string()
 */


function fc_send_mail($recipient,$subject,$message) {

	global $prefs_mailer_adr, $prefs_mailer_name, $prefs_mailer_type, $prefs_mailer_return_path, $prefs_smtp_host, $prefs_smtp_port, $prefs_smtp_encryption, $prefs_smtp_authentication, $prefs_smtp_username, $prefs_smtp_psw;
	
	$subject = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $subject );
	$message = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $message );

	require_once FC_CORE_DIR.'lib/Swift/lib/swift_required.php';
	
	if($prefs_mailer_type == 'smtp') {
		
		$trans = Swift_SmtpTransport::newInstance()
            ->setUsername("$prefs_smtp_username")
            ->setPassword("$prefs_smtp_psw")
            ->setHost("$prefs_smtp_host")
            ->setPort($prefs_smtp_port);
			
		if($prefs_smtp_encryption != '') {
			$trans->setEncryption($prefs_smtp_encryption);
		}
		
	} else {
		$trans = Swift_MailTransport::newInstance();
	}
	
	$mailer = Swift_Mailer::newInstance($trans);
	$message = Swift_Message::newInstance("$subject")
			->setFrom(array($prefs_mailer_adr => $prefs_mailer_name))
			->setTo(array($recipient['mail'] => $recipient['name']))
			->setBody("$message","text/html");
			
	if(!$mailer->send($message, $failures)) {
	  $fail = print_r($failures,true);
		$return = $fail;
	} else {
		$return = 1;
	}
	
	return $return;
}



?>