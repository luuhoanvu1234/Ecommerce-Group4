<?php
# @*************************************************************************@
# @ @author: Mansur Altamirov (Mansur_TL)									@
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ HighExpress - The Ultimate Modern Marketplace Platform                  @
# @ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
# @*************************************************************************@

if (empty($hs['is_logged'])) {
	hs_redirect('auth');
} 

$hs['page_title']    = hs_translate('Checkout - {%name%}',array('name' => $hs['config']['name']));
$hs['page_desc']     = $hs['config']['description'];
$hs['page_kw']       = $hs['config']['keywords'];
$hs['pn']            = 'checkout';
$hs['header_st']     = true;
$my_id               = $me['id'];
$total_items         = hs_basket_items_total($me['id']);
$hs['basket']        = hs_get_basket_products();
$hs['header_st']     = ((not_empty($hs['basket'])) ? true : false);
$hs['prepaym_prods'] = 0;
$hs['codpaym_prods'] = 0;
$active_items        = array_map(function($item) {
	return $item['id'];
}, $hs['basket']);

if (not_empty($hs['basket'])) {
	foreach ($hs['basket'] as $row) {
		if ($row['payment_method'] == 'pre_payments') {
			$hs['prepaym_prods'] += 1;
		}

		else if($row['payment_method'] == 'cod_payments') {
			$hs['codpaym_prods'] += 1;
		}
	}
}

$unset_payment_data =  hs_session_unset('payment_data');
$hs['addresses']    =  hs_get_user_addresses($my_id);
$hs['deliv_addr']   =  implode(",",array($me['address']['country'],$me['address']['city']));
$hs['site_content'] =  hs_loadpage('checkout/content',array(
	'total_items'   => $total_items
)); 
