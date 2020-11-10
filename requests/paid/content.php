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

elseif (empty($_GET['method']) || in_array($_GET['method'],array('card','paypal','yandmoney','qiwi','wallet','cod')) != true) {
	hs_redirect('/');
} 

else if(empty($_GET['code']) || in_array($_GET['code'],array('success','cancel')) != true) {
	hs_redirect('/');
}

else {
	$hs['page_title'] = hs_translate('Checkout - {%name%}',array('name' => $hs['config']['name']));
    $hs['page_desc']  = $hs['config']['description'];
    $hs['page_kw']    = $hs['config']['keywords'];
	$hs['pn']         = 'paid';
	$hs['header_st']  = false;
	$my_id            = $me['id'];	
	$items_data       = hs_get_order_items_data($my_id);
	$hs['items_data'] = $items_data;
	$payment_method   = strval($_GET['method']);
	$payment_status   = false;
	$orders_insert    = array();
	$stripe_pid       = null;
	$paypal_pid       = null;
	$wallet_pid       = null;
	$cod_pid          = null;
	$market_sale_rate = intval($hs['config']['market_sale_rate']);
	$seller_ids       = array();

	if ($payment_method == 'card' && not_empty($items_data)) {
		if ($hs['config']['stripe_gateway_status'] == 'off') {
			$hs['site_content'] =  hs_loadpage('paid/error',array(
				'title'         => "Payment Gateway Error!",
				'message'       => "This payment method has been disabled and is not available for placing an order!",
				'url'           => hs_link('contact_us'),
				'call2action'   => hs_translate('Get help'),
			));
		}

		else if(empty(hs_session('payment_data'))) {
			$hs['site_content'] =  hs_loadpage('paid/error',array(
				'title'         => "Transaction failed",
				'message'       => "Payment failed. Try again or use a different payment method.",
				'url'           => hs_link('contact_us'),
				'call2action'   => hs_translate('Get help'),
			));
		}
		else{
			try {
				$payment_data   = hs_session('payment_data');
				$stripe_sess_id = $payment_data['stripe_payment_session_id'];
				$session_object = \Stripe\Checkout\Session::retrieve($stripe_sess_id);
				$intent         = \Stripe\PaymentIntent::retrieve($session_object->payment_intent);

				if (hs_queryset($intent,'object') && not_empty($intent->status)) {
					if ($intent->status == 'succeeded') {
						$payment_status = true;
						$stripe_pid     = fetch_or_get($intent->id,'none'); 
						#Save payment trans. Id, for case of order cancelation!
					} 

					else if($intent['status'] == 'requires_payment_method') {
						$hs['site_content'] = hs_loadpage('paid/error',array(
							'title'         => "Transaction failed",
							'message'       => "The payment attempt was interrupted by a technical failure or the user did not pay for the goods and is trying to verify the payment",
							'url'           => hs_link('contact_us'),
							'call2action'   => hs_translate('Get help'),
						));
					} 

					else if($intent['status'] == 'requires_action' && isset($intent['next_action'])) {
						$hs['site_content'] = hs_loadpage('paid/error',array(
							'title'         => "Transaction failed",
							'message'       => "The payment from a customers's card requires additional actions",
							'url'           => hs_link('contact_us'),
							'call2action'   => hs_translate('Get help'),
						));
					} 

					else {
						$hs['site_content'] = hs_loadpage('paid/error',array(
							'title'         => "Transaction failed",
							'message'       => "Payment request returned unexpected code. Please try again later!",
							'url'           => hs_link('contact_us'),
							'call2action'   => hs_translate('Get help'),
						));
					}
				}
				else {
					$hs['site_content'] = hs_loadpage('paid/error',array(
						'title'         => "Transaction failed",
						'message'       => "An error occurred while processing your transaction. Please try again later.",
						'url'           => hs_link('contact_us'),
						'call2action'   => hs_translate('Get help'),
					));
				}
			} 

			catch (Exception $e) {
				$hs['site_content'] = hs_loadpage('paid/error',array(
					'title'         => "Transaction failed",
					'message'       => "An error occurred while processing your transaction. Please try again later.",
					'url'           => hs_link('contact_us'),
					'call2action'   => hs_translate('Get help'),
				));
			}
		}
	}

	else if($payment_method == 'paypal' && not_empty($items_data)) {
		if($hs['config']['paypal_gateway_status'] == 'off') {
			$hs['site_content'] =  hs_loadpage('paid/error',array(
				'title'         => "Payment Gateway Error!",
				'message'       => "This payment method has been disabled and is not available for placing an order!",
				'url'           => hs_link('contact_us'),
				'call2action'   => hs_translate('Get help'),
			));
		}

		else if (empty($_GET['paymentId']) || empty($_GET['token']) || empty($_GET['PayerID'])) {
			$hs['site_content'] =  hs_loadpage('paid/error',array(
				'title'         => "Error: Invalid request data",
				'message'       => "The paypal payment details are missing",
				'url'           => hs_link('contact_us'),
				'call2action'   => hs_translate('Get help'),
			));
		}
		 
		else {
			try {
				$paym_id  = hs_secure(strval($_GET['paymentId']));
				$paym_tok = hs_secure(strval($_GET['token']));
				$payer_id = hs_secure(strval($_GET['PayerID']));
				$payment  = \PayPal\Api\Payment::get($paym_id, $paypal);
			    $execute  = new \PayPal\Api\PaymentExecution();
			    $execute  = $execute->setPayerId($payer_id);

			    try{
			    	if (hs_paypal_pid_exists($paym_id) == false) {
			    		$result         = $payment->execute($execute, $paypal);
				        $payment_status = true;
				        $paypal_pid     = $paym_id;
			    	}
			    	else {
			    		$hs['site_content'] =  hs_loadpage('paid/error',array(
							'title'         => "Transaction failed",
							'message'       => "Invalid PayPal Payment ID. Please check your details!",
							'url'           => hs_link('contact_us'),
							'call2action'   => hs_translate('Get help'),
						));
			    	}
			    }

			    catch (Exception $e) {
			        $hs['site_content'] =  hs_loadpage('paid/error',array(
						'title'         => "Transaction failed",
						'message'       => "An error occurred while processing your transaction. Please try again later.",
						'url'           => hs_link('contact_us'),
						'call2action'   => hs_translate('Get help'),
					));
			    }
			} 

			catch (Exception $e) {
				$hs['site_content'] =  hs_loadpage('paid/error',array(
					'title'         => "Transaction failed",
					'message'       => "An error occurred while processing your transaction. Please try again later.",
					'url'           => hs_link('contact_us'),
					'call2action'   => hs_translate('Get help'),
				));
			}
		}
	}

	else if($payment_method == 'wallet' && not_empty($items_data)) {
		if ($hs['config']['wallet_gateway_status'] == 'on') {
			if (empty($_GET['payment_id'])) {
				$hs['site_content'] =  hs_loadpage('paid/error',array(
					'title'         => 'Invalid Request Payment ID',
					'message'       => 'The wallet payment ID is invalid or missing in get request',
					'url'           => hs_link('contact_us'),
					'call2action'   => 'Get help',
				));
			}

			if(empty(hs_session('payment_data'))) {
				$hs['site_content'] = hs_loadpage('paid/error',array(
					'title'         => 'Payment ID Error',
					'message'       => 'The wallet payment ID is invalid or missing',
					'url'           => hs_link('contact_us'),
					'call2action'   => 'Get help',
				));
			}
			else{
				$payment_data = hs_session('payment_data');
				if (empty($payment_data['user_wallet_payment_id'])) {
					$hs['site_content'] =  hs_loadpage('paid/error',array(
						'title'         => 'Payment ID Error',
						'message'       => 'The wallet payment ID is invalid or missing',
						'url'           => hs_link('contact_us'),
						'call2action'   => 'Get help',
					));
				}
				else if($payment_data['user_wallet_payment_id'] != $_GET['payment_id']) {
					$hs['site_content'] =  hs_loadpage('paid/error',array(
						'title'         => 'Payment ID Error',
						'message'       => 'The wallet payment ID You sent is not valid. Please check your detals!',
						'url'           => hs_link('contact_us'),
						'call2action'   => 'Get help',
					));
				}
				else {
					$wallet_pid     = $payment_data['user_wallet_payment_id'];
					$payment_status = true;
					#Unset payment id from user session
					hs_session_unset('payment_data');
				}
			}
		}

		else {
			$hs['site_content'] =  hs_loadpage('paid/error',array(
				'title'         => "Payment Gateway Error!",
				'message'       => "This payment method has been disabled and is not available for placing an order!",
				'url'           => hs_link('contact_us'),
				'call2action'   => hs_translate('Get help'),
			));
		}
	}

	else if($payment_method == 'cod' && not_empty($items_data)) {
		if ($hs['config']['cod_gateway_status'] == 'on') {
			if (empty($_GET['payment_id'])) {
				$hs['site_content'] =  hs_loadpage('paid/error',array(
					'title'         => 'Invalid Request Payment ID',
					'message'       => 'The COD payment ID is invalid or missing in get request',
					'url'           => hs_link('contact_us'),
					'call2action'   => 'Get help',
				));
			}

			if(empty(hs_session('payment_data'))) {
				$hs['site_content'] =  hs_loadpage('paid/error',array(
					'title'         => 'Payment ID Error',
					'message'       => 'The COD payment ID is invalid or missing',
					'url'           => hs_link('contact_us'),
					'call2action'   => 'Get help',
				));
			}
			else{
				$payment_data = hs_session('payment_data');
				if (empty($payment_data['user_cod_payment_id'])) {
					$hs['site_content'] =  hs_loadpage('paid/error',array(
						'title'         => 'Payment ID Error',
						'message'       => 'The COD payment ID is invalid or missing',
						'url'           => hs_link('contact_us'),
						'call2action'   => 'Get help',
					));
				}
				else if($payment_data['user_cod_payment_id'] != $_GET['payment_id']) {
					$hs['site_content'] =  hs_loadpage('paid/error',array(
						'title'         => 'Payment ID Error',
						'message'       => 'The COD payment ID You sent is not valid. Please check your detals!',
						'url'           => hs_link('contact_us'),
						'call2action'   => 'Get help',
					));
				}
				else {
					$cod_pid        = $payment_data['user_cod_payment_id'];
					$payment_status = true;
					#Unset payment id from user session
					hs_session_unset('payment_data');
				}
			}
		}

		else {
			$hs['site_content'] =  hs_loadpage('paid/error',array(
				'title'         => "Payment Gateway Error!",
				'message'       => "This payment method has been disabled and is not available for placing an order!",
				'url'           => hs_link('contact_us'),
				'call2action'   => hs_translate('Get help'),
			));
		}
	}

	else{
		$hs['site_content'] =  hs_loadpage('paid/error',array(
			'title'         => 'Transaction failed',
			'message'       => 'Unknown error accured while processing your payment request data',
			'url'           => hs_link('contact_us'),
			'call2action'   => hs_translate('Get help'),
		));
	}

	if ($payment_status  === true && not_empty($items_data)) {
		if (in_array($payment_method, array('card','paypal','yandmoney','qiwi','wallet','cod'))) {
			foreach ($items_data as $order_data) {
				$purchased_prod_id    =  intval($order_data['prod_id']);
				$purchased_prodvar_id =  (is_number($order_data['var_id']) ? intval($order_data['var_id']) : 0);
				$sale_price_total     =  ($order_data['sale_price'] * $order_data['qt']);
				$sale_price_total     =  ($sale_price_total + (($order_data['ship_cost'] == 'paid') ? floatval($order_data['ship_fee']) : 0));		
				$orders_insert[]      =  array(
					'seller_id'       => $order_data['seller_id'],
					'buyer_id'        => $my_id,
					'prod_id'         => $purchased_prod_id,
					'prod_sp'         => $order_data['sale_price'],
					'prod_rp'         => $order_data['reg_price'],
					'prod_sf'         => $order_data['ship_fee'],
					'prod_sc'         => $order_data['ship_cost'],
					'paid_amount'     => $sale_price_total,
					'var_id'          => $purchased_prodvar_id,
					'var_type'        => $order_data['var_type'],
					'quantity'        => $order_data['qt'],
					'status'          => 'pending',
					'cust_name'       => $me['address']['full_name'],
					'cust_phone'      => $me['address']['phone'],
					'cust_street'     => $me['address']['street'],
					'cust_off_apt'    => $me['address']['off_apt'],
					'cust_country'    => $me['address']['country'],
					'cust_state'      => $me['address']['state'],
					'cust_city'       => $me['address']['city'],
					'cust_zip'        => $me['address']['zip_postal'],
					'cust_email'      => $me['address']['email'],
					'time'            => time(),
				);

				$seller_id            = intval($order_data['seller_id']);	
				$seller_rate          = hs_market_sale_rate($sale_price_total);
				$reg_seller_rate      = hs_reg_user_sale($seller_id,$sale_price_total,$payment_method);
			
				hs_inc_merchant_sales($seller_id);

				hs_register_product_vendeal($purchased_prod_id,$seller_rate,'purchase');
				hs_update_product_stock_status($purchased_prod_id,$purchased_prodvar_id,$order_data['qt']);

				#Register me as a customer of the product owner
				hs_upsert_customer($seller_id);

				hs_notify(array(
					'notifier_id'  => $me['id'],
					'recipient_id' => $seller_id,
					'subject'      => 'order',
					'message'      => 'has placed an order for your product',
					'status'       => '0',
					'time'         => time(),
					'url'          => hs_str_form("{0}/product/{1}",array($config['url'],$order_data['prod_id'])),
				),true);

				if (in_array($seller_id, $seller_ids) != true) {
					$seller_ids[] = $seller_id;
				}
			}

			if (not_empty($orders_insert)) {
				$db            = $db->where('user_id',$my_id);
				$empty_basket  = $db->where('status','active')->delete(T_BASKET);
				$placed_orders = $db->insertMulti(T_ORDERS,$orders_insert);

				if (is_array($placed_orders) && hs_all($placed_orders,'numeric')) {
					$hs['site_content'] = hs_loadpage('paid/success');

					foreach ($placed_orders as $key => $ord_id) {
						$sale_price_total  =  ($items_data[$key]['sale_price'] * $items_data[$key]['qt']);
						$sale_price_total  =  ($sale_price_total + (($items_data[$key]['ship_cost'] == 'paid') ? floatval($items_data[$key]['ship_fee']) : 0));
						$transaction_id    =  $db->insert(T_CHKOUT_TRANS,array(
							'order_id'     => $ord_id,
							'seller_id'    => $items_data[$key]['seller_id'],
							'buyer_id'     => $my_id,
							'amount'       => $sale_price_total,
							'prod_id'      => $items_data[$key]['prod_id'],
							'var_id'       => (is_number($items_data[$key]['var_id']) ? intval($items_data[$key]['var_id']) : 0),
							'var_type'     => $items_data[$key]['var_type'],
							'status'       => 'success',
							'method'       => $payment_method,
							'stripe_pid'   => (($payment_method == 'card') ? $stripe_pid : 'none'),
							'paypal_pid'   => (($payment_method == 'paypal') ? $paypal_pid : 'none'),
							'wallet_pid'   => (($payment_method == 'wallet') ? $wallet_pid : 'none'),
							'cod_pid'      => (($payment_method == 'cod') ? $cod_pid : 'none'),
							'market_rate'  => $hs['config']['market_sale_rate'],
							'time'         => time(),
						));

					    $markrt_rate   =  (($market_sale_rate / 100) * $sale_price_total);
					    $markrt_rate   =  number_format($markrt_rate,2,'.','');
					    $insert_data   =  array(
					        'order_id' => $ord_id,
					        'trans_id' => $transaction_id,
					        'amount'   => $markrt_rate,
					        'rate'     => $market_sale_rate,
					        'time'     => time(),
					    ); $db->insert(T_MRKT_REVENUE,$insert_data);
					}

					if ($payment_method == 'cod') {
						if (not_empty($seller_ids) && hs_all($seller_ids,'numeric')) {
							foreach ($seller_ids as $seller_id) {
								hs_block_unblock_seller_products($seller_id);
							}
						}
					}
				}

				else {
					$hs['site_content'] =  hs_loadpage('paid/error',array(
						'title'         => "Transaction failed",
						'message'       => "Something went wrong while processing the data, please wait"
					));
				}
			}

			else {
				$hs['site_content'] =  hs_loadpage('paid/error',array(
					'title'         => "Transaction failed",
					'message'       => "Something went wrong while processing the data, please wait"
				));
			}
		}
		else {
			#Handle here bank transfer payments
		}
	}
}