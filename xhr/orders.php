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

if ($action == 'order_status_update') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$order_id         = fetch_or_get($_POST['ord_id'],false);
	
	if (not_num($order_id)) {
		$data['message']  = "HTTP Error 400 Bad Request. An order ID is invalid or missing!";
		$data['err_code'] = 'order_id_error';
	}
	else if(hs_order_is_canceled($order_id)) {
		$data['message']  = "HTTP Error 400 Bad Request. An order with this identifier has already been canceled!";
		$data['err_code'] = 'order_id_error';
	}
	else if(empty($_POST['status']) || in_array($_POST['status'], array_keys($hs['ord_stats'])) != true) {
		$data['message']  = hs_translate("Please select an order status for your update!");
		$data['err_code'] = 'post_status';
	}
	else if(not_empty($_POST['comment']) && trim($_POST['comment']) && len_between($_POST['comment'],15,600) != true) {
		$data['message']  = hs_translate("Comments should be between 15 - 600 characters in length");
		$data['err_code'] = 'post_comment';
	}
	else {
		$user_id      = $me['id'];
		$order_id     = intval($order_id);
		$tb_orders    = T_ORDERS;
		$tb_ordhis    = T_ORD_HIST_TL;
		$db           = $db->where('id',$order_id);
		$order_data   = $db->where('seller_id',$user_id)->getOne($tb_orders);
		$ord_status   = strval($_POST['status']);
		$notify_buyer = ((not_empty($_POST['notify'])) ? true : false);
		$comment      = (not_empty($_POST['comment'])) ? hs_croptxt(hs_secure($_POST['comment']),600) : "***";

		if(hs_queryset($order_data,'object')) {
			$order_timeline = json($order_data->timeline);
			$order_timeline = ((is_array($order_timeline)) ? $order_timeline : array());

			if (in_array($ord_status, $order_timeline)) {
				$data['status']   = 400;
				$data['message']  = hs_translate('You previously set this status to your order, therefore, you cannot set the same status twice');
				$data['err_code'] = 'post_status';
			}

			else if(in_array($order_data->status, array('canceled','expired','returned','failed'))) {
				$data['message']  = "HTTP Error 400 Bad Request. This order has already been suspended!";
				$data['err_code'] = "server_error";
			}

			else if (in_array($ord_status, array('canceled','expired','returned','failed'))) {
				$db             = $db->where('order_id',$order_id);
				$op_transaction = $db->getOne(T_CHKOUT_TRANS);
				$op_transaction = ((hs_queryset($op_transaction,'object')) ? hs_o2array($op_transaction) : array());

				if (not_empty($op_transaction)) {
					$insert_data         =  array(
						'seller_id'      => $user_id,
						'buyer_id'       => intval($order_data->buyer_id),
						'order_id'       => $order_id,
						'status'         => $ord_status,
						'buyer_notified' => (($notify_buyer) ? 'y' : 'n'),
						'comment'        => $comment,
						'time'           => time(),
					); $ins_id           =  $db->insert($tb_ordhis,$insert_data);

					if (is_number($ins_id)) {
						$data['status']   = 200;
						$data['message']  = hs_translate("Order status has been updated successfully!");
						$data['url']      = hs_link(sprintf('merchant_panel/order_details/%d',$order_id));

						$db               =  $db->where('id',$order_id);
						$up               =  $db->update(T_ORDERS,array(
							'status'      => $ord_status,
							'cancellation_time' => time()
						));

						$insert_data      =  array(
							'order_id'    => $order_id,
							'trans_id'    => $op_transaction['id'],
							'seller_id'   => $order_data->seller_id,
							'buyer_id'    => $order_data->buyer_id,
							'prod_id'     => $order_data->prod_id,
							'status'      => (($ord_status == 'canceled') ? 'declined' : $ord_status),
							'time'        => time(),
						); $db->insert(T_ORD_CANCELS,$insert_data);

						hs_notify(array(
							'notifier_id'  => $me['id'],
							'recipient_id' => intval($order_data->buyer_id),
							'subject'      => 'order',
							'message'      => 'has updated your order status',
							'status'       => '0',
							'time'         => time(),
							'url'          => hs_link(sprintf('merchant_panel/order_invoice/%d',$order_id))
						),false);
					}
					else {
						$data['status']   = 500;
						$data['message']  = "Something went wrong, please try again";
						$data['err_code'] = "server_error";
					}	
				}

				else {
					$data['message']  = "HTTP Error 400 Bad Request. Payment transaction does not exists!";
					$data['err_code'] = "server_error";
				}
			}

			else {
				$insert_data         =  array(
					'seller_id'      => $user_id,
					'buyer_id'       => intval($order_data->buyer_id),
					'order_id'       => $order_id,
					'status'         => $ord_status,
					'buyer_notified' => (($notify_buyer) ? 'y' : 'n'),
					'comment'        => $comment,
					'time'           => time(),
				); $ins_id           =  $db->insert($tb_ordhis,$insert_data);

				if (is_number($ins_id)) {
					$db               = $db->where('id',$ins_id);
					$hist_post        = $db->getOne($tb_ordhis);
					$data['status']   = 200;
					$hs['tl_post']    = hs_o2array($hist_post);
					$data['post']     = hs_loadpage('order_details/includes/posts_list_item');
					$data['message']  = hs_translate("Order status has been updated successfully!");
					$order_timeline[] = $ord_status;

					$update_status    =  $db->where('id',$order_id)->update($tb_orders,array(
						'status'      => $ord_status,
						'timeline'    => json($order_timeline,1)
					));

					if ($notify_buyer == true) {
						hs_notify(array(
							'notifier_id'  => $me['id'],
							'recipient_id' => intval($order_data->buyer_id),
							'subject'      => 'order',
							'message'      => 'has updated your order status',
							'status'       => '0',
							'time'         => time(),
							'url'          => hs_link(sprintf('merchant_panel/order_invoice/%d',$order_id))
						),false);
					}
				}
				else {
					$data['status']   = 500;
					$data['message']  = "Something went wrong, please try again";
					$data['err_code'] = "server_error";
				}
			}
		}

		else {
			$data['message']  = "HTTP Error 400 Bad Request. Order with such ID does not exists!";
			$data['err_code'] = "server_error";
		}
	}
}

