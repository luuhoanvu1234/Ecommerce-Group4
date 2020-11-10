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

if (empty($hs['is_logged'])) {
	hs_redirect('auth');
}

$hs['page_title']    = hs_translate('Messages - {%name%}',array('name' => $hs['config']['name']));
$hs['page_desc']     = $hs['config']['description'];
$hs['page_kw']       = $hs['config']['keywords'];
$hs['header_st']     = true;
$hs['pn']            = 'messages';
$hs['interloc_name'] = ((not_empty($_GET['uname'])) ? hs_secure($_GET['uname']) : null);

if (not_empty($hs['interloc_name'])) {
	if ($hs['interloc_name'] != $me['username']) {
		$hs['interloc_data'] = hs_get_user_by_name($hs['interloc_name']);

		if (not_empty($hs['interloc_data'])) {
			$hs['interloc_data'] =  hs_o2array($hs['interloc_data']);
			if ($hs['interloc_data']['active'] == '1') {
				$hs['conversation']  =  hs_get_conversation(array(
					'sent_by'        => $me['id'],
					'sent_to'        => $hs['interloc_data']['id'],
					'limit'          => 10,
					'order_in'       => 'DESC',
					'order_out'      => 'ASC',
				));
				
				hs_session('interloc_user_id',$hs['interloc_data']['id'],true);
			}

			else {
				echo hs_server_message('stop/account_disabled');
				exit();
			}
		}
	}
}

$hs['chats_history'] = hs_get_chats(array('user_id' => $me['id']));

if (not_empty($hs['chats_history'])) {
	foreach ($hs['chats_history'] as $ind => $chat) {
		$hs['chats_history'][$ind]['active_class'] = '';
		if (not_empty($hs['interloc_data']) && ($hs['interloc_data']['id'] == $chat['user_id'])) {
			$hs['chats_history'][$ind]['active_class'] = 'active'; 
		}
	}
}

$hs['site_content']  = hs_loadpage('messages/content');