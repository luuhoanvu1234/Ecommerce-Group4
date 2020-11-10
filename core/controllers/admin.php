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

function hs_ap_info_products_total() {
    global $db;
    $db  = $db->where('activity_status','active');
    $tot = $db->getValue(T_PRODUCTS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);
    return $tot;
}

function hs_ap_info_sales_total() {
    global $db;
    $tot = $db->getValue(T_ORDERS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);
    return $tot;
}

function hs_ap_info_users_total() {
    global $db;
    $tot = $db->getValue(T_USERS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);
    return $tot;
}

function hs_admin_temp_session($session_id = null) {
    global $db,$hs;

    $t_login_sess = T_SESSIONS;
    $t_admin_sess = T_ADMIN_SESSIONS;
    $session_id   = hs_secure($session_id);
    $db           = $db->join("{$t_admin_sess} a_s","l_s.id = a_s.login_sess_id",'INNER');
    $db           = $db->where('l_s.session_id',$session_id);
    $admin_sess   = $db->getOne("{$t_login_sess} l_s",array('a_s.`id`','a_s.`login_sess_id`','a_s.`admin_id`','a_s.`time`','l_s.`session_id` AS login_hash'));
    $admin_sess   = ((hs_queryset($admin_sess,'object')) ? hs_o2array($admin_sess) : array());
    return $admin_sess;
}

function hs_ap_info_yearly_main_stats() {
    global $db,$hs;
    $t_payouts   =  T_PAYOUT_REQS;
    $t_trans     =  T_CHKOUT_TRANS;
    $stats       =  array(
        'profit' => [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00],
        'sales'  => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    );

    if (empty($hs['is_admin'])) {
    	return $stats;
    }

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
        if ($stat == 'profit') {
            foreach ($year_months as $m_num => $m_time) {
                $next_num    = ($m_num + 1);
                $next_month  = (isset($year_months[$next_num]) ? $year_months[$next_num] : 0);
                $profit_summ = array();
                $db          = $db->where('time',$m_time,'>=');
                $db          = (($next_month) ? $db->where('time',$next_month,'<=') : $db);
                $res         = $db->get(T_MRKT_REVENUE,null,array('amount'));
                $res         = ((hs_queryset($res)) ? hs_o2array($res) : array());

                foreach ($res as $i) {
                    array_push($profit_summ, $i['amount']);
                }

                $stats['profit'][$m_num] = array_sum($profit_summ);
            }

            foreach ($stats['profit'] as $ind => $stat_val) {
                $stats['profit'][$ind] = sprintf('%.2f', $stat_val);
            }
        }

        else if($stat == 'sales'){  
            foreach ($year_months as $m_num => $m_time) {
                $next_num    = ($m_num + 1);
                $next_month  = (isset($year_months[$next_num]) ? $year_months[$next_num] : 0);
                $db          = $db->where('time',$m_time,'>=');
                $db          = (($next_month) ? $db->where('time',$next_month,'<=') : $db);
                $res         = $db->get(T_ORDERS,null,array('id'));
                $res         = ((hs_queryset($res)) ? hs_o2array($res) : array());  
                $total_sales = 0;

                foreach ($res as $i) {
                    $total_sales++;
                }

                $stats['sales'][$m_num] =+ $total_sales;
            }
        }
    }
    return $stats;
}

function hs_ap_info_get_products($args = false) {
	global $hs,$me,$db;
	if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
		return array();
	}

	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "keyword"   => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $keyword        =  $args['keyword'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
	$t_prods        =  T_PRODUCTS;
	$t_pvars        =  T_PROD_VARS;
	$sql            =  hs_sqltepmlate('admin/hex_ap_info_get_products',array(
		'offset'    => $offset,
		't_pvars'   => $t_pvars,
		't_prods'   => $t_prods,
		'limit'     => $limit,
		'offset_to' => $offset_to,
		'order'     => $order,
		'keyword'   => $keyword,
	));

	$data  = array();
	$prods = $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_id           = $prod_item->id;				
			$crop_length       = rand((30 / 2), 30);	
			$prod_item->name   = hs_croptxt($prod_item->name,$crop_length,'...');
			$prod_item->url    = hs_link(hs_str_form("product/{0}",array($prod_id)));
			$prod_item->edit   = hs_link(hs_str_form("merchant_panel/edit_product/{0}",array($prod_id)));
			$prod_item->thumb  = hs_get_media($prod_item->thumb);
            $prod_item->poster = hs_get_media($prod_item->poster);
            $prod_item->time   = date('d F, Y',$prod_item->time);
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_ap_info_get_new_products($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "keyword"   => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $keyword        =  $args['keyword'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
    $t_prods        =  T_PRODUCTS;
    $t_pvars        =  T_PROD_VARS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_new_products',array(
        'offset'    => $offset,
        't_pvars'   => $t_pvars,
        't_prods'   => $t_prods,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'keyword'   => $keyword,
    ));

    $data  = array();
    $prods = $db->rawQuery($sql);

    if (hs_queryset($prods)) {
        foreach ($prods as $prod_item) {
            $prod_id           = $prod_item->id;                
            $crop_length       = rand((30 / 2), 30);    
            $prod_item->name   = hs_croptxt($prod_item->name,$crop_length,'...');
            $prod_item->url    = hs_link(hs_str_form("product/{0}",array($prod_id)));
            $prod_item->edit   = hs_link(hs_str_form("merchant_panel/edit_product/{0}",array($prod_id)));
            $prod_item->thumb  = hs_get_media($prod_item->thumb);
            $prod_item->poster = hs_get_media($prod_item->poster);
            $prod_item->time   = date('d F, Y',$prod_item->time);
        }

        $data = hs_o2array($prods);
    }

    return $data;
}

