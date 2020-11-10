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

function hs_get_edited_product($prod_id = null) {
	global $me,$hs,$db;
	if (empty($hs['is_logged']) || is_number($prod_id) != true) {
		return false;
	}

	$db        = $db->where('id',$prod_id);
	$db        = $db->where('user_id',$me['id']);
	$prod_data = $db->getOne(T_PRODUCTS);
	$data      = array();

	if (hs_queryset($prod_data,'object')) {
		$db    = $db->where('prod_id',$prod_id);
		$media = $db->get(T_PROD_MEDIA);
		$media = (empty($media) != true) ? hs_o2array($media) : array();
		$prod_data->media = $media;
		$data             = hs_o2array($prod_data);
	}

	return $data;
}

function hs_prod_type_exists($data = array(),$active = false) {
	global $hs,$db;
	if (empty($data) || is_array($data) != true) {
		return false;
	}

	$conds = 0;
	foreach ($data as $col => $val) {
		$db->where($col,$val); $conds++;
	}

	if ($active == true) {
		$db->where('activity_status','active');
	}

	return ($conds) ? $db->getValue(T_PROD_VARS,'COUNT(*)') : false;
}

function hs_get_prod_fields($prod_id = null,$data = null) {
	global $hs,$db;
	if (not_num($prod_id)) {
		return false;
	}

	$prod_data = $db->where('id',$prod_id)->getOne(T_PRODUCTS,$data);
	$prod_data = (not_empty($prod_data) == true) ? hs_o2array($prod_data) : null;

	return $prod_data;
}

function hs_setprod_val($prod_id = null,$data = array()) {
	global $db,$hs;
	if ((not_num($prod_id)) || (empty($data) || is_array($data) != true)) {
		return false;
	} 

	$db     = $db->where('id', $prod_id);
	$update = $db->update(T_PRODUCTS,$data);
	return ($update == true) ? true : false;
}

function hs_setprodvar_val($var_id = null,$data = array()) {
	global $db,$hs;
	if (not_num($var_id) || (empty($data) || is_array($data) != true)) {
		return false;
	} 

	$db     = $db->where('id', $var_id);
	$update = $db->update(T_PROD_VARS,$data);
	return ($update == true) ? true : false;
}

function hs_is_prodowner($user_id = 0,$prod_id = 0) {
	global $hs,$me,$db;
	if (empty($user_id) || is_numeric($user_id) != true) {
		if (empty($hs['is_logged'])) {
			return false;
		} 

		else{
			$user_id = $me['id'];
		}
	} 

	else if(empty($prod_id) || is_numeric($prod_id) != true) {
		return false;
	}

	$isowner = $db->where('id',$prod_id)->where('user_id',$user_id)->getValue(T_PRODUCTS,'COUNT(*)');
	return (empty($isowner)) ? false : true;
}

function hs_get_prodvars_total($prod_id = 0) {
	global $hs,$db;
	if(not_num($prod_id)) {
		return 0;
	}

	$db    = $db->where('prod_id',$prod_id);
	$db    = $db->where('activity_status','active');
	$total = $db->getValue(T_PROD_VARS,'COUNT(*)');
	return (is_numeric($total)) ? $total : 0;
}

function hs_get_user_total_items($user_id = 0) {
	global $hs,$db;
	if(not_num($user_id)) {
		return 0;
	}

	$db    = $db->where('user_id',$user_id);
	$db    = $db->where('activity_status','active');
	$db    = $db->where('status','active');
	$db    = $db->where('approved','Y');
	$total = $db->getValue(T_PRODUCTS,'COUNT(*)');
	return (is_numeric($total)) ? $total : 0;
}

