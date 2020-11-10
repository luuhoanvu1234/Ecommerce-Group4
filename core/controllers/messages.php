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

function hs_create_conversations($user_one = null,$user_two = null) {
	global $db,$hs;
	if (empty($hs['is_logged']) || not_num($user_one) || not_num($user_two)) {
		return false;
	}

	$time     = time();
	$t_convs  = T_CONVERSATIONS;
	$db       = $db->where('user_one',$user_one);
	$db       = $db->where('user_two',$user_two);
	$convers1 = $db->getValue($t_convs,"COUNT(id)");

	if (empty($convers1)) {
		$db->insert($t_convs,array(
			'user_one' => $user_one,
			'user_two' => $user_two,
			'time'     => $time
		));

		$db       = $db->where('user_one',$user_two);
		$db       = $db->where('user_two',$user_one);
		$convers2 = $db->getValue($t_convs,"COUNT(id)");
		if (empty($convers2)) {
			$db->insert($t_convs,array(
				'user_two' => $user_one,
				'user_one' => $user_two,
				'time'     => $time
			));
		}
		else {
			$db = $db->where('user_one',$user_two);
			$db = $db->where('user_two',$user_one);
			$bl = $db->update($t_convs,array('time' => $time));
		}
	}
	else{
		$db       = $db->where('user_one',$user_one);
		$db       = $db->where('user_two',$user_two);
		$bl       = $db->update($t_convs,array('time' => $time));
		$db       = $db->where('user_one',$user_two);
		$db       = $db->where('user_two',$user_one);
		$convers2 = $db->getValue($t_convs,"COUNT(id)");
		if (empty($convers2)) {
			$db->insert($t_convs,array(
				'user_two' => $user_one,
				'user_one' => $user_two,
				'time'     => $time
			));
		}
		else{
			$db = $db->where('user_one',$user_two);
			$db = $db->where('user_two',$user_one);
			$bl = $db->update($t_convs,array('time' => $time));
		}
	}
}

function hs_send_message($data = null) {
	global $db,$hs;
	if (empty_array($data)) {
		return false;
	}

	$msg_id   = $db->insert(T_MESSAGES,$data);
	$sent_by  = $data['sent_by'];
	$sent_to  = $data['sent_to'];

	if (is_number($msg_id)) {
		hs_create_conversations($sent_by,$sent_to);
	}

	return $msg_id;
}

function hs_message_data($msg_id = null) {
	global $db,$hs;
	if (not_num($msg_id)) {
		return false;
	}

	$db    = $db->where('id',$msg_id);	
	$query = $db->getOne(T_MESSAGES);
	$msg   = null;

	if (hs_queryset($query,'object')) {
		if (not_empty($query->media_file)) {
			$query->media_file = hs_get_media($query->media_file);
		}

		$query->time    = date('d M, Y h:m',$query->time);
		$query->message = hs_linkify_urls($query->message);
		$msg            = hs_o2array($query);
	}
	
	return $msg;
}

function hs_get_conversation($args = array()){
	global $db,$hs,$me;
	$args    = (is_array($args)) ? $args : array();
	$options = array(
        "sent_by"   => false,
        "sent_to"   => false,
        "new"       => false,
        "order_in"  => "DESC",
        "order_out" => "DESC",
        "offset"    => false,
        "offset_to" => false,
        "limit"     => 10,
    );

    $args      = array_merge($options, $args);
    $sent_by   = $args['sent_by'];
    $sent_to   = $args['sent_to'];
    $new       = $args['new'];
    $offset    = $args['offset'];
    $limit     = $args['limit'];
    $order_in  = $args['order_in'];
    $order_out = $args['order_out'];
    $offset_to = $args['offset_to'];
    $sql       = hs_sqltepmlate('messages/hex_get_conversation',array(
        'offset'    => $offset,
        'limit'     => $limit,
        'sent_by'   => $sent_by,
        'sent_to'   => $sent_to,
        'offset_to' => $offset_to,
        'new'       => $new,
        'order_in'  => $order_in,
        'order_out' => $order_out,
        't_users'   => T_USERS,
        't_msgs'    => T_MESSAGES,
	));

    $data     = array();
    $update   = array();
	$messages = $db->rawQuery($sql);

	if (hs_queryset($messages)) {
		foreach ($messages as $message) {
			$message->side    = (($message->sent_by == $me['id']) ? 'right' : 'left');
			$message->owner   = (($message->owner == $me['id']) ? true : false);
			$message->time    = date('d M, Y h:m',$message->time);
			$message->message = hs_linkify_urls($message->message);

			if (not_empty($message->media_file)) {
				$message->media_file = hs_get_media($message->media_file);
			}

			if (empty($message->owner) && empty($message->seen)) {
				$update[] = $message->id;
			}
		}

		if (not_empty($update)) {
			$db = $db->where('id',$update,"IN");
			$up = $db->update(T_MESSAGES,array(
				'seen' => time()
			));
		}

		$data = hs_o2array($messages);
	}

	return $data;
}

function hs_get_chats($args = array()){
	global $db,$hs,$me;
	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
        "user_id"   => 0,
        "offset"    => false,
        "limit"     => 1000,
    );

    $args            =  array_merge($options, $args);
    $user_id         =  $args['user_id'];
    $offset          =  $args['offset'];
    $limit           =  $args['limit'];
	$sql             =  hs_sqltepmlate('messages/hex_get_chats_history',array(
		't_messages' => T_MESSAGES,
		't_convs'    => T_CONVERSATIONS,
		't_users'    => T_USERS,
		'offset'     => $offset,
		'user_id'    => $user_id,
		'limit'      => $limit
	));

	$data  = array();
	$chats = $db->rawQuery($sql);

	if (hs_queryset($chats)) {
		foreach ($chats as $chat) {
			$chat->avatar       = hs_get_media($chat->avatar);
			$chat->time         = date("d M, Y",$chat->time);
			$chat->chat_url     = hs_link(hs_str_form("messages/{0}",array($chat->username)));
			$chat->last_message = hs_croptxt($chat->last_message,25,'..');
			if (empty($chat->new_messages)) {
				$chat->new_messages = '';
			}
		}

		$data = hs_o2array($chats);
	}
	
	return $data;
}