function hs_ap_info_get_deleted_products($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args    = (is_array($args)) ? $args : array();
    $options = array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
    $t_prods        =  T_PRODUCTS;
    $t_orders       =  T_ORDERS;
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_deleted_products',array(
        'offset'    => $offset,
        't_prods'   => $t_prods,
        't_users'   => $t_users,
        't_orders'  => $t_orders,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
    ));

    $data  = array();
    $prods = $db->rawQuery($sql);

    if (hs_queryset($prods)) {
        foreach ($prods as $prod_item) {
            $prod_id                  = $prod_item->id;                
            $crop_length              = rand((30 / 2), 30);  
            $prod_item->prod_name     = hs_croptxt($prod_item->prod_name,$crop_length,'...');
            $prod_item->url           = hs_link(hs_str_form("product/{0}",array($prod_id)));
            $prod_item->thumb         = hs_get_media($prod_item->thumb);
            $prod_item->poster        = hs_get_media($prod_item->poster);
            $prod_item->time          = date('d F, Y',$prod_item->time);
            $prod_item->seller_avatar = hs_get_media($prod_item->seller_avatar);
        }

        $data = hs_o2array($prods);
    }

    return $data;
}

function hs_ap_delete_product($prod_id = 0) {
    global $db,$hs;
    if (empty($hs['is_admin'])) {
        return false;
    }

    $prod_id    = intval($prod_id);
    $db         = $db->where('id',$prod_id);
    $prod_item  = $db->getOne(T_PRODUCTS);

    if (hs_queryset($prod_item,'object')) {
        $db         = $db->where('prod_id',$prod_id);
        $prod_media = $db->get(T_PROD_MEDIA);

        $db         = $db->where('prod_id',$prod_id);
        $prod_vars  = $db->get(T_PROD_VARS);

        #Delete product media files
        if (hs_queryset($prod_media)) {
            foreach ($prod_media as $media) {
                hs_delete_image($media->src);
                hs_delete_image($media->thumb);
            }

            #Delete fetched data
            $db            = $db->where('prod_id',$prod_id);
            $rm_prod_media = $db->delete(T_PROD_MEDIA);
        }
        
        #Delete product variations
        if (hs_queryset($prod_vars)) {
            foreach ($prod_vars as $prodvar_item) {
                if (in_array($prodvar_item->var_type, array('color','color_size'))) {
                    hs_delete_image($prodvar_item->col_img);
                    hs_delete_image($prodvar_item->col_thumb);
                }
            }

            #Delete fetched data
            $db            = $db->where('prod_id',$prod_id);
            $rm_prod_vars  = $db->delete(T_PROD_VARS);
        }

        #Delete product from basket
        $db          = $db->where('prod_id',$prod_id);
        $prod_basket = $db->delete(T_BASKET);

        #Delete product purchase transactions
        $db          = $db->where('prod_id',$prod_id);
        $prod_chtrns = $db->delete(T_CHKOUT_TRANS);

        #Delete product purchased orders
        $db          = $db->where('prod_id',$prod_id);
        $prod_orders = $db->get(T_ORDERS);
        if (hs_queryset($prod_orders)) {
            foreach ($prod_orders as $order_item) {
                #Delete product orders history
                $db      = $db->where('order_id',$order_item->id);
                $rm_data = $db->delete(T_ORD_HIST_TL);

                #Delete market revenue records of product
                $db      = $db->where('order_id',$order_item->id);
                $rm_data = $db->delete(T_MRKT_REVENUE);

                #Delete order refunds
                $db      = $db->where('order_id',$order_item->id);
                $rm_data = $db->delete(T_ORD_CANCELS);
            }

            #Delete orders of this prod.
            $db        = $db->where('prod_id',$prod_id);
            $rm_orders = $db->delete(T_ORDERS);
        }

        #Delete product reviews data
        $db          = $db->where('prod_id',$prod_id);
        $prod_rating = $db->get(T_PROD_RATINGS);
        if (hs_queryset($prod_rating)) {
            foreach ($prod_rating as $rating_item) {
                $db       = $db->where('review_id',$rating_item->id);
                $rows_med = $db->get(T_PROD_RATING_MEDIA);

                if (hs_queryset($rows_med)) {
                    foreach ($rows_med as $row) {
                        hs_delete_image($row->file_path);
                    }
                }

                $db       = $db->where('review_id',$rating_item->id);
                $rows_del = $db->delete(T_PROD_RATING_MEDIA);
            }

            #Delete product reviews
            $db          = $db->where('prod_id',$prod_id);
            $prod_rating = $db->delete(T_PROD_RATINGS);
        }

        #Delete product reports
        $db           = $db->where('prod_id',$prod_id);
        $prod_reports = $db->delete(T_PROD_REPORTS);

        #Delete product from wishlists
        $db           = $db->where('prod_id',$prod_id);
        $prod_wishls  = $db->delete(T_WLS_ITEMS);


        hs_delete_image($prod_item->poster);
        hs_delete_image($prod_item->thumb);
        $db     = $db->where('id',$prod_id);
        $delete = $db->delete(T_PRODUCTS);
    } 
}


