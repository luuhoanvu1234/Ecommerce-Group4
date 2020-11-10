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

$hs['page_title']       = hs_translate('Trading terms of sale in - {%name%}',array('name' => $hs['config']['name']));
$hs['page_desc']        = $hs['config']['description'];
$hs['page_kw']          = $hs['config']['keywords'];
$hs['pn']               = 'merchant_terms';
$hs['header_st']        = true;
$hs['doc_page_content'] = hs_ap_info_get_static_page('doc_merchant_terms');
$hs['doc_page_content'] = ((not_empty($hs['doc_page_content'])) ? $hs['doc_page_content'] : hs_redirect('404'));
$hs['doc_page_content'] = htmlspecialchars_decode($hs['doc_page_content']);
$hs['site_content']     = hs_loadpage('merchant_terms/content');