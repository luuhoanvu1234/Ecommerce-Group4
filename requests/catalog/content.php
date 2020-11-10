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

if (empty($hs['is_logged'])) {
	hs_redirect('auth');
}

else if(empty($_GET['cat_name']) || in_array($_GET['cat_name'], array_keys($hs['categories'])) != true) {
	hs_redirect('404');
}

$catg_id                =  hs_secure($_GET['cat_name']);
$hs['catg_id']          =  $catg_id;
$catg_name              =  fetch_or_get($hs['categories'][$catg_id]);
$hs['page_title']       =  hs_translate('{%catg_name%} - Products & Prices of marketplace - {%name%}',array(
	'name'              => $hs['config']['name'],
	'catg_name'         => hs_translate($catg_name),
));
$hs['page_desc']        = $hs['config']['description'];
$hs['page_kw']          = $hs['config']['keywords'];
$hs['header_st']        = false;
$hs['pn']               = 'catalog';
$my_id                  = $me['id'];
$products_list          = hs_get_catalog_items(array('catg_id'=> $catg_id,'limit' => 40));
$hs['products']         = ((not_empty($products_list)) ? hs_o2array($products_list) : array());

if (not_empty($hs['products'])) {
	$hs['site_content'] = hs_loadpage('catalog/content',array(
		'catg_id'       => $catg_id
	));
}

else{
	$hs['site_content'] = hs_loadpage('catalog/includes/empty_page',array(
		'back_url'      => http_referer()
	));
}