function hs_ap_delete_account($acc_id = 0) {
    global $db;

    if (not_num($acc_id)) {
        return false;
    }

    $db->returnType = 'Array';
    $db             = $db->where('id',$acc_id);
    $acc_data       = $db->getOne(T_USERS);

    if (hs_queryset($acc_data) != true) {
        return false;
    }

    #Delete user products
    $db   = $db->where('user_id', $acc_id);
    $data = $db->get(T_PRODUCTS);

    if (hs_queryset($data)) {
        foreach ($data as $prod_item) {
            hs_ap_delete_product($prod_item->id);
        }
    }

    #Delete user basket products
    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_BASKET);

    #Delete user chats 1
    $db   = $db->where('user_one', $acc_id);
    $data = $db->delete(T_CONVERSATIONS);

    #Delete user chats 2
    $db   = $db->where('user_two', $acc_id);
    $data = $db->delete(T_CONVERSATIONS);

    #Get user messages 1
    $db   = $db->where('sent_by', $acc_id);
    $data = $db->get(T_MESSAGES);

    if (hs_queryset($data)) {
        foreach ($data as $msg_item) {
            hs_delete_image($msg_item->media_file);
        }

        #Delete user messages 1
        $db   = $db->where('sent_by', $acc_id);
        $data = $db->delete(T_MESSAGES);
    }

    #Get user messages 2
    $db   = $db->where('sent_to', $acc_id);
    $data = $db->get(T_MESSAGES);

    if (hs_queryset($data)) {
        foreach ($data as $msg_item) {
            hs_delete_image($msg_item->media_file);
        }

        #Delete user messages 2
        $db   = $db->where('sent_to', $acc_id);
        $data = $db->delete(T_MESSAGES);
    }

    #Delete user transactions1
    $db   = $db->where('seller_id', $acc_id);
    $data = $db->delete(T_CHKOUT_TRANS);

    #Delete user transactions2
    $db   = $db->where('buyer_id', $acc_id);
    $data = $db->delete(T_CHKOUT_TRANS);    

    #Delete user data storage sessions
    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_DATA_SESSIONS);

    #Delete user notifications 1
    $db   = $db->where('notifier_id', $acc_id);
    $data = $db->delete(T_NOTIFS);

    #Delete user notifications 2
    $db   = $db->where('recipient_id', $acc_id);
    $data = $db->delete(T_NOTIFS);

    #Delete user orders 1
    $db   = $db->where('seller_id', $acc_id);
    $data = $db->delete(T_ORDERS);


    #Delete user orders 2
    $db   = $db->where('buyer_id', $acc_id);
    $data = $db->delete(T_ORDERS);

    #Delete user orders history 1
    $db   = $db->where('seller_id', $acc_id);
    $data = $db->delete(T_ORD_HIST_TL);


    #Delete user orders history 2
    $db   = $db->where('buyer_id', $acc_id);
    $data = $db->delete(T_ORD_HIST_TL);

    #Delete user orders refunds 1
    $db   = $db->where('seller_id', $acc_id);
    $data = $db->delete(T_ORD_CANCELS);

    #Delete user orders refunds 2
    $db   = $db->where('buyer_id', $acc_id);
    $data = $db->delete(T_ORD_CANCELS);

    #Delete user withdrawals
    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_PAYOUT_REQS);

    #Delete user feedbacks
    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_PROD_RATINGS);

    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_PROD_REPORTS);

    #Fetch user feedback media
    $db   = $db->where('user_id', $acc_id);
    $data = $db->get(T_PROD_RATING_MEDIA);

    if (hs_queryset($data)) {
        foreach ($data as $media_data) {
            hs_delete_image($media_data->file_path);
        }

        #Delete user feedback media
        $db   = $db->where('user_id', $acc_id);
        $data = $db->delete(T_PROD_RATING_MEDIA);
    }

    #Delete user sessions
    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_SETTINGS);

    #Delete user from custs 1
    $db   = $db->where('seller_id', $acc_id);
    $data = $db->delete(T_STORE_CUSTOMERS);

    #Delete user from custs 2
    $db   = $db->where('buyer_id', $acc_id);
    $data = $db->delete(T_STORE_CUSTOMERS);

    #Delete user temp data
    $db   = $db->where('user_id', $acc_id);
    $data = $db->delete(T_TEMP_DATA);

    #Fetch user wishlists
    $db   = $db->where('user_id', $acc_id);
    $data = $db->get(T_WISHLIST);

    if (hs_queryset($data)) {
        foreach ($data as $ls_data) {
            $db = $db->where('list_id',$ls_data->id);
            $rm = $db->delete(T_WLS_ITEMS);
        }

        #Fetch user wishlists
        $db   = $db->where('user_id', $acc_id);
        $data = $db->delete(T_WISHLIST);
    }

    #Delete user from admins
    $db   = $db->where('user_id', $acc_id);
    $data = $db->get(T_ADMINS);

    $db = $db->where('id',$acc_id);
    $rm = $db->delete(T_USERS);

    if ($acc_data['avatar'] != 'upload/users/user-avatar.png') {
        hs_delete_image($acc_data['avatar']);
    }

    return true;
}

