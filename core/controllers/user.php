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

function hs_user_data($user_id = 0) {
    global $db, $hs;
    if (not_num($user_id)) {
        return false;
    } 

    $db           = $db->where('id', $user_id);
    $fetched_data = $db->getOne(T_USERS);

    if (empty($fetched_data)) {
        return false;
    }
  
    $fetched_data->name = hs_str_form("{0} {1}",array(
        0 => $fetched_data->fname,
        1 => $fetched_data->lname
    ));
    
    $fetched_data->avatar    = hs_get_media($fetched_data->avatar);
    $fetched_data->url       = hs_link(hs_str_form("{0}",array($fetched_data->username)));
    $fetched_data->chaturl   = hs_link(hs_str_form("messages/{0}",array($fetched_data->username)));
    $fetched_data->country   = hs_country($fetched_data->country_id);
    $fetched_data->address   = array();
    $fetched_data->is_root   = hs_is_admin($user_id);
    $fetched_data->last_seen = date("d M, y h:m A",$fetched_data->last_active);
    $account_address_data    = array(
        'full_name'          => $fetched_data->name,
        'phone'              => $fetched_data->phone,
        'street'             => $fetched_data->street,
        'off_apt'            => $fetched_data->off_apt,
        'country'            => hs_country($fetched_data->country_id),
        'state'              => $fetched_data->state,
        'city'               => $fetched_data->city,
        'zip_postal'         => $fetched_data->zip_postal,
        'email'              => $fetched_data->email,
    );
    $fetched_data->acc_addr  = (hs_is_valid_address($account_address_data)) ? $account_address_data : false;

    if ($fetched_data->deliv_addr == 'default') {
        $address_data          = array(
            'full_name'        => $fetched_data->name,
            'phone'            => $fetched_data->phone,
            'street'           => $fetched_data->street,
            'off_apt'          => $fetched_data->off_apt,
            'country'          => hs_country($fetched_data->country_id),
            'state'            => $fetched_data->state,
            'city'             => $fetched_data->city,
            'zip_postal'       => $fetched_data->zip_postal,
            'email'            => $fetched_data->email,
        );
        $fetched_data->address = (hs_is_valid_address($address_data)) ? $address_data : false;
    }
    else {
        $db->returnType        = 'Array';
        $addr_id               = intval($fetched_data->deliv_addr);
        $db                    = $db->where('user_id',$user_id);
        $db                    = $db->where('id',$addr_id);
        $db                    = $db;
        $addr_data             = $db->getOne(T_DELIV_ADDRS);
        $addr_data['country']  = fetch_or_get($addr_data['country_id']);
        $addr_data['country']  = hs_country($addr_data['country']);
        $fetched_data->address = (hs_is_valid_address($addr_data)) ? $addr_data : false;
    }

    return $fetched_data;
}

function hs_get_user_by_name($uname = null) {
    global $hs,$db;

    if (empty($uname)) {
        return false;
    }
    $uname = hs_secure($uname);
    $db    = $db->where('username',$uname);
    $udata = $db->getOne(T_USERS,'id');

    if (hs_queryset($udata,'object')) {
        $user_id = intval($udata->id);
        $udata   = hs_user_data($user_id);
    }

    return $udata;
}

function hs_is_valid_address($addr_data = false) {
    global $hs;

    if (empty($addr_data) || is_array($addr_data) != true) {
        return false;
    }

    else {
        $addr_fields   = array();
        $addr_fields[] = (empty($addr_data['full_name']))  ? true : false;
        $addr_fields[] = (empty($addr_data['phone']))      ? true : false;
        $addr_fields[] = (empty($addr_data['street']))     ? true : false;
        $addr_fields[] = (empty($addr_data['off_apt']))    ? true : false;
        $addr_fields[] = (empty($addr_data['country']))    ? true : false;
        $addr_fields[] = (empty($addr_data['state']))      ? true : false;
        $addr_fields[] = (empty($addr_data['city']))       ? true : false;
        $addr_fields[] = (empty($addr_data['zip_postal'])) ? true : false;
        $addr_fields[] = (empty($addr_data['email']))      ? true : false;
        return in_array(true,  $addr_fields) ? false : true;
    }
}

function hs_is_admin($user_id = null) {
    global $db;
    if (not_num($user_id)) {
        return false;
    }

    $db = $db->where('user_id',$user_id);
    $bl = $db->getValue(T_ADMINS,'COUNT(*)');
    $bl = ((not_empty($bl)) ? true : false);
    return $bl;
}

function hs_is_user_active($user_id = 0) {
    global $db;
    $db = $db->where('active', '1');
    $db = $db->where('id', hs_secure($user_id));
    return ($db->getValue(T_USERS, 'count(*)') > 0) ? true : false;
}

function hs_email_exists($email = '') {
    global $db;
    return ($db->where('email', hs_secure($email))->getValue(T_USERS, 'count(*)') > 0) ? true : false;
}

