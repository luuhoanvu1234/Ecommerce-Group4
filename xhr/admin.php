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


# Attention, this message is exclusively comic in nature,
# and the author did not try to offend anyone.

if (empty($hs['is_admin'])) {
	echo("An unknown dolphin is trying to access the admin panel. We detected your location and send S.W.A.T, we kindly ask you don't try to run!");
	exit;
}

else if($action == 'search_new_products') {
	if (not_empty($_GET['keyword'])) {
		$products = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 150) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$products      =  hs_ap_info_get_new_products(array(
				'limit'    => 7,
				'order'    => 'DESC',
				'keyword'  => $keyword
			));
		}

		if (not_empty($products)) {
			foreach ($products as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('new_products/includes/prod_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('new_products/includes/search_data_404');
		}
	}
}

else if($action == 'get_new_products') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$products  = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 150) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$products       =  hs_ap_info_get_new_products(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword,
			));

			$products = array_reverse($products);
		}
		else if($offset_to == 'down' && $last_id) {
			$products       =  hs_ap_info_get_new_products(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword,
			));
		}

		if (not_empty($products)) {
			foreach ($products as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('new_products/includes/prod_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'approve_product') {
	if (is_number($_POST['id'])) {
		$prod_id   = intval($_POST['id']);
		$prod_data = hs_get_prod_fields($prod_id,array('user_id'));
		$prod_data = ((is_array($prod_data)) ? $prod_data : array());
		$seller_id = fetch_or_get($prod_data['user_id'],0);

		if (hs_is_seller($seller_id) != true) {
			hs_update_user_data($seller_id,array('is_seller' => 'Y'));
		}

		hs_setprod_val($prod_id,array('approved' => 'Y'));
	}
}

else if($action == 'delete_product') {
	if (is_number($_POST['id'])) {
		$product_id      = intval($_POST['id']);
		$data['status']  = 200; hs_ap_delete_product($product_id);
		$data['message'] = hs_translate("The product item has been successfully deleted!");
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The product ID is missing or invalid!";
	}
}

else if($action == 'search_market_products') {
	if (not_empty($_GET['keyword'])) {
		$products = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 150) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$products      =  hs_ap_info_get_products(array(
				'limit'    => 7,
				'order'    => 'DESC',
				'keyword'  => $keyword,
				'approved' => true
			));
		}

		if (not_empty($products)) {
			foreach ($products as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('market_products/includes/prod_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('market_products/includes/search_data_404');
		}
	}
}

else if($action == 'get_market_products') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$products  = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 150) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$products       =  hs_ap_info_get_products(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword,
				'approved'  => true
			));

			$products = array_reverse($products);
		}
		else if($offset_to == 'down' && $last_id) {
			$products       = hs_ap_info_get_products(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword,
				'approved'  => true
			));
		}

		if (not_empty($products)) {
			foreach ($products as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('market_products/includes/prod_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'search_market_merchants') {
	if (not_empty($_GET['keyword'])) {
		$merchants = array();
		$data      = array('status' => 404);
		$html_arr  = array();
		$keyword   = fetch_or_get($_GET['keyword'],'');
		$keyword   = trim($keyword);
		$keyword   = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$merchants     = hs_ap_info_get_merchants(array(
				'limit'    => 10,
				'keyword'  => $keyword
			));
		}

		if (not_empty($merchants)) {
			foreach ($merchants as $hs['user_data']) {
				array_push($html_arr, hs_loadpage('market_merchants/includes/user_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('market_merchants/includes/search_data_404');
		}
	}
}

else if($action == 'get_market_merchants') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to  = strval($_GET['dir']);
		$last_id    = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id   = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$merchants  = array();
		$data       = array('status' => 404);
		$html_arr   = array();
		$keyword    = fetch_or_get($_GET['keyword'],'');
		$keyword    = trim($keyword);
		$keyword    = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$merchants       = hs_ap_info_get_merchants(array(
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword,
			));

			$merchants = array_reverse($merchants);
		}
		else if($offset_to == 'down' && $last_id) {
			$merchants       = hs_ap_info_get_merchants(array(
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword,
			));
		}

		if (not_empty($merchants)) {
			foreach ($merchants as $hs['user_data']) {
				array_push($html_arr, hs_loadpage('market_merchants/includes/user_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'search_market_users') {
	if (not_empty($_GET['keyword'])) {
		$users    = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$users         =  hs_ap_info_get_users(array(
				'limit'    => 7,
				'keyword'  => $keyword
			));
		}

		if (not_empty($users)) {
			foreach ($users as $hs['user_data']) {
				array_push($html_arr, hs_loadpage('market_users/includes/user_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('market_users/includes/search_data_404');
		}
	}
}

else if($action == 'get_market_users') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to  = strval($_GET['dir']);
		$last_id    = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id   = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$users      = array();
		$data       = array('status' => 404);
		$html_arr   = array();
		$keyword    = fetch_or_get($_GET['keyword'],'');
		$keyword    = trim($keyword);
		$keyword    = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$users          =  hs_ap_info_get_users(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword,
			));

			$users = array_reverse($users);
		}
		else if($offset_to == 'down' && $last_id) {
			$users          =  hs_ap_info_get_users(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword,
			));
		}

		if (not_empty($users)) {
			foreach ($users as $hs['user_data']) {
				array_push($html_arr, hs_loadpage('market_users/includes/user_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'get_market_moders') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to  = strval($_GET['dir']);
		$last_id    = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id   = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$moders     = array();
		$data       = array('status' => 404);
		$html_arr   = array();


		if ($offset_to == 'up' && $first_id) {
			$moders         =  hs_ap_info_get_moders(array(
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
			));

			$moders = array_reverse($moders);
		}
		else if($offset_to == 'down' && $last_id) {
			$moders          =  hs_ap_info_get_moders(array(
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
			));
		}

		if (not_empty($moders)) {
			foreach ($moders as $hs['user_data']) {
				array_push($html_arr, hs_loadpage('market_moderators/includes/user_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'search_user_reports') {
	if (not_empty($_GET['keyword'])) {
		$reports  = array();
		$data     = array('status' => 404);
		$html_arr = array();
		$keyword  = fetch_or_get($_GET['keyword'],'');
		$keyword  = trim($keyword);
		$keyword  = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if($keyword) {
			$reports       =  hs_ap_info_get_customer_reports(array(
				'limit'    => 10,
				'keyword'  => $keyword
			));
		}

		if (not_empty($reports)) {
			foreach ($reports as $hs['report_data']) {
				array_push($html_arr, hs_loadpage('reports/includes/report_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('reports/includes/search_data_404');
		}
	}
}

else if($action == 'get_user_reports') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to  = strval($_GET['dir']);
		$last_id    = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id   = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$reports    = array();
		$data       = array('status' => 404);
		$html_arr   = array();
		$keyword    = fetch_or_get($_GET['keyword'],'');
		$keyword    = trim($keyword);
		$keyword    = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id) {
			$reports        =  hs_ap_info_get_customer_reports(array(
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
				'keyword'   => $keyword,
			));

			$reports = array_reverse($reports);
		}
		else if($offset_to == 'down' && $last_id) {
			$reports        = hs_ap_info_get_customer_reports(array(
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
				'keyword'   => $keyword,
			));
		}

		if (not_empty($reports)) {
			foreach ($reports as $hs['report_data']) {
				array_push($html_arr, hs_loadpage('reports/includes/report_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'delete_report') {
	if (is_number($_POST['report_id'])) {
		$report_id = intval($_POST['report_id']);
		$db        = $db->where('id',$report_id);
		$delete    = $db->delete(T_PROD_REPORTS);
		if ($delete) {
			$data['status']  = 200;
			$data['message'] = "The report has been successfully deleted!";
		}
		else {
			$data['status']  = 500;
			$data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The report id is missing or invalid!";
	}
}

else if($action == 'report_data') {
	if (is_number($_GET['id'])) {
		$report_id           = intval($_GET['id']);
		$hs['report_data']   = hs_ap_info_get_customer_report_data($report_id);
		
		if (not_empty($hs['report_data'])) {
			$data['status']  = 200;
			$data['html']    = hs_loadpage('reports/modals/report_message');

			if ($hs['report_data']['seen'] == '0') {
				$db              = $db->where('id',$report_id);
				$update          = $db->update(T_PROD_REPORTS,array('seen' => '1'));
				$data['is_seen'] = 1;
			}
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Invalid request data. The report with such id does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The report id is missing or invalid!";
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
			$requests       =  hs_ap_info_get_market_payouts(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'status'    => 'pending',
				'order'     => 'ASC'
			));

			$requests = array_reverse($requests);
		}
		else if($offset_to == 'down' && $last_id) {
			$requests       = hs_ap_info_get_market_payouts(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'status'    => 'pending',
			));
		}

		if (not_empty($requests)) {
			foreach ($requests as $hs['req_data']) {
				array_push($html_arr, hs_loadpage('market_payouts/includes/request_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'decline_payout_request') {
	if (is_number($_POST['req_id'])) {
		$req_id  = intval($_POST['req_id']);
		$db      = $db->where('id',$req_id);
		$request = $db->getOne(T_PAYOUT_REQS);
		if (hs_queryset($request,'object')) {
			$db                = $db->where('id',$req_id);
			$update            = $db->update(T_PAYOUT_REQS,array('status' => 'declined'));
			hs_notify(array(
				'notifier_id'  => $me['id'],
				'recipient_id' => $request->user_id,
				'subject'      => 'withdrawal',
				'message'      => 'Your withdrawal request has been declined by the administration of the market!',
				'status'       => '0',
				'time'         => time(),
				'url'          => hs_str_form("merchant_panel/withdrawals"),
			),true);

			$insert_data       =  array(
				'user_id'      => $request->user_id,
				'title'        => 'Payout request is declined!',
				'message'      => 'Your withdrawal request has been declined by the administration of the market!',
				'type'         => 'error',
				'static'       => 'Y',
				'time'         => time()
			); $db->insert(T_ANNOUNC,$insert_data);

			$data['status']  = 200;
			$data['message'] = hs_translate("The payout request has been declined successfully!");
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Invalid request data. The request with such ID does not exists!";
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request ID is missing or invalid!";
	}
}

else if($action == 'accept_payout_request') {
	if (is_number($_POST['req_id'])) {
		$req_id         = intval($_POST['req_id']);
		$db->returnType = 'Array';
		$db             = $db->where('id',$req_id);
		$request        = $db->getOne(T_PAYOUT_REQS);

		if (hs_queryset($request)) {
			$user_id        = intval($request['user_id']);
			$db->returnType = 'Array';
			$user_data      = $db->where('id',$user_id);
			$user_data      = $db->getOne(T_USERS,'wallet');

			if (hs_queryset($user_data)) {
				$db     = $db->where('id',$req_id);
				$update = $db->update(T_PAYOUT_REQS,array('status' => 'paid'));

				hs_update_user_data($user_id,array(
					'wallet' => ($user_data['wallet'] - $request['amount'])
				));

				hs_notify(array(
					'notifier_id'  => $me['id'],
					'recipient_id' => $user_id,
					'subject'    => 'withdrawal',
					'message'    => 'Your withdrawal request has been accepted!',
					'status'     => '0',
					'time'       => time(),
					'url'        => hs_link("merchant_panel/withdrawals"),
				),true);

				$insert_data     =  array(
					'user_id'    => $user_id,
					'title'      => 'Payout request is accepted!',
					'message'    => 'Congratulations! Your withdrawal request has been accepted by our administration team. You are paid {%paid_amount%} through PayPal to {%paypal_link%} link ID',
					'type'       => 'success',
					'static'     => 'Y',
					'json_data'  => json(array(
						'paid_amount' => sprintf("<b>%s %s</b>",hs_currency($request['currency']),$request['amount']),
						'paypal_link' => sprintf("<b>%s</b>",urldecode($request['pp_link'])),
					),true),
					'time'       => time()
				); $db->insert(T_ANNOUNC,$insert_data);

				$data['status']  = 200;
				$data['message'] = hs_translate("The payout request has been accepted successfully!");
			}
			else {
				$data['status']  = 404;
				$data['message'] = "Invalid request data. The request with such ID does not exists!";
			}
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Invalid request data. The request with such ID does not exists!";
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request ID is missing or invalid!";
	}
}

else if($action == 'get_market_checkouts') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to    = strval($_GET['dir']);
		$last_id      = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id     = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$transactions = array();
		$data         = array('status' => 404);
		$html_arr     = array();
		$order_id     = fetch_or_get($_GET['filter']['order_id'],false);
		$order_id     = ((is_number($order_id)) ? $order_id : false);
		$customer     = fetch_or_get($_GET['filter']['customer'],false);
		$customer     = ((len_between($customer,1,60)) ? hs_secure($customer) : false);		
		$seller       = fetch_or_get($_GET['filter']['seller'],false);
		$seller       = ((len_between($seller,1,60)) ? hs_secure($seller) : false);	
		$amount       = fetch_or_get($_GET['filter']['amount'],false);
		$amount       = ((len_between($amount,1,60)) ? hs_secure($amount) : false);		
		$status       = fetch_or_get($_GET['filter']['status'],false);
		$status       = ((in_array($status, array('success','failed'))) ? hs_secure($status) : false);
		$method       = fetch_or_get($_GET['filter']['method'],false);
		$method       = ((in_array($method, array('card','paypal','wallet'))) ? hs_secure($method) : false);

		if ($offset_to == 'up' && $first_id) {
			$transactions   =  hs_ap_info_get_market_checkouts(array(
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'customer'  => $customer,
				'seller'    => $seller,
				'amount'    => $amount,
				'status'    => $status,
				'method'    => $method,
				'order_id'  => $order_id,
				'order'     => 'ASC'
			));

			$transactions = array_reverse($transactions);
		}
		else if($offset_to == 'down' && $last_id) {
			$transactions   =  hs_ap_info_get_market_checkouts(array(
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'customer'  => $customer,
				'seller'    => $seller,
				'amount'    => $amount,
				'status'    => $status,
				'method'    => $method,
				'order_id'  => $order_id,
			));
		}

		if (not_empty($transactions)) {
			foreach ($transactions as $hs['trans_data']) {
				array_push($html_arr, hs_loadpage('market_transactions/includes/transaction_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'filter_market_checkouts') {
	$data          =  array('status' => 404);
	$html_arr      =  array();
	$order_id      =  fetch_or_get($_GET['order_id'],false);
	$order_id      =  ((is_number($order_id)) ? $order_id : false);
	$customer      =  fetch_or_get($_GET['customer'],false);
	$customer      =  ((len_between($customer,1,60)) ? hs_secure($customer) : false);	
	$seller        =  fetch_or_get($_GET['seller'],false);
	$seller        =  ((len_between($seller,1,60)) ? hs_secure($seller) : false);	
	$amount        =  fetch_or_get($_GET['amount'],false);
	$amount        =  ((len_between($amount,1,60)) ? hs_secure($amount) : false);		
	$status        =  fetch_or_get($_GET['status'],false);
	$status        =  ((in_array($status, array('success','failed'))) ? hs_secure($status) : false);
	$method        =  fetch_or_get($_GET['method'],false);
	$method        =  ((in_array($method, array('card','paypal','wallet','cod'))) ? hs_secure($method) : false);
	$transactions  =  hs_ap_info_get_market_checkouts(array(
		'limit'    => 10,
		'customer' => $customer,
		'seller'   => $seller,
		'amount'   => $amount,
		'order_id' => $order_id,
		'status'   => $status,
		'method'   => $method,
		'order'    => 'DESC'
	));

	if (not_empty($transactions)) {
		foreach ($transactions as $hs['trans_data']) {
			array_push($html_arr, hs_loadpage('market_transactions/includes/transaction_li'));
		}

		$data['status'] = 200;
		$data['html']   = implode('', $html_arr);
	}
	else {
		$data['status'] = 404;
		$data['html']   = hs_loadpage('market_transactions/includes/search_data_404');
	}
}

else if ($action == 'save_configs') {	
	$data['status']    = 400;
	$data['err_field'] = null;
	$raw_configs       = $db->get(T_CONFIG);
	$raw_configs       = ((hs_queryset($raw_configs)) ? hs_o2array($raw_configs) : array());
	$exclude_vals      = array('site_logo','site_favicon');

	if ($raw_configs) {
		foreach ($raw_configs as $config_data) {
			if (isset($_POST[$config_data['name']]) && in_array($config_data['name'], $exclude_vals) != true) {
				# Specific values from configurations that 
				# cannot be passed to the hs_secure function

				if ($config_data['name'] == 'google_analytics') {
					$conf_new_val = htmlspecialchars($_POST[$config_data['name']]);
				}
				else if ($config_data['name'] == 'contacts_info') {
					$conf_new_val = hs_secure($_POST[$config_data['name']],true,true);
				}
				else{
					$conf_new_val = hs_secure($_POST[$config_data['name']],false,false);
				}
				
				if ($config_data['regex']) {
					preg_match('/^\{(?P<min>[0-9]{1,11})\,(?P<max>[0-9]{1,11})\}$/is', $config_data['regex'],$val_range);
					if (not_empty($val_range)) {
						$min_len = fetch_or_get($val_range['min'],0);
						$max_len = fetch_or_get($val_range['max'],0);

						if (len_between($conf_new_val,$min_len,$max_len)) {
							hs_ap_save_config($config_data['name'],$conf_new_val);
						}
						else {
							$field_label       =  strval($config_data['title']);
							$data['message']   =  hs_translate('Invalid value of field: {%field_name%}',array(
								'field_name'   => $field_label
							));
							$data['err_field'] =  $config_data['name']; break;
						}
					}
					else {
						if (preg_match($config_data['regex'], $conf_new_val)) {
							hs_ap_save_config($config_data['name'],$conf_new_val);
						}
						else {
							$field_label       =  strval($config_data['title']);
							$data['message']   =  hs_translate('Invalid value of field: {%field_name%}',array(
								'field_name'   => $field_label
							));
							$data['err_field'] =  $config_data['name']; break;
						}
					}
				}
				else {
					hs_ap_save_config($config_data['name'],$conf_new_val);
				}
			}

			else if($config_data['name'] == 'site_logo') {
				if (empty($_FILES['site_logo']) != true) {
					try {
						$file_info  = array(
				            'file'  => $_FILES['site_logo']['tmp_name'],
				            'name'  => $_FILES['site_logo']['name']
				        );

				        $file_upload = hs_upload_logos($file_info,'logo');

				        if (not_empty($file_upload)) {
				        	hs_ap_save_config('site_logo',$file_upload); continue;
				        }
				        else {
				        	$data['message']   = 'An error occurred while uploading the image, please make sure that the "upload/images/" folder is available for writing by the user of the web server. E.g. www-data user';
							$data['err_field'] = 'site_logo_favicon'; break;
				        }

			        } catch (Exception $e) { /*pass*/ }
		        }
			}
			
			else if($config_data['name'] == 'site_favicon') {
				if (empty($_FILES['site_favicon']) != true) {
					try {
						$file_info  = array(
				            'file'  => $_FILES['site_favicon']['tmp_name'],
				            'name'  => $_FILES['site_favicon']['name']
				        );

				        $file_upload = hs_upload_logos($file_info,'favicon');
				        if (not_empty($file_upload)) {
				        	hs_ap_save_config('site_favicon',$file_upload); continue;
				        }
				        else {
				        	$data['message']   = 'An error occurred while uploading the image, please make sure that the "upload/images/" folder is available for writing by the user of the web server. E.g. www-data user';
							$data['err_field'] = 'site_logo_favicon'; break;
				        }
			        } catch (Exception $e) { /*pass*/ }
		        }
			}
		}

		if (empty($data['err_field'])) {
			$data['status']  = 200;
			$data['message'] = hs_translate('Your changes have been saved successfully!');
		}
	}
	else {
		$data['status']    = 500;
		$data['err_field'] = "Error, found while processing your request. Please try again later!";
	}
}

else if($action == 'announce') {
	$data['status']    =  400;
	$data['err_field'] =  null;
	$form_fields       =  array(
		'title'        => fetch_or_get($_POST['title'],null),
		'url'          => fetch_or_get($_POST['url'],''),
		'body'         => fetch_or_get($_POST['body'],null),
		'type'         => fetch_or_get($_POST['type'],null),
	);

	foreach ($form_fields as $field_name => $field_val) {
		if ($field_name == 'title') {
			if (empty($field_val)) {
				$data['err_field'] = 'title';
				$data['message']   = hs_translate('The announcement title is required!');
				break;
			}

			else if (len_between($field_val,1,120) != true) {
				$data['err_field'] = 'title';
				$data['message']   = hs_translate('The announcement title length is too long. Maximun length is 120 characters!');
				break;
			}
		}

		else if($field_name == 'url') {
			if (not_empty($field_val)) {
				if (is_url($field_val) != true) {
					$data['err_field'] = 'url';
					$data['message']   = hs_translate('The announcement url you entered is not valid. Please check your details!');
					break;
				}
			}
		}

		else if ($field_name == 'body') {
			if (empty($field_val)) {
				$data['err_field'] = 'body';
				$data['message']   = hs_translate('The announcement message is required!');
				break;
			}

			else if (len_between($field_val,1,600) != true) {
				$data['err_field'] = 'body';
				$data['message']   = hs_translate('The announcement message length is too long. Maximun length is 600 characters!');
				break;
			}
		}

		else if ($field_name == 'type') {
			if (empty($field_val) || in_array($field_val, array('info','warning','error','success')) != true) {
				$data['err_field'] = 'type';
				$data['message']   = hs_translate('The announcement message type is missing or invalid. Please check your details!');
				break;
			}
		}
	}

	if (empty($data['err_field'])) {
		$db     = $db->where('admin','0');
		$db     = $db->where('active','1');
		$users  = $db->get(T_USERS,null,array('id'));
		$users  = ((hs_queryset($users)) ? hs_o2array($users) : array());
		$insert = array();

		if (not_empty($users)) {
			foreach ($users as $u_data) {
				array_push($insert,array(
					'user_id'      => $u_data['id'],
					'title'        => $form_fields['title'],
					'message'      => $form_fields['body'],
					'url'          => $form_fields['url'],
					'type'         => $form_fields['type'],
					'static'       => 'Y',
					'message_type' => 'custom',
					'time'         => time()
				)); 
			}

			$inserts         = $db->insertMulti(T_ANNOUNC,$insert);
			$data['status']  = 200;
			$data['message'] = hs_translate("Your announcement has been sent to the all active users of the market!");
		}

		else {
			$data['status']  = 500;
			$data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
}

else if($action == 'update_sitemap') {
	$data['status']    =  400;
	$data['err_field'] =  null;
	$update_interval   =  array('daily','always','hourly','weekly','monthly','yearly','never');
	$form_fields       =  array(
		'entries'      => fetch_or_get($_POST['entries'],null),
		'update'       => fetch_or_get($_POST['update'],null),
		'lastmod'      => fetch_or_get($_POST['lastmod'],null),
		'client_time'  => fetch_or_get($_POST['client_time'],null),
	);

	if (is_writable('sitemap') != true) {
		$data['err_field'] = 'null';
		$data['message']   = hs_translate('The sitemaps storage folder does not exists or not writable!');
	}

	else if(is_writable('sitemap/sitemap-index.xml') != true) {
		$data['err_field'] = 'null';
		$data['message']   = hs_translate('The sitemap-index.xml does not exists or not writable!');
	}

	else if(is_writable('sitemap/maps') != true) {
		$data['err_field'] = 'null';
		$data['message']   = hs_translate('The sitemap/maps forder does not exists or not writable!');
	}

	else {
		foreach ($form_fields as $field_name => $field_val) {
			if ($field_name == 'entries') {
				if (not_num($field_val)) {
					$data['err_field'] = 'entries';
					$data['message']   = hs_translate('The entries per sitemap field is required!');
					break;
				}

				else if (int_between($field_val,100,3000) != true) {
					$data['err_field'] = 'entries';
					$data['message']   = hs_translate('The entries per sitemap field value must be between 100 - 3000!');
					break;
				}
			}

			else if($field_name == 'update') {
				if (empty($field_val) || in_array($field_val, $update_interval) != true) {
					$data['err_field'] = 'update';
					$data['message']   = hs_translate('Change frequency field value is missing or invalid!');
					break;
				}
			}

			else if($field_name == 'lastmod') {
				if (empty($field_val) || in_array($field_val, array('none','server_time','client_time')) != true) {
					$data['err_field'] = 'lastmod';
					$data['message']   = hs_translate('Last modification field value is missing or invalid!');
					break;
				}
				else {
					if ($field_val == 'client_time' && empty($form_fields['client_time'])) {
						$data['err_field'] = 'lastmod';
						$data['message']   = hs_translate('Please enter the last modification date or select another option above!');
						break;
					}
				}
			}
		}
	}

	if (empty($data['err_field'])) {
		$db               = $db->where('activity_status','active');
		$db               = $db->where('status','active');
		$db               = $db->where('editing_stage','saved');
		$db               = $db->where('approved','Y');
		$db               = $db->orderBy('id','DESC');
		$db               = $db->orderBy('sold','DESC');
		$db               = $db->orderBy('rating','DESC');
		$prods            = $db->get(T_PRODUCTS,null,array('id'));
		$prods            = (hs_queryset($prods)) ? hs_o2array($prods) : array();
		$hs['sm_lastmod'] = 'None';
		$hs['changefreq'] = $form_fields['update'];

		foreach ($prods as $pind => $prod_data) {
			$prods[$pind]['prod_url'] = hs_link(sprintf("share/product?id=%d",$prod_data['id']));
		}

		if ($form_fields['lastmod'] == 'server_time') {
			$hs['sm_lastmod'] = date('Y-m-d');
		}

		else if($form_fields['lastmod'] == 'client_time') {
			$hs['sm_lastmod'] = $form_fields['client_time'];
		}

		if (not_empty($prods)) {		
			$old_maps           = glob('sitemap/maps/*.xml');
			$old_maps           = ((is_array($old_maps) && not_empty($old_maps)) ? $old_maps : array());
			$maps_chunk         = intval($form_fields['entries']);
			$prods_chunks       = array_chunk($prods, $form_fields['entries']);
			$hs['map_includes'] = array();
			$xml_prod_urls      = array();
			$xml_sitemaps       = array();

			if (not_empty($old_maps)) {
				foreach($old_maps as $old_site_map){
				    try {
				    	@unlink($old_site_map);
				    } catch (Exception $e) { /* pass */ }
				}
			}

			try {
				foreach ($prods_chunks as $ind => $hs['entries_chunk']) {
					foreach ($hs['entries_chunk'] as $prod_item) {
						$xml_prod_urls[] = hs_file('sitemap/temps/url.xml',array(
							'prod_url'   => $prod_item['prod_url'],
							'sm_lastmod' => $hs['sm_lastmod'],
							'changefreq' => $hs['changefreq']
						));
					}

					$sitemap_inc     = sprintf("site-map-%d.xml",$ind);
					$sitemap_include = sprintf("sitemap/maps/%s",$sitemap_inc);

					file_put_contents($sitemap_include,hs_file('sitemap/temps/urlset.xml',array(
						'urlset' => implode('', $xml_prod_urls)
					)));

					array_push($hs['map_includes'], hs_link(sprintf("sitemap/maps/%s",$sitemap_inc)));
				}

				if (not_empty($hs['map_includes'])) {
					foreach ($hs['map_includes'] as $site_map_url) {
						$xml_sitemaps[]  = hs_file('sitemap/temps/sitemap.xml',array(
							'map_url'    => $site_map_url,
							'sm_lastmod' => $hs['sm_lastmod']
						));
					}
					
					file_put_contents('sitemap/sitemap-index.xml',hs_file('sitemap/temps/sitemap-index.xml',array(
						'sitemap_set' => implode('', $xml_sitemaps)
					)));
				}

				$data['status']      = 200;
				$data['message']     = hs_translate('Site map has been updated successfully.');
				$data['last_update'] = date('d F, Y h:m P');
				hs_ap_save_config('last_sitemap_update',$data['last_update']);

			} 

			catch (Exception $e) {
				$data['status']  = 500;
				$data['message'] = 'Error found while processing your request. Please try again later!';
			}
		}
		else {
			$data['err_field'] = 'none';
			$data['status']    = 400;
			$data['message']   = hs_translate('There are no products in your catalog to create a site map.');
		}
	}
}

else if($action =='create_backup') {
	$data['status']  = 500;
	$data['message'] = 'Error found while processing your request. Please try again later!';
	$new_backup      = hs_ap_create_backup();

	if (is_number($new_backup)) {
		$data['status']  =  200;
		$data['message'] =  hs_translate('The new site backup has been successfully created!');
		$insert_data     =  array(
			'user_id'    => $me['id'],
			'title'      => 'The new backup successfully created!',
			'message'    => 'The backup creating process has been successfully finished. We recommend you to download your backups via FTP.',
			'type'       => 'success',
			'static'     => 'N',
			'time'       => time()
		); $db->insert(T_ANNOUNC,$insert_data);
	}
}

else if($action =='delete_backup') {
	$data['status']  = 500;
	$data['message'] = 'Error found while processing your request. Please try again later!';

	if (not_num($_POST['backup_id'])) {
		$data['status']  = 400;
		$data['message'] = 'The backup ID is missing or invalid. Please check your details!';
	}
	else{
		$buckup_id = intval($_POST['backup_id']);
		$db        = $db->where('id',$buckup_id);
		$backup    = $db->getOne(T_BACKUPS);

		if (hs_queryset($backup,'object')) {
			try {
				$backup_dir   = sprintf('site_backups/%s',$backup->backup_dir);
				$backup_files = sprintf('site_backups/%s/%s',$backup->backup_dir,$backup->files_backup);
				$backup_sql   = sprintf('site_backups/%s/%s',$backup->backup_dir,$backup->sql_backup);
				@unlink($backup_files);
				@unlink($backup_sql);
				@rmdir($backup_dir);

				$db              = $db->where('id',$buckup_id);
				$rm_backup       = $db->delete(T_BACKUPS);
				$data['status']  = 200;
				$data['message'] = hs_translate('The backup has been successfully deleted!');
				
			} catch (Exception $e) {/*pass*/}
		}
	}
}

else if($action == 'get_account_removals') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$requests  = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$requests       = hs_ap_info_get_account_removals(array(
				'limit'     => 10,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC'
			));

			$requests = array_reverse($requests);
		}
		else if($offset_to == 'down' && $last_id) {
			$requests       = hs_ap_info_get_account_removals(array(
				'limit'     => 10,
				'offset'    => $last_id,
				'offset_to' => 'lt',
			));
		}

		if (not_empty($requests)) {
			foreach ($requests as $hs['req_data']) {
				array_push($html_arr, hs_loadpage('account_removals/includes/request_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'account_removal_request_message') {
	if (is_number($_GET['id'])) {
		$request_id     =  intval($_GET['id']);
		$requests       =  hs_ap_info_get_account_removals(array(
			'limit'     => 1,
			'ids'       => array($request_id)
		));
		$requests       = ((is_array($requests)) ? $requests : array());
		$hs['req_data'] = fetch_or_get($requests[0],false);

		if (not_empty($hs['req_data'])) {
			$data['status']  = 200;
			$data['html']    = hs_loadpage('account_removals/modals/request_message');
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Invalid request data. The request with such id does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request id is missing or invalid!";
	}
}

else if($action == 'static_page') {
	if (not_empty($_POST['page'])) {
		$page_name = fetch_or_get($_POST['page']);
		$page_cont = fetch_or_get($_POST['content']);

		if ($page_name == 'doc_aboutus_page') {
			try {
				hs_ap_info_set_static_page($page_name,strip_brs($page_cont));
				$data['status']  = 200;
				$data['message'] = hs_translate("Your changes have been saved successfully!");
			} 
			catch (Exception $e) {
				$data['status']  = 400;
				$data['message'] = "Invalid request data. The page name is missing or invalid!";
			}
		}
		else if ($page_name == 'doc_privacy_policy') {
			try {
				hs_ap_info_set_static_page($page_name,strip_brs($page_cont));
				$data['status']  = 200;
				$data['message'] = hs_translate("Your changes have been saved successfully!");
			} 
			catch (Exception $e) {
				$data['status']  = 400;
				$data['message'] = "Invalid request data. The page name is missing or invalid!";
			}
		}
		else if ($page_name == 'doc_terms') {
			try {
				hs_ap_info_set_static_page($page_name,strip_brs($page_cont));
				$data['status']  = 200;
				$data['message'] = hs_translate("Your changes have been saved successfully!");
			} 
			catch (Exception $e) {
				$data['status']  = 400;
				$data['message'] = "Invalid request data. The page name is missing or invalid!";
			}
		}
		else if ($page_name == 'doc_merchant_terms') {
			try {
				hs_ap_info_set_static_page($page_name,strip_brs($page_cont));
				$data['status']  = 200;
				$data['message'] = hs_translate("Your changes have been saved successfully!");
			} 
			catch (Exception $e) {
				$data['status']  = 400;
				$data['message'] = "Invalid request data. The page name is missing or invalid!";
			}
		}
	}
	else{
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The page name is missing or invalid!";
	}
}

else if($action == 'get_customer_refunds') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$requests  = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$requests       =  hs_get_customer_refunds(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC'
			));

			$requests = array_reverse($requests);
		}
		else if($offset_to == 'down' && $last_id) {
			$requests       =  hs_get_customer_refunds(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
			));
		}

		if (not_empty($requests)) {
			foreach ($requests as $hs['req_data']) {
				array_push($html_arr, hs_loadpage('customer_refunds/includes/request_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'get_customer_refund_details') {	
	if (is_number($_GET['request_id'])) {
		$req_id         = intval($_GET['request_id']);
		$hs['req_data'] = hs_get_customer_refund_details($req_id);

		if (not_empty($hs['req_data'])) {
			$data['status'] = 200;
			$data['html']   = hs_loadpage('customer_refunds/modals/request_details');
		}
		else {
			$data['status']  = 500;
			$data['message'] = 'Error found while processing your request. Please try again later!';
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The refund request ID is missing or invalid!";
	}
}

else if($action == 'delete_customer_refund_request') {	
	if (is_number($_GET['request_id'])) {
		$req_id   = intval($_GET['request_id']);
		$req_data = hs_get_customer_refund_details($req_id);

		if (not_empty($req_data)) {
			$refund_fee  = floatval($hs['config']['order_cancellation_fee']);
			$seller_data = $db->where('id',$req_data['seller_id'])->getOne(T_USERS);
			$buyer_data  = $db->where('id',$req_data['buyer_id'])->getOne(T_USERS);

			if (not_empty($seller_data) && not_empty($buyer_data)) {
				$seller_data      = hs_o2array($seller_data);
				$buyer_data       = hs_o2array($buyer_data);
				$seller_wallet    = $seller_data['wallet'];
				$payment_amount   = $req_data['payment_amount'];
				$market_sale_rate = intval($hs['config']['market_sale_rate']);

				if (in_array($req_data['payment_method'], array('card','paypal'))) {

					$seller_wallet = ($seller_data['wallet'] - hs_market_sale_rate($payment_amount));

					if (in_array($req_data['status'], array('declined','expired','returned','failed'))) {
						$seller_wallet = ($seller_wallet - $refund_fee);
					}

					hs_update_user_data($seller_data['id'],array(
						'wallet' => num2digs($seller_wallet)
					));

					if ($req_data['payment_method'] == 'card') {
						$insert_data       =  array(
							'user_id'      => $me['id'],
							'title'        => 'Refund is almost complete!',
							'message'      => 'The cancellation and refund process is almost complete, except that you need to go to the your Stripe dashboard panel, find the transaction with the number: <b>{%trans_id%}</b> and make a refund.',
							'type'         => 'warning',
							'static'       => 'Y',
							'json_data'    => json(array('trans_id' => $req_data['stripe_pid']),true),
							'time'         => time()
						); $db->insert(T_ANNOUNC,$insert_data);
					}

					else if ($req_data['payment_method'] == 'paypal') {
						$insert_data       = array(
							'user_id'      => $me['id'],
							'title'        => 'Refund is almost complete!',
							'message'      => 'Important: The cancellation and refund process is almost complete, except that you need to go to the your Paypal business account, find the transaction with the number: <b>{%trans_id%}</b> and issue a refund.',
							'type'         => 'warning',
							'static'       => 'Y',
							'json_data'    => json(array('trans_id' => $req_data['paypal_pid']),true),
							'time'         => time()
						); $db->insert(T_ANNOUNC,$insert_data);
					}
				}

				else if($req_data['payment_method'] == 'wallet') {

					$seller_wallet = ($seller_data['wallet'] - hs_market_sale_rate($payment_amount));

					if (in_array($req_data['status'], array('declined','expired','returned','failed'))) {
						$seller_wallet = ($seller_wallet - $refund_fee);
					}

					else {
						$payment_amount = ($payment_amount - $refund_fee);
					}

					hs_update_user_data($seller_data['id'],array(
						'wallet' => num2digs($seller_wallet)
					));

					hs_update_user_data($buyer_data['id'],array(
						'wallet' => num2digs(($buyer_data['wallet'] += $payment_amount))
					));

					$insert_data  =  array(
						'user_id' => $me['id'],
						'title'   => 'Refund has been completed succesffully!',
						'message' => "The process of cancellation and refund to the buyer is fully completed.",
						'type'    => 'success',
						'static'  => 'N',
						'time'    => time()
					); $db->insert(T_ANNOUNC,$insert_data);
				}

				else if($req_data['payment_method'] == 'cod'){

					$seller_wallet = ($seller_wallet + (($market_sale_rate / 100) * $payment_amount));

					if (in_array($req_data['status'], array('declined','expired','returned','failed'))) {
						$seller_wallet = ($seller_wallet - $refund_fee);
					}

					hs_update_user_data($seller_data['id'],array(
						'wallet' => num2digs($seller_wallet)
					)); 
					
					#Unblock seller blocked goods
					hs_block_unblock_seller_products($seller_data['id']);

					$insert_data  =  array(
						'user_id' => $me['id'],
						'title'   => 'Order has been canceled!',
						'message' => 'The process of canceling and deleting an order is completed successfully.',
						'type'    => 'success',
						'static'  => 'N',
						'time'    => time()
					); $db->insert(T_ANNOUNC,$insert_data);
				}

				$seller_sales   = hs_dec_merchant_sales($seller_data['id']);
				$prod_id        = intval($req_data['prod_id']);
				$reg_vend_deal  = hs_register_product_vendeal($prod_id,hs_market_sale_rate($payment_amount),'refund');
				$db             = $db->where('id',$req_id);
				$rm_oc_req      = $db->delete(T_ORD_CANCELS);
				$db             = $db->where('id',$req_data['order_id']);
				$rm_ord_data    = $db->delete(T_ORDERS);
				$db             = $db->where('order_id',$req_data['order_id']);
				$rm_ord_tlh     = $db->delete(T_ORD_HIST_TL);
				$db             = $db->where('order_id',$req_data['order_id']);
				$rm_ch_trans    = $db->delete(T_CHKOUT_TRANS);
				$db             = $db->where('order_id',$req_data['order_id']);
				$rm_mark_rev    = $db->delete(T_MRKT_REVENUE);
				$data['status'] = 200;
			}

			else {
				$data['status']  = 500;
				$data['message'] = 'Error found while processing your request. Please try again later!';
			}
		}

		else {
			$data['status']  = 500;
			$data['message'] = 'Error found while processing your request. Please try again later!';
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The refund request ID is missing or invalid!";
	}
}

else if($action == 'get_deleted_products') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$products  = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$products       =  hs_ap_info_get_deleted_products(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC',
			));

			$products = array_reverse($products);
		}
		else if($offset_to == 'down' && $last_id) {
			$products       =  hs_ap_info_get_deleted_products(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
				'order'     => 'DESC',
			));
		}

		if (not_empty($products)) {
			foreach ($products as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('product_removals/includes/prod_list_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'delete_account') {
	if (is_number($_POST['user_id'])) {
		$account_id      = intval($_POST['user_id']);
		$data['status']  = 200; hs_ap_delete_account($account_id);
		$data['message'] = "This account has been successfully deleted!";
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The account ID is missing or invalid!";
	}
}

else if($action == 'as3_api_contest') {
	try {
		include_once('core/libs/s3/vendor/autoload.php');
	    $hs['config']['as3_storage']     = 'on';
	    $hs['config']['as3_onup_delete'] = 'no';       
        $test_upload                     = hs_upload2s3('upload/images/as3-do-not-delete.png');

        if ($test_upload == true) {
        	$data['status']  = 200;
        	$data['message'] = 'Connection test was successful!';
        }

	    else {
	        $data['status']  = 500;
	        $data['message'] = "Error found while processing your request. Please try again later!";
	    }
	}
	catch (Exception $e) {
	    $data['status']  = 400;
	    $data['message'] = $e->getMessage();
	}
}

elseif ($action == 'change_language_sortorder') {
	$sin = fetch_or_get($_POST['sort_order'],0);
	$lid = fetch_or_get($_POST['lang_id'],0);

	if (not_num($sin)) {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The sort item number is missing or invalid!";
	}

	else if (not_num($lid)) {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The language ID is missing or invalid!";
	}

	else {
		$db->returnType = 'Array';
		$db             = $db->where('id',$lid);
		$language_item  = $db->getOne(T_LANGUAGES);
		$sin            = intval($sin);
		$lid            = intval($lid);

		if (hs_queryset($language_item)) {
			if ($language_item['sort_order'] != $sin) {
				$data['status']  =  200;
				$data['message'] =  hs_translate('Your changes have been saved successfully!');
				$db              =  $db->where('id',$lid);
				$up              =  $db->update(T_LANGUAGES,array(
					'sort_order' => $sin
				));


				
				$db->returnType = 'Array';
				$db             = $db->where('sort_order',$sin);
				$db             = $db->where('id',$lid,'<>');
				$dupl           = $db->getOne(T_LANGUAGES);

				if (hs_queryset($dupl)) {
					$db = $db->where('sort_order',$sin);
					$db = $db->where('id',$lid,'<>');
					$up = $db->update(T_LANGUAGES,array(
						'sort_order' => $language_item['sort_order']
					));
				}
			}
			else {
				$data['status']  = 200;
				$data['message'] = hs_translate('Your changes have been saved successfully!');
			}
		}

		else {
			$data['status']  = 400;
			$data['message'] = "Invalid request data. The language with such ID does not exists";
		}
	}
}

elseif ($action == 'add_new_language') {
	$lang_name   = fetch_or_get($_POST['lang_name'],null);
	$alpha2_code = fetch_or_get($_POST['alpha2_code'],null);
	$status      = fetch_or_get($_POST['status'],null);
	$all_langs   = hs_get_languages('all');
	$lang_names  = array();

	foreach ($all_langs as $row) {
		array_push($lang_names, $row['lang_name']);
	}

	if (empty($lang_name)) {
		$data['status']    = 400;
		$data['message']   = hs_translate('Please enter a name for the new language!');
		$data['err_field'] = 'lang_name';
	}

	else if (preg_match('/^[a-z]+$/', $lang_name) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Invalid characters in the language name");
		$data['err_field'] = 'lang_name';
	}

	else if (len_between($lang_name,4,30) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Please enter a language name between 4 and 30 characters!");
		$data['err_field'] = 'lang_name';
	}

	else if(in_array($lang_name, $lang_names)) {
		$data['status']    = 400;
		$data['message']   = hs_translate("This language is already exists!");
		$data['err_field'] = 'lang_name';
	}

	else if (empty($alpha2_code) || in_array($alpha2_code, array_values($hs['country_codes'])) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Alpha-2 country code is missing or invalid!");
		$data['err_field'] = 'alpha2_code';
	}

	else {
		try {
			$t_langs   = T_LANGS;
			$lang_name = strtolower($lang_name);
	        $query     = mysqli_query($mysqli,"ALTER TABLE `{$t_langs}` ADD  `{$lang_name}` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';");

	        if ($query) {
	        	$status        = (($status == 'on') ? 'active' : 'inactive');
	        	$ltp           =  hs_get_first_active_langname();
	        	$up            =  $db->rawQuery("UPDATE `{$t_langs}` SET `{$lang_name}` = `{$ltp}`");
	        	$insert_data   =  array(
	        		'lang_key' => $lang_name,
	        		$lang_name => ucfirst($lang_name)
	        	);

	        	foreach ($lang_names as $row) {
	        		$insert_data[$row] = ucfirst($lang_name);
	        	} $db->insert(T_LANGS,$insert_data);

	        	$db->returnType = 'Array';
	        	$db             = $db->orderBy('sort_order','DESC');
	        	$last_sin       = $db->getOne(T_LANGUAGES,array('sort_order'));
	        	$last_sin       = ((hs_queryset($last_sin)) ? $last_sin : array());
	        	$last_sin_val   = fetch_or_get($last_sin['sort_order'],0);

	        	$db->insert(T_LANGUAGES,array(
	        		'lang_name'  => $lang_name,
	        		'a2_code'    => $alpha2_code,
	        		'status'     => $status,
	        		'sort_order' => ($last_sin_val + 1)
	        	));

	        	$data['status']  = 200;
				$data['message'] = hs_translate("The new language has been successfully added!");

				$insert_data     =  array(
					'user_id'    => $me['id'],
					'title'      => 'The new language has been successfully added',
					'message'    => 'Congratulations! A new interface language has been successfully added. However, you must manually translate the texts into the {%lang_name%} language. Click on this link {%link%} to start editing.',
					'type'       => 'success',
					'static'     => 'Y',
					'json_data'  => json(array(
						'lang_name' => sprintf("<b>%s</b>",ucfirst($lang_name)),
						'link'      => sprintf('<a class="text-inline-link" href="%s">%s</a>',hs_link("merchant_panel/edit_language?lang=$lang_name"),$lang_name),
					),true),
					'time'       => time()
				); $db->insert(T_ANNOUNC,$insert_data);
	        }

	        else {
	        	$data['status']  = 500;
	        	$data['message'] = $db->getLastError();
	        }
		} 

		catch (Exception $e) {
			$data['status']  = 500;
	        $data['message'] = $e->getMessage();
		}
	}
}

else if ($action == 'toggle_lang_status') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$lang_name        = fetch_or_get($_POST['lang'],null);
	$status           = fetch_or_get($_POST['status'],'none');
	$all_langs        = hs_get_languages('all');
	$lang_names       = array();
	$active_languages = hs_get_languages('active');

	foreach ($all_langs as $row) {
		array_push($lang_names, $row['lang_name']);
	}

	if (empty($lang_name) || in_array($lang_name, $lang_names) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(in_array($status, array('on','off')) != true) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if (count($active_languages) < 2 && $status == 'off') {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['action']   = 'undo';
		$data['message']  = hs_translate("You cannot disable all interface languages, at least one language must be enabled");
	} 

	else {
		$status          = (($status == 'on') ? 'active' : 'inactive');
		$db              = $db->where('lang_name',$lang_name);
		$up              = $db->update(T_LANGUAGES,array('status' => $status));
		$data['status']  = 200;
		$data['message'] = hs_translate("Language status has been successfully changed!");
		$active_lang     = hs_get_first_active_langname();

		if ($status == 'inactive') {
			$db = $db->where('language',$lang_name);
			$up = $db->update(T_USERS,array(
				'language' => $active_lang
			));
		}

		if ($lang_name == $hs['config']['language']) {
			hs_ap_save_config('language',$active_lang);
		}
	}
}

else if ($action == 'delete_language') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$lang_name        = fetch_or_get($_POST['lang_name'],null);
	$all_langs        = hs_get_languages('all');
	$lang_names       = array();
	$active_languages = hs_get_languages('active');

	foreach ($all_langs as $row) {
		array_push($lang_names, $row['lang_name']);
	}

	if (empty($lang_name) || in_array($lang_name, $lang_names) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if (count($active_languages) < 2) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = hs_translate("You cannot remove this interface language since there are no active languages to use!");
	} 

	else {
		try {
			$t_langs         =  T_LANGS;
			$db              =  $db->where('lang_name',$lang_name);
			$query           =  $db->delete(T_LANGUAGES);
			$db              =  $db->where('lang_key',$lang_name);
			$query           =  $db->delete(T_LANGS);
			$query           =  $db->rawQuery("ALTER TABLE `{$t_langs}` DROP `{$lang_name}`;");

			$data['status']  =  200;
			$data['message'] =  hs_translate("Language has been successfully deleted!");
			$active_lang     =  hs_get_first_active_langname();

			$db              =  $db->where('language',$lang_name);
			$query           =  $db->update(T_USERS,array(
				'language'   => $active_lang
			));

			if ($lang_name == $hs['config']['language']) {
				hs_ap_save_config('language',$active_lang);
			}
		} 
		catch (Exception $e) {
			$data['status']  = 500;
	        $data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
}

else if($action == 'search_langs') {
	if (not_empty($_GET['keyword'])) {
		$el_name             = hs_session('el_name');
		$hs['el_name']       = $el_name;
		$hs['langs_dataset'] = array();
		$data                = array('status' => 404);
		$html_arr            = array();
		$keyword             = fetch_or_get($_GET['keyword'],'');
		$keyword             = trim($keyword);
		$keyword             = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if($keyword && not_empty($hs['el_name'])) {
			$hs['langs_dataset'] =  hs_ap_info_get_lang_datasets(array(
				'limit'          => 10,
				'keyword'        => $keyword,
				'language'       => $hs['el_name']
			));
		}

		if (not_empty($hs['langs_dataset']) && not_empty($hs['el_name'])) {
			foreach ($hs['langs_dataset'] as $hs['lang_data_li']) {
				array_push($html_arr, hs_loadpage('edit_language/includes/lang_data_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}

		else {
			$hs['search_query'] = $keyword;
			$data['status']     = 404;
			$data['html']       = hs_loadpage('edit_language/includes/search_data_404');
		}
	}
}

else if($action == 'get_languages') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$el_name             = hs_session('el_name');
		$hs['el_name']       = $el_name;
		$offset_to           = strval($_GET['dir']);
		$last_id             = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id            = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$hs['langs_dataset'] = array();
		$data                = array('status' => 404);
		$html_arr            = array();
		$keyword             = fetch_or_get($_GET['keyword'],'');
		$keyword             = trim($keyword);
		$keyword             = ((len_between(trim($keyword),1, 60) ) ? hs_secure($keyword) : '');

		if ($offset_to == 'up' && $first_id && not_empty($el_name)) {
			$hs['langs_dataset'] =  hs_ap_info_get_lang_datasets(array(
				'limit'          => 10,
				'offset'         => $first_id,
				'offset_to'      => 'lt',
				'order'          => 'DESC',
				'keyword'        => $keyword,
				'language'       => $el_name,
			));

			$hs['langs_dataset'] = array_reverse($hs['langs_dataset']);
		}
		else if($offset_to == 'down' && $last_id && not_empty($el_name)) {
			$hs['langs_dataset'] =  hs_ap_info_get_lang_datasets(array(
				'limit'          => 10,
				'offset'         => $last_id,
				'offset_to'      => 'gt',
				'order'          => 'ASC',
				'keyword'        => $keyword,
				'language'       => $el_name,
			));
		}

		if (not_empty($hs['langs_dataset'])) {
			foreach ($hs['langs_dataset'] as $hs['lang_data_li']) {
				array_push($html_arr, hs_loadpage('edit_language/includes/lang_data_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'save_language_text') {
	$el_name          = hs_session('el_name');
	$lang_text        = fetch_or_get($_POST['lang_text'],null);
	$lang_id          = fetch_or_get($_POST['lang_id'],0);
	$data['err_code'] = 0;

	if (empty($el_name)) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else if(not_num($lang_id)) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else if(empty($lang_text)) {
		$data['err_code']  = 3;
		$data['err_field'] = 'lang_text';
		$data['status']    = 400;
		$data['message']   = hs_translate("The language text is missing or invalid!");
	}

	else if(len_between($lang_text,2,500) != true) {
		$data['err_code']  = 4;
		$data['err_field'] = 'lang_text';
		$data['status']    = 400;
		$data['message']   = hs_translate("Language text must be between 2 and 500 characters long");
	}

	if (empty($data['err_code'])) {
		$lang_text       = hs_secure($lang_text);
		$db              = $db->where('id',$lang_id);
		$up              = $db->update(T_LANGS,array($el_name => $lang_text));
		$data['status']  = 200;
		$data['message'] = hs_translate('Your changes have been saved successfully!');
	}
}

else if($action == 'get_categories') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to       = strval($_GET['dir']);
		$last_id         = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id        = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$hs['all_catgs'] = array();
		$data            = array('status' => 404);
		$html_arr        = array();

		if ($offset_to == 'up' && $first_id) {
			$hs['all_catgs'] =  hs_ap_info_get_prod_categories(array(
				'limit'      => 10,
				'offset'     => $first_id,
				'offset_to'  => 'lt',
				'order'      => 'DESC',
			));

			$hs['all_catgs'] = array_reverse($hs['all_catgs']);
		}
		else if($offset_to == 'down' && $last_id) {
			$hs['all_catgs'] =  hs_ap_info_get_prod_categories(array(
				'limit'      => 10,
				'offset'     => $last_id,
				'offset_to'  => 'gt',
				'order'      => 'ASC',
			));
		}

		if (not_empty($hs['all_catgs'])) {
			foreach ($hs['all_catgs'] as $hs['catg_data']) {
				array_push($html_arr, hs_loadpage('prod_categories/includes/catg_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

elseif ($action == 'change_catg_sortorder') {
	$sin = fetch_or_get($_POST['sort_order'],0);
	$cid = fetch_or_get($_POST['catg_id'],null);

	if (not_num($sin)) {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The sort item number is missing or invalid!";
	}

	else if (empty($cid)) {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The category ID is missing or invalid!";
	}

	else {
		$db->returnType = 'Array';
		$db             = $db->where('catg_id',$cid);
		$catg_item      = $db->getOne(T_PROD_CATS);
		$sin            = intval($sin);
		$cid            = $cid;

		if (hs_queryset($catg_item)) {
			if ($catg_item['sort_order'] != $sin) {
				$data['status']  =  200;
				$data['message'] =  hs_translate('Your changes have been saved successfully!');
				$db              =  $db->where('catg_id',$cid);
				$up              =  $db->update(T_PROD_CATS,array(
					'sort_order' => $sin
				));
				
				$db->returnType = 'Array';
				$db             = $db->where('sort_order',$sin);
				$db             = $db->where('catg_id',$cid,'<>');
				$dupl           = $db->getOne(T_PROD_CATS);

				if (hs_queryset($dupl)) {
					$db = $db->where('sort_order',$sin);
					$db = $db->where('catg_id',$cid,'<>');
					$up = $db->update(T_PROD_CATS,array(
						'sort_order' => $catg_item['sort_order']
					));
				}
			}
			else {
				$data['status']  = 200;
				$data['message'] = hs_translate('Your changes have been saved successfully!');
			}
		}

		else {
			$data['status']  = 400;
			$data['message'] = "Invalid request data. The category with such ID does not exists";
		}
	}
}

else if ($action == 'toggle_catg_status') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$catg_id          = fetch_or_get($_POST['cid'],null);
	$status           = fetch_or_get($_POST['status'],'none');

	if(in_array($status, array('on','off')) != true) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if (count($hs['categories']) < 2 && $status == 'off') {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = hs_translate("You cannot disable all product categories, at least one category must be enabled");
	} 

	else {
		$status          = (($status == 'on') ? 'active' : 'inactive');
		$db              = $db->where('catg_id',$catg_id);
		$up              = $db->update(T_PROD_CATS, array('status' => $status));
		$data['status']  = 200;
		$data['message'] = hs_translate("Category status has been successfully changed!");

		if ($status == 'inactive') {
			$db = $db->where('category',$catg_id);
			$up = $db->update(T_PRODUCTS,array(
				'category'      => 'none',
				'editing_stage' => 'unsaved',
				'approved'      => 'N',
			));
		}
	}
}

elseif ($action == 'add_new_category') {
	$catg_name = fetch_or_get($_POST['catg_name'],null);
	$status    = fetch_or_get($_POST['status'],null);
	$all_catgs = hs_get_product_categories('all','all');

	if (empty($catg_name)) {
		$data['status']    = 400;
		$data['message']   = hs_translate('Please enter a name for the new category!');
		$data['err_field'] = 'catg_name';
	}

	else if (preg_match('/^[\w]+$/', hs_txtslug($catg_name)) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Invalid characters in the category name");
		$data['err_field'] = 'catg_name';
	}

	else if (len_between($catg_name,4,55) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Please enter a category name between 4 and 55 characters!");
		$data['err_field'] = 'catg_name';
	}

	else if(in_array($catg_name, array_keys($all_catgs))) {
		$data['status']    = 400;
		$data['message']   = hs_translate("This category is already exists!");
		$data['err_field'] = 'catg_name';
	}

	else {
		try {
        	$status        =  (($status == 'on') ? 'active' : 'inactive');
        	$catg_id       =  hs_txtslug($catg_name);
        	$lang_key      =  hs_gen_lang_key($catg_name);
        	$all_langs     =  hs_get_languages('all');
        	$insert_data   =  array(
        		'lang_key' => $lang_key,
        	);

        	foreach ($all_langs as $row) {
        		$insert_data[$row['lang_name']] = ucfirst($catg_name);
        	} $db->insert(T_LANGS,$insert_data);

        	$db->returnType = 'Array';
        	$db             = $db->orderBy('sort_order','DESC');
        	$last_sin       = $db->getOne(T_PROD_CATS,array('sort_order'));
        	$last_sin       = ((hs_queryset($last_sin)) ? $last_sin : array());
        	$last_sin_val   = fetch_or_get($last_sin['sort_order'],1);

        	$db->insert(T_PROD_CATS,array(
        		'catg_id'    => $catg_id,
        		'catg_name'  => $catg_name,
        		'status'     => $status,
        		'sort_order' => ($last_sin_val + 1)
        	));

        	$data['status']  = 200;
			$data['message'] = hs_translate("The new product category has been successfully added!");
		} 

		catch (Exception $e) {
			$data['status']  = 500;
	        $data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
}

else if ($action == 'delete_category') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$catg_id          = fetch_or_get($_POST['catg_id'],null);
	$all_catgs        = hs_get_product_categories('all','all');
	$active_catgs     = hs_get_product_categories('all','active');
	$catg_name        = null;

	if (empty($catg_id) || in_array($catg_id, array_keys($all_catgs)) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if (count($active_catgs) < 2) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = hs_translate("You cannot delete this product category, as there are no active product categories to use!");
	} 

	else {
		try {
			$catg_name          =  fetch_or_get($all_catgs[$catg_id],'none');
			$db                 =  $db->where('catg_id',$catg_id);
			$query              =  $db->delete(T_PROD_CATS);
			$db                 =  $db->where('lang_key', hs_gen_lang_key($catg_name));
			$query              =  $db->delete(T_LANGS);
			$sort_order_iter    =  1;

			$data['status']     =  200;
			$data['message']    =  hs_translate("Product category has been successfully deleted!");

			$db                 =  $db->where('category',$catg_id);
			$up                 =  $db->update(T_PRODUCTS,array(
				'category'      => 'none',
				'editing_stage' => 'unsaved',
				'approved'      => 'N',
			));

			foreach ($all_catgs as $cid => $cname) {
				if ($cid != $catg_id) {
					$db = $db->where('catg_id',$cid);
					$up = $db->update(T_PROD_CATS,array(
						'sort_order' => $sort_order_iter
					)); $sort_order_iter += 1;
				}
			}
		} 
		catch (Exception $e) {
			$data['status']  = 500;
	        $data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
}

elseif ($action == 'add_new_currency') {
	$curr_name   = fetch_or_get($_POST['curr_name'],null);
	$curr_code   = fetch_or_get($_POST['curr_code'],null);
	$curr_symbol = fetch_or_get($_POST['curr_symbol'],null);
	$currencies  = hs_get_currencies();
	$site_currs  = array();

	foreach ($currencies as $curr_data) {
		$site_currs[] = $curr_data['curr_code'];
	}

	if (empty($curr_name) || len_between($curr_name,4,50) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate('Please enter a name for the new currency from 4 to 50 characters long!');
		$data['err_field'] = 'curr_name';
	}

	else if (empty($curr_code)) {
		$data['status']    = 400;
		$data['message']   = hs_translate('Please enter a code of the new currency!');
		$data['err_field'] = 'curr_code';
	}

	else if (preg_match('/^[a-z]+$/', hs_txtslug($curr_code)) != true) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Invalid characters in the currency code!");
		$data['err_field'] = 'curr_code';
	}

	else if (len($curr_code) != 3) {
		$data['status']    = 400;
		$data['message']   = hs_translate("Please enter the standard currency code ISO 4217 Alpha-3!");
		$data['err_field'] = 'curr_code';
	}

	else if(in_array($curr_code, $site_currs)) {
		$data['status']    = 400;
		$data['message']   = hs_translate("This currency is already exists!");
		$data['err_field'] = 'curr_code';
	}

	else if(empty($curr_symbol) || len($curr_symbol) > 3) {
		$data['status']    = 400;
		$data['message']   = hs_translate("The currency symbol is missing or entered incorrectly. Please enter a unique currency symbol in UTF-8 format!");
		$data['err_field'] = 'curr_symbol';
	}

	else {
		try {
        	$insert_data      =  array(
        		'curr_name'   => $curr_name,
        		'curr_code'   => $curr_code,
        		'curr_symbol' => $curr_symbol,
        	); $db->insert(T_CURRENCIES,$insert_data);

        	$data['status']  = 200;
			$data['message'] = hs_translate("The new currency has been successfully added!");
		} 

		catch (Exception $e) {
			$data['status']  = 500;
	        $data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
}

else if($action == 'set_default_currency') {
	$data['err_code'] = 0;
	$curr_id          = fetch_or_get($_POST['curr_id'],'none');
	$data['status']   = 400;

	if (not_num($curr_id)) {
		$data['status']   = 400;
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$db->returnType = 'Array';
		$db             = $db->where('id',$curr_id);
		$curr_data      = $db->getOne(T_CURRENCIES);

		if (hs_queryset($curr_data)) {
			if ($curr_data['curr_code'] != $hs['config']['market_currency']) {
				$data['status']   =  200; hs_ap_save_config('market_currency',$curr_data['curr_code']);
				$data['message']  =  hs_translate("Market currency has been successfully changed to: {%curr_name%}",array(
					'curr_name'   => sprintf("<b>%s</b>",$curr_data['curr_name'])
				));
			}
			else {
				$data['status']   = 400;
				$data['err_code'] = 2;
				$data['message']  = "Error: Invalid request data. This currency is already used by default!";
			}
		}
		else {
			$data['status']   = 400;
			$data['err_code'] = 3;
			$data['message']  = "Error: Invalid request data. The currency with such ID does not exists!";
		}
	}
}

else if($action == 'delete_currency') {
	$data['err_code'] = 0;
	$curr_id          = fetch_or_get($_POST['curr_id'],'none');
	$data['status']   = 400;

	if (not_num($curr_id)) {
		$data['status']   = 400;
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$db->returnType = 'Array';
		$db             = $db->where('id',$curr_id);
		$curr_data      = $db->getOne(T_CURRENCIES);

		if (hs_queryset($curr_data)) {
			if ($curr_data['curr_code'] != $hs['config']['market_currency']) {
				$data['status']  =  200;
				$data['message'] =  hs_translate("The currency {%currency%} was successfully deleted!",array(
					'currency'   => hs_html_el('b',$curr_data['curr_name'])
				));
				$db              =  $db->where('id',$curr_id);
				$rm              =  $db->delete(T_CURRENCIES);
			}
			else {
				$data['status']   = 400;
				$data['err_code'] = 2;
				$data['message']  = hs_translate("You cannot delete the default currency of the market!");
			}
		}
		else {
			$data['status']   = 400;
			$data['err_code'] = 3;
			$data['message']  = "Error: Invalid request data. The currency with such ID does not exists!";
		}
	}
}

else if($action == 'get_transaction_details') {
	$data['err_code'] = 0;
	$trans_id         = fetch_or_get($_GET['trans_id'],false);
	$data['status']   = 400;

	if (not_num($trans_id)) {
		$data['status']   = 400;
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$hs['trans_data'] = hs_ap_info_get_transaction_details($trans_id);
		if (hs_queryset($hs['trans_data'])) {
			$data['status'] = 200;
			$data['html']   = hs_loadpage('market_transactions/modals/transaction_details');
		}
		else {
			$data['status']   = 400;
			$data['err_code'] = 3;
			$data['message']  = "Error: Invalid request data. The transaction with such ID does not exists!";
		}
	}
}

else if($action == 'edit_account') {
	$data['err_code'] = 0;
	$account_id       = fetch_or_get($_GET['account_id'],false);
	$data['status']   = 400;

	if (not_num($account_id)) {
		$data['status']   = 400;
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$hs['acc_data'] = hs_user_data($account_id);
		$hs['acc_data'] = ((hs_queryset($hs['acc_data'],'object')) ? hs_o2array($hs['acc_data']) : array());

		if (not_empty($hs['acc_data'])) {
			$data['status'] = 200;
			$data['html']   = hs_loadpage('market_users/modals/edit_user_data');
		}
		else {
			$data['status']   = 400;
			$data['err_code'] = 2;
			$data['message']  = "Error: Invalid request data. The user with such ID does not exists!";
		}
	}
}

else if ($action == 'upload_user_avatar') {
	$data['message'] = "Error: Invalid request data. Please try again later!";
    $data['status']  = 400;
    $update_data     = array();
    $user_id         = fetch_or_get($_POST['user_id'],null);
    $user_id         = ((is_number($user_id)) ? $user_id : false);

    if (not_empty($user_id) && not_empty($_FILES['avatar'])) {
    	$db->returnType = 'Array';
    	$db             =  $db->where('id',$user_id);
    	$user_data      =  $db->getOne(T_USERS);
        $file_info      =  array(
            'file'      => $_FILES['avatar']['tmp_name'],
            'size'      => $_FILES['avatar']['size'],
            'name'      => $_FILES['avatar']['name'],
            'type'      => $_FILES['avatar']['type'],
            'crop'      => array('width' => 120, 'height' => 120),
            'allowed'   => 'jpg,png,jpeg,gif'
        );

        if (hs_queryset($user_data)) {

	        $file_upload = hs_upload($file_info);

	        if (not_empty($file_upload['filename'])) {
	            $update_data['avatar'] = $file_upload['filename'];     
	            $data['status']        = 200;
	            $data['url']           = hs_get_media($file_upload['filename']);
	            $rm                    = hs_delete_image($user_data['avatar']);
	            $up                    = hs_update_user_data($user_id,$update_data);
	        } 
	        else{
	        	$data['status']  = 500;
	        	$data['message'] = "Error found while processing your request, please try again later.";
	        }
        }
        else {
        	$data['status']  = 500;
	        $data['message'] = "Error found while processing your request, please try again later.";
        }
    }
}

else if ($action == 'update_user_data' && not_empty($_POST['user_id'])) {
	$data['message']   = "Error: Invalid request data. Please try again later!";
    $data['status']    = 400;
	$data['err_field'] = null;
	$user_id           = fetch_or_get($_POST['user_id'],null);
    $user_id           = ((is_number($user_id)) ? $user_id : false);
	$db->returnType    = 'Array';
    $db                = $db->where('id',$user_id);
    $user_data         = $db->getOne(T_USERS);

    if (not_empty($user_data)) {
		$user_data_fields  =  array(
			'fname'        => fetch_or_get($_POST['fname']),
			'lname'        => fetch_or_get($_POST['lname']),
			'bio'          => fetch_or_get($_POST['bio']),
			'email'        => fetch_or_get($_POST['email']),
			'phone'        => fetch_or_get($_POST['phone']),
			'whatsapp'     => fetch_or_get($_POST['whatsapp']),
			'facebook'     => fetch_or_get($_POST['facebook']),
			'instagram'    => fetch_or_get($_POST['instagram']),
			'google_plus'  => fetch_or_get($_POST['google_plus']),
			'twitter'      => fetch_or_get($_POST['twitter']),
			'youtube'      => fetch_or_get($_POST['youtube'])
		);

		foreach ($user_data_fields as $field_name => $field_val) {
			if ($field_name == 'fname') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("First name field is required for the user and should not be empty!");
		            $data['err_field'] = $field_name; break;
		        }

				else if (len_between($field_val,3,25) != true) {
		            $data['message']   = hs_translate("The first name field length must be between 3 / 25 characters length");
		            $data['err_field'] = $field_name; break;
		        }

				else if (preg_match('/^[\w\s]+$/',$field_val) != true) {
		            $data['message']   = hs_translate("The first name contains invalid characters. Allowed characters are a-z");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if ($field_name == 'lname') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("The last name field is required for the user and should not be empty!");
		            $data['err_field'] = $field_name; break;
		        }

				else if (len_between($field_val,3,25) != true) {
		            $data['message']   = hs_translate("The last name field length must be between 3 / 25 characters length");
		            $data['err_field'] = $field_name; break;

		        }

				else if (preg_match('/^[\w\s]+$/',$field_val) != true) {
		            $data['message']   = hs_translate("The last name contains invalid characters. Allowed characters are a-z");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if ($field_name == 'bio') {
				if (not_empty($field_val)) {
		            if (len_between($field_val,12,215) != true) {
			            $data['message']   = hs_translate("The user bio field length must be between 12 / 220 characters length");
			            $data['err_field'] = $field_name; break;
			        }
		        }
			}

			else if ($field_name == 'email') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("The e-mail address field is required for the user and should not be empty!");
		            $data['err_field'] = $field_name; break;
		        }

				else if ($user_data['email'] != $field_val && hs_email_exists($field_val)) {
		            $data['message']   = hs_translate("This E-mail is already taken!");
		            $data['err_field'] = $field_name; break;
		        }

		        else if (filter_var($field_val, FILTER_VALIDATE_EMAIL) != true || (len($field_val) > 30)) {
		            $data['message']   = hs_translate("This e-mail is invalid. Please enter a valid E-mail address!");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if ($field_name == 'phone') {
				if (not_empty($field_val)) {
			        if (preg_match('/^[0-9\-\(\)\/\+\s]*$/',$field_val) != true) {
			            $data['message']   = hs_translate("The phone number you entered is not vlaid!");
			            $data['err_field'] = $field_name; break;
			        }
			        else if (len($field_val) < 7 || len($field_val) > 15) {
			            $data['message']   = hs_translate("The phone number you entered is not vlaid!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if ($field_name == 'whatsapp') {
				if (not_empty($field_val)) {
			        if (preg_match('/^[0-9\-\(\)\/\+\s]*$/',$field_val) != true) {
			            $data['message']   = hs_translate("The whatsapp number you entered is not vlaid.");
			            $data['err_field'] = $field_name; break;
			        }
			        else if (len($field_val) < 7 || len($field_val) > 15) {
			            $data['message']   = hs_translate("The whatsapp number you entered is not vlaid.");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if(in_array($field_name, array('youtube','twitter','google_plus','facebook','instagram'))) {
				if (not_empty($field_val) && is_url($field_val) != true) {
					$data['message']   = hs_translate("Please enter a valid url address!");
			        $data['err_field'] = $field_name; break;
				}
			}
		}

		if (empty($data['err_field'])) {
			$user_id     = $user_data['id'];
	        $update_data = array();

	        foreach ($user_data_fields as $field_name => $field_val) {
	        	if ($field_name == 'fname' || $field_name == 'lname') {
	        		$field_val = trim($field_val);
	        	}
	        	
	        	$field_val                = ((not_empty($field_val)) ? hs_secure($field_val) : '');
	        	$update_data[$field_name] = $field_val;
	        }

	        $db     = $db->where('id',$user_id);
	        $update = $db->update(T_USERS, $update_data);

	        if (not_empty($update)) {
	            $data         =  array(
		            'status'  => 200,
		            'message' => hs_translate('Your changes have been successfully saved!'),
		        );
	        }

	        else{
	        	$data         =  array(
		            'status'  => 400,
		            'message' => 'Error found while processing your request, please try again later.',
		        );
	        }
	    }
    }
    else {
    	$data['message'] = "Error: Invalid request data. User with such ID does not exists!";
    	$data['status']  = 404;
    }
}

else if ($action == 'update_addres_info' && not_empty($_POST['user_id'])) {
	$data['message']   = "Error: Invalid request data. Please try again later!";
    $data['status']    = 400;
	$data['err_field'] = null;
	$user_id           = fetch_or_get($_POST['user_id'],null);
    $user_id           = ((is_number($user_id)) ? $user_id : false);
	$db->returnType    = 'Array';
    $db                = $db->where('id',$user_id);
    $user_data         = $db->getOne(T_USERS);

    if (not_empty($user_data)) {
		$user_data_fields =  array(
			'country_id'  => fetch_or_get($_POST['country_id']),
			'state'       => fetch_or_get($_POST['state']),
			'city'        => fetch_or_get($_POST['city']),
			'street'      => fetch_or_get($_POST['street']),
			'off_apt'     => fetch_or_get($_POST['off_apt']),
			'zip_postal'  => fetch_or_get($_POST['zip_postal']),
		);

		foreach ($user_data_fields as $field_name => $field_val) {
			if ($field_name == 'country_id') {
				if (not_empty($field_val)) {
			        if (in_array($field_val,array_keys($hs['countries'])) != true) {
			            $data['message']   = hs_translate("The user country field value is not valid!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if ($field_name == 'state') {
				if (not_empty($field_val)) {
			        if (len_between($field_val,3,45) != true) {
			            $data['message']   = hs_translate("Invalid state name. Please check your details!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if ($field_name == 'city') {
				if (not_empty($field_val)) {
			        if (len_between($field_val,3,55) != true) {
			            $data['message']   = hs_translate("Invalid city name. Please check your details!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if ($field_name == 'street') {
				if (not_empty($field_val)) {
			        if (len_between($field_val,3,60) != true) {
			            $data['message']   = hs_translate("Invalid street name. Please check your details!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if ($field_name == 'off_apt') {
				if (not_empty($field_val)) {
			        if (len_between($field_val,4,60) != true) {
			            $data['message']   = hs_translate("Invalid office or apartment address. Please check your details!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}

			else if ($field_name == 'zip_postal') {
				if (not_empty($field_val)) {
			        if (not_num($field_val) || len_between($field_val,5,10) != true) {
			            $data['message']   = hs_translate("Invalid zip/postal code. Please check your details!");
			            $data['err_field'] = $field_name; break;
			        }
				}
			}
		}

		if (empty($data['err_field'])) {
			$user_id     = $user_data['id'];
	        $update_data = array();

	        foreach ($user_data_fields as $field_name => $field_val) {
	        	$field_val                = hs_secure($field_val);
	        	$update_data[$field_name] = $field_val;
	        }

	        $db     = $db->where('id',$user_id);
	        $update = $db->update(T_USERS, $update_data);

	        if (not_empty($update)) {
	            $data         =  array(
		            'status'  => 200,
		            'message' => hs_translate('Your changes have been successfully saved!'),
		        );
	        }

	        else{
	        	$data         =  array(
		            'status'  => 400,
		            'message' => 'Error found while processing your request, please try again later.',
		        );
	        }
	    }
    }
    else {
    	$data['message'] = "Error: Invalid request data. User with such ID does not exists!";
    	$data['status']  = 404;
    }
}

else if ($action == 'update_user_advanced_info' && not_empty($_POST['user_id'])) {
	$data['message']   = "Error: Invalid request data. Please try again later!";
    $data['status']    = 400;
	$data['err_field'] = null;
	$user_id           = fetch_or_get($_POST['user_id'],null);
    $user_id           = ((is_number($user_id)) ? $user_id : false);
	$db->returnType    = 'Array';
    $db                = $db->where('id',$user_id);
    $user_data         = $db->getOne(T_USERS);

    if (not_empty($user_data)) {
		$user_data_fields =  array(
			'wallet'      => fetch_or_get($_POST['wallet'],'0.00'),
			'verified'    => fetch_or_get($_POST['verified'],'0'),
			'user_type'   => fetch_or_get($_POST['user_type'],false),
		);

		foreach ($user_data_fields as $field_name => $field_val) {
			if ($field_name == 'wallet') {
				if (is_numeric($field_val) != true || floatval($field_val) < 0) {
			        $data['message']   = hs_translate("The account balance amount is invalid!");
			        $data['err_field'] = $field_name; break;
				}
			}

			else if ($field_name == 'verified') {
				if (in_array($field_val, array('0','1')) != true) {
			        $data['message']   = "Error: Invalid request data. Please check your details";
			        $data['err_field'] = $field_name; break;
				}
			}
			
			else if ($field_name == 'user_type') {
				if (in_array($field_val, array('moder','user')) != true) {
			        $data['message']   = "Error: Invalid request data. Please check your details";
			        $data['err_field'] = $field_name; break;
				}
			}
		}

		if (empty($data['err_field'])) {
			$user_id     = $user_data['id'];
	        $update_data = array();

	        foreach ($user_data_fields as $field_name => $field_val) {
	        	if (in_array($field_name, array('user_type')) != true) {
	        		$field_val                = hs_secure($field_val);
	        		$update_data[$field_name] = $field_val;
	        	}
	        }

	        $db     = $db->where('id',$user_id);
	        $update = $db->update(T_USERS, $update_data);

	        if (not_empty($update)) {
	        	$up           =  hs_block_unblock_seller_products($user_id);
	        	$user_type    =  fetch_or_get($user_data_fields['user_type'],false);
	            $data         =  array(
		            'status'  => 200,
		            'message' => hs_translate('Your changes have been successfully saved!'),
		        );

		        if ($user_type == 'moder') {
		        	if (hs_is_admin($user_id) != true) {
		        		$up                =  hs_update_user_data($user_id,array('admin' => '1'));
		        		$insert_data       =  array(
		        			'user_id'      => $user_id,
		        			'time'         => time()
		        		); 

		        		$ins               =  $db->insert(T_ADMINS,$insert_data);
						$ins               =  $db->insert(T_ANNOUNC,array(
							'user_id'      => $user_id,
							'title'        => 'Moderator account!',
							'message'      => 'Congratulations that your account has been privileged as a moderator of the site, now you have the ability to manage advanced features of the site',
							'url'          => hs_link('merchant_panel/dashboard'),
							'type'         => 'success',
							'static'       => 'Y',
							'message_type' => 'system',
							'time'         => time()
						));
		        	}
		        }
		        else {
		        	if (hs_is_admin($user_id) == true) {
			        	$db                =  $db->where('user_id',$user_id);
			        	$rm                =  $db->delete(T_ADMINS);
			        	$up                =  hs_update_user_data($user_id,array('admin' => '0'));
			        	$ins               =  $db->insert(T_ANNOUNC,array(
							'user_id'      => $user_id,
							'title'        => 'Deprivation of moderator privileges!',
							'message'      => 'Unfortunately, your account has been deprived of the privileges of a site moderator, now you do not have the ability to manage advanced features of the site',
							'url'          => hs_link('merchant_panel/dashboard'),
							'type'         => 'warning',
							'static'       => 'Y',
							'message_type' => 'system',
							'time'         => time()
						));
					}
		        }
	        }

	        else{
	        	$data         =  array(
		            'status'  => 400,
		            'message' => 'Error found while processing your request, please try again later.',
		        );
	        }
	    }
    }
    else {
    	$data['message'] = "Error: Invalid request data. User with such ID does not exists!";
    	$data['status']  = 404;
    }
}

else if($action == 'disable_account') {
	$data['err_code'] = 0;
	$account_id       = fetch_or_get($_POST['user_id'],false);
	$status           = fetch_or_get($_POST['status'],false);
	$data['status']   = 400;

	if (not_num($account_id)) {
		$data['status']   = 400;
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else if(in_array($status, array('disable','enable')) != true) {
		$data['status']   = 400;
		$data['err_code'] = 2;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		if ($status == 'enable') {
			$up              = hs_update_user_data($account_id,array('active' => '1'));
			$data['status']  = 200;
			$data['message'] = hs_translate('This account has been successfully enabled!');
			$db              = $db->where('user_id',$account_id);
			$up              = $db->update(T_PRODUCTS,array('status' => 'active'));
		}
		else {
			$up              = hs_update_user_data($account_id,array('active' => '2'));
			$data['status']  = 200;
			$data['message'] = hs_translate('This account has been successfully disabled!');
			$db              = $db->where('user_id',$account_id);
			$up              = $db->update(T_PRODUCTS,array('status' => 'inactive'));
		}
	}
}

else if($action == 'delete_moder') {
	$data['err_code'] = 0;
	$moder_id         = fetch_or_get($_POST['user_id'],false);
	$data['status']   = 400;

	if (not_num($moder_id)) {
		$data['status']   = 400;
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$data['status']    =  200;
		$data['message']   =  hs_translate('Moderator has been successfully deleted');
		$db                =  $db->where('user_id',$moder_id);
    	$rm                =  $db->delete(T_ADMINS);
    	$up                =  hs_update_user_data($moder_id,array('admin' => '0'));
    	$ins               =  $db->insert(T_ANNOUNC,array(
			'user_id'      => $moder_id,
			'title'        => 'Deprivation of moderator privileges!',
			'message'      => 'Unfortunately, your account has been deprived of the privileges of a site moderator, now you do not have the ability to manage advanced features of the site',
			'url'          => hs_link('merchant_panel/dashboard'),
			'type'         => 'warning',
			'static'       => 'Y',
			'message_type' => 'system',
			'time'         => time()
		));
	}
}

else if($action == 'get_account_verifications') {
	if (not_empty($_GET['dir']) && in_array($_GET['dir'], array('up','down'))) {
		$offset_to = strval($_GET['dir']);
		$last_id   = ((is_number($_GET['last'])) ? intval($_GET['last']) : 0);
		$first_id  = ((is_number($_GET['first'])) ? intval($_GET['first']) : 0);
		$requests  = array();
		$data      = array('status' => 404);
		$html_arr  = array();

		if ($offset_to == 'up' && $first_id) {
			$requests       =  hs_ap_info_get_account_verification(array(
				'limit'     => 7,
				'offset'    => $first_id,
				'offset_to' => 'gt',
				'order'     => 'ASC'
			));

			$requests = array_reverse($requests);
		}
		else if($offset_to == 'down' && $last_id) {
			$requests       =  hs_ap_info_get_account_verification(array(
				'limit'     => 7,
				'offset'    => $last_id,
				'offset_to' => 'lt',
			));
		}

		if (not_empty($requests)) {
			foreach ($requests as $hs['req_data']) {
				array_push($html_arr, hs_loadpage('account_verification/includes/request_li'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if($action == 'account_verification_req_data') {
	if (is_number($_GET['id'])) {
		$request_id     =  intval($_GET['id']);
		$requests       =  hs_ap_info_get_account_verification(array(
			'limit'     => 1,
			'ids'       => array($request_id)
		));

		$requests       = ((is_array($requests)) ? $requests : array());
		$hs['req_data'] = fetch_or_get($requests[0],false);

		if (not_empty($hs['req_data'])) {
			$data['status']  = 200;
			$data['html']    = hs_loadpage('account_verification/modals/request_message');
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Invalid request data. The request with such id does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request id is missing or invalid!";
	}
}

else if($action == 'delete_verification_request') {
	if (is_number($_POST['id'])) {
		$request_id      = intval($_POST['id']);
		$db->returnType  = 'Array';
		$db              = $db->where('id',$request_id);
		$req_data        = $db->getOne(T_VERIF_REQS);

		if (hs_queryset($req_data)) {
			$db = $db->where('id',$request_id);
			$rm = $db->delete(T_VERIF_REQS);
			$rm = hs_delete_image($req_data['id_photo']);
			$rm = hs_delete_image($req_data['pr_photo']);

			$data['status']  = 200;
			$data['message'] = hs_translate('Verification request was successfully deleted!');
		}

		else {
			$data['status']  = 400;
			$data['message'] = "Invalid request data. The request with such ID does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request id is missing or invalid!";
	}
}

else if($action == 'reject_verification_request') {
	if (is_number($_POST['id'])) {
		$request_id      = intval($_POST['id']);
		$db->returnType  = 'Array';
		$db              = $db->where('id',$request_id);
		$req_data        = $db->getOne(T_VERIF_REQS);

		if (hs_queryset($req_data)) {
			if ($req_data['status'] == 'pending') {		
				$user_id     =  intval($req_data['user_id']);
				$db          =  $db->where('id',$request_id);
				$up          =  $db->update(T_VERIF_REQS,array(
					'status' => 'rejected'
				));

				$db->insert(T_ANNOUNC,array(
					'user_id'      => $user_id,
					'title'        => 'Request rejected!',
					'message'      => 'Unfortunately, your request for verification of your account was rejected by the market administrator. Please contact us if you have any questions',
					'url'          => hs_link('merchant_panel/main_settings'),
					'type'         => 'error',
					'static'       => 'Y',
					'message_type' => 'system',
					'time'         => time()
				));

				$data['status']  = 200;
				$data['message'] = hs_translate('Verification request was successfully rejected!');
			}

			else {
				$data['status']  = 400;
				$data['message'] = "Invalid request data. Please check your details!";
			}
		}
		
		else {
			$data['status']  = 400;
			$data['message'] = "Invalid request data. The request with such ID does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request id is missing or invalid!";
	}
}

else if($action == 'accept_verification_request') {
	if (is_number($_POST['id'])) {
		$request_id      = intval($_POST['id']);
		$db->returnType  = 'Array';
		$db              = $db->where('id',$request_id);
		$req_data        = $db->getOne(T_VERIF_REQS);

		if (hs_queryset($req_data)) {
			if ($req_data['status'] == 'pending') {		
				$user_id = intval($req_data['user_id']);
				$db      = $db->where('id',$request_id);
				$rm      = $db->delete(T_VERIF_REQS);
				$up      = hs_update_user_data($user_id,array('verified' => '1'));

				$db->insert(T_ANNOUNC,array(
					'user_id'      => $user_id,
					'title'        => 'Verified account',
					'message'      => 'Congratulations your account has been successfully verified at your request, thank you for verifying your identity',
					'url'          => hs_link('merchant_panel/main_settings'),
					'type'         => 'success',
					'static'       => 'Y',
					'message_type' => 'system',
					'time'         => time()
				));

				$data['status']  = 200;
				$data['message'] = hs_translate('This user has been verified successfully!');
			}

			else {
				$data['status']  = 400;
				$data['message'] = "Invalid request data. Please check your details!";
			}
		}
		
		else {
			$data['status']  = 400;
			$data['message'] = "Invalid request data. The request with such ID does not exists!";
		}
	}

	else {
		$data['status']  = 400;
		$data['message'] = "Invalid request data. The request id is missing or invalid!";
	}
}