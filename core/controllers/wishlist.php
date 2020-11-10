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

function hs_get_wishlists($user_id = '') {
    global $db,$hs,$me;
    if(not_num($user_id)) {
        return false;
    }

    $db    = $db->where('user_id',$user_id);
    $query = $db->get(T_WISHLIST);
    $lists = array();

    if (hs_queryset($query) == true) {
        foreach ($query as $wsh_ls) {
            $wsh_ls->url         = hs_link(sprintf("wishlist/%s",$wsh_ls->hash_id));
            $wsh_ls->total_items = hs_get_wishlist_total_items($wsh_ls->id);
        }

        $lists = hs_o2array($query);
    }

    return $lists;
}

function hs_get_total_wishlists($user_id = '',$type = null) {
    global $db,$hs,$me;
    if(not_num($user_id)) {
        return false;
    }

    if (in_array($type, array('static','removable'))) {
        $db = $db->where('type',$type);
    }

    $db    = $db->where('user_id',$user_id);
    $total = $db->getValue(T_WISHLIST,'COUNT(*)');
    return (is_number($total) && $total > 0) ? intval($total) : 0;
}

function hs_get_wishlist_total_items($list_pk = null) {
    global $db;
    if (not_num($list_pk)) {
        return 0;
    }

    $t_prd = T_PRODUCTS;
    $t_wli = T_WLS_ITEMS;

    $db    = $db->join("`{$t_prd}` prd","wli.`prod_id` = prd.`id`","LEFT OUTER");
    $db    = $db->where('prd.`activity_status`','active');
    $db    = $db->where('prd.`status`','active');
    $db    = $db->where('prd.`approved`','Y');
    $db    = $db->where('wli.`list_id`',$list_pk);
    $query = $db->get("`{$t_wli}` wli");
    $total = (hs_queryset($query) == true) ? count($query) : 0;

    return $total;
}

function hs_get_wishlists_data($list_id = null) {
    global $db,$hs;

    $data     = null;
    $user_id  = $hs['me']['id'];
    $db       = $db->where('user_id',$user_id);
    $db       = $db->where('hash_id',$list_id);
    $wishlist = $db->getOne(T_WISHLIST);
    if (hs_queryset($wishlist,'object')) {
        $wishlist->url = hs_link(sprintf("wishlist/%s",$wishlist->hash_id));
        $data          = hs_o2array($wishlist);
    }

    return $data;
}

function hs_get_wishlists_items($args = array()) {
	global $db,$hs;
	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
        "user_id"   => null,
        "list_id"   => null,
        "catg_id"   => null,
        "offset"    => null,
        "keyword"   => null,
        "condition" => null,
        "sortby"    => null,
        "brand"     => null,
        "ship_cost" => null,
        "ship_time" => null,
        "sell_cntr" => null,
        "min_price" => null,
        "max_price" => null,
        "limit"     => 10,
    );
    $args           =  array_merge($options, $args);
    $user_id        =  $args['user_id'];
    $brand          =  $args['brand'];
    $ship_cost      =  $args['ship_cost'];
    $sortby         =  $args['sortby'];
    $sell_cntr      =  $args['sell_cntr'];
    $ship_time      =  $args['ship_time'];
    $min_price      =  $args['min_price'];
    $max_price      =  $args['max_price'];
    $condition      =  $args['condition'];
    $list_id        =  $args['list_id'];
    $catg_id        =  $args['catg_id'];
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $keyword        =  $args['keyword'];
    $sql            =  hs_sqltepmlate('products/hex_get_wishlists_items',array(
        'list_id'   => $list_id,
        'brand'     => $brand,
        'ship_cost' => $ship_cost,
        'sortby'    => $sortby,
        'sell_cntr' => $sell_cntr,
        'ship_time' => $ship_time,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'condition' => $condition,
        'user_id'   => $user_id,
        'catg_id'   => $catg_id,
        'offset'    => $offset,
        'keyword'   => $keyword,
        'limit'     => $limit,
        't_prods'   => T_PRODUCTS,
        't_wlist'   => T_WLS_ITEMS,
        't_users'   => T_USERS,
    ));

    $data           = array();
    $list_data      = $db->rawQuery($sql);

    if (hs_queryset($list_data) == true) {
        foreach ($list_data as $list_item) {
            $list_item->poster     = hs_get_media($list_item->poster);
            $list_item->sale_price = hs_money($list_item->sale_price,0);
            $list_item->reg_price  = hs_money($list_item->reg_price,0);
            $list_item->prod_url   = hs_link(sprintf("product/%s",$list_item->prod_id));
            $list_item->discount   = hs_price_discount($list_item->reg_price,$list_item->sale_price);
        }

        $data = hs_o2array($list_data); 
    }

    return $data;
}