function hs_create_user_session($user_id = 0, $platform = 'web') {
    global $db, $me;
    if (empty($user_id)) {
        return false;
    }
    $session_id      =  sha1(rand(11111, 99999)) . time() . md5(microtime() . $user_id);
    $insert_data     =  array(
        'user_id'    => $user_id,
        'session_id' => $session_id,
        'platform'   => $platform,
        'time'       => time()
    );

    $insert              = $db->insert(T_SESSIONS, $insert_data);
    $_SESSION['user_id'] = $session_id;
   
    setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), '/') or die('unable to create cookie');
    $me['is_logged'] = true;
    return $insert;
}

function hs_get_user_addresses($user_id = 0) {
    global $db, $me;
    if (empty($user_id)) {
        return false;
    }

    $db    = $db->where('user_id',$user_id);
    $addrs = $db->get(T_DELIV_ADDRS);
    $data  = array();
    if (hs_queryset($addrs)) {
        foreach ($addrs as $address) {
            $address->country = hs_country($address->country_id);
        }

        $data = hs_o2array($addrs);
    }

    return $data;
}

function hs_reg_user_sale($seller_id = 0, $amount = 0,$payment_method = 'none') {
    global $db,$hs;
    if (not_num($seller_id) || not_num($amount)) {
        return false;
    }

    $seller_data      = $db->where('id',$seller_id)->getOne(T_USERS);
    $update           = false;
    $market_sale_rate = intval($hs['config']['market_sale_rate']);

    if (hs_queryset($seller_data,'object')) {
        if ($payment_method == 'cod') {
            $selldebt    =  (($market_sale_rate / 100) * $amount);
            $sellrate    =  num2digs($selldebt);
            $wallet_stat =  ($seller_data->wallet - $selldebt);
            $update_data =  array(
                'wallet' => $wallet_stat
            );

            $db     = $db->where('id',$seller_id);
            $update = $db->update(T_USERS,$update_data);   
        }
        else {
            $sellrate    =  hs_market_sale_rate($amount);
            $sellrate    =  num2digs($sellrate);
            $update_data =  array(
                'wallet' => ($seller_data->wallet += $sellrate)
            );

            $db     = $db->where('id',$seller_id);
            $update = $db->update(T_USERS,$update_data);
        }
    }

    return $update;
}

function hs_block_unblock_seller_products($user_id = false) {
    global $db,$hs;
    if (not_num($user_id)) {
        return false;
    }

    $db->returnType = 'Array';
    $db             = $db->where('id',$user_id);
    $udata          = $db->getOne(T_USERS);

    if (hs_queryset($udata)) {
        if (hs_is_admin($user_id)) {
            return false;
        }
        else {
            if (empty($udata['wallet']) || ($udata['wallet'] <= '0.00')) {
                $db->returnType = 'Array';
                $db             = $db->where('user_id',$user_id);
                $db             = $db->where('activity_status','active');
                $db             = $db->where('status',array('active','inactive'),'IN');
                $db             = $db->where('editing_stage','saved');
                $db             = $db->where('payment_method',array('cod_payments','all_payments'),'IN');
                $seller_goods   = $db->get(T_PRODUCTS);

                if (hs_queryset($seller_goods)) {
                    foreach ($seller_goods as $prod_data) {
                        hs_setprod_val($prod_data['id'],array(
                            'status' => 'blocked'
                        ));
                    }

                    hs_ap_send_announcement(array(
                        'user_id'   => $user_id,
                        'title'     => 'Temporary products blocking!',
                        'message'   => 'Hi {%name%}! Unfortunately, your account has run out of funds for deducting sales commission, and in connection with this, we temporarily blocked some of your products. If you have any questions, please contact our support team.',
                        'type'      => 'info',
                        'static'    => 'Y',
                        'json_data' => json(array(
                            'name'  => hs_html_el('b',sprintf("%s %s",$udata['fname'],$udata['lname']))
                        ),true),
                        'slug'      => 'temp_prods_blocking',
                        'time'      => time()
                    ));
                }
            }
            else {
                $db->returnType = 'Array';
                $db             = $db->where('user_id',$user_id);
                $db             = $db->where('activity_status','active');
                $db             = $db->where('status','blocked');
                $db             = $db->where('editing_stage','saved');
                $db             = $db->where('payment_method',array('cod_payments','all_payments'),'IN');
                $seller_goods   = $db->get(T_PRODUCTS);

                if (hs_queryset($seller_goods)) {
                    foreach ($seller_goods as $prod_data) {
                        hs_setprod_val($prod_data['id'],array(
                            'status' => $prod_data['last_status']
                        ));
                    }
                }
            }
        }
    }
    else {
        return false;
    }
}

function hs_market_sale_rate($amount = 0,$rate = 0) {
    global $db,$hs;
    if (not_num($amount)) {
        return 0;
    }

    $rate  = ((is_number($rate)) ? $rate : intval($hs['config']['market_sale_rate']));
    return ($amount - ($amount * ($rate/100)));
}

