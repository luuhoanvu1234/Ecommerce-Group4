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
	echo("Unfortunately, we do not recognize you. Please login to access this data.");
	exit;
}

else if ($action == 'create_new_wls') {
	$total_wls    = hs_get_total_wishlists($me['id'],'removable');
	$user_max_wls = intval($hs['config']['user_max_wls']);

	if ($total_wls > $user_max_wls) {
		$data['err_code'] =  1;
		$data['status']   =  400;
		$data['message']  =  hs_translate("Sorry, but you cannot create more than {%max%} custom wishlists!",array(
			'max'         => $user_max_wls
		));
	}

	else {
		if (empty($_POST['list_name'])) {
			$data['err_code'] = 1;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. List name is invalid or missing";
		}

		else if(len($_POST['list_name']) > 20) {
			$data['err_code'] = 2;
			$data['status']   = 400;
			$data['message']  = hs_translate("The list name can not be more than 20 characters long");
		}

		else if(len($_POST['list_name']) < 3) {
			$data['err_code'] = 3;
			$data['status']   = 400;
			$data['message']  = hs_translate("The list name you entered is too short. Please check your details!");
		}

		else {
			$user_id           =  $me['id'];
			$wish_list_name    =  strval(hs_secure($_POST['list_name']));
			$check_list_exists =  array(
				'user_id'      => $user_id,
				'list_name'    => $wish_list_name,
			);

			if (hs_where_exists(T_WISHLIST,$check_list_exists) == true) {
				$data['err_code'] = 4;
				$data['status']   = 400;
				$data['message']  = hs_translate("You already have a wish list with this name. Please choose a different name!");
			}

			else {
				$wl_hash_id     =  hs_croptxt(sha1($wish_list_name),25);
				$insert_data    =  array(
					'user_id'   => $user_id,
					'list_name' => $wish_list_name,
					'hash_id'   => $wl_hash_id,
					'type'      => 'removable',
					'time'      => time()
				); $insert_id   =  $db->insert(T_WISHLIST,$insert_data);

				if (is_numeric($insert_id)) {
					$data['status']   = 200;
					$data['err_code'] = 0;
					$data['url']      = hs_link(sprintf("wishlist/%s",$wl_hash_id));
					$data['message']  = hs_translate("The new wishlist has been created successfully.");
				}
				else {
					$data['err_code'] = 5;
					$data['status']   = 400;
					$data['message']  = "Sorry, something went wrong. Please try again later.";
				}
			}
		}
	}
}

else if ($action == 'edit_rename_wls') {
	if (empty($_POST['list_name'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. List name is invalid or missing";
	}

	else if(len($_POST['list_name']) > 20) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = hs_translate("The list name can not be more than 20 characters long");
	}

	else if(len($_POST['list_name']) < 3) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = hs_translate("The list name you entered is too short. Please check your details!");
	}

	else if(not_num($_POST['list_id'])) {
		$data['err_code'] =  4;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. List id is invalid or missing";
	}

	else {
		$user_id             = $me['id'];
		$list_id             = intval($_POST['list_id']);
		$wish_list_name      = strval(hs_secure($_POST['list_name']));
		$check_is_list_owner = array(
			'user_id'        => $user_id,
			'id'             => $list_id,
		);
		$check_list_exists   = array(
			'user_id'        => $user_id,
			'list_name'      => $wish_list_name,
		);

		if (hs_where_exists(T_WISHLIST,$check_is_list_owner) != true) {
			$data['err_code'] = 5;
			$data['status']   = 400;
			$data['message']  = "Wish list with such id does not exists.";
		}

		else if (hs_where_exists(T_WISHLIST,$check_list_exists) == true) {
			$data['err_code'] = 6;
			$data['status']   = 400;
			$data['message']  = hs_translate("You already have a wish list with this name. Please choose a different name!");
		}

		else {

			$db             = $db->where('id',$list_id);
			$db             = $db->where('user_id',$user_id);
			$update         = $db->update(T_WISHLIST,array(
				'list_name' => $wish_list_name
			));

			if ($update == true) {
				$data['status']   = 200;
				$data['err_code'] = 0;
				$data['message']  = hs_translate("Your wish list has been renamed successfully. Please wait ..");
			}
			else {
				$data['err_code'] = 7;
				$data['status']   = 400;
				$data['message']  = "Sorry, something went wrong. Please try again later.";
			}
		}
	}
}

