<?php

//prohibit unauthorized access
require("core/access.php");

foreach($_POST as $key => $val) {
	$$key = @htmlspecialchars($val, ENT_QUOTES); 
}


/* save upload preferences */
if(isset($_POST['update_posts'])) {

	$data = $db_content->update("fc_preferences", [
		"prefs_posts_entries_per_page" =>  $prefs_posts_entries_per_page,
		"prefs_posts_images_prefix" =>  $prefs_posts_images_prefix,
		"prefs_posts_default_banner" =>  $prefs_posts_default_banner,
		"prefs_posts_url_pattern" =>  $prefs_posts_url_pattern,
		"prefs_posts_products_default_tax" =>  $prefs_posts_products_default_tax,
		"prefs_posts_products_tax_alt1" =>  $prefs_posts_products_tax_alt1,
		"prefs_posts_products_tax_alt2" =>  $prefs_posts_products_tax_alt2,
		"prefs_posts_products_default_currency" =>  $prefs_posts_products_default_currency,
		"prefs_posts_event_time_offset" =>  $prefs_posts_event_time_offset
	], [
	"prefs_id" => 1
	]);	
}



if(isset($_POST)) {
	$fc_preferences = get_preferences();
	
	foreach($fc_preferences as $k => $v) {
	   $$k = stripslashes($v);
	}
}


echo '<form action="?tn=system&sub=posts" method="POST">';

echo'<fieldset>';
echo'<legend>'.$lang['label_entries'].'</legend>';
echo '<div class="form-group">';
echo '<label>'.$lang['label_entries_per_page'].'</label>';
echo '<input type="text" class="form-control" name="prefs_posts_entries_per_page" value="'.$prefs_posts_entries_per_page.'">';
echo '</div>';
echo '</fieldset>';


echo'<fieldset>';
echo'<legend>'.$lang['label_images'].'</legend>';
echo '<div class="form-group">';
echo '<label>'.$lang['label_images_prefix'].'</label>
			<input type="text" class="form-control" name="prefs_posts_images_prefix" value="'.$prefs_posts_images_prefix.'">
			</div>';
$all_images = fc_get_all_images();
echo '<div class="form-group">';
echo '<label>'.$lang['label_default_image'].'</label>';
				
echo '<select class="form-control custom-select" name="prefs_posts_default_banner">';
echo '<option value="use_standard">'.$lang['use_standard'].'</option>';

if($prefs_posts_default_banner == 'without_image') { $sel_without_image = 'selected'; }
echo '<option value="without_image" '.$sel_without_image.'>'.$lang['dont_use_an_image'].'</option>';
foreach ($all_images as $img) {
	unset($sel);
	if($prefs_posts_default_banner == $img) {
		$sel = "selected";
	}
	echo "<option $sel value='$img'>$img</option>";
}
				
echo '</select>';
				
echo '</div>';
echo '</fieldset>';




/* URL and Permalinks */

echo '<fieldset>';
echo '<legend>URL</legend>';

if($prefs_posts_url_pattern == "by_date") {
	$select_modus_date = "checked";
} else {
	$select_modus_title = "checked";
}

echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_posts_url_pattern" value="by_date" id="pattern_date" '.$select_modus_date.'>
				<label for="pattern_date">' . $lang['url_by_date'] . '</label>
	 		</div>';
echo '<div class="form-check">
				<input class="form-check-input" type="radio" name="prefs_posts_url_pattern" value="by_filename" id="pattern_title" '.$select_modus_title.'>
				<label for="pattern_title">' . $lang['url_by_title'] . '</label>
	 		</div>';

echo '</fieldset>';


/* products */

echo '<fieldset>';
echo '<legend>'.$lang['post_type_product'].'</legend>';

echo '<div class="row">';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['products_default_tax'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_default_tax" value="'.$prefs_posts_products_default_tax.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_product_tax_alt1'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_tax_alt1" value="'.$prefs_posts_products_tax_alt1.'">
			</div>';
echo '</div>';
echo '<div class="col">';
echo '<div class="form-group">
				<label>' . $lang['label_product_tax_alt2'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_tax_alt2" value="'.$prefs_posts_products_tax_alt2.'">
			</div>';
echo '</div>';
echo '</div>';
			

echo '<div class="form-group">
				<label>' . $lang['products_default_currency'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_products_default_currency" value="'.$prefs_posts_products_default_currency.'">
			</div>';
echo'</fieldset>';



/* events */

echo '<fieldset>';
echo '<legend>'.$lang['post_type_event'].'</legend>';
echo '<div class="form-group">
				<label>' . $lang['label_event_time_offset'] . '</label>
				<input type="text" class="form-control" name="prefs_posts_event_time_offset" value="'.$prefs_posts_event_time_offset.'">
				<small class="form-text text-muted">'.$lang['event_time_offset_help_text'].'</small>
			</div>';
echo'</fieldset>';


















echo '<input type="submit" class="btn btn-save" name="update_posts" value="'.$lang['update'].'">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

echo '</form>';


?>