else if($action == 'cancel_order') {
	$data['status'] = 400;
	$order_id       = fetch_or_get($_POST['order_id'],false);

	if (is_number($order_id)) {
		if (hs_order_is_canceled($order_id) == true) {
			$data['status']  = 500;
			$data['message'] = "Error found while processing your request. Please try again later!";
		}
		else {
			$order_id   = intval($order_id);
			$db         = $db->where('id',$order_id);
			$db         = $db->where('buyer_id',$me['id']);
			$order_data = $db->getOne(T_ORDERS);
			$order_data = ((hs_queryset($order_data,'object')) ? hs_o2array($order_data) : array());

			if (not_empty($order_data)) {
				$db  = $db->where('order_id',$order_data['id']);
				$opt = $db->getOne(T_CHKOUT_TRANS);
				$opt = ((hs_queryset($opt,'object')) ? hs_o2array($opt) : array());

				if (not_empty($opt)) {
					$db                     = $db->where('id',$order_id);
					$up                     = $db->update(T_ORDERS,array(
						'status'            => 'canceled',
						'cancellation_time' => time()
					));

					$insert_data       =  array(
						'order_id'     => $order_id,
						'trans_id'     => $opt['id'],
						'seller_id'    => $order_data['seller_id'],
						'buyer_id'     => $order_data['buyer_id'],
						'prod_id'      => $order_data['prod_id'],
						'status'       => 'canceled',
						'time'         => time(),
					); $db->insert(T_ORD_CANCELS,$insert_data);

					$data['status']    = 200;
					$data['message']   = hs_translate('Your order cancellation request has been sent successfully.');

					hs_notify(array(
						'notifier_id'  => $me['id'],
						'recipient_id' => intval($order_data['seller_id']),
						'subject'      => 'order_cancellation',
						'message'      => 'has canceled his order for your product',
						'status'       => '0',
						'time'         => time(),
						'url'          => hs_link(sprintf('merchant_panel/order_details/%d',$order_id))
					),false);
				}
				else {
					$data['status']  = 500;
					$data['message'] = "Error found while processing your request. Please try again later!";
				}
			}
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Error: The order ID is missing or invalid. Please check your details!";
	}
}

else if($action == 'get_print_template') {
	if (is_number($_GET['order_id'])) {
		$order_id         = intval($_GET['order_id']);
		$hs['order_data'] = hs_get_order_invoice($order_id);

		if (not_empty($hs['order_data'])) {
			$data['status'] = 200;
			$data['html']   = hs_loadpage('order_invoice/includes/print_template');
		}
		else {
			$data['status']  = 500;
			$data['message'] = "Error found while processing your request. Please try again later!";
		}
	}
	else {
		$data['status']  = 400;
		$data['message'] = "Error: The order ID is missing or invalid. Please check your details!";
	}
}