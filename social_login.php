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

require_once("core/__init.php");

$soc_nets = array('Google','Facebook','Twitter');
$provider = false;

if (not_empty($_GET['provider']) && in_array($_GET['provider'], $soc_nets)) {
    $provider = strval($_GET['provider']);
}

require_once('core/libs/social_login/vendor/autoload.php');
require_once('core/libs/social_login/soc_login_config.php');

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

if ($provider) {
    try {
        $hybridauth    = new Hybridauth($social_login_config);
        $auth_provider = $hybridauth->authenticate($provider);
        $tokens        = $auth_provider->getAccessToken();
        $user_profile  = $auth_provider->getUserProfile();

        if ($user_profile && isset($user_profile->identifier)) {
            $fname      = fetch_or_get($user_profile->firstName,time());
            $lname      = fetch_or_get($user_profile->lastName,time());
            $prov_email = "mail.com";
            $prov_prefx = "xx_";

            if ($provider == 'Google') {
                $prov_email = 'google.com';
                $prov_prefx = 'go_';
            } 

            else if ($provider == 'Facebook') {
                $prov_email = 'facebook.com';
                $prov_prefx = 'fa_';
            } 

            else if ($provider == 'Twitter') {
                $prov_email = 'twitter.com';
                $prov_prefx = 'tw_';
            }

            $user_name  = sprintf('%s@%s',$prov_prefx, $user_profile->identifier);
            $user_email = sprintf('%s@%s',$user_name,$prov_email);

            if (not_empty($user_profile->email)) {
                $user_email = $user_profile->email;
            }
            if (hs_email_exists($user_email) === true) {
            	$db->returnType = 'Array';         
            	$db             = $db->where('email', $user_email);
            	$_user          = $db->getOne(T_USERS);

                hs_create_user_session($_user['id'],'web');
                hs_redirect('/');
            } 

            else {

            	$email_code        =  sha1(time() + rand(111,999));
		        $password_hashed   =  password_hash(time(), PASSWORD_DEFAULT);
		        $user_ip           =  get_ip();
		        $user_ip           =  ((filter_var($user_ip, FILTER_VALIDATE_IP) == true) ? $user_ip : '0.0.0.0');
		        $insert_data       =  array(
		            'fname'        => hs_secure(hs_txtslug($fname)),
		            'lname'        => hs_secure(hs_txtslug($lname)),
		            'password'     => $password_hashed,
		            'email'        => $user_email,
		            'active'       => '1',
		            'em_code'      => $email_code,
		            'last_active'  => time(),
		            'joined'       => time(),
		            'ipv4_address' => $user_ip,
		            'language'     => $hs['config']['language'],
		        ); $user_id        =  $db->insert(T_USERS, $insert_data);

		        if (is_number($user_id)) {
		        	hs_create_user_session($user_id,'web');
		            $fname         = hs_croptxt(md5(time()),9);
		            $lname         = '_u';
		            $social_url    = fetch_or_get($user_profile->profileURL,'');
		            $avatar        = fetch_or_get($user_profile->photoURL,null);
		            $up_data       = array(
		            	'username' => (sprintf("%s_%s@%d",$fname,$lname,$user_id)),
		            );

		            if ($provider == 'Google') {
	                    $up_data['bio']         = hs_secure(fetch_or_get($user_profile->description));
	                    $up_data['google_plus'] = $social_url;
	                }

	                else if ($provider == 'Facebook') {
	                    $up_data['bio']      = hs_secure(fetch_or_get($user_profile->description));
	                    $up_data['facebook'] = $social_url;
	                }

	                else if ($provider == 'Twitter') {
	                    $up_data['bio']     = hs_secure(fetch_or_get($user_profile->description));
	                    $up_data['twitter'] = $social_url;
	                }

	                if (is_url($avatar)) {
	                	$avatar            = hs_import_image($avatar);
	                	$avatar            = ((file_exists($avatar) == true) ? $avatar : 'upload/users/user-avatar.png');
	                	$up_data['avatar'] = $avatar;
	                }

		            hs_update_user_data($user_id,$up_data);
		            
		            require_once('core/controllers/event_handlers/on_user_signup.php');

		            hs_redirect('/');
		        }
            }
        }
    }
    catch (Exception $e) {
        exit($e->getMessage());
    }
} 

else {
    hs_redirect("auth");
}
?>