function hs_ap_info_get_merchants($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args    = (is_array($args)) ? $args : array();
    $options = array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "keyword"   => false,
    );

    $args           = array_merge($options, $args);
    $offset         = $args['offset'];
    $limit          = $args['limit'];
    $order          = $args['order'];
    $keyword        = $args['keyword'];
    $offset_to      = $args['offset_to'];
    $data           = array();
    $t_prods        = T_PRODUCTS;
    $t_users        = T_USERS;
    $t_payouts      = T_PAYOUT_REQS;
    $sql            = hs_sqltepmlate('admin/hex_ap_info_get_merchants',array(
        'offset'    => $offset,
        't_prods'   => $t_prods,
        't_users'   => $t_users,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'keyword'   => $keyword,
        't_payouts' => $t_payouts,
    ));

    $data  = array();
    $users = $db->rawQuery($sql);

    if (hs_queryset($users)) {
        foreach ($users as $user_data) {
            $banner_code            = fetch_or_get($hs['country_codes'][$user_data->country_id],'us');
            $user_data->banner      = hs_banner($banner_code);
            $user_data->url         = hs_link($user_data->username);
            $user_data->avatar      = hs_get_media($user_data->avatar);
            $user_data->last_active = date('d M, Y',$user_data->last_active);
            $user_data->lpout       = hs_translate('Never');
            if (not_empty($user_data->lp_date) && not_empty($user_data->lp_amount) && not_empty($user_data->lp_currency)) {
                $user_data->lpout   = hs_str_form("{0} ({1}{2})",array(
                    date('d M, Y',$user_data->lp_date),
                    hs_money($user_data->lp_amount),
                    hs_currency($user_data->lp_currency),
                ));
            }
        }

        $data = hs_o2array($users);
    }

    return $data;
}

function hs_ap_info_get_users($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "keyword"   => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $keyword        =  $args['keyword'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_users',array(
        'offset'    => $offset,
        't_users'   => $t_users,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'keyword'   => $keyword,
    ));

    $data  = array();
    $users = $db->rawQuery($sql);

    if (hs_queryset($users)) {
        foreach ($users as $user_data) {
            $user_data->url         = hs_link($user_data->username);
            $user_data->avatar      = hs_get_media($user_data->avatar);
            $user_data->last_active = date('d M, Y',$user_data->last_active);
            $banner_code            = fetch_or_get($hs['country_codes'][$user_data->country_id],'us');
            $user_data->banner      = hs_banner($banner_code);
        }

        $data = hs_o2array($users);
    }

    return $data;
}

function hs_ap_info_get_total_merchants() {
    global $db;
    $db  = $db->where('is_seller','Y');
    $res = $db->getValue(T_USERS,'COUNT(*)');
    $res = (is_number($res)) ? $res : 0;
    return $res;
}

function hs_ap_info_get_total_users() {
    global $db;
    $res = $db->getValue(T_USERS,'COUNT(*)');
    $res = (is_number($res)) ? $res : 0;
    return $res;
}

function hs_ap_info_get_total_moders() {
    global $db;
    $res = $db->getValue(T_ADMINS,'COUNT(*)');
    $res = (is_number($res)) ? $res : 0;
    return $res;
}

function hs_ap_info_get_customer_reports($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args    = (is_array($args)) ? $args : array();
    $options = array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "keyword"   => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $keyword        =  $args['keyword'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
    $t_prods        =  T_PRODUCTS;
    $t_users        =  T_USERS;
    $t_reports      =  T_PROD_REPORTS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_customer_reports',array(
        'offset'    => $offset,
        't_users'   => $t_users,
        't_prods'   => $t_prods,
        't_reports' => $t_reports,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'keyword'   => $keyword,
    ));

    

    $data    = array();
    $reports = $db->rawQuery($sql);
    if (hs_queryset($reports)) {
        foreach ($reports as $report) {
            $report->prod_url    = hs_link(hs_str_form("product/{0}",array($report->prod_id)));
            $report->seller_url  = hs_link(hs_str_form("{0}",array($report->prod_seller)));
            $report->avatar      = hs_get_media($report->avatar);
            $report->time        = date('d M, Y',$report->time);
            $report->prod_img    = hs_get_media($report->prod_img);
            $crop_length         = rand((42 / 2), 42);
            $report->prod_name   = hs_croptxt($report->prod_name,$crop_length,60);
        }

        $data = hs_o2array($reports);
    }

    return $data;
}

