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

if (empty($_GET['id']) || is_numeric($_GET['id']) != true) {
	hs_redirect('404');
} 

elseif (hs_is_prodowner($me['id'],$_GET['id']) !== true) {
	hs_redirect('404');
}

$upsert_type	 = hs_session("upsert_type","edit");
$prod_id         = intval($_GET['id']);
$hs['prod_data'] = hs_product_form_data($prod_id);

if (not_empty($hs['prod_data'])) {
	$hs['pn']               =  $store_page;
	$hs['page_title']       =  hs_translate('Edit - {%name%}',array('name' => $hs['prod_data']['name']));
	$hs['page_desc']        =  $hs['config']['description'];
	$hs['page_kw']          =  $hs['config']['keywords'];
	$hs['prod_id']          =  $prod_id;

	$hs['site_content']     =  hs_loadpage("upsert_product/update",array(
		"prod_id"           => $hs['prod_id'],
		"prod_name"         => $hs['prod_data']['name'],
		"prod_desc"         => hs_br2nl($hs['prod_data']['description']),
		"prod_reg_price"    => $hs['prod_data']['reg_price'],
		"prod_sale_price"   => $hs['prod_data']['sale_price'],
		"prod_quantity"     => $hs['prod_data']['quantity'],
		"prod_origin"       => $hs['prod_data']['origin'],
		"prod_brand"        => $hs['prod_data']['brand'],
		"prod_model_number" => $hs['prod_data']['model_number'],
		"prod_weight"       => $hs['prod_data']['weight'],
		"prod_length"       => $hs['prod_data']['length'],
		"prod_width"        => $hs['prod_data']['width'],
		"prod_height"       => $hs['prod_data']['height'],
		"prod_poster"       => $hs['prod_data']['poster'],
		"sku"               => $hs['prod_data']['sku'],
	));

	hs_session('edit_product_data',array('prod_id' => $prod_id));
	hs_setprod_val($prod_id,array('editing_stage' => 'unsaved'));
} 

else{
	echo "Something went wrong. Please try again later!";
	exit;
}