function hs_product_data($prod_id = null) {
	global $db,$hs,$me;
	if (not_num($prod_id)) {
		return false;
	}

	$data    = null;
	$db      = $db->where('id',$prod_id);
	$db      = $db->where('activity_status','active');
	$db      = $db->where('editing_stage','saved');
	$product = $db->getOne(T_PRODUCTS);
	$valid   = true;

	if (hs_queryset($product,'object')) {	
		$product->is_variable = (in_array($product->variation_type, array('color','size','color_size')));
		if ($product->is_variable === true) {
			$product->quantity = 0;
			$product_vars      = hs_get_prod_vars($prod_id,$product->variation_type);
			if (hs_queryset($product_vars)) {	
				$product->vars = $product_vars;

				if ($product->variation_type == 'color_size') {
					$product->active_var = fetch_or_get($product_vars[0],array());

					$db->returnType = 'Array';
					$db             = $db->where('prod_id',$prod_id);
					$db             = $db->where('var_type','color_size');
					$db             = $db->where('activity_status','active');
					$db             = $db->where('status','active');
					$cs_vars        = $db->get(T_PROD_VARS,null,array('quantity'));
					$cs_vars        = ((hs_queryset($cs_vars)) ? $cs_vars : array());

					foreach ($cs_vars as $cs_var_item) {
						$product->quantity += intval($cs_var_item['quantity']);
					}
				}

				else {
					foreach ($product_vars as $var_item) {
						$product->quantity += intval($var_item->quantity);
					}
				}
			}
		}

		$prod_media = $db->where('prod_id',$prod_id)->get(T_PROD_MEDIA,null,array('src','thumb'));
		if (not_empty($prod_media) && is_array($prod_media)) {
			$product->media = $prod_media;
			foreach ($product->media as $med_row) {
				$med_row->thumb = hs_get_media($med_row->thumb);
				$med_row->src   = hs_get_media($med_row->src);	
			}
		} 

		$check_is_liked =  array(
			'prod_id'   => $prod_id,
			'user_id'   => $me['id'],
		);

		$product->is_approved = (($product->approved == 'Y') ? true : false);
		$product->poster      = hs_get_media($product->poster);
		$product->thumb       = hs_get_media($product->thumb);
		$product->fs_price    = hs_price($product->sale_price);
		$product->fr_price    = hs_price($product->reg_price);
		$product->price_disc  = hs_price_discount($product->reg_price,$product->sale_price);
		$product->liked       = hs_where_exists(T_WLS_ITEMS,$check_is_liked);
		$product->owner       = ($product->user_id == $me['id']);
		$product->edit        = hs_link(sprintf("merchant_panel/edit_product/%s",$prod_id));
		$product->pres_link   = hs_link(sprintf("product/%s",$prod_id));
		$product->share_link  = hs_link(sprintf("share/product?id=%s",$prod_id));
		$product->ship_fee    = ((not_empty($product->shipping_fee)) ? hs_money($product->shipping_fee) : '0.00');
		$data                 = hs_o2array($product);	
	}

	return $data;
}

function hs_product_form_data($prod_id = null) {
	global $db,$hs;
	if (not_num($prod_id)) {
		return false;
	}

	$data    = null;
	$db      = $db->where('id',$prod_id);
	$product = $db->getOne(T_PRODUCTS);

	if (hs_queryset($product,'object')) {	
		$product->is_variable = (in_array($product->variation_type, array(
			'color',
			'size',
			'color_size'
		)));

		$product->is_measurable = (in_array($product->variation_type, array(
			'size',
			'color_size'
		)));

		$db         = $db->where('prod_id',$prod_id);
		$db         = $db->orderBy('id','DESC');
		$prod_media = $db->get(T_PROD_MEDIA,null,array('src','thumb','id'));

		if (hs_queryset($prod_media)) {
			$product->media = $prod_media;
			foreach ($product->media as $med_row) {
				$med_row->src   = hs_get_media($med_row->src);
				$med_row->thumb = hs_get_media($med_row->thumb);
			}
		} 

		$product->poster  = hs_get_media($product->poster);
		$product->totvars = ($product->is_variable) ? hs_get_prodvars_total($prod_id) : 0;
		$data             = hs_o2array($product);
		
	}

	return $data;
}

function hs_get_prod_vars($prod_id = null,$type = null) {
	global $db,$hs;
	if (empty($prod_id) || is_numeric($prod_id) != true) {
		return false;
	} 

	else if(empty($type) || !in_array($type, array('color','size','color_size'))) {
		return false;
	}

	$data = null;

	if($type == 'color_size') {
		$db   = $db->where('prod_id',$prod_id);
		$db   = $db->where('var_type','color_size');
		$db   = $db->where('activity_status','active');
		$db   = $db->groupBy('col_hex');
		$vars = $db->get(T_PROD_VARS,20,array('col_hex'));
		$vars = (not_empty($vars) && is_array($vars)) ? $vars : array();
		
		foreach ($vars as $var_group) {
			$var_data = hs_get_prod_csv_data($prod_id,$var_group->col_hex);
			if (not_empty($var_data)) {
				$data[] = $var_data;
			}
		}
	} 

	else if($type == 'color') {
		$db   = $db->where('prod_id',$prod_id);
		$db   = $db->where('var_type','color');
		$db   = $db->where('activity_status','active');
		$vars = $db->get(T_PROD_VARS,20);
		$vars = (not_empty($vars) && is_array($vars)) ? $vars : array();
		
		foreach ($vars as $var_item) {
			$var_image         = $var_item->col_img;
			$var_thumb         = $var_item->col_thumb;
			$var_item->col_img = hs_get_media($var_image);
			$var_item->thumb   = hs_get_media($var_thumb);
			$data[]            = $var_item;
		}
	} 

	else if($type == 'size') {
		$db   = $db->where('prod_id',$prod_id);
		$db   = $db->where('var_type','size');
		$db   = $db->where('activity_status','active');
		$vars = $db->get(T_PROD_VARS,20);
		$vars = (!empty($vars) && is_array($vars)) ? $vars : array();
		
		foreach ($vars as $var_item) {
			$data[] = $var_item;
		}
	}

	return $data;
}

