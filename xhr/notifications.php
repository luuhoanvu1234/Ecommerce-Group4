<?php
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)									@
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ HighExpress - The Ultimate Modern Marketplace Platform                  @
# @ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
# @*************************************************************************@

if ($action == 'get_unseen') {
	$unseen_nots    = hs_get_unseen_notifications();
	$data['status'] = 404;
	if (is_number($unseen_nots)) {
		$data['status'] = 200;
		$data['count']  = $unseen_nots;
	}
}

else if($action == 'get_notifications') {
	$note_as_seen      =  array();
	$html_arr          =  array();
	$notifs            =  hs_get_notifications(array(
		'limit'        => 50,
		'recipient_id' => $me['id'],
	));

	if (not_empty($notifs)) {
		foreach ($notifs as $hs['notif_data']) {
			array_push($html_arr,hs_loadpage('notifications/includes/rt_notifs_list'));
			if ($hs['notif_data']['status'] == '0') {
				array_push($note_as_seen, $hs['notif_data']['id']);
			}
		}

		$data['status'] = 200;
		$data['html']   = implode('', $html_arr);

		if (not_empty($note_as_seen)) {
			$db = $db->where('id',$note_as_seen,'IN');
			$up = $db->update(T_NOTIFS,array('status' => '1'));
		}
	}
	else {
		$html           = hs_loadpage('notifications/includes/no_rt_notifications');
		$data['status'] = 404;
		$data['html']   = $html;
	}
}

else if($action == 'delete_notifs') {
	if (not_empty($_POST['notifs']) && is_array($_POST['notifs']) && hs_all($_POST['notifs'],'numeric')) {
		$notifs          = array_values($_POST['notifs']);
		$db              = $db->where('id',$notifs,"IN");
		$db              = $db->where('recipient_id',$me['id']);
		$delete          = $db->delete(T_NOTIFS);
		$data['status']  = 200;
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Error: Invalid request data. Please check your details";
	}
}

else if($action == 'load_notifications') {
	if (is_number($_GET['offset'])) {
		$data['status']    = 404;
		$list_items        = array();
		$notifs            = hs_get_notifications(array(
			'offset'       => intval($_GET['offset']),
			'recipient_id' => $me['id'],
			'limit'        => 20
		));

		if (not_empty($notifs)) {
			foreach ($notifs as $hs['notif']) {
				array_push($list_items, hs_loadpage('notifications/includes/st_notifs_list'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $list_items);
		}
	}
}
?>