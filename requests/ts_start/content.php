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

else if(empty($hs['is_admin']) || empty($_GET['uname'])) {
	hs_redirect('/');
}

else if($me['username'] == strval($_GET['uname'])) {
	hs_redirect('/');
}

else {
	$uname          = strval($_GET['uname']);
	$uname          = hs_secure($uname);
    $db             = $db->where('username',$uname);
    $udata          = $db->getOne(T_USERS,'id');
    $udata          = ((hs_queryset($udata,'object')) ? hs_o2array($udata) : array());

	if (not_empty($udata)) {
		if (not_empty($_SESSION['user_id'])) {
	        $db->where('session_id', hs_secure($_SESSION['user_id']))->delete(T_SESSIONS);
	    }

	    if (not_empty($_COOKIE['user_id'])) {
	        $db->where('session_id', hs_secure($_COOKIE['user_id']))->delete(T_SESSIONS);
	    }

		$udata_id             = $udata['id'];
		$new_sess_id          = hs_create_user_session($udata_id);

		$temp_session         = array(
			'login_sess_id'   => $new_sess_id,
			'admin_id'        => $me['id'],
			'time'            => time(),
		); $db->insert(T_ADMIN_SESSIONS,$temp_session);

		echo hs_server_message("wait/temp_session_start");
        hs_redirect_after('merchant_panel/dashboard',5);
	}

	else {
		$insert_data    = array(
			'user_id'   => $me['id'],
			'title'     => 'Failed to create temporary session.!',
			'message'   => 'Could not find a user with this name, please check your details or try again later!',
			'type'      => 'success',
			'static'    => 'Y',
			'time'      => time()
		); $db->insert(T_ANNOUNC,$insert_data);

		hs_redirect('/');
	}
}