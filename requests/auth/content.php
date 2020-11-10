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

if ($hs['is_logged']) {
	hs_redirect('/');
}

$hs['auth_page']  =  fetch_or_get($_GET['auth_page'],'login');
$hs['page_title'] =  $hs['config']['title'];
$hs['page_desc']  =  $hs['config']['description'];
$hs['page_kw']    =  $hs['config']['keywords'];
$hs['pn']         =  'auth';
$hs['header_st']  =  false;
$hs['em_code']    =  (empty($_GET['em_code'])) ? null : $_GET['em_code'];
$hs['prods']      =  hs_get_preview_products(array(
	'limit'       => 15
));

$hs['prods']  = ((empty($hs['prods'])) ? array() : $hs['prods']);
$hs['ghosts'] = ((count($hs['prods']) < 15) ? range(1, (15 - count($hs['prods']))) : array());

if ($hs['auth_page'] == 'login') {
	$hs['first_item'] = (empty($hs['prods'][0])) ? array() : $hs['prods'][0];
	$hs['fi_sprice']  = (empty($hs['first_item']['fs_price']) ? hs_price('0.00') : $hs['first_item']['fs_price']);
    $hs['fi_rprice']  = (empty($hs['first_item']['fr_price']) ? hs_price('0.00') : $hs['first_item']['fr_price']);
}

$hs['site_content'] = hs_loadpage('auth/content');
