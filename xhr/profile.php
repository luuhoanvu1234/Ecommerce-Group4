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

if ($action == 'load_profile_items') {
	if(not_num($_GET['offset'])) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data!";
	}

	else {
		$profile_id     = hs_session('profile_id');
		$profile_page   = hs_session('profile_page');
		$offset         = intval($_GET['offset']);
		$user_items     = array();
		$html_array     = array();
		$data['status'] = 404;

		if (is_number($profile_id) && not_empty($profile_page)) {
			if ($profile_page == 'traditems') {
				$user_items   =  hs_get_products(array(
					'user_id' => $profile_id,
					'limit'   => 20,
					'offset'  => $offset,
					'nf_dgt'  => 0
				));
			}

			else if($profile_page == 'bestproducts') {
				$user_items     =  hs_get_products(array(
					'user_id'   => $profile_id,
					'limit'     => 20,
					'ob_rating' => true,
					'offset'    => $offset,
					'nf_dgt'    => 0
				));
			}

			else if($profile_page == 'mostsold') {
				$user_items    =  hs_get_products(array(
					'user_id'  => $profile_id,
					'limit'    => 20,
					'ob_sales' => true,
					'offset'   => $offset,
					'nf_dgt'   => 0
				));
			}	
		}

		if (not_empty($user_items)) {
			foreach ($user_items as $hs['prod_item']) {
				array_push($html_array, hs_loadpage('profile/includes/cards/prod_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_array);
		}
	}
}

else if($action == 'load_reviews') {
	$profile_id = hs_session('profile_id');
	if (not_empty($profile_id)) {
		$stars  = array();
		$html   = array();
		$data   = array('status' => 404);
		$offset = (is_number($_POST['offset']) ? intval($_POST['offset']) : null);

		if (not_empty($_POST['filters']['one_star'])) {
			array_push($stars, '1');
		}
		if (not_empty($_POST['filters']['two_stars'])) {
			array_push($stars, '2');
		}
		if (not_empty($_POST['filters']['three_stars'])) {
			array_push($stars, '3');
		}
		if (not_empty($_POST['filters']['four_stars'])) {
			array_push($stars, '4');
		}
		if (not_empty($_POST['filters']['five_stars'])) {
			array_push($stars, '5');
		}
		
		$profile_reviews = hs_get_profile_reviews(array(
			'prof_id' => $profile_id,
			'sortby' => $stars,
			'offset' => $offset,
			'limit' => 20
		));

		if (not_empty($profile_reviews)) {
			foreach ($profile_reviews as $hs['review_data']) {
				array_push($html, hs_loadpage('profile/includes/cards/review'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html);
		}
	}
	else {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Profile id is missing!";
	}
}

else if ($action == 'request_payout') {
	$data['err_code'] =  0;
	$data['status']   =  400;
	$site_currencies  =  array();	
	$form_data_fileds =  array(
		'lastreq'     => null,
		'amount'      => fetch_or_get($_POST['amount'],null),
		'pp_link'     => fetch_or_get($_POST['pp_link'],null),
		'currency'    => fetch_or_get($_POST['currency'],null),
		'password'    => fetch_or_get($_POST['password'],null),
	);

	foreach ($hs['currencies'] as $curr_data) {
		$site_currencies[] = $curr_data['curr_code'];
	}

	foreach ($form_data_fileds as $field_name => $field_val) {
		if ($field_name == 'lastreq') {
			if (hs_check_can_seller_withdraw($me['id']) != true) {
				$data['message']  = 'Error: Early withdrawal request!';
		        $data['err_code'] = 'lastreq_error'; break;
			}
		}

		else if ($field_name == 'amount') {
			if (not_num($field_val)) {
				$data['message']  = hs_translate("Please enter the payout amount!");
	            $data['err_code'] = $field_name; break;
			}

			else if (floatval($field_val) < floatval($config['min_payout'])) {
	            $data['message']  = hs_translate("Invalid payment amount. Minimum payout amount is: {%min_payout%} {%currency%}",array(
	            	'min_payout'  => hs_money($config['min_payout']),
	            	'currency'    => $config['currency'],
	            ));

	            $data['err_code'] = $field_name; break;
	        }

			else if (floatval($field_val) > floatval($me['wallet_val'])) {
	            $data['message']  =  hs_translate("Your account balance is less than the request amount. Your balance now is: {%balance%}",array(
	            	'balance'     => sprintf('%s %s',$me['wallet_all'],$config['currency'])
	            ));
	            $data['err_code'] = $field_name; break;
	        }
		}

		else if ($field_name == 'pp_link') {
			if (empty($field_val)) {
	            $data['message']  = hs_translate("Please your Paypal-Me payment link!");
	            $data['err_code'] = $field_name; break;
	        }

	        else if (is_url($field_val) != true || empty(preg_match('/^https\:\/\/www\.paypal\.me\/[\w]{4,60}$/i', $field_val))) {
	            $data['message']  = hs_translate("The Paypal payment address you entered is not valid. Pleas check your details");
	            $data['err_code'] = $field_name; break;
	        }
		}

		else if ($field_name == 'currency') {
			if (empty($field_val)) {
	            $data['message']  = hs_translate("Please select the payout currency type!");
	            $data['err_code'] = $field_name; break;
	        }

	        else if(in_array(strval($field_val), $site_currencies) != true) {
				$data['message']  = "The payout currency you selected is not valid. Please check your details";
	            $data['err_code'] = $field_name; break;
	        }
		}

		else if ($field_name == 'password') {
			if (empty($field_val)) {
	            $data['message']  = hs_translate("Please enter your account password!");
	            $data['err_code'] = $field_name; break;
	        }

	        else if(password_verify($field_val, $me['password']) != true) {
				$data['message']  = hs_translate("The password you entered is not valid. Please check your details");
	            $data['err_code'] = $field_name; break;
	        }
		}
	}

	if (empty($data['err_code'])) {
		$insert_data   =  array(
            'user_id'  => $me['id'],
            'pp_link'  => urlencode($form_data_fileds['pp_link']),
            'amount'   => hs_secure($form_data_fileds['amount']),
            'currency' => hs_secure($form_data_fileds['currency']),
            'status'   => 'pending',
            'time'     => time(),
        ); $insert_id  =  $db->insert(T_PAYOUT_REQS,$insert_data);

		if (is_number($insert_id)) {
			$data['status']  = 200;
			$data['message'] = hs_translate("Your payment request has been sent successfully. Please note that it will take several business days to process your request.");
			$insert_data     = array(
				'user_id'    => $me['id'],
				'title'      => 'Withdrawal requested successfully!',
				'message'    => 'Your payment request has been sent successfully. Please note that it will take several business days to process your request.',
				'type'       => 'success',
				'static'     => 'N',
				'time'       => time()
			); $db->insert(T_ANNOUNC,$insert_data);
		}

		else {
			$data['message']  = "Error found while processing your request. Please try again later!";
        	$data['status']   = 500;
		}
    }
}

else if($action == 'get_payout_requests') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$requests  = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$requests = hs_get_user_payouts(array(
				'user_id'   => $me['id'],
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC'
			));

			$requests = array_reverse($requests);
		}
		else if($offset_to == 'down' && $last_id) {
			$requests = hs_get_user_payouts(array(
				'user_id'   => $me['id'],
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
			));
		}

		if (not_empty($requests)) {
			foreach ($requests as $hs['req_data']) {
				array_push($html_arr, hs_loadpage('withdrawals/includes/request_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'get_customers_orders') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$orders    = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$orders = hs_get_customer_orders(array(
				'seller_id' => $me['id'],
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword
			));

			$orders = array_reverse($orders);
		}
		else if($offset_to == 'down' && $last_id) {
			$orders = hs_get_customer_orders(array(
				'seller_id' => $me['id'],
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword
			));
		}

		if (not_empty($orders)) {
			foreach ($orders as $hs['ord_item']) {
				array_push($html_arr, hs_loadpage('customer_orders/includes/order_list'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'search_customers_orders') {
	if (not_empty($_GET['keyword'])) {
		$orders   = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$orders         = hs_get_customer_orders(array(
				'seller_id' => $me['id'],
				'limit'     => 10,
				'order'     => 'DESC',
				'keyword'   => $keyword
			));
		}

		if (not_empty($orders)) {
			foreach ($orders as $hs['ord_item']) {
				array_push($html_arr, hs_loadpage('customer_orders/includes/order_list'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage("customer_orders/includes/search_data_404");
		}
	}
}

else if($action == 'search_customers') {
	if (not_empty($_GET['keyword'])) {
		$customers = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$customers      = hs_get_store_customers(array(
				'seller_id' => $me['id'],
				'limit'     => 10,
				'order'     => 'DESC',
				'keyword'   => $keyword
			));
		}

		if (not_empty($customers)) {
			foreach ($customers as $hs['cust_data']) {
				array_push($html_arr, hs_loadpage('customers/includes/cust_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage("customers/includes/search_data_404");
		}
	}
}

else if($action == 'get_my_customers') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$customers = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$customers      = hs_get_store_customers(array(
				'seller_id' => $me['id'],
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword
			));

			$customers = array_reverse($customers);
		}
		else if($offset_to == 'down' && $last_id) {
			$customers      = hs_get_store_customers(array(
				'seller_id' => $me['id'],
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword
			));
		}

		if (not_empty($customers)) {
			foreach ($customers as $hs['cust_data']) {
				array_push($html_arr, hs_loadpage('customers/includes/cust_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'get_my_orders') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$orders    = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$orders = hs_get_my_orders(array(
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword
			));

			$orders = array_reverse($orders);
		}
		else if($offset_to == 'down' && $last_id) {
			$orders = hs_get_my_orders(array(
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword
			));
		}

		if (not_empty($orders)) {
			foreach ($orders as $hs['order_data']) {
				array_push($html_arr, hs_loadpage('my_orders/includes/order_list'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'search_my_orders') {
	if (not_empty($_GET['keyword'])) {
		$orders   = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$orders       = hs_get_my_orders(array(
				'limit'   => 10,
				'order'   => 'DESC',
				'keyword' => $keyword
			));
		}

		if (not_empty($orders)) {
			foreach ($orders as $hs['order_data']) {
				array_push($html_arr, hs_loadpage('my_orders/includes/order_list'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('my_orders/includes/search_data_404');
		}
	}
}

else if($action == 'get_my_products' && not_empty($hs['is_logged'])) {
	$data['status'] =  404;
	$offset_to      =  false;
	$products       =  array();
	$data           =  array('status' => 404);
	$html_arr       =  array();
	$filter         =  array(
		'limit'     => 7,
		'order'     => 'DESC',
	);

	if (not_empty($_POST['dir'])) {
		if (in_array($_POST['dir'], array('up','down'))) {
			$offset_to = hs_secure($_POST['dir']);
			$first_id  = ((is_number($_POST['first'])) ? intval($_POST['first']) : false);
			$last_id   = ((is_number($_POST['last'])) ? intval($_POST['last']) : false);
			
			if ($offset_to == 'up') {
				$filter['offset']    = $first_id;
				$filter['offset_to'] = 'gt';
				$filter['order']     = 'ASC';
			}

			else {
				$filter['offset']    = $last_id;
				$filter['offset_to'] = 'lt';
				$filter['order']     = 'DESC';
			}
		}
	}

	if (not_empty($_POST['prod_name'])) {
		if (len_between(trim($_POST['prod_name']),1, 150)) {
			$filter['keyword'] = hs_secure($_POST['prod_name']);
		}
	}

	if (not_empty($_POST['prod_status'])) {
		if (in_array($_POST['prod_status'], array('all','active','inactive','blocked'))) {
			$filter['prod_status'] = hs_secure($_POST['prod_status']);
		}
	}

	if (not_empty($_POST['var_type'])) {
		if (in_array($_POST['var_type'], array('all','single','color','size','color_size'))) {
			$filter['var_type'] = hs_secure($_POST['var_type']);
		}
	}

	if (not_empty($_POST['sku'])) {
		if (len_between(trim($_POST['sku']),1, 22)) {
			$filter['sku'] = hs_secure($_POST['sku']);
		}
	}

	if (not_empty($_POST['payment_method'])) {
		if (in_array($_POST['payment_method'], array('all_payments','cod_payments','pre_payments'))) {
			$filter['payment_method'] = hs_secure($_POST['payment_method']);
		}
	}

	if (not_empty($_POST['approval_status'])) {
		if (in_array($_POST['approval_status'], array('all','approved','not_approved'))) {
			$filter['approval_status'] = hs_secure($_POST['approval_status']);
		}
	}	

	if (not_empty($_POST['category'])) {
		if (in_array($_POST['category'], array_keys($hs['categories']))) {
			$filter['category'] = hs_secure($_POST['category']);
		}
	}

	$products = hs_get_my_products($filter);

	if (not_empty($products)) {
		if ($offset_to == 'up') {
			$products = array_reverse($products);
		}

		foreach ($products as $hs['prod_item']) {
			array_push($html_arr, hs_loadpage('my_products/includes/prod_list_item'));
		}

		$data['status'] = 200;
		$data['html']   = implode('', $html_arr);
	}

	else {
		if (empty($offset_to)) {
			$data['html'] = hs_loadpage('my_products/includes/search_data_404');
		}
	}
}

else if($action == 'get_draft_products') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$products  = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$products       =  hs_get_draft_products(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC'
			));

			$products = array_reverse($products);
		}
		else if($offset_to == 'down' && $last_id) {
			$products       =  hs_get_draft_products(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC'
			));
		}

		if (not_empty($products)) {
			
			foreach ($products as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('products_draft/includes/prod_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'search_transactions') {
	if (not_empty($_GET['keyword'])) {
		$trans_ls = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$trans_ls = hs_get_account_transactions(array(
				'account_id' => $me['id'],
				'limit'      => 7,
				'order'      => 'DESC',
				'keyword'    => $keyword
			));
		}

		if (not_empty($trans_ls)) {
			foreach ($trans_ls as $hs['trans_data']) {
				array_push($html_arr, hs_loadpage('wallet/includes/trans_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage("wallet/includes/search_data_404");
		}
	}
}

else if($action == 'get_account_transactions') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$trans_ls  = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 32) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$trans_ls = hs_get_account_transactions(array(
				'account_id' => $me['id'],
				'offset'     => $first_id,
				'offset_to'  => 'gt',
				'order'      => 'ASC',
				'keyword'    => $keyword,
				'limit'      => 7
			));

			$trans_ls = array_reverse($trans_ls);
		}
		else if($offset_to == 'down' && $last_id) {
			$trans_ls = hs_get_account_transactions(array(
				'account_id' => $me['id'],
				'offset'     => $last_id,
				'offset_to'  => 'lt',
				'order'      => 'DESC',
				'keyword'    => $keyword,
				'limit'      => 7
			));
		}

		if (not_empty($trans_ls)) {
			foreach ($trans_ls as $hs['trans_data']) {
				array_push($html_arr, hs_loadpage('wallet/includes/trans_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'get_account_reviews') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$reviews   = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$reviews = hs_get_account_reviews(array(
				'user_id'   => $me['id'],
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC'
			));

			$reviews = array_reverse($reviews);
		}
		else if($offset_to == 'down' && $last_id) {
			$reviews = hs_get_account_reviews(array(
				'user_id'   => $me['id'],
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
			));
		}

		if (not_empty($reviews)) {
			foreach ($reviews as $hs['rev_data']) {
				array_push($html_arr, hs_loadpage('account_reviews/includes/review_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'account_review_data') {
	if (is_number($_GET['id'])) {
		$review_id     =  intval($_GET['id']);
		$reviews       =  hs_get_account_reviews(array(
			'limit'    => 1,
			'user_id'  => $me['id'],
			'media'    => true,
			'ids'      => array($review_id)
		));

		$reviews        = ((is_array($reviews)) ? $reviews : array());
		$hs['rev_data'] = fetch_or_get($reviews[0],false);

		if (not_empty($hs['rev_data'])) {
			$data['status']  = 200;
			$data['html']    = hs_loadpage('account_reviews/modals/review_message');
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Invalid request data. The review with such id does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The review id is missing or invalid!";
	}
}
?>