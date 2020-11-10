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

$hs['pn']           =  $store_page;
$hs['page_title']   =  hs_translate('Customer orders');
$hs['page_desc']    =  $hs['config']['description'];
$hs['page_kw']      =  $hs['config']['keywords'];
$hs['orders_list']  =  hs_get_customer_orders(array(
	'seller_id'     => $me['id'],
	'limit'         => 10
));

$hs['site_content'] = hs_loadpage("customer_orders/content");