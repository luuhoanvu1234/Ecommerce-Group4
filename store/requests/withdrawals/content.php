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

$hs['pn']                  =  $store_page;
$hs['page_title']          =  hs_translate('Withdrawals - {%name%}',array('name' => $hs['me']['name']));
$hs['page_desc']           =  $hs['config']['description'];
$hs['page_kw']             =  $hs['config']['keywords'];
$hs['total_reqs']          =  hs_get_payouts_total($me['id']);
$hs['total_reqs_paid']     =  hs_get_payouts_total($me['id'],'paid');
$hs['total_reqs_declined'] =  hs_get_payouts_total($me['id'],'declined');
$hs['total_reqs_pending']  =  hs_get_payouts_total($me['id'],'pending');
$hs['yearly_stats']        =  hs_account_yearly_monetization_stats($me['id']);
$hs['lpc']                 =  hs_get_user_lp_currency($me['id']);
$hs['payout_requests']     =  hs_get_user_payouts(array(
	'user_id'              => $me['id'],
	'limit'                => 7,
)); $hs['site_content']    =  hs_loadpage("withdrawals/content");