function hs_ap_info_get_customer_report_data($id = false) {
    global $hs,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin']) || not_num($id)) {
        return null;
    }

    $data           =  array();
    $t_users        =  T_USERS;
    $t_reports      =  T_PROD_REPORTS;
    $t_prods        =  T_PRODUCTS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_customer_report_data',array(
        'report_id' => $id,
        't_reports' => $t_reports,
        't_users'   => $t_users,
        't_prods'   => $t_prods,
    ));

    $data   = array();
    $report = $db->rawQueryOne($sql);
    if (hs_queryset($report,'object')) {
        $report->prod_url   = hs_link(hs_str_form("product/{0}",array($report->prod_id)));
        $report->seller_url = hs_link(hs_str_form("{0}",array($report->prod_seller)));
        $report->avatar     = hs_get_media($report->avatar);
        $report->time       = date('d M, Y',$report->time);
        $data               = hs_o2array($report);
    }

    return $data;
}

function hs_ap_info_get_payouts_total($status = null) {
    global $db;

    if (not_empty($status)) {
        if (in_array($status, array('pending','paid','declined'))) {
            $db = $db->where('status',$status);
        }
    }

    $tot = $db->getValue(T_PAYOUT_REQS,'COUNT(*)');
    $tot = ((is_number($tot)) ? $tot : 0);
    return $tot;
}

function hs_ap_info_get_market_payouts($args = array()) {
    global $hs,$me,$db;
    if (empty($args)) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "limit"     => 10,
        "offset"    => null,
        "offset_to" => null,
        "status"    => null,
        "order"     => "DESC"
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $status         =  $args['status'];
    $order          =  $args['order'];
    $limit          =  $args['limit'];
    $offset_to      =  $args['offset_to'];   
    $t_pouts        =  T_PAYOUT_REQS;
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_market_payouts',array(
        'limit'     => $limit,
        'offset'    => $offset,
        'offset_to' => $offset_to,
        't_pouts'   => $t_pouts,
        't_users'   => $t_users,
        'order'     => $order,
        'status'    => $status,
    ));

    $data     = array();
    $requests = $db->rawQuery($sql);

    if (hs_queryset($requests)) {
        foreach ($requests as $req_data) {
            $req_data->time        = date('d M, Y',$req_data->time);
            $req_data->avatar      = hs_get_media($req_data->avatar);
            $req_data->seller_url  = hs_link($req_data->username);
            $req_data->balance     = hs_money($req_data->balance); 
            $req_data->pp_link     = urldecode($req_data->pp_link);        
            $req_data->pp_link_src = sprintf("%s/%d%s",$req_data->pp_link,$req_data->amount,strtoupper($req_data->currency));
            $req_data->amount      = hs_money($req_data->amount);

        }

        $data = hs_o2array($requests);
    }

    return $data;
}

function hs_ap_info_yearly_payouts_stats() {
    global $db,$hs,$me;

    $t_payouts    = T_PAYOUT_REQS;
    $t_mark_reven = T_MRKT_REVENUE;
    $stats        = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);
    $year_months  = array(
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


    foreach ($year_months as $m_num => $m_time) {
        $next_num   = ($m_num + 1);
        $next_month = (isset($year_months[$next_num]) ? $year_months[$next_num] : 0);
        $db         = $db->where('time',$m_time,'>=');
        $db         = (($next_month) ? $db->where('time',$next_month,'<=') : $db);
        $res        = $db->get($t_mark_reven,null,array('amount'));
        $res        = ((hs_queryset($res)) ? hs_o2array($res) : array());

        foreach ($res as $i) {
            $stats[$m_num] =+ floatval($i['amount']);
        }
    }

    foreach ($stats as $ind => $stat_val) {
        $stats[$ind] = sprintf('%.2f',$stat_val);
    }

    return $stats;
}

function hs_ap_info_get_market_checkouts($args = array()) {
    global $hs,$me,$db;
    if (empty($args)) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "limit"     => 10,
        "offset"    => null,
        "offset_to" => null,
        "status"    => null,
        "order"     => "DESC",
        "customer"  => false,
        "seller"    => false,
        "amount"    => false,
        "status"    => false,
        "method"    => false,
        "order_id"  => false,
    );
    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $status         =  $args['status'];
    $order          =  $args['order'];
    $limit          =  $args['limit'];
    $offset_to      =  $args['offset_to']; 
    $customer       =  $args['customer'];
    $seller         =  $args['seller'];
    $amount         =  $args['amount'];
    $order_id       =  $args['order_id'];
    $status         =  $args['status'];
    $method         =  $args['method'];
    $t_couts        =  T_CHKOUT_TRANS;
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_market_checkouts',array(
        'limit'     => $limit,
        'offset'    => $offset,
        'offset_to' => $offset_to,
        't_couts'   => $t_couts,
        't_users'   => $t_users,
        'order'     => $order,
        'status'    => $status,
        'customer'  => $customer,
        'seller'    => $seller,
        'amount'    => $amount,
        'method'    => $method,
        'order_id'  => $order_id,
    ));

    $data           = array();
    $transactions   = $db->rawQuery($sql);

    if (hs_queryset($transactions)) {
        foreach ($transactions as $trans_data) {
            $trans_data->time          = date('d M, Y h:m',$trans_data->time);
            $trans_data->seller_avatar = hs_get_media($trans_data->seller_avatar);
            $trans_data->buyer_avatar  = hs_get_media($trans_data->buyer_avatar);
            $trans_data->seller_url    = hs_link($trans_data->seller_uname);
            $trans_data->buyer_url     = hs_link($trans_data->buyer_uname);
            $trans_data->amount        = hs_money($trans_data->amount);
        }

        $data = hs_o2array($transactions);
    }

    return $data;
}

