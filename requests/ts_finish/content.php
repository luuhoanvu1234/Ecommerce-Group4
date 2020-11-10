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

else if(empty($hs['temp_session'])) {
	hs_redirect('404');
}

else {
    $session_id   = $hs['temp_session']['login_hash'];
    $temp_sess_id = $hs['temp_session']['id'];
    $db           = $db->where('user_id', $me['id']);
    $db           = $db->where('session_id', hs_secure($session_id));
    $delete_ts    = $db->delete(T_SESSIONS);
    $db           = $db->where('id', $temp_sess_id);
    $delete_as    = $db->delete(T_ADMIN_SESSIONS);
    $admin_id     = $hs['temp_session']['admin_id'];
	$new_sess_id  = hs_create_user_session($admin_id);


	echo hs_server_message("wait/temp_session_finish");
    hs_redirect_after('merchant_panel/dashboard',5);
}