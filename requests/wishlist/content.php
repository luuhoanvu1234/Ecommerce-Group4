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

if (empty($hs['is_logged'])) {
	hs_redirect('auth');
}

$hs['page_title']     = hs_translate('Wish lists - {%name%}',array('name' => $hs['config']['name']));
$hs['page_desc']      = $hs['config']['description'];
$hs['page_kw']        = $hs['config']['keywords'];

$list_id              = (not_empty($_GET['ls_id'])) ? hs_secure($_GET['ls_id']) : null;
$hs['header_st']      = false;
$hs['pn']             = 'wishlist';
$hs['list_data']      = array();
$hs['list_id']        = $list_id;
$my_id                = $me['id'];
$hs['lists']          = hs_get_wishlists($me['id']);
$hs['list_pk_id']     = '';

if (not_empty($list_id)) {
	$hs['list_id']    =  $list_id;
	$hs['list_data']  =  hs_get_wishlists_data($hs['list_id']);
	$hs['list_pk_id'] =  (not_empty($hs['list_data'])) ? $hs['list_data']['id'] : '';
	$hs['page_title'] =  (($hs['list_data']['type'] == 'static') ? hs_translate($hs['list_data']['list_name']) : $hs['list_data']['list_name']);
	$hs['list_items'] =  hs_get_wishlists_items(array(
		'user_id'     => $my_id,
		'list_id'     => $hs['list_pk_id'],
		'limit'       => 40
	));
}

else {
	$hs['list_items'] =  hs_get_wishlists_items(array(
		'user_id'     => $my_id,
		'limit'       => 40
	));
}

$hs['site_content'] =  hs_loadpage('wishlist/content',array(
	'list_pk_id'    => $hs['list_pk_id']
));