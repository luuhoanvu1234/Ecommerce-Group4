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

$today              =  date('Y:m:d');
$hs['pn']           =  $store_page;
$hs['page_title']   =  hs_translate('Wallet - {%name%}',array('name' => $hs['me']['name']));
$hs['page_desc']    =  $hs['config']['description'];
$hs['page_kw']      =  $hs['config']['keywords'];
$hs['transactions'] =  hs_get_account_transactions(array(
	'account_id'    => $me['id'],
	'limit'         => 7,
));

$hs['daily_revenue'] = hs_get_account_revenue(array(
	'account_id' => $me['id'],
	'date'       => date('U', strtotime('-24 hours', strtotime(hs_str_form("{0} 00:00:00",array($today)))))
));

$hs['monthly_revenue'] = hs_get_account_revenue(array(
	'account_id' => $me['id'],
	'date'       => date('U',strtotime(date("Y:m:01 00:00:00")))
));

$hs['total_revenue'] = hs_get_account_revenue(array(
	'account_id' => $me['id'],
	'date'       => null
));

$hs['weekly_stats'] = hs_account_wallet_ws();
$hs['site_content'] = hs_loadpage("wallet/content");