function hs_get_store_customers($args = array()) {
    global $db,$hs;
    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => null,
        "limit"     => 10,
        "seller_id" => null,
        "order"     => 'DESC',
        "offset_to" => null,
        "keyword"   => null,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $seller_id      =  $args['seller_id']; 
    $order          =  $args['order']; 
    $offset_to      =  $args['offset_to']; 
    $keyword        =  $args['keyword']; 
    $sql            =  hs_sqltepmlate('users/hex_get_store_customers',array(
        'offset'    => $offset,
        'order'     => $order,
        'limit'     => $limit,
        'seller_id' => $seller_id,
        'offset_to' => $offset_to,
        'keyword'   => $keyword,
        't_custs'   => T_STORE_CUSTOMERS,
        't_orders'  => T_ORDERS,
        't_users'   => T_USERS
    ));


    $data      = array();
    $customers = $db->rawQuery($sql);

    if (hs_queryset($customers) == true) {
        foreach ($customers as $cust_data) {
            $banner_code             = fetch_or_get($hs['country_codes'][$cust_data->country_id],'us');
            $cust_data->banner       = hs_banner($banner_code);
            $cust_data->cust_avatar  = hs_get_media($cust_data->cust_avatar);
            $cust_data->last_order   = date("d M, Y",$cust_data->last_order);
            $cust_data->cust_profile = hs_link($cust_data->username);
        }

        $data = hs_o2array($customers); 
    }

    return $data;
}

function hs_get_account_transactions($args = array()) {
    global $db,$hs;
    $args            =  (is_array($args)) ? $args : array();
    $options         =  array(
        "offset"     => null,
        "limit"      => 10,
        "account_id" => null,
        "offset_to"  => null,
        "order"      => 'DESC',
        "keyword"    => null,
    );

    $args            =  array_merge($options, $args);
    $offset          =  $args['offset'];
    $limit           =  $args['limit'];
    $account_id      =  $args['account_id']; 
    $offset_to       =  $args['offset_to']; 
    $order           =  $args['order']; 
    $keyword         =  $args['keyword']; 
    $sql             =  hs_sqltepmlate('users/hex_get_account_transactions',array(
        'offset'     => $offset,
        'limit'      => $limit,
        'account_id' => $account_id,
        'offset_to'  => $offset_to,
        'order'      => $order,
        'keyword'    => $keyword,
        't_trans'    => T_CHKOUT_TRANS,
        't_users'    => T_USERS
    ));

    $data            = array();
    $transactions    = $db->rawQuery($sql);

    if (hs_queryset($transactions) == true) {
        foreach ($transactions as $trans_data) {
            $trans_data->seller_avatar = hs_get_media($trans_data->seller_avatar);
            $trans_data->buyer_avatar = hs_get_media($trans_data->buyer_avatar);
            $trans_data->date = date("d M, Y h:m",$trans_data->time);
        }

        $data = hs_o2array($transactions); 
    }

    return $data;   
}

function hs_get_account_revenue($args = array()) {
    global $db,$hs;
    $args    = (is_array($args)) ? $args : array();
    $options = array(
        "offset"     => null,
        "limit"      => 10,
        "account_id" => null,
        "date"       => null,
    );

    $args            =  array_merge($options, $args);
    $offset          =  $args['offset'];
    $limit           =  $args['limit'];
    $account_id      =  $args['account_id']; 
    $date            =  $args['date']; 
    $sql             =  hs_sqltepmlate('users/hex_get_account_revenue',array(
        'offset'     => $offset,
        'limit'      => $limit,
        'account_id' => $account_id,
        'date'       => $date,
        't_trans'    => T_CHKOUT_TRANS,
    ));

    $total   = 0.00;
    $revenue = $db->rawQuery($sql);

    if (hs_queryset($revenue) == true) {
        foreach ($revenue as $rev) {
            $total += hs_market_sale_rate($rev->amount);
        }
    }

    return $total;   
}

function hs_account_wallet_ws($user_id = null) {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return false;
    }

    $t_trans    =  T_CHKOUT_TRANS;
    $user_id    =  $me['id'];
    $stats      =  array(
        'sales' => array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00)
    );

    $wd1 = strtotime("last sunday midnight");
    $wd2 = date('U',strtotime("+24 hours",$wd1));
    $wd3 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 2))),$wd1));
    $wd4 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 3))),$wd1));
    $wd5 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 4))),$wd1));
    $wd6 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 5))),$wd1));
    $wd7 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 6))),$wd1));
    $wd8 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 7))),$wd1));

    $db  = $db->where('seller_id',$user_id)->where('time',$wd1,'>=');
    $d1r = $db->where('time',$wd2,'<=')->get($t_trans,null,'amount');
    $d1r = ((hs_queryset($d1r)) ? hs_o2array($d1r) : array());
    foreach ($d1r as $i) {
        $stats['sales'][0] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd2,'>=');
    $d2r = $db->where('time',$wd3,'<=')->get($t_trans,null,'amount');
    $d2r = ((hs_queryset($d2r)) ? hs_o2array($d2r) : array());
    foreach ($d2r as $i) {
        $stats['sales'][1] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd3,'>=');
    $d3r = $db->where('time',$wd4,'<=')->get($t_trans,null,'amount');
    $d3r = ((hs_queryset($d3r)) ? hs_o2array($d3r) : array());
    foreach ($d3r as $i) {
        $stats['sales'][2] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd4,'>=');
    $d4r = $db->where('time',$wd5,'<=')->get($t_trans,null,'amount');
    $d4r = ((hs_queryset($d4r)) ? hs_o2array($d4r) : array());
    foreach ($d4r as $i) {
        $stats['sales'][3] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd5,'>=');
    $d5r = $db->where('time',$wd6,'<=')->get($t_trans,null,'amount');
    $d5r = ((hs_queryset($d5r)) ? hs_o2array($d5r) : array());
    foreach ($d5r as $i) {
        $stats['sales'][4] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd6,'>=');
    $d6r = $db->where('time',$wd7,'<=')->get($t_trans,null,'amount');
    $d6r = ((hs_queryset($d6r)) ? hs_o2array($d6r) : array());
    foreach ($d6r as $i) {
        $stats['sales'][5] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd7,'>=');
    $d7r = $db->where('time',$wd8,'<')->get($t_trans,null,'amount');
    $d7r = ((hs_queryset($d7r)) ? hs_o2array($d7r) : array());
    foreach ($d7r as $i) {
        $stats['sales'][6] =+ hs_market_sale_rate($i['amount']);
    }

    foreach ($stats['sales'] as $key => $stat_val) {
        $stats['sales'][$key] = sprintf('%.2f', $stat_val);
    }

    return $stats;
}

