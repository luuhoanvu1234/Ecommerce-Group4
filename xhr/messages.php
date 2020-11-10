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

if (empty($hs['is_logged'])) {
	$data['status'] = 400;
	$data['error']  = 'Invalid access token';
}

else if ($action == 'send') {
	$send_to = hs_session('interloc_user_id');
	if (is_number($send_to)) {
		if (not_empty($_FILES['image']) && not_empty($_FILES['image']['tmp_name'])) {	
            $file_info    =  array(
                'file'    => $_FILES['image']['tmp_name'],
                'size'    => $_FILES['image']['size'],
                'name'    => $_FILES['image']['name'],
                'type'    => $_FILES['image']['type'],
                'crop'    => array('width' => 800, 'height' => 600),
                'allowed' => 'jpg,png,jpeg,gif'
            );

            $file_upload = hs_upload($file_info);

            if (not_empty($file_upload['filename'])) {
                $filename        =  $file_upload['filename'];               
                $insert_data     =  array(
					'sent_by'    => $me['id'],
					'sent_to'    => $send_to,
					'owner'      => $me['id'],
					'message'    => 'Image',
					'media_file' => $filename,
					'media_type' => 'image',
					'seen'       => 0,
					'time'       => time(),
				);

				$message = hs_send_message($insert_data);
				if (not_empty($message)) {
					$data['status'] = 200;
				}
				else {
					$data['status']  = 500;
					$data['message'] = 'Something went wrong. Please try again later!';
				}
            } 
            else{
            	$data['status']  = 500;
            	$data['message'] = "Error found while processing your request, please try again later.";
            }  
		}

		else if (not_empty($_POST['message']) && len_between($_POST['message'],1,3000)) {
			$insert_data  =  array(
				'sent_by' => $me['id'],
				'sent_to' => $send_to,
				'owner'   => $me['id'],
				'message' => hs_secure($_POST['message']),
				'seen'    => 0,
				'time'    => time(),
			);

			$message = hs_send_message($insert_data);
			if (not_empty($message)) {
				$data['status'] = 200;
			}
			else {
				$data['status']  = 500;
				$data['message'] = 'Something went wrong. Please try again later!';
			}
		}
		else {
			$data['status']  = 400;
			$data['message'] = 'Error: 404 Invalid request data!';
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = 'Error: 404 Invalid request data!';
	}
}

else if($action == 'get_messages') {
	$send_to             = hs_session('interloc_user_id');
	$hs['interloc_data'] = hs_user_data($send_to);
	if (is_number($send_to) && not_empty($hs['interloc_data'])) {
		$hs['interloc_data'] =  hs_o2array($hs['interloc_data']);
		$data['status']      =  404;
		$html                =  array();
		$offset              =  ((is_number($_GET['offset'])) ? intval($_GET['offset']) : false);
		$messages            =  hs_get_conversation(array(
			'sent_by'        => $me['id'],
			'sent_to'        => $send_to,
			'limit'          => 100,
			'offset'         => $offset,
			'order_in'       => 'DESC',
			'order_out'      => 'ASC',
			'offset_to'      => 'gt'
		));

		if (not_empty($messages)) {
			foreach ($messages as $hs['msg_data']) {
				array_push($html, hs_loadpage('messages/includes/message'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html);
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = 'Error: 404 Invalid request data!';
	}
}

else if($action == 'get_old_messages') {
	$send_to             = hs_session('interloc_user_id');
	$hs['interloc_data'] = hs_user_data($send_to);
	if (is_number($send_to) && not_empty($hs['interloc_data']) && is_number($_GET['offset'])) {
		$hs['interloc_data'] = hs_o2array($hs['interloc_data']);
		$data['status']      = 404;
		$html                = array();
		$offset              = intval($_GET['offset']);
		$messages            = hs_get_conversation(array(
			'sent_by'        => $me['id'],
			'sent_to'        => $send_to,
			'limit'          => 200,
			'offset'         => $offset,
			'offset_to'      => 'lt',
			'order_in'       => 'DESC',
			'order_out'      => 'DESC',
		));

		if (not_empty($messages)) {
			foreach ($messages as $hs['msg_data']) {
				array_push($html, hs_loadpage('messages/includes/message'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html);
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = 'Error: 404 Invalid request data!';
	}
}

else if($action == 'update_chats_history') {
	$hs['chats_history'] = hs_get_chats(array('user_id' => $me['id']));
	$data['status']      = 404;
	$send_to             = hs_session('interloc_user_id');
	if (not_empty($hs['chats_history'])) {

		foreach ($hs['chats_history'] as $ind => $chat) {
			$hs['chats_history'][$ind]['active_class'] = '';
			if (not_empty($send_to) && ($send_to == $chat['user_id'])) {
				$hs['chats_history'][$ind]['active_class'] = 'active'; 
			}
		}

		$data['status'] = 200;
		$data['html']   = hs_loadpage('messages/includes/chats_history');
	}
}

else if($action == 'clear_chat') {
	$send_to = hs_session('interloc_user_id');
	if (not_empty($send_to)) {
		$db             = $db->where('sent_by',$me['id']);
		$db             = $db->where('sent_to',$send_to);
		$q1             = $db->update(T_MESSAGES,array('deleted_fs1' => 'Y'));
		$db             = $db->where('sent_to',$me['id']);
		$db             = $db->where('sent_by',$send_to);
		$q2             = $db->update(T_MESSAGES,array('deleted_fs2' => 'Y'));
		$data['status'] = (($q1 && $q2) ? 200 : 300);
	}
}

else if($action == 'delete_chat') {
	$send_to = hs_session('interloc_user_id');
	if (not_empty($send_to)) {
		$db             = $db->where('sent_by',$me['id']);
		$db             = $db->where('sent_to',$send_to);
		$q1             = $db->update(T_MESSAGES,array('deleted_fs1' => 'Y'));
		$db             = $db->where('sent_to',$me['id']);
		$db             = $db->where('sent_by',$send_to);
		$q2             = $db->update(T_MESSAGES,array('deleted_fs2' => 'Y'));
		$db             = $db->where('user_one',$me['id']);
		$db             = $db->where('user_two',$send_to);
		$q3             = $db->delete(T_CONVERSATIONS);
		$data['status'] = (($q1 && $q2 && $q3) ? 200 : 300);
	}
}

else if($action == 'delete_messages') {
	if (not_empty($_POST['messages'])) {
		if (is_array($_POST['messages']) && hs_all($_POST['messages'],'numeric')) {
			$messages       = array_values($_POST['messages']);
			$db             = $db->where('id',$messages,"IN");
			$db             = $db->where('sent_by',$me['id']);
			$q1             = $db->update(T_MESSAGES,array('deleted_fs1' => 'Y'));	
			$db             = $db->where('id',$messages,"IN");
			$db             = $db->where('sent_to',$me['id']);
			$q2             = $db->update(T_MESSAGES,array('deleted_fs2' => 'Y'));
			$data['status'] = (($q1 && $q2) ? 200 : 300);
		}
	}
}

else if($action == 'get_unseen') {
	$data['status'] = 404;
	$db    = $db->where('sent_to',$me['id']);
	$db    = $db->where('seen','0');
	$total = $db->getValue(T_MESSAGES,'COUNT(*)');
	if (not_empty($total)) {
		$data['status'] = 200;
		$data['count']  = $total;
	}
}
?>