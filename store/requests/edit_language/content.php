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

if(empty($hs['is_admin'])) {
	hs_redirect('404');
}

$lang_name  = fetch_or_get($_GET['lang'],'none');
$all_langs  = hs_get_languages('all');
$lang_names = array();

foreach ($all_langs as $row) {
	array_push($lang_names, $row['lang_name']);
}

if (empty($lang_name) || in_array($lang_name, $lang_names) != true) {
	hs_redirect('404');
}
else {
	
	hs_session('el_name',$lang_name);

	$hs['el_name']       = $lang_name;
	$hs['langs_dataset'] = hs_ap_info_get_lang_datasets(array('limit' => 10,'language' => $lang_name));
	$hs['pn']            = 'site_languages';
	$hs['page_title']    = hs_translate('Manage languages');
	$hs['page_desc']     = $hs['config']['description'];
	$hs['page_kw']       = $hs['config']['keywords'];
	$hs['site_content']  = hs_loadpage("edit_language/content");
}