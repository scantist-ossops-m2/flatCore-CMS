<?php
//error_reporting(E_ALL ^E_NOTICE);


// get the posting-page by 'type_of_use' and $languagePack
$target_page = $db_content->select("fc_pages", "page_permalink", [
	"AND" => [
		"page_type_of_use" => "display_post",
		"page_language" => $page_contents['page_language']
	]
]);

// template files
$tpl_list_index = file_get_contents("styles/$prefs_template/templates/posts/post-list-index.tpl");
$tpl_list_m = file_get_contents("styles/$prefs_template/templates/posts/post-list-m.tpl");
$tpl_list_m_wo = file_get_contents("styles/$prefs_template/templates/posts/post-list-m-wo.tpl");
$tpl_list_i = file_get_contents("styles/$prefs_template/templates/posts/post-list-i.tpl");
$tpl_list_g = file_get_contents("styles/$prefs_template/templates/posts/post-list-g.tpl");
$tpl_list_v = file_get_contents("styles/$prefs_template/templates/posts/post-list-v.tpl");
$tpl_list_e = file_get_contents("styles/$prefs_template/templates/posts/post-list-e.tpl");
$tpl_list_l = file_get_contents("styles/$prefs_template/templates/posts/post-list-l.tpl");
$tpl_list_p = file_get_contents("styles/$prefs_template/templates/posts/post-list-p.tpl");

$tpl_pagination = file_get_contents("styles/$prefs_template/templates/posts/pagination.tpl");
$tpl_pagagination_list = file_get_contents("styles/$prefs_template/templates/posts/pagination_list.tpl");



$sql_start = ($posts_start*$posts_limit)-$posts_limit;
if($sql_start < 0) {
	$sql_start = 0;
}





$get_posts = fc_get_post_entries($sql_start,$posts_limit,$posts_filter);
$cnt_filter_posts = $get_posts[0]['cnt_posts'];
$cnt_get_posts = count($get_posts);

$nextPage = $posts_start+$posts_limit;
$prevPage = $posts_start-$posts_limit;
$cnt_pages = ceil($cnt_filter_posts / $posts_limit);


$pag_list = '';
$arr_pag = array();

for($i=0;$i<$cnt_pages;$i++) {
	
	$active_class = '';
	$set_start = $i+1;
	
	if($i == 0 && $posts_start < 1) {
		$set_start = 1;
		$active_class = 'active';
	}
	
	
	if($set_start == $posts_start) {
		$active_class = 'active';
		$current_page = $set_start;
	}
	
	$pagination_link = fc_set_pagination_query($display_mode,$set_start);
	
	$pag_list_item = $tpl_pagagination_list;
	$pag_list_item = str_replace("{pag_href}", $pagination_link, $pag_list_item);
	$pag_list_item = str_replace("{pag_nbr}", $set_start, $pag_list_item);
	$pag_list_item = str_replace("{pag_active_class}", $active_class, $pag_list_item);
	$arr_pag[] = $pag_list_item;
	
}

$pag_start = $current_page-4;

if($pag_start < 0) { $pag_start = 0; }
$arr_pag = array_slice($arr_pag, $pag_start, 5);

foreach($arr_pag as $pag) {
	$pag_list .= $pag;
}

$nextstart = $posts_start+1;
$prevstart = $posts_start-1;

$older_link_query = fc_set_pagination_query($display_mode,$nextstart);
$newer_link_query = fc_set_pagination_query($display_mode,$prevstart);

if($prevstart < 1) {
	$prevstart = 1;
	$newer_link_query = '#';
}

if($nextstart > $cnt_pages) {
	$older_link_query = '#';
}


$tpl_pagination = str_replace("{pag_prev_href}", $newer_link_query, $tpl_pagination);
$tpl_pagination = str_replace("{pag_next_href}", $older_link_query, $tpl_pagination);
$tpl_pagination = str_replace("{pagination_list}", $pag_list, $tpl_pagination);

$show_start = $sql_start+1;
$show_end = $show_start+($posts_limit-1);

if($show_end > $cnt_filter_posts) {
	$show_end = $cnt_filter_posts;
}