function hs_get_prod_csv_data($prod_id = null,$col_hex = null) {
	global $db,$hs;
	if (empty($prod_id) || is_numeric($prod_id) != true) {
		return false;
	} 

	else if(empty($col_hex) || !in_array($col_hex, array_keys($hs['color_types']))) {
		return false;
	}

	$data = array();
	$db   = $db->where('prod_id',$prod_id);
	$db   = $db->where('col_hex',$col_hex);
	$db   = $db->where('var_type','color_size');
	$db   = $db->where('activity_status','active');
	$db   = $db->groupBy('col_hex');
	$csv  = $db->getOne(T_PROD_VARS);

	if (hs_queryset($csv,'object')) {
		$db                 = $db->where('prod_id',$prod_id);
		$db                 = $db->where('col_hex',$col_hex);
		$db                 = $db->where('var_type','color_size');
		$db                 = $db->where('activity_status','active');
		$variations         = $db->get(T_PROD_VARS,20);

		foreach ($variations as $var) {
			$var->col_img   = hs_get_media($var->col_img);
			$var->col_thumb = hs_get_media($var->col_thumb);
		}

		$data['var_id']     = $csv->id; 
		$data['prod_id']    = $csv->prod_id; 
		$data['color_hex']  = $csv->col_hex; 
		$data['color_name'] = $csv->col_name; 
		$data['color_img']  = hs_get_media($csv->col_img);
		$data['quantity']   = intval($csv->quantity);
		$data['color_size'] = $csv->size;
		$data['variations'] = (not_empty($variations)) ? $variations : array();
	}

	return $data;
}

function hs_preview_prod_vars($prod_id = null) {
	global $db,$hs;
	if (not_num($prod_id)) {
		return array();
	}

	$data    = array();
	$db      = $db->where('prod_id',$prod_id);
	$db      = $db->where('activity_status','active');
	$pd_vars = $db->get(T_PROD_VARS);

	if (hs_queryset($pd_vars)) {
		foreach ($pd_vars as $var_item) {
			$var_item->sale_price = hs_price($var_item->sale_price);
			$var_item->reg_price  = hs_price($var_item->reg_price);

			if (in_array($var_item->var_type, array('color','color_size')) ) {
				$var_item->col_img = hs_get_media($var_item->col_img);
			}
		}

		$data = hs_o2array($pd_vars);
	}

	return $data;
}

function hs_clear_orphan_vars($prod_id = null) {
	global $db,$hs;
	if (not_num($prod_id)) {
		return false;
	}

	$db          = $db->where('prod_id',$prod_id);
	$db          = $db->where('activity_status','orphan');
	$orphan_vars = $db->get(T_PROD_VARS);

	if (hs_queryset($orphan_vars)) {
		$db = $db->where('prod_id',$prod_id);
		$db = $db->where('activity_status','orphan');
		$rm = $db->delete(T_PROD_VARS);

		foreach ($orphan_vars as $orphan_vchild) {
			if (in_array($orphan_vchild->var_type, array('color','color_size'))) {
				hs_delete_image($orphan_vchild->col_img);
				hs_delete_image($orphan_vchild->col_thumb);
			}
		}
	}

	return true;
}

function hs_purge_orphan_prods($user_id = null) {
	global $db,$hs,$me;
	if (empty($user_id) || is_numeric($user_id) != true) {
		if (empty($hs['is_logged'])) {
			return false;
		} 

		else{
			$user_id = $me['id'];
		}
	} 

	$db    = $db->where('user_id',$user_id);
	$db    = $db->where('activity_status','orphan');
	$prods = $db->get(T_PRODUCTS);
	$res   = false;

	if (hs_queryset($prods)) {
		$db    = $db->where('user_id',$user_id);
		$db    = $db->where('activity_status','orphan');
		$res   = $db->delete(T_PRODUCTS);
		foreach ($prods as $prod_item) {
			$media = $db->where('prod_id',$prod_item->id)->get(T_PROD_MEDIA);
			if (hs_queryset($media)) {	
				foreach ($media as $file) {
					hs_delete_image($file->src);
					hs_delete_image($file->thumb);
				}

				$db = $db->where('prod_id',$prod_item->id);
				$rm = $db->delete(T_PROD_MEDIA);
			}

			$vars = $db->where('prod_id',$prod_item->id)->get(T_PROD_VARS);
			if (hs_queryset($vars)) {
				
				foreach ($vars as $var_item) {
					if (in_array($var_item->var_type, array('color','color_size'))) {
						hs_delete_image($var_item->col_img);
						hs_delete_image($var_item->col_thumb);
					}
				}

				$db = $db->where('prod_id',$prod_item->id);
				$rm = $db->delete(T_PROD_VARS);
			}
		}
	}

	return $res;
}

