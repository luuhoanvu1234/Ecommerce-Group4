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

if (not_num($_GET['id'])) {
	hs_redirect('/');
}

$order_id           = intval($_GET['id']);
$hs['pn']           = 'customer_orders';
$hs['child_pn']     = 'order_details';
$hs['page_title']   = hs_translate('Order details - {%name%}',array('name' => $hs['config']['name']));
$hs['page_desc']    = $hs['config']['description'];
$hs['page_kw']      = $hs['config']['keywords'];
$hs['order_data']   = hs_get_customer_order_details($order_id);

if (not_empty($hs['order_data'])) {
	$hs['order_data']['is_canceled']  = hs_order_is_canceled($hs['order_data']['id']);
	$hs['order_data']['is_suspended'] = in_array($hs['order_data']['status'], array('canceled','expired','returned','failed'));
	$hs['site_content']               = hs_loadpage("order_details/content");
}

else{
	hs_redirect('404');
}