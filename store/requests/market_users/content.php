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

if(empty($hs['is_admin'])) {
	hs_redirect('/');
}

$hs['pn']           = $store_page;
$hs['page_title']   = hs_translate('Market users - {%name%}',array('name' => $hs['config']['name']));
$hs['page_desc']    = $hs['config']['description'];
$hs['page_kw']      = $hs['config']['keywords'];
$hs['total_users']  = hs_ap_info_get_total_users();
$hs['market_users'] = hs_ap_info_get_users(array('limit' => 7));
$hs['site_content'] = hs_loadpage("market_users/content");