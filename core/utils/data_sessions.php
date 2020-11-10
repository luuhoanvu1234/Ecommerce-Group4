<?php 
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)                                    @
# @ @author_url 1: hhttps://www.highexpress-store.ru.com                    @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ HighExpress - The Ultimate Modern Marketplace Platform                  @
# @ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
# @*************************************************************************@

function hs_data_session_get() {
	global $db,$me,$hs;
	if (empty($hs['is_logged'])) {
		return false;
	}

	$uid  = intval($me['id']);
	$db   = $db->where('user_id',$uid);
	$ses  = $db->getOne(T_DATA_SESSIONS);
	$data = array();



	if (hs_queryset($ses,'object')) {
		$json = trim($ses->json);
		$data = json($json);
		$data = (is_array($data)) ? $data : array();
	}

	else {
		$json   = json(array(),true);
		$insert = array(
			'user_id' => $uid,
			'json'    => $json,
			'time'    => time()
		);

		$insert = $db->insert(T_DATA_SESSIONS,$insert);
		$data   = array();
	}

	return $data;
}

function hs_data_session_set($data = array()) {
	global $db,$me,$hs;
	if (empty($hs['is_logged']) || is_array($data) != true) {
		return false;
	}

	$uid  = intval($me['id']);
	$db   = $db->where('user_id',$uid);
	$ses  = $db->getOne(T_DATA_SESSIONS);

	if (empty($ses)) {
		$json   = json($data,true);
		$insert = array(
			'user_id' => $uid,
			'json'    => $json,
			'time'    => time()
		);

		$insert = $db->insert(T_DATA_SESSIONS,$insert);
	}

	else {
		$json   = json($data,true);
		$db     = $db->where('user_id',$uid);
		$update = $db->update(T_DATA_SESSIONS,array('json' => $json));
	}

	return true;
}
?>