function hs_ap_info_get_transaction_details($trans_id = false) {
    global $hs,$db;
    if (not_num($trans_id)) {
        return false;
    }

    $t_couts        =  T_CHKOUT_TRANS;
    $t_users        =  T_USERS;
    $t_prods        =  T_PRODUCTS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_transaction_details',array(
        'trans_id'  => $trans_id,
        't_couts'   => $t_couts,
        't_users'   => $t_users,
        't_prods'   => $t_prods,
    ));

    $data       = array();
    $trans_data = $db->rawQueryOne($sql);

    if (hs_queryset($trans_data,'object')) {
        $trans_data->time          = date('d M, Y h:m',$trans_data->time);
        $trans_data->seller_avatar = hs_get_media($trans_data->seller_avatar);
        $trans_data->buyer_avatar  = hs_get_media($trans_data->buyer_avatar);
        $trans_data->seller_url    = hs_link($trans_data->seller_uname);
        $trans_data->buyer_url     = hs_link($trans_data->buyer_uname);
        $trans_data->prod_url      = hs_link(sprintf("product/%d",$trans_data->prod_id));
        $trans_data->prod_name     = hs_croptxt($trans_data->prod_name,32,'..');
        $trans_data->amount        = hs_money($trans_data->amount);

        $data = hs_o2array($trans_data);
    }

    return $data;
}

function hs_ap_info_get_market_total_checkouts() {
    global $db;

    $tot = $db->getValue(T_CHKOUT_TRANS,'COUNT(*)');
    $tot = ((is_number($tot) == true) ? $tot : 0);
    return $tot;
}

function hs_ap_save_config($key = false,$val = "none") {
    global $db,$hs;
    if (in_array($key, array_keys($hs['config']))) {
        $db = $db->where('name',$key);
        $up = $db->update(T_CONFIG,array('value' => $val));
    }
    else{
        return false;
    }
}

function hs_ap_create_backup() {
    global $mysqli,$db,$hs;
    $time    = time();
    $date    = date('d-m-Y');
    $backup  = new MySQLDump($mysqli);

    try {
        if (file_exists("site_backups/{$date}_{$time}") != true) {
            @mkdir("site_backups/{$date}_{$time}", 0777, true);
        }

        if (file_exists("site_backups/$date/index.html") != true) {
            $f = @fopen("site_backups/$date/index.html", "a+");
            @fclose($f);
        }

        if (file_exists('site_backups/.htaccess') != true) {
            $f = @fopen("site_backups/.htaccess", "a+");
            @fwrite($f, "deny from all\nOptions -Indexes");
            @fclose($f);
        }

        if (file_exists("site_backups/$date/index.html") != true) {
            $f = @fopen("site_backups/$date/index.html", "a+");
            @fclose($f);
        }

        if (file_exists('site_backups/index.html') != true) {
            $f = @fopen("site_backups/index.html", "a+");
            @fwrite($f, "");
            @fclose($f);
        }

        $folder_name  = "site_backups/{$date}_{$time}";
        $sql_backup   = "$folder_name/SQL-Backup-{$time}-{$date}.sql";
        $files_backup = "$folder_name/Files-Backup-{$time}-{$date}.zip";
        $put          = @$backup->save($sql_backup);
        $rootPath     = ROOTPATH;
        $zip          = new ZipArchive();
        $act          = (ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $open         = $zip->open($files_backup,$act);


        if ($open !== true) {
            return false;
        }

        $riiter = RecursiveIteratorIterator::LEAVES_ONLY;
        $rditer = new RecursiveDirectoryIterator($rootPath);
        $files  = new RecursiveIteratorIterator($rditer,$riiter);

        foreach ($files as $name => $file) {
            if (preg_match('/\bsite_backups\b/', $file) != true) {
                if (!$file->isDir()) {
                    $filePath     = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();

        hs_ap_save_config('last_backup',date('Y-m-d h:i:s'));

        $insert_id         = $db->insert(T_BACKUPS,array(
            'backup_dir'   => "{$date}_{$time}",
            'files_backup' => "Files-Backup-{$time}-{$date}.zip",
            'sql_backup'   => "SQL-Backup-{$time}-{$date}.sql",
            'file_size'    => filesize($files_backup),
            'sql_size'     => filesize($sql_backup),
            'time'         => time(),
        ));

        return $insert_id;    
    } 
    catch (Exception $e) {
        return false;
    }
}

function hs_ap_info_get_backups() {
    global $db,$hs;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }
    
    $db      = $db->orderBy('id','DESC');
    $backups = $db->get(T_BACKUPS);
    $data    = array();

    if (hs_queryset($backups)) {
        foreach ($backups as $backup_data) {
            $backup_data->time      = date('d F, Y h:m',$backup_data->time);
            $backup_data->file_size = filesize_formatted($backup_data->file_size);
            $backup_data->sql_size  = filesize_formatted($backup_data->sql_size);
        }

        $data = hs_o2array($backups);
    }

    return $data;
}

function hs_ap_info_get_account_removals($args = array()) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "ids"       => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $offset_to      =  $args['offset_to'];
    $ids            =  ((is_array($args['ids']) && hs_all($args['ids'],'numeric')) ? implode(',', $args['ids']) : false);
    $data           =  array();
    $t_acc_rms      =  T_ACC_DEL_REQS;
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_account_removals',array(
        'offset'    => $offset,
        't_users'   => $t_users,
        't_acc_rms' => $t_acc_rms,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'ids'       => $ids,
    ));

    $data     = array();
    $requests = $db->rawQuery($sql);

    if (hs_queryset($requests)) {
        foreach ($requests as $req_data) {
            $username            = $req_data->username;
            $req_data->url       = hs_link($username);
            $req_data->ts_url    = hs_link(sprintf("temporary_session_start/%s",$username));
            $req_data->avatar    = hs_get_media($req_data->avatar);
            $req_data->time      = date('d M, Y h:m',$req_data->time);
            $req_data->last_seen = date('d M, Y h:m',$req_data->last_seen);
        }

        $data = hs_o2array($requests);
    }

    return $data;
}

