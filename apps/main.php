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

if ($page == 'ts_finish') {
	include_once("requests/ts_finish/content.php");
}

else if (not_empty($hs['temp_session'])) {
	echo hs_server_message('stop/temp_session_lock'); exit();
}

else {
	if (file_exists(sprintf("requests/%s/content.php",$page))) {
		include_once(sprintf("requests/%s/content.php",$page));

		if (empty($hs['site_content'])) {
			include_once('requests/404/content.php');
		}
	} 

	else {
		hs_redirect('404');
	}
}

$hs['announcements'] =  ((empty($hs['is_logged'])) ? array() : hs_get_announcement($me['id']));
$hs['site_content']  =  $hs['site_content'];
$content             =  hs_loadpage('mp_container',array(
	'theme_url'      => $hs['theme_url'],
	'site_url'       => $hs['theme_url'],
)); echo $content;exit();
