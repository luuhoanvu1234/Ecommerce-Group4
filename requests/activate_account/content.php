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
	hs_redirect("auth/login");
}

elseif ($hs['is_logged'] && $hs['me']['active'] == '1') {
	hs_redirect("/");
}

else {
	echo hs_server_message("stop/account_inactive");
    exit();
}