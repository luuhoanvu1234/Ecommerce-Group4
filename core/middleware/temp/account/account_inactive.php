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

$hs['resend_ace_status'] = hs_session('resend_ace_status');

if ($hs['resend_ace_status'] == true) {
	hs_session_unset('resend_ace_status');
}

echo hs_server_message("stop/account_inactive");
exit();