function hs_get_announcement($user_id = 0) {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return array();
    }

    $data    = array();
    $db      = $db->where('user_id',$me['id']);
    $db      = $db->orderBy('id','ASC');
    $query   = $db->get(T_ANNOUNC,1);
    $query   = (hs_queryset($query)) ? hs_o2array($query) : array();
    $deleted = array();

    foreach ($query as $msg) {
        if ($msg['static'] == 'N') {
            $deleted[] = $msg['id'];
        }

        if ($msg['message_type'] == 'system') {
            $json_data      = json($msg['json_data']);
            $msg['message'] = hs_translate($msg['message'],$json_data);
            $msg['title']   = hs_translate($msg['title']);
        }

        $data[] = $msg;
    }

    if (not_empty($deleted)) {
        $db->where('id',$deleted,'IN')->delete(T_ANNOUNC);
    }
    return $data;
}

function hs_get_profile_rating($prof_id = false) {
    global $db, $hs;
    if (not_num($prof_id)) {
        return 0.0;
    }

    $t_usr  = T_USERS;
    $t_prd  = T_PRODUCTS;
    $_rtg   = T_PROD_RATINGS;
    $db     = $db->join("{$t_prd} p","r.`prod_id` = p.`id`","INNER");
    $db     = $db->join("{$t_usr} u","p.`user_id` = u.`id`","INNER");
    $db     = $db->where('u.`id`',$prof_id);
    $db     = $db->where('r.`activity_status`','active');
    $query  = $db->get("{$_rtg} r",null,'r.`valuation`');
    $rating = array(
        '1' => 0,
        '2' => 0,
        '3' => 0,
        '4' => 0,
        '5' => 0,
        '6' => 0,
    );

    if (hs_queryset($query)) {
        foreach ($query as $row_item) {
            $rating[$row_item->valuation] += intval($row_item->valuation);
        }
    }

    if (array_sum($rating)) {
        $n1 = (($rating['5'] * 5) + ($rating['4'] * 4) + ($rating['3'] * 3) + ($rating['2'] * 2) + ($rating['1'] * 1));
        $n2 = ($rating['5'] + $rating['4'] + $rating['3'] + $rating['2'] + $rating['1']);
        return ($n1 / $n2); 
    } 
    else {
        return 0.0;
    }
}

function hs_profile_total_reviews($prof_id = null,$val = 'all') {
    global $db,$hs;
    if (not_num($prof_id)) {
        return 0;
    }

    $pid    = intval($prof_id);
    $t_usr  = T_USERS;
    $t_prd  = T_PRODUCTS;
    $_rtg   = T_PROD_RATINGS;
    $db     = $db->join("{$t_prd} p","r.`prod_id` = p.`id`","INNER");
    $db     = $db->join("{$t_usr} u","p.`user_id` = u.`id`","INNER");
    $db     = $db->where('u.`id`',$pid);
    $db     = $db->where('r.`activity_status`','active');
    
    if (in_array($val, array(1,2,3,4,5))) {
        $db = $db->where('r.`valuation`',$val);
    }

    $tot    = $db->getValue("{$_rtg} r",'COUNT(*)');
    return (is_number($tot)) ? $tot : 0;  
}

function hs_get_profile_reviews($args = array()) {
    global $db,$hs,$me;
    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "prof_id"   => null,
        "valuation" => null,
        "limit"     => 10,
        "offset"    => null,
        "by_ids"    => null,
        "sortby"    => null,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $prof_id        =  $args['prof_id'];
    $valuation      =  $args['valuation'];
    $limit          =  $args['limit'];
    $sortby         =  ((is_array($args['sortby'])) ? implode(',', $args['sortby']) : null);
    $data           =  array();
    $sql            =  hs_sqltepmlate('reviews/hex_get_profile_reviews',array(
        'offset'    => $offset,
        'limit'     => $limit,
        'prof_id'   => $prof_id,
        'valuation' => $valuation,
        'sortby'    => $sortby,
        't_reviews' => T_PROD_RATINGS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
    ));
    
    $reviews_list = $db->rawQuery($sql);

    if (hs_queryset($reviews_list) == true) {
        foreach ($reviews_list as $row_item) {
            $row_item->avatar = hs_get_media($row_item->avatar);
            $row_item->ulink  = hs_link($row_item->username);
            $row_item->time   = date('d F, Y',$row_item->time);
            $row_item->media  = hs_get_review_media($row_item->id);
            $row_item->stars  = hs_rating_stars($row_item->valuation);
        }

        $data = hs_o2array($reviews_list); 
    }

    return $data;
}