function hs_get_my_products($args = false) {
	global $hs,$me,$db;
	if (empty($hs['is_logged'])) {
		return false;
	}

	$args                 =  (is_array($args)) ? $args : array();
	$options              =  array(
        "offset"          => false,
        "limit"           => 10,
        "offset_to"       => false,
        "order"           => 'DESC',
        "keyword"         => false,
        "prod_status"     => false,
        "var_type"        => false,
        "sku"             => false,
        "payment_method"  => false,
        "approval_status" => false,
        "category"        => false,
    );

    $args                 =  array_merge($options, $args);
    $offset               =  $args['offset'];
    $limit                =  $args['limit'];
    $order                =  $args['order'];
    $keyword              =  $args['keyword'];
    $prod_status          =  $args['prod_status'];
    $var_type             =  $args['var_type'];
    $sku                  =  $args['sku'];
    $payment_method       =  $args['payment_method'];
    $approval_status      =  $args['approval_status'];
    $category             =  $args['category'];
    $offset_to            =  $args['offset_to'];
    $data                 =  array();
	$t_prods              =  T_PRODUCTS;
	$t_pvars              =  T_PROD_VARS;
	$sql                  =  hs_sqltepmlate('products/hex_get_my_products',array(
		'user_id'         => $me['id'],
		'offset'          => $offset,
		'offset_to'       => $offset_to,
		't_pvars'         => $t_pvars,
		't_prods'         => $t_prods,
		'limit'           => $limit,
		'order'           => $order,
		'keyword'         => $keyword,
		'prod_status'     => $prod_status,
		'var_type'        => $var_type,
		'sku'             => $sku,
		'payment_method'  => $payment_method,
		'approval_status' => $approval_status,
		'category'        => $category,
	));

	$data  = array();
	$prods = $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_id           = $prod_item->id;				
			$crop_length       = rand((32 / 2), 32);	
			$prod_item->name   = hs_croptxt($prod_item->name,$crop_length,'...');
			$prod_item->url    = hs_link(hs_str_form("product/{0}",array($prod_id)));
			$prod_item->edit   = hs_link(hs_str_form("merchant_panel/edit_product/{0}",array($prod_id)));
			$prod_item->thumb  = hs_get_media($prod_item->thumb);
			$prod_item->poster = hs_get_media($prod_item->poster);
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_get_draft_products($args = false) {
	global $hs,$me,$db;
	if (empty($hs['is_logged'])) {
		return false;
	}

	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
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
	$t_pvars        =  T_PROD_VARS;
	$sql            =  hs_sqltepmlate('products/hex_get_draft_products',array(
		'user_id'   => $me['id'],
		'offset'    => $offset,
		't_pvars'   => $t_pvars,
		't_prods'   => $t_prods,
		'limit'     => $limit,
		'offset_to' => $offset_to,
		'order'     => $order,
	));

	$data  = array();
	$prods = $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_id           = $prod_item->id;				
			$crop_length       = rand((32 / 2), 32);	
			$prod_item->name   = hs_croptxt($prod_item->name,$crop_length,'...');
			$prod_item->url    = hs_link(hs_str_form("product/{0}",array($prod_id)));
			$prod_item->edit   = hs_link(hs_str_form("merchant_panel/edit_product/{0}",array($prod_id)));
			$prod_item->thumb  = hs_get_media($prod_item->thumb);
			$prod_item->poster = hs_get_media($prod_item->poster);
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_basket_upsert($upsert_data = array()) {
	global $hs,$me,$db;
	if (empty($hs['is_logged'])) {
		return false;
	} 

	else if(is_array($upsert_data) != true) {
		return false;
	}

	$user_id = $me['id'];
	$db      = $db->where('prod_id',$upsert_data['prod_id']);
	$db      = $db->where('user_id',$user_id);
	$data    = null;
	if (is_number($upsert_data['var_id'])) {
		$db  = $db->where('var_id',$upsert_data['var_id']);
	}

	$bt_item = $db->getOne(T_BASKET);
	if (hs_queryset($bt_item,'object')) {
		$data = 'update';
		if ($upsert_data['quantity'] != $bt_item->quantity) {
			$db->where('id',$bt_item->id)->update(T_BASKET,array(
				'quantity' => $upsert_data['quantity']
			));
		}
	} 
	else {
		$data = 'insert';
		$db->insert(T_BASKET,$upsert_data);
	}

	return $data;
}

function hs_basket_items_total($user_id = false) {
	global $db;
	if (is_number($user_id) != true) {
		return false;
	}

	$t_basket      =  T_BASKET;
	$t_prods       =  T_PRODUCTS;
	$sql           =  hs_sqltepmlate('products/hex_get_basket_total_products',array(
		'user_id'  => $user_id,
		't_basket' => $t_basket,
		't_prods'  => $t_prods,
	));

	$data           = array();
	$db->returnType = 'Array';
	$query          = $db->rawQueryOne($sql);
	$total          = false;

	if (hs_queryset($query)) {
		$total = intval($query['total_items']);
	}

	return ((is_number($total) == true) ? $total : "");
}

function hs_get_basket_products($offset = false) {
	global $hs,$me,$db;
	if (empty($hs['is_logged'])) {
		return false;
	}

	$user_id       =  $me['id'];
	$t_basket      =  T_BASKET;
	$t_prods       =  T_PRODUCTS;
	$t_pvars       =  T_PROD_VARS;
	$sql           =  hs_sqltepmlate('products/hex_get_basket_products',array(
		'user_id'  => $user_id,
		't_basket' => $t_basket,
		't_pvars'  => $t_pvars,
		't_prods'  => $t_prods,
	));
	$data          = array();
	$prods         = $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_id          = $prod_item->prod_id;
			$prod_item->thumb = hs_get_media($prod_item->thumb);
			$prod_item->isvar = ((is_number($prod_item->var_id)) ? true : false);
			$prod_item->url   = hs_link(sprintf("product/%s",$prod_id));
			$prod_item->name  = hs_croptxt($prod_item->prod_name,70,"...");
			$prod_item->disc  = "0 %";
			$prod_item->total = ($prod_item->sale_price * $prod_item->qt);
			$prod_item->disc  = hs_price_discount($prod_item->reg_price,$prod_item->sale_price);

			if ($prod_item->shipping_cost == 'paid') {
				$prod_item->total += floatval($prod_item->shipping_fee);
			}
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_get_products($args = false) {
	global $hs,$me,$db;
	if (empty($hs['is_logged'])) {
		return false;
	}

	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
        "offset"    => null,
        "limit"     => 10,
        "user_id"   => null,
        "exclude"   => null,
        "catg_id"   => null,
        "ob_rating" => null,
        "ob_sales"  => null,
        "crop_name" => null,
        "crop_leng" => null,
        "crop_end"  => null,
        "approved"  => null,
        "not_appr"  => null,
        "nf_dgt"    => 2,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $user_id        =  $args['user_id'];
    $catg_id        =  $args['catg_id'];
    $ob_rating      =  $args['ob_rating'];
    $ob_sales       =  $args['ob_sales'];
    $crop_name      =  $args['crop_name'];
    $crop_leng      =  $args['crop_leng'];
    $crop_end       =  $args['crop_end'];
    $approved       =  $args['approved'];
    $not_appr       =  $args['not_appr'];
    $num_format_dgt =  $args['nf_dgt'];
    $exclude        =  ((is_array( $args['exclude']) && hs_all($args['exclude'],'numeric')) ? implode(',', $args['exclude']) : null);
    $data           =  array();
	$t_prods        =  T_PRODUCTS;
	$t_pvars        =  T_PROD_VARS;
	$sql            =  hs_sqltepmlate('products/hex_get_products',array(
		'user_id'   => $user_id,
		'exclude'   => $exclude,
		'offset'    => $offset,
		'catg_id'   => $catg_id,
		't_pvars'   => $t_pvars,
		't_prods'   => $t_prods,
		'ob_rating' => $ob_rating,
		'ob_sales'  => $ob_sales,
		'limit'     => $limit,
		'approved'  => $approved,
		'not_appr'  => $not_appr,
	));
	$data           =  array();
	$prods          =  $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_id             = $prod_item->id;
			$prod_item->url      = hs_link(hs_str_form("product/{0}",array($prod_id)));	
			$prod_item->poster   = hs_get_media($prod_item->poster);
			$prod_item->stars    = hs_rating_stars($prod_item->rating);
			$prod_item->fr_price = hs_money($prod_item->reg_price,$num_format_dgt);
			$prod_item->fs_price = hs_money($prod_item->sale_price,$num_format_dgt);
			$prod_item->discount = hs_price_discount($prod_item->reg_price,$prod_item->sale_price);

			if (not_empty($crop_name) && is_number($crop_leng)) {
				$crop_end        = ((is_string($crop_end)) ? $crop_end : '...');
				$crop_length     = rand(($crop_leng / 2), $crop_leng);
				$prod_item->name = hs_croptxt($prod_item->name,$crop_length,$crop_end);
			}
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_delete_basket_items($user_id = false, $item_ids = array()) {
	global $db,$hs,$me;
	if (not_num($user_id)) {
		if (empty($hs['is_logged'])) {
			return false;
		} 
		else{
			$user_id = $me['id'];
		}
	} 

	else if(empty($item_ids) || is_array($item_ids) != true) {
		return true;
	}

	$db       = $db->where('id',$item_ids,"IN");
	$db       = $db->where('user_id',$user_id);
	$rm_items = $db->delete(T_BASKET);

	return (not_empty($rm_items)) ? true : false;
}

function hs_get_order_summary($user_id = false, $items = array()) {
	global $db,$hs,$me;
	if (not_num($user_id)) {
		if (empty($hs['is_logged'])) {
			return false;
		} 
		else{
			$user_id = $me['id'];
		}
	}

	else if(empty($items) || is_array($items) != true) {
		return false;
	}

	else if(hs_all($items,'numeric') != true) {
		return false;
	}

	$t_prods         =  T_PRODUCTS;
	$t_pvars         =  T_PROD_VARS;
	$t_basket        =  T_BASKET;
	$bitems          =  implode(',', $items);
	$sql             =  hs_sqltepmlate('orders/hex_get_order_summary',array(
		'user_id'    => $user_id,
		't_pvars'    => $t_pvars,
		't_prods'    => $t_prods,
		't_basket'   => $t_basket,
		'bitems'     => $bitems,
	));
	$queryset        =  $db->rawQuery($sql);
	$queryset        =  (hs_queryset($queryset)) ? hs_o2array($queryset) : null;
	$sale_price      =  0;
	$reg_price       =  0;
	$prod_cost       =  0;
	$ship_cost       =  0;
	$to_pay          =  0;
	$data            =  array(
		'to_pay'     => '0.00',
		'ship_cost'  => '0',
		'discount'   => '0',
		'prod_cost'  => '0',
	);

	if ($queryset) {
		foreach ($queryset as $bskt_item) {
			$reg_price  += ($bskt_item['reg_price'] * $bskt_item['quantity']);
			$sale_price += ($bskt_item['sale_price'] * $bskt_item['quantity']);

			if ($bskt_item['shipping_cost'] == 'paid') {
				$ship_cost  += ($bskt_item['shipping_fee']);
				$sale_price += ($bskt_item['shipping_fee']);
				$reg_price  += ($bskt_item['shipping_fee']);
			}
		}

		$ship_cost         = ((not_empty($ship_cost)) ? hs_money($ship_cost) : hs_translate("Free"));
		$data['to_pay']    = hs_money($sale_price);
		$data['ship_cost'] = $ship_cost;
		$data['prod_cost'] = hs_money($reg_price);
		$data['discount']  = sprintf("%d%%",hs_price_discount($reg_price,$sale_price));
	}

	return $data;
}

function hs_get_order_items_data($user_id = false) {
	global $db,$hs,$me;
	if (not_num($user_id)) {
		if (empty($hs['is_logged'])) {
			return false;
		} 
		else{
			$user_id = $me['id'];
		}
	}

	$t_prods       =  T_PRODUCTS;
	$t_pvars       =  T_PROD_VARS;
	$t_basket      =  T_BASKET;
	$t_users       =  T_USERS;
	$sql           =  hs_sqltepmlate('orders/hex_get_order_items_data',array(
		'user_id'  => $user_id,
		't_pvars'  => $t_pvars,
		't_users'  => $t_users,
		't_prods'  => $t_prods,
		't_basket' => $t_basket,
	));

	$queryset  = $db->rawQuery($sql);
	$queryset  = (hs_queryset($queryset)) ? hs_o2array($queryset) : null;
	$data      = array();
	return $queryset;
}

function hs_where_exists($table = null,$data = array()) {
	global $db,$hs;

	if (empty($table) || empty($data) || is_array($data) != true) {
		return false;
	}

	foreach ($data as $col => $val) {
		$db->where($col,$val);
	}

	$count = $db->getValue($table,"COUNT(*)");

	return ($count > 0) ? true : false;
}

function hs_product_sku_exists($prod_id = null,$sku = null) {
	global $db,$hs,$me;

	if (empty($hs['is_logged']) || not_num($prod_id) || empty($sku)) {
		return false;
	}

	$db    = $db->where('id',$prod_id,'<>');
	$db    = $db->where('user_id',$me['id']);
	$db    = $db->where('activity_status','active');
	$db    = $db->where('sku',hs_secure($sku));
	$count = $db->getValue(T_PRODUCTS,"COUNT(*)");

	return ($count > 0) ? true : false;
}

function hs_product_var_sku_exists($prod_id = null,$var_id = null,$sku = null) {
	global $db,$hs;

	if (not_num($prod_id) || not_num($var_id) || empty($sku)) {
		return false;
	}

	$db    = $db->where('id',$var_id,'<>');
	$db    = $db->where('prod_id',$prod_id);
	$db    = $db->where('activity_status','active');
	$db    = $db->where('sku',hs_secure($sku));
	$count = $db->getValue(T_PROD_VARS,"COUNT(*)");

	return ($count > 0) ? true : false;
}

function hs_get_catalog_items($args = array()) {
	global $db,$hs;
	$args           = (is_array($args)) ? $args : array();
	$options        = array(
        "catg_id"   => null,
        "offset"    => null,
        "brand"     => null,
        "keyword"   => null,
        "condition" => null,
        "ship_cost" => null,
        "ship_time" => null,
        "ship_cntr" => null,
        "sortby"    => null,
        "min_price" => null,
        "sell_cntr" => null,
        "max_price" => null,
        "limit"     => 10,
    );

    $args           =  array_merge($options, $args);
    $brand          =  $args['brand'];
    $ship_cost      =  $args['ship_cost'];
    $min_price      =  $args['min_price'];
    $keyword        =  $args['keyword'];
    $max_price      =  $args['max_price'];
    $condition      =  $args['condition'];
    $ship_time      =  $args['ship_time'];
    $catg_id        =  $args['catg_id'];
    $ship_cntr      =  $args['ship_cntr'];
    $offset         =  $args['offset'];
    $sortby         =  $args['sortby'];
    $limit          =  $args['limit'];
    $sell_cntr      =  $args['sell_cntr'];
    $data           =  array();
    $sql            =  hs_sqltepmlate('products/hex_get_catalog_items',array(
        'brand'     => $brand,
        'keyword'   => $keyword,
        'condition' => $condition,
        'ship_time' => $ship_time,
        'ship_cntr' => $ship_cntr,
        'ship_cost' => $ship_cost,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'catg_id'   => $catg_id,
        'offset'    => $offset,
        'sortby'    => $sortby,
        'sell_cntr' => $sell_cntr,
        'limit'     => $limit,
        't_prods'   => T_PRODUCTS,
        't_users'   => T_USERS,
    ));

    $items_data = $db->rawQuery($sql);

    if (hs_queryset($items_data) == true) {
        foreach ($items_data as $row_item) {
            $row_item->poster   = hs_get_media($row_item->poster);
            $row_item->prod_url = hs_link(sprintf("product/%s",$row_item->id));
            $row_item->fr_price = hs_money($row_item->reg_price,0);
			$row_item->fs_price = hs_money($row_item->sale_price,0);
			$row_item->discount = hs_price_discount($row_item->reg_price,$row_item->sale_price);
        }

        $data = hs_o2array($items_data); 
    }

    return $data;
}

function hs_prod_total_reviews($prod_id = null,$val = 'all') {
	global $db,$hs;
	if (not_num($prod_id)) {
		return 0;
	}

	$pid = intval($prod_id);
	$db  = $db->where('prod_id',$pid);
	$db  = $db->where('activity_status','active');

	if (in_array($val, array(1,2,3,4,5))) {
		$db = $db->where('valuation',$val);
	}

	$tot = $db->getValue(T_PROD_RATINGS,'COUNT(*)');

	return (is_number($tot)) ? $tot : 0;
}

function hs_get_prod_reviews($args = array()) {
	global $db,$hs,$me;
	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
        "prod_id"   => null,
        "valuation" => null,
        "limit"     => 10,
        "offset"    => null,
        "by_ids"    => null,
        "sortby"    => null,
    );

    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $prod_id        =  $args['prod_id'];
    $valuation      =  $args['valuation'];
    $limit          =  $args['limit'];
    $sortby         =  $args['sortby'];
    $by_ids         =  ((is_array($args['by_ids']) && hs_all($args['by_ids'],'numeric')) ? implode(',', $args['by_ids']) : null);

    $data           =  array();
    $sql            =  hs_sqltepmlate('reviews/hex_get_prod_reviews',array(
        'offset'    => $offset,
        'limit'     => $limit,
        'prod_id'   => $prod_id,
        'valuation' => $valuation,
        'by_ids'    => $by_ids,
        'sortby'    => $sortby,
        't_reviews' => T_PROD_RATINGS,
        't_users'   => T_USERS,
    ));

    $reviews_list = $db->rawQuery($sql);

    if (hs_queryset($reviews_list) == true) {
        foreach ($reviews_list as $row_item) {
            $row_item->avatar = hs_get_media($row_item->avatar);
            $row_item->ulink  = hs_link($row_item->username);
            $row_item->time   = date('d F, Y',$row_item->time);
            $row_item->media  = hs_get_review_media($row_item->id);
            $row_item->owner  = ($row_item->user_id == $me['id']);
            $row_item->stars  = hs_rating_stars($row_item->valuation);
        }

        $data = hs_o2array($reviews_list); 
    }

    return $data;
}

function hs_get_review_media($review_id = null) {
	global $hs,$db;
	if (not_num($review_id)) {
		return array();
	}

	$data  = array();
	$reid  = intval($review_id);
	$query = $db->where('review_id',$reid)->get(T_PROD_RATING_MEDIA);
	if (hs_queryset($query)) {
		foreach ($query as $rev_media) {
			$rev_media->file_path = hs_get_media($rev_media->file_path);
		}

		$data = $query;
	}

	return $data;
}

function hs_delete_prod_review($review_id = null) {
	global $hs,$db;
	if (not_num($review_id)) {
		return 0;
	}
	
	$db  = $db->where('id',$review_id);
	$rev = $db->getOne(T_PROD_RATINGS);
	if (hs_queryset($rev,'object')) {
		$rev_media  = $db->where('review_id',$review_id)->get(T_PROD_RATING_MEDIA);
		$delete_rev = $db->where('id',$review_id)->delete(T_PROD_RATINGS);

		if (hs_queryset($rev_media)) {
			foreach($rev_media as $med_row) {
				hs_delete_image($med_row->file_path);
			}

			$db->where('review_id',$review_id)->delete(T_PROD_RATING_MEDIA);
		}

		try {
			#Update prod review 
			$prod_rating   = hs_get_prod_rating($rev->prod_id);
			$prod_rating   = number_format($prod_rating, 1, '.', ' ');
			$update_rating = $db->where('id',$rev->prod_id)->update(T_PRODUCTS,array('rating' => $prod_rating));
		} catch (Exception $e) { /*pass*/ }
	}

	return true;
}

function hs_get_prod_rating($prod_id = false) {
    global $db, $hs;
    if (not_num($prod_id)) {
        return 0;
    }
    $db     = $db->where('prod_id',$prod_id);
    $db     = $db->where('activity_status','active');
    $query  = $db->get(T_PROD_RATINGS);
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
        return 0;
    }
}

function hs_search_products($args = false) {
	global $db,$hs;
	if (empty($hs['is_logged'])) {
		return false;
	}

	$args         = (is_array($args)) ? $args : array();
	$options      = array(
        "limit"   => 10,
        "keyword" => null,
    );
    $args         = array_merge($options, $args);
    $limit        = $args['limit'];
    $keyword      = $args['keyword'];
	$t_prods      = T_PRODUCTS;
	$sql          = hs_sqltepmlate('products/hex_search_products',array(
		't_prods' => $t_prods,
		'limit'   => $limit,
		'keyword' => $keyword,
	));
	$data         = array();
	$prods        = $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_id           = $prod_item->id;
			$prod_item->url    = hs_link(hs_str_form("product/{0}",array($prod_id)));	
			$crop_length       = rand((40 / 2), 40);
			$prod_item->query  = hs_croptxt($prod_item->query,$crop_length,'...');
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_register_product_vendeal($prod_id = 0, $amount = 0, $deal = 'purchase') {
	global $db, $hs;

	if (not_num($prod_id)) {
		return false;
	}

	$prod_id   = intval($prod_id);
	$db        = $db->where('id', $prod_id);
	$db        = $db->where('activity_status', 'active');
	$prod_data = $db->getOne(T_PRODUCTS);
	
	if (hs_queryset($prod_data,'object')) {
		$prod_data   = hs_o2array($prod_data);
		$prod_prof   = floatval($prod_data['profit']);
		$prod_sales  = intval($prod_data['sold']);
		$db          = $db->where('id',$prod_id);
		if ($deal == 'purchase') {
			$update      =  $db->update(T_PRODUCTS,array(
				'profit' => ($prod_prof += $amount),
				'sold'   => ($prod_sales += 1)
			));
		}
		else if($deal == 'refund') {
			$update      =  $db->update(T_PRODUCTS,array(
				'profit' => ($prod_prof -= $amount),
				'sold'   => ($prod_sales -= 1)
			));
		}
		else {
			return false;
		}
	}

	return true;
}

function hs_update_product_stock_status($prod_id = 0, $var_id = 0,$qty = 0) {
	global $db, $hs;

	if (not_num($prod_id) && not_num($var_id)) {
		return false;
	}

	elseif (not_num($qty)) {
		return false;
	}

	else {
		if (is_number($var_id)) {
			$prod_id   = intval($prod_id);
			$var_id    = intval($var_id);
			$db        = $db->where('id', $var_id);
			$db        = $db->where('prod_id', $prod_id);
			$db        = $db->where('activity_status', 'active');
			$var_data  = $db->getOne(T_PROD_VARS);

			if (hs_queryset($var_data,'object')) {
				$db            = $db->where('id',$var_id);
				$update        = $db->update(T_PROD_VARS,array(
					'quantity' => ($var_data->quantity -= $qty),
				));
			}
		}

		else {
			$prod_id   = intval($prod_id);
			$db        = $db->where('id', $prod_id);
			$db        = $db->where('activity_status', 'active');
			$prod_data = $db->getOne(T_PRODUCTS);

			if (hs_queryset($prod_data,'object')) {
				$db            =  $db->where('id',$prod_id);
				$update        =  $db->update(T_PRODUCTS,array(
					'quantity' => ($prod_data->quantity -= $qty),
				));
			}
		}
	}

	return true;
}

function hs_is_product_presentable($id = null) {
	global $db;

	if (not_num($id)) {
		return false;
	}

	$id = intval($id);
	$db = $db->where('id',$id);
	$db = $db->where('activity_status','active');
	$db = $db->where('editing_stage','saved');
	$db = $db->where('status','active');
	$rs = $db->getValue(T_PRODUCTS,'COUNT(*)');

	return (is_number($rs)) ? true : false;
}

function hs_get_preview_products($args = false) {
	global $db;
	$args           =  (is_array($args)) ? $args : array();
	$options        =  array(
        "offset"    => null,
        "limit"     => 15
    );
    $args           =  array_merge($options, $args);
    $offset         =  $args['offset'];
    $limit          =  $args['limit'];
    $data           =  array();
	$t_prods        =  T_PRODUCTS;
	$sql            =  hs_sqltepmlate('products/hex_get_preview_products',array(
		'offset'    => $offset,
		't_prods'   => $t_prods,
		'limit'     => $limit,
	));

	$data           = array();
	$prods          = $db->rawQuery($sql);

	if (hs_queryset($prods)) {
		foreach ($prods as $prod_item) {
			$prod_item->poster   = hs_get_media($prod_item->poster);
			$prod_item->fr_price = hs_price($prod_item->reg_price,0);
			$prod_item->fs_price = hs_price($prod_item->sale_price,0);
			$prod_item->discount = hs_price_discount($prod_item->reg_price,$prod_item->sale_price);
		}

		$data = hs_o2array($prods);
	}

	return $data;
}

function hs_toggle_prod_status($prod_id = false,$status = false,$prev_status) {
	global $db,$hs;

	if (not_num($prod_id)) {
		return false;
	}

	else {
		if ($status == 'on') {
			$db               =  $db->where('id',$prod_id);
			$up               =  $db->update(T_PRODUCTS,array(
				'status'      => 'active',
				'last_status' => 'active',
			));
		}
		else {
			$db               =  $db->where('id',$prod_id);
			$up               =  $db->update(T_PRODUCTS,array(
				'status'      => 'inactive',
				'last_status' => 'inactive',
			));

			#Delete this product from user baskets
			$db = $db->where('prod_id',$prod_id);
			$rm = $db->delete(T_BASKET);
		}
	}
}