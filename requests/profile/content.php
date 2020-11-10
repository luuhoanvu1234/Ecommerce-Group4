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

elseif (empty($_GET['uname'])) {
	hs_redirect('/');
}

else {
	$prf_page          = (!empty($_GET['tab']) ? $_GET['tab'] : 'traditems');
	$uname             = hs_secure($_GET['uname']);
	$hs['prof_data']   = hs_get_user_by_name($uname);
	$hs['prf_page']    = $prf_page;
	$hs['pn']          = 'profile';
	$hs['header_st']   = true;
	$hs['prof_data']   = ((not_empty($hs['prof_data'])) ? hs_o2array($hs['prof_data']) : null);

	if (not_empty($hs['prof_data'])) {
		if ($hs['prof_data']['active'] == '1') {
			$profile_id                = intval($hs['prof_data']['id']);
			$hs['prof_rating']         = hs_get_profile_rating($profile_id);
			$hs['prof_rating_format']  = number_format($hs['prof_rating'], 1, ',', ' ');
			$hs['prof_rating_stars']   = hs_rating_stars($hs['prof_rating']);
			$hs['user_items']          = array();
			$hs['prof_items_total']    = hs_get_user_total_items($profile_id);
			$hs['prof_data']['isme']   = ($hs['prof_data']['id'] == $me['id']);
			$hs['prof_rating_total']   = hs_profile_total_reviews($profile_id);
			$hs['prof_data']['ti_url'] = hs_str_form("{0}/{1}/traditems",array(
				$config['url'],
				$hs['prof_data']['username'],
			));

			$hs['prof_data']['ms_url'] = hs_str_form("{0}/{1}/mostsold",array(
				$config['url'],
				$hs['prof_data']['username'],
			));

			$hs['prof_data']['bp_url'] = hs_str_form("{0}/{1}/bestproducts",array(
				$config['url'],
				$hs['prof_data']['username'],
			));

			$hs['prof_data']['cr_url'] = hs_str_form("{0}/{1}/reviews",array(
				$config['url'],
				$hs['prof_data']['username'],
			));

			if (in_array($prf_page, array('traditems','bestproducts','mostsold','reviews'))) {
				if ($prf_page == 'traditems') {
					$hs['user_items'] =  hs_get_products(array(
						'user_id'     => $profile_id,
						'limit'       => 10,
						'approved'    => true,
						'nf_dgt'      => 0
					));
				}
				else if($prf_page == 'bestproducts') {
					$hs['user_items'] =  hs_get_products(array(
						'user_id'     => $profile_id,
						'limit'       => 10,
						'ob_rating'   => true,
						'approved'    => true,
						'nf_dgt'      => 0
					));
				}
				else if($prf_page == 'mostsold') {
					$hs['user_items'] =  hs_get_products(array(
						'user_id'     => $profile_id,
						'limit'       => 10,
						'ob_sales'    => true,
						'approved'    => true,
						'nf_dgt'      => 0
					));
				}
				else {
					$hs['prof_rating_bar']     =  sprintf("p%d",(20 * $hs['prof_rating']));
					$hs['prof_rating_total5']  =  hs_profile_total_reviews($profile_id,5);
					$hs['prof_rating_total4']  =  hs_profile_total_reviews($profile_id,4);
					$hs['prof_rating_total3']  =  hs_profile_total_reviews($profile_id,3);
					$hs['prof_rating_total2']  =  hs_profile_total_reviews($profile_id,2);
					$hs['prof_rating_total1']  =  hs_profile_total_reviews($profile_id,1);
					$hs['profile_reviews']     =  hs_get_profile_reviews(array(
						'prof_id'              => $profile_id,
						'limit'                => 20
					));
					
					$hs['prof_rating_percent'] = hs_profife_ratings_percentage(array(
						'5' => $hs['prof_rating_total5'],
						'4' => $hs['prof_rating_total4'],
						'3' => $hs['prof_rating_total3'],
						'2' => $hs['prof_rating_total2'],
						'1' => $hs['prof_rating_total1']
					));
				}

		        $hs['page_title']   =  $hs['prof_data']['name'];
	            $hs['page_desc']    =  $hs['prof_data']['bio'];
	            $hs['page_kw']      =  '';
				$hs['site_content'] =  hs_loadpage('profile/content',array(
					'page_content'  => hs_loadpage("profile/includes/$prf_page")
				));

				hs_session('profile_id',$profile_id);
				hs_session('profile_page',$prf_page);
			}
		}

		else {
			echo hs_server_message('stop/account_disabled');
			exit();
		}
	}
}