else if ($action == 'delete_wls') {
	if(not_num($_POST['list_id'])) {
		$data['err_code']    =  1;
		$data['status']      = 400;
		$data['message']     = "Error: Invalid request data. List id is invalid or missing";
	}
	else {
		$user_id             =  $me['id'];
		$list_id             =  intval($_POST['list_id']);
		$check_is_list_owner =  array(
			'type'           => 'removable',
			'user_id'        => $user_id,
			'id'             => $list_id,
		);

		if (hs_where_exists(T_WISHLIST,$check_is_list_owner) != true) {
			$data['err_code'] = 2;
			$data['status']   = 400;
			$data['message']  = "Wish list with such id does not exists.";
		}

		else {
			$db               = $db->where('user_id',$user_id);
			$rm               = $db->where('id',$list_id)->delete(T_WISHLIST);
			$db               = $db->where('user_id',$user_id);
			$rm               = $db->where('list_id',$list_id)->delete(T_WLS_ITEMS);
			$data['status']   = 200;
			$data['err_code'] = 0;
			$data['message']  = hs_translate("Your wish list has been removed successfully. Please wait ..");
		}
	}
}

else if ($action == 'add_prod_2wls') {
	if (not_empty($_POST['insert'])) {
		if (not_num($_POST['list_id'])) {
			$data['err_code'] = 1;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. List id is invalid or missing";
		}

		else if(not_num($_POST['prod_id'])) {
			$data['err_code'] = 2;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Product id is invalid or missing";
		}

		else if(hs_is_prodowner($me['id'],$_POST['prod_id']) == true) {
			$data['err_code'] = 3;
			$data['status']   = 400;
			$data['message']  = "You can't add your own product to your wishlist";
		}

		else {
			$list_id              =  intval($_POST['list_id']);
			$prod_id              =  intval($_POST['prod_id']);
			$my_id                =  $me['id'];
			$check_list_exists    =  array(
				'id'              => $list_id,
				'user_id'         => $my_id,
			);
			$check_prod_exists    =  array(
				'id'              => $prod_id,
				'activity_status' => 'active',
				'status'          => 'active',
				'approved'        => 'Y',
			);
			$check_items_exists   =  array(
				'user_id'         => $my_id,
				'prod_id'         => $prod_id,
			);

			if (hs_where_exists(T_WISHLIST,$check_list_exists) != true) {
				$data['err_code'] = 4;
				$data['status']   = 400;
				$data['message']  = "Wish list with this id does not exist!";
			}

			else if(hs_where_exists(T_PRODUCTS,$check_prod_exists) != true) {
				$data['err_code'] = 5;
				$data['status']   = 400;
				$data['message']  = "Product item with this id does not exist or not active (Not approved)!";
			}

			else if(hs_where_exists(T_WLS_ITEMS,$check_items_exists)) {
				$data['err_code'] = 6;
				$data['status']   = 400;
				$data['message']  = "You have already added this product to wishlist.";
			}
			else {
				$insert_data  =  array(
					'list_id' => $list_id,
					'prod_id' => $prod_id,
					'user_id' => $my_id,
					'time'    => time(),
				); $insert_id =  $db->insert(T_WLS_ITEMS,$insert_data);

				
				if (is_numeric($insert_id)) {
					$data['code']     = 'insert';
					$data['status']   = 200;
					$data['message']  = hs_translate("Product successfully added to your wishlist.");
				}
				else {
					$data['err_code'] = 7;
					$data['status']   = 500;
					$data['message']  = "Sorry, something went wrong. Please try again later.";
				}
			}
		}
	} 

	else if(not_empty($_POST['upsert'])) {
		$total_wls    = hs_get_total_wishlists($me['id'],'removable');
		$user_max_wls = intval($hs['config']['user_max_wls']);

		if ($total_wls > $user_max_wls) {
			$data['err_code'] =  1;
			$data['status']   =  400;
			$data['message']  =  hs_translate("Sorry, but you cannot create more than {%max%} custom wishlists!",array(
				'max'         => $user_max_wls
			));
		}
		else {
			if (empty($_POST['list_name'])) {
				$data['err_code'] = 1;
				$data['status']   = 400;
				$data['message']  = hs_translate("Error: Invalid request data. List name is invalid or missing");
			}

			else if(len($_POST['list_name']) > 20) {
				$data['err_code'] = 2;
				$data['status']   = 400;
				$data['message']  = hs_translate("The list name can not be more than 20 characters long.");
			}

			else if(len($_POST['list_name']) < 3) {
				$data['err_code'] = 3;
				$data['status']   = 400;
				$data['message']  = hs_translate("The list name you entered is too short. Please check your details.");
			}

			else if(not_num($_POST['prod_id'])) {
				$data['err_code'] = 3;
				$data['status']   = 400;
				$data['message']  = "Error: Invalid request data. Product id is invalid or missing";
			}

			else if(hs_is_prodowner($me['id'],$_POST['prod_id']) == true) {
				$data['err_code'] = 4;
				$data['status']   = 400;
				$data['message']  = "You can't add your own product to your wishlist";
			}

			else {
				$user_id              =  $me['id'];
				$prod_id              =  intval($_POST['prod_id']);
				$wish_list_name       =  strval(hs_secure($_POST['list_name']));
				$check_prod_exists    =  array(
					'id'              => $prod_id,
					'status'          => 'active',
					'activity_status' => 'active',
					'approved'        => 'Y',
				);
				$check_list_exists    = array(
					'user_id'         => $user_id,
					'list_name'       => $wish_list_name,
				);

				if (hs_where_exists(T_WISHLIST,$check_list_exists) == true) {
					$data['err_code'] = 4;
					$data['status']   = 400;
					$data['message']  = hs_translate("You already have a wish list with this name. Please choose a different name.");
				}

				else if (hs_where_exists(T_PRODUCTS,$check_prod_exists) != true) {
					$data['err_code'] = 4;
					$data['status']   = 400;
					$data['message']  = "Product item with this id does not exist or not active (Not approved)!";
				}

				else {
					$insert_data    =  array(
						'user_id'   => $user_id,
						'list_name' => $wish_list_name,
						'hash_id'   => mb_substr(sha1($wish_list_name),0,25),
						'time'      => time(),
						'type'      => 'removable'
					); $new_list_id =  $db->insert(T_WISHLIST,$insert_data);

					

					if (is_numeric($new_list_id)) {
						$insert_data  =  array(
							'list_id' => $new_list_id,
							'prod_id' => $prod_id,
							'user_id' => $user_id,
							'time'    => time(),
						); $insert_id =  $db->insert(T_WLS_ITEMS,$insert_data);

						if (is_numeric($insert_id)) {
							$data['code']     = 'upsert';
							$data['ls_id']    = $new_list_id;
							$data['ls_name']  = $wish_list_name;
							$data['status']   = 200;
							$data['message']  = hs_translate("Product successfully added to your new wishlist.");
						}

						else {
							$data['err_code'] = 5;
							$data['status']   = 500;
							$data['message']  = "Sorry, something went wrong. Please try again later.";
						}
					}
					else {
						$data['err_code'] = 6;
						$data['status']   = 400;
						$data['message']  = "Sorry, something went wrong. Please try again later.";
					}
				}
			}
		}
	}
}

