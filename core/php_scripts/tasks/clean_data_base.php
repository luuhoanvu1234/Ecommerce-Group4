<?php 
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)                                    @
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ HighExpress - The Ultimate Modern Marketplace Platform                  @
# @ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
# @*************************************************************************@

# 1) Delete orphan products from Data base

$time_5d_ago    = strtotime('-2days',$curr_time);
$db->returnType = 'Array';
$db             = $db->where('activity_status',array('inactive','orphan'), 'IN');
$db             = $db->where('time',$time_5d_ago, '<=');
$db->returnType = 'Array';
$orphan_rows    = $db->get(T_PRODUCTS);
$selected_rows  = array();

if (hs_queryset($orphan_rows)) {
	foreach ($orphan_rows as $prod_data) {
		$db->returnType  = 'Array';
		$prod_id         = $prod_data['id'];
		$prod_media      = $db->where('prod_id',$prod_id)->get(T_PROD_MEDIA);
		$selected_rows[] = $prod_data['id'];

		#Delete product media files
		if (hs_queryset($prod_media)) {
			foreach ($prod_media as $file_data) {
				hs_delete_image($file_data['src']);
				hs_delete_image($file_data['thumb']);
			}

			$db = $db->where('prod_id',$prod_id);
			$rm = $db->delete(T_PROD_MEDIA);
		}

		hs_delete_image($prod_data['thumb']);
		hs_delete_image($prod_data['poster']);


		$db->returnType = 'Array';
		$db             = $db->where('prod_id',$prod_data['id']);
		$orphan_rows    = $db->get(T_PROD_VARS);

		if (hs_queryset($orphan_rows)) {
			foreach ($orphan_rows as $var_data) {
				hs_delete_image($var_data['col_img']);
				hs_delete_image($var_data['col_thumb']);
			}

			$db = $db->where('prod_id',$prod_data['id']);
			$rm = $db->delete(T_PROD_VARS);
		}
	}

	$db = $db->where('id',$selected_rows, 'IN');
	$rm = $db->delete(T_PRODUCTS);
}

# 2) Delete orphan product variations from Data base

$db->returnType = 'Array';
$db             = $db->where('activity_status',array('inactive','orphan'), 'IN');
$db             = $db->where('time',$time_5d_ago, '<=');
$orphan_rows    = $db->get(T_PROD_VARS);
$selected_rows  = array();

if (hs_queryset($orphan_rows)) {
	foreach ($orphan_rows as $var_data) {
		hs_delete_image($var_data['col_img']);
		hs_delete_image($var_data['col_thumb']);

		array_push($selected_rows, $var_data['id']);
	}

	$db = $db->where('id',$selected_rows, 'IN');
	$rm = $db->delete(T_PROD_VARS);
}

# 3) Delete inactive (not activated after registration) user accounts
$db = $db->where('active', '0');
$db = $db->where('admin', '0');
$rm = $db->delete(T_USERS);

# 3) Delete all local image files if media storage mode on Amazon servers is enabled
if ($hs['config']['as3_storage'] == 'on') {
	$db->returnType = 'Array';
	$db             = $db->where('time',$time_5d_ago, '<=');
	$temp_media     = $db->get(T_TEMP_MEDIA);
	$selected_rows  = array();

	if (not_empty($temp_media)) {
		foreach ($temp_media as $temp_media_data) {
			if (file_exists($temp_media_data['file_path'])) {
				try {
					@unlink($temp_media_data['file_path']);
					array_push($selected_rows, $temp_media_data['id']);
				} catch (Exception $e) { /* pass */ }
			}
		}

		if (not_empty($selected_rows)) {
			$db = $db->where('id',$selected_rows,'IN');
			$rm = $db->delete(T_TEMP_MEDIA);
		}
	}
} 