function hs_profife_ratings_percentage($ratings = array()) {
    if (is_array($ratings) != true || empty($ratings)) {
        return array(
            '5' => 0,
            '4' => 0,
            '3' => 0,
            '2' => 0,
            '1' => 0,
        );
    }

    $all = 0;
    foreach ($ratings as $value) {
        $all += $value;
    }
    $percentage_total = $all;
    $data             = array();
    foreach ($ratings as $key => $value) {
        $data[$key] = 0;
        if ($percentage_total > 0) {
            $data[$key] = hs_str_form("{0}%",array(number_format(($value / $percentage_total) * 100),'%'));
        }
    }

    return $data;
}

function hs_get_products_total($user_id = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return 0;
    }

    $uid = intval($user_id);
    $db  = $db->where('user_id',$uid);
    $db  = $db->where('activity_status','active');
    $db  = $db->where('status',array('active','inactive','blocked'),'IN');
    $db  = $db->where('editing_stage','saved');
    $tot = $db->getValue(T_PRODUCTS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);

    return $tot;
}

function hs_get_draft_products_total($user_id = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return 0;
    }

    $uid = intval($user_id);
    $db  = $db->where('user_id',$uid);
    $db  = $db->where('activity_status','active');
    $db  = $db->where('status',array('active','inactive','blocked'),'IN');
    $db  = $db->where('editing_stage','unsaved');
    $tot = $db->getValue(T_PRODUCTS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);

    return $tot;
}

function hs_get_orders_total($user_id = null,$status = false) {
    global $db,$hs;
    if (not_num($user_id)) {
        return 0;
    }

    $uid = intval($user_id);
    $db  = $db->where('seller_id',$uid);

    if ($status && in_array($status, array_keys($hs['ord_stats']))) {
        $db = $db->where('status',$status);
    }
    
    $tot = $db->getValue(T_ORDERS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);

    return $tot;
}

function hs_get_customers_total($user_id = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return 0;
    }

    $uid = intval($user_id);
    $db  = $db->where('seller_id',$uid);
    $db  = $db->groupBy('buyer_id');    
    $tot = $db->get(T_ORDERS,null,'id');
    $tot = ((hs_queryset($tot)) ? count($tot) : 0);
    return $tot;
}

function hs_get_payouts_total($user_id = null,$status = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return 0;
    }

    $uid = intval($user_id);
    $db  = $db->where('user_id',$uid);
    if (not_empty($status)) {
        if (in_array($status, array('pending','paid','declined'))) {
            $db = $db->where('status',$status);
        }
    }

    $tot = $db->getValue(T_PAYOUT_REQS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);
    return $tot;
}

