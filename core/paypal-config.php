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

require_once('libs/paypal/vendor/autoload.php');
$paypal_conf = array(
 	"secret_key"      =>  $hs['config']['paypal_api_key'],
 	"publishable_key" =>  $hs['config']['paypal_api_pass']
);

$paypal = new \PayPal\Rest\ApiContext(
	new \PayPal\Auth\OAuthTokenCredential(
		$hs['config']['paypal_api_key'],
		$hs['config']['paypal_api_pass']
	)
);

$paypal->setConfig(
    array(
    	$hs['config']['paypal_mode']
    )
);

