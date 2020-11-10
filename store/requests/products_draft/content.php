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

$hs['pn']           =  $store_page;
$hs['page_title']   =  hs_translate('Draft - {%name%}',array('name' => $hs['me']['name']));
$hs['page_desc']    =  $hs['config']['description'];
$hs['page_kw']      =  $hs['config']['keywords'];
$hs['my_prods']     =  hs_get_draft_products(array(
	'limit'         => 7
));

$hs['my_draft_tot'] = hs_get_draft_products_total($me['id']);
$hs['site_content'] = hs_loadpage("products_draft/content");