function hs_store_weekly_stats($user_id = null) {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return false;
    }

    $t_trans     =  T_CHKOUT_TRANS;
    $t_orders    =  T_ORDERS;
    $user_id     =  $me['id'];
    $stats       =  array(
        'sales'  => array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
        'orders' => array(0, 0, 0, 0, 0, 0, 0),
    );

    $wd1 = strtotime("last sunday midnight");
    $wd2 = date('U',strtotime("+24 hours",$wd1));
    $wd3 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 2))),$wd1));
    $wd4 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 3))),$wd1));
    $wd5 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 4))),$wd1));
    $wd6 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 5))),$wd1));
    $wd7 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 6))),$wd1));
    $wd8 = date('U',strtotime(hs_str_form("+{0} hours",array((24 * 7))),$wd1));

    $db  = $db->where('seller_id',$user_id)->where('time',$wd1,'>=');
    $d1r = $db->where('time',$wd2,'<=')->get($t_trans,null,'amount');
    $d1r = ((hs_queryset($d1r)) ? hs_o2array($d1r) : array());
    foreach ($d1r as $i) {
        $stats['sales'][0] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd2,'>=');
    $d2r = $db->where('time',$wd3,'<=')->get($t_trans,null,'amount');
    $d2r = ((hs_queryset($d2r)) ? hs_o2array($d2r) : array());
    foreach ($d2r as $i) {
        $stats['sales'][1] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd3,'>=');
    $d3r = $db->where('time',$wd4,'<=')->get($t_trans,null,'amount');
    $d3r = ((hs_queryset($d3r)) ? hs_o2array($d3r) : array());
    foreach ($d3r as $i) {
        $stats['sales'][2] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd4,'>=');
    $d4r = $db->where('time',$wd5,'<=')->get($t_trans,null,'amount');
    $d4r = ((hs_queryset($d4r)) ? hs_o2array($d4r) : array());
    foreach ($d4r as $i) {
        $stats['sales'][3] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd5,'>=');
    $d5r = $db->where('time',$wd6,'<=')->get($t_trans,null,'amount');
    $d5r = ((hs_queryset($d5r)) ? hs_o2array($d5r) : array());
    foreach ($d5r as $i) {
        $stats['sales'][4] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd6,'>=');
    $d6r = $db->where('time',$wd7,'<=')->get($t_trans,null,'amount');
    $d6r = ((hs_queryset($d6r)) ? hs_o2array($d6r) : array());
    foreach ($d6r as $i) {
        $stats['sales'][5] =+ hs_market_sale_rate($i['amount']);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd7,'>=');
    $d7r = $db->where('time',$wd8,'<')->get($t_trans,null,'amount');
    $d7r = ((hs_queryset($d7r)) ? hs_o2array($d7r) : array());
    foreach ($d7r as $i) {
        $stats['sales'][6] =+ hs_market_sale_rate($i['amount']);
    }

    foreach ($stats['sales'] as $key => $stat_val) {
        $stats['sales'][$key] = sprintf('%.2f', $stat_val);
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd1,'>=');
    $d1r = $db->where('time',$wd2,'<=')->getValue($t_orders,'COUNT(*)');
    $d1r = ((is_number($d1r)) ? intval($d1r) : 0);
    if ($d1r) {
        $stats['orders'][0] = $d1r;
    } 

    $db  = $db->where('seller_id',$user_id)->where('time',$wd2,'>=');
    $d2r = $db->where('time',$wd3,'<=')->getValue($t_orders,'COUNT(*)');
    $d2r = ((is_number($d2r)) ? intval($d2r) : 0);
    if ($d2r) {
        $stats['orders'][1] = $d2r;
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd3,'>=');
    $d3r = $db->where('time',$wd4,'<=')->getValue($t_orders,'COUNT(*)');
    $d3r = ((is_number($d3r)) ? intval($d3r) : 0);
    if ($d3r) {
        $stats['orders'][2] = $d3r;
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd4,'>=');
    $d4r = $db->where('time',$wd5,'<=')->getValue($t_orders,'COUNT(*)');
    $d4r = ((is_number($d4r)) ? intval($d4r) : 0);
    if ($d4r) {
        $stats['orders'][3] = $d4r;
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd5,'>=');
    $d5r = $db->where('time',$wd6,'<=')->getValue($t_orders,'COUNT(*)');
    $d5r = ((is_number($d5r)) ? intval($d5r) : 0);
    if ($d5r) {
        $stats['orders'][4] = $d5r;
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd6,'>=');
    $d6r = $db->where('time',$wd7,'<=')->getValue($t_orders,'COUNT(*)');
    $d6r = ((is_number($d6r)) ? intval($d6r) : 0);
    if ($d6r) {
        $stats['orders'][5] = $d6r;
    }

    $db  = $db->where('seller_id',$user_id)->where('time',$wd7,'>=');
    $d7r = $db->where('time',$wd8,'<=')->getValue($t_orders,'COUNT(*)');
    $d7r = ((is_number($d7r)) ? intval($d7r) : 0);
    if ($d7r) {
        $stats['orders'][6] = $d7r;
    }

    return $stats;
}

function hs_get_user_payouts($args = array()) {
    global $hs,$me,$db;

    if (empty($args)) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "user_id"   => null,
        "limit"     => 10,
        "offset"    => null,
        "offset_to" => null,
        "status"    => null,
        "order"     => "DESC"
    );

    $args      = array_merge($options, $args);
    $offset    = $args['offset'];
    $user_id   = $args['user_id'];
    $status    = $args['status'];
    $order     = $args['order'];
    $limit     = $args['limit'];
    $offset_to = $args['offset_to'];
    $offset_to = (($offset_to == 'lt') ? '<' : '>');
    $data      = array();
    $db        = $db->where('user_id',$user_id);
    $db        = $db->orderBy('id',$order);

    if (is_number($offset)) {
        $db  = $db->where('id',$offset,$offset_to);
    }

    if (not_empty($status) && in_array($status, array('pending','paid','declined'))) {
        $db  = $db->where('status',$status);
    }

    $requests = $db->get(T_PAYOUT_REQS,$limit);

    if (hs_queryset($requests)) {
        foreach ($requests as $req_data) {
            $req_data->time    = date('d F, Y h:m A',$req_data->time);
            $req_data->amount  = number_format($req_data->amount,2,'.',' ');
            $req_data->pp_link = urldecode($req_data->pp_link);
        }

        $data = hs_o2array($requests);
    }

    return $data;
}