function hs_ap_info_get_account_verification($args = array()) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "ids"       => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $offset_to      =  $args['offset_to'];
    $ids            =  ((is_array($args['ids']) && hs_all($args['ids'],'numeric')) ? implode(',', $args['ids']) : false);
    $data           =  array();
    $t_acc_vrf      =  T_VERIF_REQS;
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_account_verifications',array(
        'offset'    => $offset,
        't_users'   => $t_users,
        't_acc_vrf' => $t_acc_vrf,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'ids'       => $ids,
    ));

    $data     = array();
    $requests = $db->rawQuery($sql);

    if (hs_queryset($requests)) {
        foreach ($requests as $req_data) {
            $username            = $req_data->username;
            $req_data->url       = hs_link($username);
            $req_data->avatar    = hs_get_media($req_data->avatar);
            $req_data->id_photo  = hs_get_media($req_data->id_photo);
            $req_data->pr_photo  = hs_get_media($req_data->pr_photo);
            $req_data->time      = date('d M, Y h:m',$req_data->time);
        }

        $data = hs_o2array($requests);
    }

    return $data;
}

function hs_ap_info_get_account_removals_tatal() {
    global $db;

    $tot = $db->getValue(T_ACC_DEL_REQS,'COUNT(*)');
    $tot = ((is_number($tot) == true) ? $tot : 0);
    return $tot;
}

function hs_ap_info_get_account_verification_tatal() {
    global $db;

    $tot = $db->getValue(T_VERIF_REQS,'COUNT(*)');
    $tot = ((is_number($tot) == true) ? $tot : 0);
    return $tot;
}

function hs_ap_info_get_static_page($page_name = "") {
    global $db;

    if (empty($page_name)) {
        return "";
    }

    $page_name = hs_secure($page_name,true,false);
    $db        = $db->where('page_name',$page_name);
    $page_cont = $db->getOne(T_STATIC_PAGES);
    $page_cont = ((hs_queryset($page_cont,'object')) ? hs_o2array($page_cont) : array());
    $data      = "";


    if (not_empty($page_cont)) {
        $data = fetch_or_get($page_cont['page_content'],$data);
    }

    return $data;
}

function hs_ap_info_set_static_page($page_name = "",$content = "") {
    global $db;

    if (empty($page_name) || empty($content)) {
        return false;
    }

    $page_name = hs_secure($page_name);
    $page_cont = hs_html_secure($content);
    $db        = $db->where('page_name',$page_name);
    $update    = $db->update(T_STATIC_PAGES,array('page_content' => $page_cont));

    return (($update == true) ? true : false);
}

function hs_ap_info_get_lang_datasets($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'ASC',
        "keyword"   => false,
        "language"  => 'none'
    );

    $args           = array_merge($options, $args);
    $offset         = $args['offset'];
    $limit          = $args['limit'];
    $order          = $args['order'];
    $keyword        = $args['keyword'];
    $offset_to      = $args['offset_to'];
    $language       = $args['language'];
    $db->returnType = 'Array';
    $db             = $db->orderBy('id',$order);

    if ($offset) {
        if ($offset_to == 'gt') {
            $db->where('id',$offset,'>');
        }
        else {
            $db->where('id',$offset,'<');
        }
    }

    if ($keyword) {
        $db->where($language,"%$keyword%",'LIKE');
    }

    $data  = array();   
    $langs = $db->get(T_LANGS,$limit,array("id",$language));

    if (hs_queryset($langs)) {
        foreach ($langs as $lang_data) {
            $lang_data[$language] = stripcslashes($lang_data[$language]);
            $lang_data[$language] = html_entity_decode($lang_data[$language], ENT_QUOTES | ENT_HTML5);
            $lang_data[$language] = htmlspecialchars_decode($lang_data[$language]);
            $data[]               = $lang_data;
        }
    }

    return $data;
}

