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

require_once('libs/stripe/vendor/autoload.php');
$stripe_conf = array(
 	"secret_key"      =>  $hs['config']['stripe_api_key'],
 	"publishable_key" =>  $hs['config']['stripe_api_pass']
);

\Stripe\Stripe::setApiKey($stripe_conf['publishable_key']); 
