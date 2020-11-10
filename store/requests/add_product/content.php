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

hs_purge_orphan_prods($me['id']);
$prod_id              =  $db->insert(T_PRODUCTS,array(
	'user_id'         => $me['id'],
	'activity_status' => 'orphan',
	'time'            => time()
));

if (is_numeric($prod_id)) {
	$upsert_type	    = hs_session("upsert_type","insert");
	$hs['pn']           = $store_page;
	$hs['page_title']   = hs_translate('Create a new product');
	$hs['page_desc']    = $hs['config']['description'];
	$hs['page_kw']      = $hs['config']['keywords'];
	$hs['prod_id']      = $prod_id;
	$hs['site_content'] = hs_loadpage("upsert_product/upsert",array(
		"prod_id"       => $hs['prod_id'],
	));

	hs_session('edit_product_data',array('prod_id' => $prod_id));
} 

else{
	echo "Can not create a product. try again later";
	exit;
}