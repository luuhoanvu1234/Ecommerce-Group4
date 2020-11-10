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


function hs_get_customer_orders($args = array()) {
	global $db,$hs;
	$args    = (is_array($args)) ? $args : array();
	$options = array(
        "offset"    => null,
        "limit"     => 7,
        "seller_id" => null,
        "order"     => 'DESC',
        "offset_to" => null,
        'keyword'   => null
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $seller_id      =  $args['seller_id'];
    $offset_to      =  $args['offset_to'];
    $keyword        =  $args['keyword'];
    $data           =  array();
    $sql            =  hs_sqltepmlate('orders/hex_get_customer_orders',array(
        'offset'    => $offset,
        'limit'     => $limit,
        'order'     => $order,
        'seller_id' => $seller_id,
        'offset_to' => $offset_to,
        'keyword'   => $keyword,
        't_orders'  => T_ORDERS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
        't_pvars'   => T_PROD_VARS,
    ));

    $orders = $db->rawQuery($sql);

    if (hs_queryset($orders) == true) {
    	foreach ($orders as $ord_item) {
    		$ord_item->placed_date   = date('d F, Y',$ord_item->placed_date);
    		$ord_item->total         = ($ord_item->sale_price * $ord_item->quantity);
            $ord_item->total         = hs_money($ord_item->total);
    		$ord_item->url           = hs_link(sprintf("merchant_panel/order_details/%s",$ord_item->id));
            $ord_item->buyer_contact = hs_link(sprintf("messages/%s",$ord_item->buyer_uname));
            $ord_item->avatar        = hs_get_media($ord_item->buyer_avatar);
            $rand_len                = rand((32 / 2), 32);
            $ord_item->prod_name     = hs_croptxt($ord_item->prod_name,$rand_len,'..');
            $ord_item->prod_url      = hs_link(sprintf("product/%d",$ord_item->prod_id));
    	}

        $data = hs_o2array($orders); 
    }

    return $data;
}

function hs_get_my_orders($args = array()) {
    global $db,$hs,$me;
    $args    = (is_array($args)) ? $args : array();
    $options = array(
        "offset"    => null,
        "limit"     => 10,
        "order"     => 'DESC',
        "offset_to" => null,
        'keyword'   => null

    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $offset_to      =  $args['offset_to'];
    $keyword        =  $args['keyword'];
    $order          =  $args['order'];
    $data           =  array();
    $sql            =  hs_sqltepmlate('orders/hex_get_my_orders',array(
        'offset'    => $offset,
        'limit'     => $limit,
        'buyer_id'  => $me['id'],
        'offset_to' => $offset_to,
        'keyword'   => $keyword,
        'order'     => $order,
        't_orders'  => T_ORDERS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
        't_pvars'   => T_PROD_VARS,
    ));

    $orders = $db->rawQuery($sql);

    if (hs_queryset($orders) == true) {
        foreach ($orders as $ord_item) {
            $ord_item->placed_date    = date('d F, Y',$ord_item->placed_date);
            $ord_item->total          = ($ord_item->sale_price * $ord_item->quantity);
            $ord_item->total          = hs_money($ord_item->total);
            $ord_item->seller_avatar  = hs_get_media($ord_item->seller_avatar);
            $ord_item->seller_profile = hs_get_media($ord_item->seller_uname);
            $ord_item->seller_contact = hs_get_media(sprintf('messages/%s',$ord_item->seller_uname));
            $ord_item->url            = hs_link(sprintf("merchant_panel/order_invoice/%s",$ord_item->id));
            $rand_len                 = rand((32 / 2), 32);
            $ord_item->prod_name      = hs_croptxt($ord_item->prod_name,$rand_len,'..');
            $ord_item->prod_url       = hs_link(sprintf("product/%d",$ord_item->prod_id));
        }

        $data = hs_o2array($orders); 
    }

    return $data;
}

function hs_get_customer_order_details($order_id = null) {
	global $db,$hs,$me;
	if (not_num($order_id) || empty($hs['is_logged'])) {
		return false;
	}

	$data           =  array();
	$sql            =  hs_sqltepmlate('orders/hex_customer_order_details',array(
        'order_id'  => $order_id,
        'seller_id' => $me['id'],
        't_orders'  => T_ORDERS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
        't_pvars'   => T_PROD_VARS,
        't_couts'   => T_CHKOUT_TRANS,
    ));

    $order = $db->rawQueryOne($sql);

    if (hs_queryset($order,'object')) {
    	$order->prod_poster = hs_get_media($order->prod_poster);
        $shiping_fee        = (($order->shipping_cost == 'paid') ? floatval($order->shipping_fee) : 0);
        $order->prod_url    = hs_link(sprintf('product/%s',$order->prod_id));
        $order->totalamount = ($order->sale_price * $order->quantity);
        $order->totalpaid   = $order->paid_amount;
        $tl_posts           = $db->where('order_id',$order_id)->get(T_ORD_HIST_TL);
        $order->tl_posts    = (hs_queryset($tl_posts)) ? $tl_posts : array();
        $order->placed_date = date('d M, Y h:m',$order->placed_date);
        $order->buyer_url   = hs_link(sprintf("messages/%s", $order->buyer_username));
    	$data               = hs_o2array($order);
    }

    return $data;
}

function hs_get_order_invoice($order_id = null) {
    global $db,$hs,$me;
    if (not_num($order_id) || empty($hs['is_logged'])) {
        return false;
    }

    $data           =  array();
    $sql            =  hs_sqltepmlate('orders/hex_order_invoice',array(
        'order_id'  => $order_id,
        'buyer_id'  => $me['id'],
        't_orders'  => T_ORDERS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
        't_couts'   => T_CHKOUT_TRANS,
        't_pvars'   => T_PROD_VARS,
    ));

    $order = $db->rawQueryOne($sql);

    if (hs_queryset($order,'object')) {
        $shiping_fee        = (($order->shipping_cost == 'paid') ? floatval($order->shipping_fee) : 0);
        $order->prod_poster = hs_get_media($order->prod_poster);
        $order->totalamount = ($order->sale_price * $order->quantity);
        $order->totalpaid   = $order->paid_amount;

        $tl_posts           = $db->where('order_id',$order_id)->get(T_ORD_HIST_TL);
        $order->tl_posts    = (hs_queryset($tl_posts)) ? $tl_posts : array();
        $order->placed_date = date('d M, Y h:m',$order->placed_date);
        $order->seller_url  = hs_link(sprintf("messages/%s", $order->seller_username));
        $data               = hs_o2array($order);
    }

    return $data;
}

function hs_order_is_canceled($order_id = null) {
    global $db,$hs,$me;
    if (not_num($order_id) || empty($hs['is_logged'])) {
        return false;
    }

    $db = $db->where('order_id',$order_id);
    $db = $db->where('status','canceled');
    $rs = $db->getValue(T_ORD_CANCELS,'COUNT(*)');
    return (($rs > 0) ? true : false);
}

function hs_get_customer_refunds($args = array()) {
    global $db,$hs;
    $args           = (is_array($args)) ? $args : array();
    $options        = array(
        "offset"    => null,
        "limit"     => 7,
        "order"     => 'DESC',
        "offset_to" => null,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $offset_to      =  $args['offset_to'];
    $sql            =  hs_sqltepmlate('orders/hex_get_customer_refunds',array(
        'offset'    => $offset,
        'limit'     => $limit,
        'order'     => $order,
        'offset_to' => $offset_to,
        't_orders'  => T_ORDERS,
        't_ord_cnc' => T_ORD_CANCELS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
        't_trans'   => T_CHKOUT_TRANS,
    ));

    $data     = array();
    $requests = $db->rawQuery($sql);

    if (hs_queryset($requests) == true) {
        foreach ($requests as $req_data) {
            $rand_len                 = rand((32 / 2), 32);
            $req_data->time           = date('d F, Y h:m',$req_data->time);
            $req_data->cust_avatar    = hs_get_media($req_data->cust_avatar);
            $req_data->prod_name      = hs_croptxt($req_data->prod_name,$rand_len,'...');
            $req_data->prod_url       = hs_link(sprintf("product/%d",$req_data->prod_id));
            $req_data->buyer_contact  = hs_link($req_data->cust_uname);
        }

        $data = hs_o2array($requests); 
    }

    return $data;
}

function hs_get_customer_refund_details($req_id = null) {
    global $db,$hs;
    if (not_num($req_id)) {
        return false;
    }

    $sql            =  hs_sqltepmlate('orders/hex_get_customer_refund_details',array(
        'id'        => $req_id,
        't_orders'  => T_ORDERS,
        't_ord_cnc' => T_ORD_CANCELS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
        't_trans'   => T_CHKOUT_TRANS,
    ));

    $data     = array();
    $request  = $db->rawQueryOne($sql);

    if (hs_queryset($request,'object') == true) {
        $request->time           = date('d F, Y h:m',$request->time);
        $request->cust_avatar    = hs_get_media($request->cust_avatar);
        $request->seller_avatar  = hs_get_media($request->seller_avatar);
        $request->prod_name      = hs_croptxt($request->prod_name,42,'...');
        $request->prod_url       = hs_link(sprintf("product/%d",$request->prod_id));
        $request->buyer_contact  = hs_link($request->cust_uname);
        $request->seller_contact = hs_link($request->seller_uname);

        $data = hs_o2array($request); 
    }

    return $data;
}

function hs_get_customer_refunds_total() {
    global $db,$hs;
    $total = $db->getValue(T_ORD_CANCELS,'COUNT(*)');
    return (is_number($total)) ? intval($total) : 0;
}

function hs_paypal_pid_exists($pid = null) {
    global $db,$hs;
    if (empty($pid)) {
        return true;
    }

    else {
        $db = $db->where('paypal_pid',$pid);
        $db = $db->where('status','success');
        $rs = $db->getValue(T_CHKOUT_TRANS,'COUNT(*)');
        return ((is_number($rs) == true) ? true : false);
    }
}
?>