function hs_get_langs_usage($langs = array()) {
    global $db,$hs;

    if (empty($langs) || is_array($langs) != true) {
        return array();
    }

    $data = array();

    foreach ($langs as $lang_data) {
        $db                 = $db->where('language',$lang_data['lang_name']);
        $db                 = $db->where('active','1');
        $lang_data['usage'] = $db->getValue(T_USERS,'COUNT(*)');
        $data[]             = $lang_data;
    }

    return $data;
}

function hs_get_first_active_langname() {
    global $db,$hs;

    $db  = $db->where('lang_name','english');
    $db  = $db->where('status','active');
    $res = $db->getValue(T_LANGUAGES,'COUNT(*)');

    if (not_empty($res)) {
        return 'english';
    }

    else {
        $db->returnType = 'Array';
        $db             = $db->orderBy('sort_order','ASC');
        $db             = $db->where('status','active');
        $lang_data      = $db->getOne(T_LANGUAGES);
        $lang_name      = fetch_or_get($lang_data['lang_name'],null);

        if (empty($lang_name)) {
            die("Error: No active interface languages found!");
        }

        return $lang_name;
    }
}


function hs_ap_info_get_prod_categories($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'ASC',
        "keyword"   => false,
        "status"    => false,
    );

    $args           = array_merge($options, $args);
    $offset         = $args['offset'];
    $limit          = $args['limit'];
    $order          = $args['order'];
    $keyword        = $args['keyword'];
    $status         = $args['status'];
    $offset_to      = $args['offset_to'];
    $db->returnType = 'Array';
    $db             = $db->orderBy('sort_order',$order);

    if ($offset) {
        if ($offset_to == 'gt') {
            $db->where('sort_order',$offset,'>');
        }
        else {
            $db->where('sort_order',$offset,'<');
        }
    }

    if ($keyword) {
        $db->where('catg_name',"%$keyword%",'LIKE');
    }

    if ($status && in_array($status, array('active','inactive'))) {
        $db->where('status',$status);
    }

    $categories = $db->get(T_PROD_CATS,$limit);
    $data       = array();

    if (hs_queryset($categories)) {
        foreach ($categories as $catg_data) {
            $catg_data['usage'] = hs_ap_info_get_prod_category_usage($catg_data['catg_id']);
            $catg_data['url']   = hs_link(sprintf("catalog/%s",$catg_data['catg_id']));
            $data[]             = $catg_data;
        }
    }

    return $data;
}

function hs_ap_info_get_prod_category_usage($catg_name = 'none') {
    global $db,$hs;

    if (empty($catg_name)) {
        return 0;
    }

    $db    = $db->where('category',$catg_name);
    $db    = $db->where('status',array('active','inactive'),'IN');
    $db    = $db->where('activity_status','active');
    $usage = $db->getValue(T_PRODUCTS,'COUNT(*)');
    return (is_number($usage)) ? $usage : 0;
}


function hs_ap_info_get_moders($args = false) {
    global $hs,$me,$db;
    if (empty($hs['is_logged']) || empty($hs['is_admin'])) {
        return array();
    }

    $args           =  (is_array($args)) ? $args : array();
    $options        =  array(
        "offset"    => false,
        "limit"     => 10,
        "offset_to" => false,
        "order"     => 'DESC',
        "keyword"   => false,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $order          =  $args['order'];
    $keyword        =  $args['keyword'];
    $offset_to      =  $args['offset_to'];
    $data           =  array();
    $t_admins       =  T_ADMINS;
    $t_users        =  T_USERS;
    $sql            =  hs_sqltepmlate('admin/hex_ap_info_get_moders',array(
        'offset'    => $offset,
        't_users'   => $t_users,
        'limit'     => $limit,
        'offset_to' => $offset_to,
        'order'     => $order,
        'keyword'   => $keyword,
        't_admins'  => $t_admins,
    ));

    $data  = array();
    $users = $db->rawQuery($sql);

    if (hs_queryset($users)) {
        foreach ($users as $user_data) {
            $user_data->url         = hs_link($user_data->username);
            $user_data->avatar      = hs_get_media($user_data->avatar);
            $user_data->last_active = date('d M, Y',$user_data->last_active);
        }

        $data = hs_o2array($users);
    }

    return $data;
}

function hs_ap_send_announcement($insert_data = array()) {
    global $db,$hs;

    if (empty($insert_data) || is_array($insert_data) != true) {
        return false;
    }

    else {
        $type    = fetch_or_get($insert_data['slug'],false);
        $user_id = fetch_or_get($insert_data['user_id'],0);
        $slugs   = array('temp_prods_blocking');

        if (not_empty($type) && in_array($type, $slugs)) {
            $db = $db->where('user_id',$user_id);
            $db = $db->where('slug',$type);
            $rm = $db->delete(T_ANNOUNC);
        }

        $insert = $db->insert(T_ANNOUNC,$insert_data);
    }
}