else if ($action == 'delete_pi_wls') {
	if(not_num($_POST['prod_id']) && not_num($_POST['wl_item_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product id is invalid or missing";
	}

	else {
		if (not_empty($_POST['wl_item_id'])) {
			$prod_id          = intval($_POST['wl_item_id']);
			$my_id            = $me['id'];
			$db               = $db->where('user_id',$my_id);
			$db               = $db->where('id',$prod_id);
			$delete_pfrom_wls = $db->delete(T_WLS_ITEMS);
		}
		else {
			$prod_id          = intval($_POST['prod_id']);
			$my_id            = $me['id'];
			$db               = $db->where('user_id',$my_id);
			$db               = $db->where('prod_id',$prod_id);
			$delete_pfrom_wls = $db->delete(T_WLS_ITEMS);
		}
		
		$data['status']   = 200;
		$data['message']  = hs_translate("Product has been successfully removed from your wishlist");
	}
}

else if($action == 'load') {
	$fiter_data   =  array(
		'user_id' => $me['id'],
		'limit'   => 40,
	);

	if(not_empty($_GET['list_id'])) {
		$fiter_data['list_id'] = hs_secure($_GET['list_id']);
	}

	if(not_empty($_GET['catg_id']) && $_GET['catg_id'] != 'none') {
		$fiter_data['catg_id'] = hs_secure($_GET['catg_id']);
	}

	if (not_empty($_GET['sortby']) && in_array($_GET['sortby'], array('price_up','price_down','newest','rating','sales'))) {
		$fiter_data['sortby'] = strval($_GET['sortby']);
	}

	if (not_empty($_GET['brand']) && len_between($_GET['brand'],2,50)) {
		$fiter_data['brand'] = hs_secure($_GET['brand']);
	}

	if (not_empty($_GET['keyword']) && len_between($_GET['keyword'],3,50)) {
		$fiter_data['keyword'] = hs_secure($_GET['keyword']);
	}

	if (not_empty($_GET['condition']) && in_array($_GET['condition'], array('1','2','3'))) {
		$fiter_data['condition'] = strval($_GET['condition']);
	}

	if (not_empty($_GET['ship_cost']) && in_array($_GET['ship_cost'], array('paid','free'))) {
		$fiter_data['ship_cost'] = strval($_GET['ship_cost']);
	}

	if (not_empty($_GET['ship_time']) && in_array($_GET['ship_time'], array('1_bd','2_3_bd','4_7_bd','8_15_bd','within_1_month','within_2_months','within_3_months'))) {
		$fiter_data['ship_time'] = strval($_GET['ship_time']);
	}

	if (is_number($_GET['offset'])) {
		$fiter_data['offset'] = intval($_GET['offset']);
	}

	if (is_number($_GET['min_price']) && is_number($_GET['max_price'])) {
		if (intval($_GET['min_price']) < intval($_GET['max_price'])) {
			$fiter_data['min_price'] = intval($_GET['min_price']);
			$fiter_data['max_price'] = intval($_GET['max_price']);
		}
	}

	else {
		if (is_number($_GET['min_price'])) {
			$fiter_data['min_price'] = intval($_GET['min_price']);
		}

		if (is_number($_GET['max_price'])) {
			$fiter_data['max_price'] = intval($_GET['max_price']);
		}
	}

	if (not_empty($_GET['seller_country']) && is_array($_GET['seller_country'])) {
		$seller_countries = array();
		foreach ($_GET['seller_country'] as $cnt_id) {
			if (in_array($cnt_id, array_keys($hs['countries']))) {
				$seller_countries[] = $cnt_id;
			}
		}

		if (not_empty($seller_countries)) {
			$fiter_data['sell_cntr'] = implode(',',$seller_countries);
		}
	}

	$data             = array();
	$data['status']   = 404;
	$data['offset']   = ((not_empty($fiter_data['offset'])) ? 1 : 0);
	$hs['list_items'] = hs_get_wishlists_items($fiter_data);
	$html_arr         = array();

	if (not_empty($hs['list_items'])) {
		foreach ($hs['list_items'] as $hs['ls_item']) {
			array_push($html_arr, hs_loadpage('wishlist/includes/prod_item'));
		}

		$data['status'] = 200;
		$data['html']   = implode('', $html_arr);	
	}
	else if(empty($hs['list_items']) && empty($fiter_data['offset'])) {
		$data['html']   = hs_loadpage('wishlist/includes/filter_404');
	}
}