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

function hs_notify($data = array(),$rm_duplicate = false) {
	global $db,$hs,$me;
	if (empty($hs['is_logged'])) {
		return false;
	}

	if ($rm_duplicate) {
		$duplicate         = array(
			'notifier_id'  => $data['notifier_id'],
			'recipient_id' => $data['recipient_id'],
			'subject'      => $data['subject'],
		);

		foreach ($duplicate as $key => $val) {
			$db->where($key,$val);
		}

		$db->delete(T_NOTIFS);
	}

	$insert_id = $db->insert(T_NOTIFS,$data);
	return $insert_id;
}

function hs_get_unseen_notifications() {
	global $db,$hs,$me;
	if (empty($hs['is_logged'])) {
		return 0;
	}

	$user_id = $me['id'];
	$db      = $db->where('recipient_id',$user_id);
	$db      = $db->where('status','0');
	$total   = $db->getValue(T_NOTIFS,"COUNT(*)");
	return ((is_numeric($total)) ? $total : 0);
}

function hs_get_notifications($args = array()) {
	global $db,$hs;
	$args              =  (is_array($args)) ? $args : array();
	$options           =  array(
        "offset"       => null,
        "limit"        => 10,
        "recipient_id" => null,
    );

    $args              =  array_merge($options, $args);
    $offset            =  $args['offset'];
    $limit             =  $args['limit'];
    $recipient_id      =  $args['recipient_id'];
    $data              =  array();
    $sql               =  hs_sqltepmlate('notifs/hex_get_rt_notifications',array(
        'offset'       => $offset,
        'limit'        => $limit,
        'recipient_id' => $recipient_id,
        't_users'      => T_USERS,
        't_notifs'     => T_NOTIFS,
    ));

    $notifs = $db->rawQuery($sql);

    if (hs_queryset($notifs) == true) {
    	foreach ($notifs as $notif_data) {
    		$notif_data->notifier_avatar = hs_get_media($notif_data->notifier_avatar);
    		$notif_data->time            = date('d M, y',$notif_data->time);
    		$notif_data->message         = hs_translate($notif_data->message);
    	}

        $data = hs_o2array($notifs); 
    }

    return $data;
}