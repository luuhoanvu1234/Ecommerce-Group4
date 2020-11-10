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

$hs['pn']             = $store_page;
$hs['page_title']     = hs_translate('Account settings - {%name%}',array('name' => $hs['me']['name']));
$hs['page_desc']      = $hs['config']['description'];
$hs['page_kw']        = $hs['config']['keywords'];
$hs['del_req_exists'] = hs_where_exists(T_ACC_DEL_REQS,array('user_id' => $me['id']));
$me['verif_status']   = hs_get_verif_status($me['id']);
$hs['site_content']   = hs_loadpage("main_settings/content");