//eol pagination

$posts_list = '';
foreach($get_posts as $k => $post) {
	
	
	$post_releasedate = date('Y-m-d',$get_posts[$k]['post_releasedate']);
	$post_releasedate_year = date('Y',$get_posts[$k]['post_releasedate']);
	$post_releasedate_month = date('m',$get_posts[$k]['post_releasedate']);
	$post_releasedate_day = date('d',$get_posts[$k]['post_releasedate']);
	$post_releasedate_time = date('H:i:s',$get_posts[$k]['post_releasedate']);

	/* event dates */
	
	$event_start_day = date('d',$get_posts[$k]['post_event_startdate']);
	$event_start_month = date('m',$get_posts[$k]['post_event_startdate']);
	$event_start_month_text = $lang["m$event_start_month"];
	$event_start_year = date('Y',$get_posts[$k]['post_event_startdate']);
	$event_end_day = date('d',$get_posts[$k]['post_event_enddate']);
	$event_end_month = date('m',$get_posts[$k]['post_event_enddate']);
	$event_end_year = date('Y',$get_posts[$k]['post_event_enddate']);
	
	/* entry date */
	$entrydate_year = date('Y',$get_posts[$k]['post_date']);
	
	
	/* post images */
	$first_post_image = '';
	$post_images = explode("<->", $get_posts[$k]['post_images']);
	if($post_images[1] != "") {
		$first_post_image = '/' . $img_path . '/' . str_replace('../content/images/','',$post_images[1]);
	} else if($fc_prefs['default_banner'] == "" OR $fc_prefs['default_banner'] == "use_standard") {
		$first_post_image = FC_INC_DIR ."/modules/publisher.mod/$pub_tpl_dir/images/no-image.png";
	} else if($fc_prefs['default_banner'] == "without_image") {
		$this_entry = $tpl_list_m_wo;
	} else {
		$first_post_image = "/$img_path/" . $pub_preferences['default_banner'];
	}
	
	
	
	
	if($get_posts[$k]['post_type'] == 'm') {	
		if($first_post_image != "") {
			$this_entry = $tpl_list_m;
		} else {
			$this_entry = $tpl_list_m_wo;
		}
	}
	if($get_posts[$k]['post_type'] == 'i') {
		$this_entry = $tpl_list_i;
	}
	if($get_posts[$k]['post_type'] == 'g') {
		$this_entry = $tpl_list_g;
		$gallery_dir = 'content/galleries/'.$entrydate_year.'/gallery'.$get_posts[$k]['post_id'].'/';
		$fp = $gallery_dir.'*_tmb.jpg';
		$tmb_tpl = file_get_contents("styles/$prefs_template/templates/posts/thumbnail.tpl");
		$thumbs_array = glob("$fp");
		arsort($thumbs_array);
		$cnt_thumbs_array = count($thumbs_array);
		if($cnt_thumbs_array > 0) {
			
			$first_post_image = "/" . str_replace('_tmb','_img',$thumbs_array[0]);
			
			$thumbnails_str = '';
			$x = 0;
			foreach($thumbs_array as $tmb) {
				$x++;
				$tmb_str = $tmb_tpl;
				
				$tmb_src = '/'.$tmb;
				$img_src = str_replace('_tmb','_img',$tmb_src);
				$tmb_str = str_replace('{tmb_src}', $tmb_src, $tmb_str);
				$tmb_str = str_replace('{img_src}', $img_src, $tmb_str);
				$thumbnails_str .= $tmb_str;
				
				if($x == 5) {
					break;
				}
				
			}
		}
	}
	if($get_posts[$k]['post_type'] == 'v') {
		$this_entry = $tpl_list_v;
		$vURL = parse_url($get_posts[$k]['post_video_url']);
		parse_str($vURL['query'],$video); //$video['v'] -> youtube video id
	}
	if($get_posts[$k]['post_type'] == 'l') {
		$this_entry = $tpl_list_l;
	}
	if($get_posts[$k]['post_type'] == 'e') {
		$this_entry = $tpl_list_e;
	}
	if($get_posts[$k]['post_type'] == 'p') {
		$this_entry = $tpl_list_p;
	}
	
	$post_filename = basename($get_posts[$k]['post_slug']);
	$post_href = FC_INC_DIR . "/".$target_page[0]."$post_filename-".$get_posts[$k]['post_id'].".html";

	$post_teaser = htmlspecialchars_decode($get_posts[$k]['post_teaser']);
	$post_text = htmlspecialchars_decode($get_posts[$k]['post_text']);
	$post_releasedate = date('d.m.Y',$get_posts[$k]['post_releasedate']);
	
	$post_categories = explode('<->',$get_posts[$k]['post_categories']);
	$cat_str = '';
	foreach($all_categories as $cats) {
		
		if(in_array($cats['cat_id'], $post_categories)) {
			$cat_str .= '<a href="/'.$fct_slug.$cats['cat_name_clean'].'/">'.$cats['cat_name'].'</a> ';
		}
		
		
	}
	
	$this_entry = str_replace("{read_more_text}", $lang['btn_read_more'], $this_entry);
	$this_entry = str_replace('{post_href}', $post_href, $this_entry);
	
	$this_entry = str_replace('{post_author}', $get_posts[$k]['post_autor'], $this_entry);
	$this_entry = str_replace('{post_title}', $get_posts[$k]['post_title'], $this_entry);
	$this_entry = str_replace('{post_teaser}', $post_teaser, $this_entry);
	$this_entry = str_replace('{post_img_src}', $first_post_image, $this_entry);
	
	$this_entry = str_replace("{video_id}", $video['v'], $this_entry);
	
	$this_entry = str_replace('{post_releasedate}', $post_releasedate, $this_entry);
	$this_entry = str_replace("{post_cats}", $cat_str, $this_entry);
	
	$this_entry = str_replace("{post_releasedate_ts}", $get_posts[$k]['post_releasedate'], $this_entry); /* timestring */
	$this_entry = str_replace("{post_releasedate}", $post_releasedate, $this_entry);

	$this_entry = str_replace("{post_lastedit}", $post_lastedit, $this_entry);
	$this_entry = str_replace("{post_lastedit_from}", $post_lastedit_from, $this_entry);
	$this_entry = str_replace("{event_start_day}", $event_start_day, $this_entry);
	$this_entry = str_replace("{event_start_month}", $event_start_month, $this_entry);
	$this_entry = str_replace("{event_start_month_text}", $event_start_month_text, $this_entry);
	$this_entry = str_replace("{event_start_year}", $event_start_year, $this_entry);
	$this_entry = str_replace("{event_end_day}", $event_end_day, $this_entry);
	$this_entry = str_replace("{event_end_month}", $event_end_month, $this_entry);
	$this_entry = str_replace("{event_end_year}", $event_end_year, $this_entry);
	$this_entry = str_replace("{post_tpl_event_hotline}", $tpl_hotline, $this_entry);
	$this_entry = str_replace("{post_event_hotline}", $post_data['event_hotline'], $this_entry);
	$this_entry = str_replace("{post_event_price_note}", $post_data['event_price_note'], $this_entry);
	$this_entry = str_replace("{post_tpl_event_prices}", $price_list, $this_entry);	
	
	$this_entry = str_replace("{post_thumbnails}", $thumbnails_str, $this_entry);
	
	
	$posts_list .= $this_entry;
	
}

$page_content = $tpl_list_index;
$page_content = str_replace('{pagination}', $tpl_pagination, $page_content);
$page_content = str_replace('{post_list}', $posts_list, $page_content);
$page_content = str_replace("{post_cnt}", $cnt_filter_posts, $page_content);
$page_content = str_replace("{lang_entries}", $lang['label_entries'], $page_content);
$page_content = str_replace("{lang_entries_total}", $lang['label_entries_total'], $page_content);
$page_content = str_replace("{post_start_nbr}", $show_start, $page_content);
$page_content = str_replace("{post_end_nbr}", $show_end, $page_content);


$modul_content = $page_content.$debug_string;

?>