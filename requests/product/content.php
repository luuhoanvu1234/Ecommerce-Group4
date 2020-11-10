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

if (empty($hs['is_logged']) || not_num($_GET['id'])) {
	hs_redirect("/");
} 

else{
	$product_id       = hs_secure($_GET['id']);
	$hs['prod_data']  = hs_product_data($product_id);
	$hs['wishlists']  = hs_get_wishlists($me['id']);
	$is_admin         = $hs['is_admin']; 
	$hs['pn']         = 'product';
	$hs['header_st']  = true;

	if (not_empty($hs['prod_data'])) {
		$hs['prod_owner']        = hs_user_data($hs['prod_data']['user_id']);
		$prod_rating             = $hs['prod_data']['rating'];			
		$hs['prod_data']['desc'] = htmlspecialchars_decode($hs['prod_data']['description']);

		if (not_empty($hs['prod_owner'])) {
			if (empty($hs['prod_data']['owner']) && $hs['prod_data']['status'] == 'inactive') {
				hs_redirect("404");
			}

			else if(empty($hs['prod_data']['owner']) && $hs['prod_data']['status'] == 'blocked') {
				$hs['prod_owner']   =  hs_o2array($hs['prod_owner']);
				$hs['site_content'] =  hs_loadpage('product/includes/pages/prod_blocked',array(
					'seller_url'    => $hs['prod_owner']['url']
 				));
			}
		
			else {
				if (not_empty($hs['prod_data']['is_approved']) || not_empty($hs['prod_data']['owner']) || not_empty($is_admin)) {
					$hs['prod_owner']     =  hs_o2array($hs['prod_owner']);
					$check_has_purchased  =  array('buyer_id' => $me['id'],'prod_id' => $product_id);
					$hs['has_purchased']  =  hs_where_exists(T_ORDERS,$check_has_purchased);
					$hs['total_reviews0'] =  hs_prod_total_reviews($product_id,'all');
					$hs['total_reviews1'] =  hs_prod_total_reviews($product_id,'1');
					$hs['total_reviews2'] =  hs_prod_total_reviews($product_id,'2');
					$hs['total_reviews3'] =  hs_prod_total_reviews($product_id,'3');
					$hs['total_reviews4'] =  hs_prod_total_reviews($product_id,'4');
					$hs['total_reviews5'] =  hs_prod_total_reviews($product_id,'5');
					$prod_rating_fval     =  floatval($prod_rating);
					$hs['prod_rating']    =  ((not_empty($prod_rating_fval)) ? $prod_rating : '');
					$hs['rating_stars']   =  hs_rating_stars($prod_rating);
					$hs['seller_rating']  =  hs_get_profile_rating($hs['prod_owner']['id']);
					$hs['seller_rating']  =  hs_rating_stars($hs['seller_rating']);
					$hs['reviews_list']   =  hs_get_prod_reviews(array( 'prod_id' => $product_id,'limit' => 10));
					$hs['related_prods']  =  hs_get_products(array(
						'ob_sales'        => true,
						'ob_rating'       => true,
						'approved'        => true,
						'catg_id'         => $hs['prod_data']['category'],
						'limit'           => 50,
						'nf_dgt'          => 0,
						'exclude'         => array($product_id)
					));

					$hs['page_title']   = $hs['prod_data']['name'];
					$hs['page_desc']    = $hs['prod_data']['description'];
					$hs['page_kw']      = $hs['prod_data']['keywords'];
					$hs['site_content'] = hs_loadpage('product/content');
				}

				else {
					$hs['prod_owner']   =  hs_o2array($hs['prod_owner']);
					$hs['site_content'] =  hs_loadpage('product/includes/pages/not_approved',array(
						'seller_url'    => $hs['prod_owner']['url']
	 				));
				}
			}
		}
	}
}