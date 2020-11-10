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

require_once("core/__init.php");

$page = (isset($_GET['page'])) ? $_GET['page'] : 'home';
if (not_empty($_SERVER['HTTP_HOST'])) {
    $server_scheme = fetch_or_get($_SERVER["HTTPS"]);
    $protocol      = ($server_scheme == "on") ? "https://" : "http://";
    $http_url      = ($protocol . $_SERVER['HTTP_HOST']);
    $url           = parse_url($site_url);

    if (not_empty($url)) {
        if ($url['scheme'] == 'http') {
            if ($http_url != ('http://' . $url['host'])) {
                hs_redirect('/');
            }
        } 

        else {
            if ($http_url != ('https://' . $url['host'])) {
                hs_redirect('/');
            }
        }
    }
}

switch ($page) {
	case 'store':
		require_once("apps/store.php");
		break;
	default:
		require_once("apps/main.php");
		break;
}
