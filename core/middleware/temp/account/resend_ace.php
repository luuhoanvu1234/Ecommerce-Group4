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

hs_session('resend_ace_status',true);

$send_email_data   = array(
	'from_email'   => $hs['config']['email'],
	'from_name'    => $hs['config']['name'],
	'to_email'     => $me['email'],
	'to_name'      => $me['name'],
	'subject'      => hs_translate("Activate your account"),
	'charSet'      => 'UTF-8',
	'is_html'      => true,
	'message_body' => hs_loadpage('emails/activate_account', array(
		"name"     => $me['name'],
		"em_code"  => $me['em_code'],
	)),
); 

hs_send_mail($send_email_data);

hs_redirect('/');