function hs_account_yearly_monetization_stats($user_id = null) {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return false;
    }

    $t_payouts       =  T_PAYOUT_REQS;
    $t_trans         =  T_CHKOUT_TRANS;
    $user_id         =  intval($user_id);
    $stats           =  array(
        'revenue'    => array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
        'commission' => array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
        'payouts'    => array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00),
    );


    $year_months = array(
        date('U',strtotime(date("Y:01:01 00:00:00"))),
        date('U',strtotime(date("Y:02:01 00:00:00"))),
        date('U',strtotime(date("Y:03:01 00:00:00"))),
        date('U',strtotime(date("Y:04:01 00:00:00"))),
        date('U',strtotime(date("Y:05:01 00:00:00"))),
        date('U',strtotime(date("Y:06:01 00:00:00"))),
        date('U',strtotime(date("Y:07:01 00:00:00"))),
        date('U',strtotime(date("Y:08:01 00:00:00"))),
        date('U',strtotime(date("Y:09:01 00:00:00"))),
        date('U',strtotime(date("Y:10:01 00:00:00"))),
        date('U',strtotime(date("Y:11:01 00:00:00"))),
        date('U',strtotime(date("Y:12:01 00:00:00")))
    );

    foreach (array_keys($stats) as $stat) {
        if ($stat == 'revenue') {
            foreach ($year_months as $m_num => $m_time) {
                $next_num   = ($m_num + 1);
                $next_month = (isset($year_months[$next_num]) ? $year_months[$next_num] : 0);

                $db  = $db->where('seller_id',$user_id)->where('time',$m_time,'>=');
                $db  = (($next_month) ? $db->where('time',$next_month,'<=') : $db);

                $res = $db->get($t_trans,null,array('amount','market_rate'));
                $res = ((hs_queryset($res)) ? hs_o2array($res) : array());

                foreach ($res as $i) {
                    $stats['revenue'][$m_num] =+ hs_market_sale_rate($i['amount'],$i['market_rate']);
                }
            }

            foreach ($stats['revenue'] as $ind => $stat_val) {
                $stats['revenue'][$ind] = sprintf('%.2f', $stat_val);
            }
        }

        else if($stat == 'commission'){
            foreach ($year_months as $m_num => $m_time) {
                $next_num   = ($m_num + 1);
                $next_month = (isset($year_months[$next_num]) ? $year_months[$next_num] : 0);

                $db  = $db->where('seller_id',$user_id)->where('time',$m_time,'>=');
                $db  = (($next_month) ? $db->where('time',$next_month,'<=') : $db);

                $res = $db->get($t_trans,null,array('amount','market_rate'));
                $res = ((hs_queryset($res)) ? hs_o2array($res) : array());
                
                foreach ($res as $i) {
                    $rated_amount = hs_market_sale_rate($i['amount'],$i['market_rate']);
                    $stats['commission'][$m_num] =+ ($i['amount'] - $rated_amount);
                }
            }

            foreach ($stats['commission'] as $ind => $stat_val) {
                $stats['commission'][$ind] = sprintf('%.2f', $stat_val);
            }
        }

        else if($stat == 'payouts'){
            foreach ($year_months as $m_num => $m_time) {
                $next_num   = ($m_num + 1);
                $next_month = (isset($year_months[$next_num]) ? $year_months[$next_num] : 0);

                $db  = $db->where('user_id',$user_id);
                $db  = $db->where('status','paid');
                $db  = $db->where('time',$m_time,'>=');
                $db  = (($next_month) ? $db->where('time',$next_month,'<=') : $db);
                $res = $db->get($t_payouts,null,array('amount'));
                $res = ((hs_queryset($res)) ? hs_o2array($res) : array());
                
                foreach ($res as $i) {
                    $stats['payouts'][$m_num] =+ $i['amount'];
                }
            }

            foreach ($stats['payouts'] as $ind => $stat_val) {
                $stats['payouts'][$ind] = sprintf('%.2f', $stat_val);
            }
        }
    }

    return $stats;
}

function hs_get_user_lp_currency($user_id = null) {
    global $db,$hs;
    if (empty($hs['is_logged'])) {
        return false;
    }

    $t_payouts      = T_PAYOUT_REQS;
    $user_id        = intval($user_id);
    $db->returnType = 'Array';
    $db             = $db->where('user_id',$user_id);
    $db             = $db->where('status','paid');
    $db             = $db->orderBy('id','DESC');
    $last_payout    = $db->getOne($t_payouts,'currency');
    $lpc            = "";

    if (hs_queryset($last_payout)) {
        $lpc = hs_currency($last_payout['currency']);
    }

    return $lpc;
}

function hs_update_user_data($user_id = null,$data = array()) {
    global $db,$hs;
    if ((not_num($user_id)) || (empty($data) || is_array($data) != true)) {
        return false;
    } 

    $db     = $db->where('id', $user_id);
    $update = $db->update(T_USERS,$data);
    return ($update == true) ? true : false;
}

function hs_inc_merchant_sales($user_id = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return false;
    } 

    $db       = $db->where('id', $user_id);
    $merchant = $db->getOne(T_USERS,array('sales'));
    if (hs_queryset($merchant,'object')) {
        $merchant       = hs_o2array($merchant);
        $merchant_sales = fetch_or_get($merchant['sales'],0);

        if (is_numeric($merchant_sales)) {
            hs_update_user_data($user_id,array('sales' => ($merchant_sales += 1)));
        }
    }
}

function hs_upsert_customer($user_id = null) {
    global $db,$hs,$me;
    if (empty($hs['is_logged']) || not_num($user_id)) {
        return false;
    } 

    $db       = $db->where('seller_id', $user_id);
    $db       = $db->where('buyer_id', $me['id']);
    $customer = $db->getOne(T_STORE_CUSTOMERS);
    if (hs_queryset($customer,'object') == false) {
        $insert_data    = array(
            'seller_id' => $user_id,
            'buyer_id'  => $me['id'],
            'time'      => time(),
        ); $db->insert(T_STORE_CUSTOMERS,$insert_data);
    }
}

