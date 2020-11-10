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
	hs_redirect('404');
}

$hs['all_langs']    = hs_get_languages('all');
$hs['all_langs']    = hs_get_langs_usage($hs['all_langs']);
$hs['pn']           = $store_page;
$hs['page_title']   = hs_translate('Manage languages');
$hs['page_desc']    = $hs['config']['description'];
$hs['page_kw']      = $hs['config']['keywords'];
$hs['site_content'] = hs_loadpage("site_languages/content");