function hs_dec_merchant_sales($user_id = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return false;
    } 

    $db       = $db->where('id', $user_id);
    $merchant = $db->getOne(T_USERS,array('sales'));
    if (hs_queryset($merchant,'object')) {
        $merchant       = hs_o2array($merchant);
        $merchant_sales = fetch_or_get($merchant['sales'],0);

        if (is_numeric($merchant_sales)) {
            hs_update_user_data($user_id,array('sales' => ($merchant_sales -= 1)));
        }
    }
}

function hs_is_seller($uid = null) {
    global $db;
    if (not_num($uid)) {
        return false;
    }

    $db  = $db->where('id',$uid);
    $db  = $db->where('is_seller','Y');
    $res = $db->getValue(T_USERS,'COUNT(*)');
    $res = (is_number($res) && $res == 1) ? true : false;
    return $res;
}


function hs_get_account_reviews($args = array()) {
    global $db,$hs,$me;
    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "user_id"   => 0,
        "valuation" => null,
        "limit"     => 10,
        "offset"    => null,
        "ids"       => null,
        "offset_to" => false,
        "media"     => false,
        "order"     => 'DESC',
    );

    $args           =  array_merge($options, $args);
    $user_id        =  $args['user_id'];
    $offset         =  $args['offset'];
    $offset_to      =  $args['offset_to'];  
    $valuation      =  $args['valuation'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];     
    $media_files    =  $args['media'];     
    $ids            =  $args['ids'];     
    $ids            =  ((is_array($ids) && hs_all($ids,'numeric')) ? implode(',', $ids) : false);
    $sql            =  hs_sqltepmlate('reviews/hex_get_account_reviews',array(
        'offset'    => $offset,
        'offset_to' => $offset_to,
        'limit'     => $limit,
        'user_id'   => $user_id,
        'valuation' => $valuation,
        'order'     => $order,
        'ids'       => $ids,
        't_reviews' => T_PROD_RATINGS,
        't_users'   => T_USERS,
        't_prods'   => T_PRODUCTS,
    ));

    $data           = array();
    $reviews_list   = $db->rawQuery($sql);

    if (hs_queryset($reviews_list) == true) {
        foreach ($reviews_list as $row_item) {
            $row_item->avatar = hs_get_media($row_item->avatar);
            $row_item->ulink  = hs_link($row_item->username);
            $row_item->plink  = hs_link(sprintf("product/%d",$row_item->prod_id));
            $row_item->time   = date('d F, Y',$row_item->time);
            $row_item->stars  = hs_rating_stars($row_item->valuation);
            $row_item->media  = (($media_files == true) ? hs_get_review_media($row_item->id) : null);
            $crop_length      = rand((30 / 2), 30);  
            $row_item->pname  = hs_croptxt($row_item->prod_name,$crop_length,'...');
        }

        $data = hs_o2array($reviews_list); 
    }

    return $data;
}

function hs_get_account_reviews_total($user_id = null) {
    global $db;
    if (not_num($user_id)) {
        return 0;

    }

    $t_prd = T_PRODUCTS;
    $t_rev = T_PROD_RATINGS;
    $db    = $db->join("{$t_prd} prd","rev.prod_id = prd.id",'INNER');
    $db    = $db->where('prd.user_id',$user_id);
    $db    = $db->where('prd.activity_status','active');
    $db    = $db->where('prd.approved','Y');
    $db    = $db->where('prd.editing_stage','saved');
    $db    = $db->where('rev.activity_status','active');
    $tot   = $db->get("{$t_rev} rev");
    $tot   = ((hs_queryset($tot)) ? count($tot) : 0);

    return $tot;
}

function hs_check_can_seller_withdraw($user_id = null) {
    global $db,$hs;
    if (not_num($user_id)) {
        return false;
    }

    $payout_period = intval($hs['config']['payout_period']);
    $curr_time     = time();
    $time_offset   = strtotime("-{$payout_period}days",$curr_time);
    $db            = $db->where('user_id',$user_id);
    $db            = $db->where('time',$time_offset,'>=');
    $can_request   = $db->getValue(T_PAYOUT_REQS,'COUNT(*)');

    return ($can_request > 0) ? false : true;
}

function hs_is_user_blocked($user_id = false) {
    global $hs,$db;

    if (not_num($user_id)) {
        return false;
    }

    $db  = $db->where('user_id',$user_id);
    $res = $db->getValue(T_BLOCKS,'COUNT(*)');

    return (is_number($res) && $res > 0) ? true : false; 
}

function hs_get_verif_status($user_id = false) {
    global $db,$hs;

    if (not_num($user_id)) {
        return false;
    }

    $db->returnType = 'Array';
    $db             = $db->where('user_id',$user_id);
    $req_data       = $db->getOne(T_VERIF_REQS);
    $status         = 'none';

    if (hs_queryset($req_data)) {
        $status = $req_data['status'];
    }

    return $status;
}