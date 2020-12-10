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

if ($action == 'lp' && not_empty($_GET['page'])) {
	$template_name = hs_secure($_GET['page']);

	if ($template_name == 'var_addon_form' && not_empty($_GET['type'])) {
		if(hs_session('edit_product_data')) {
			$var_addon_type    = hs_secure($_GET['type']);
			$edit_product_data = hs_session('edit_product_data');
			$prod_id           = intval($edit_product_data['prod_id']);

			if (hs_is_prodowner($me['id'],$prod_id)) {
				$hs['prod_id']   = $prod_id;
				$product_item    = hs_get_edited_product($prod_id);
				$hs['pd_item']   = $product_item;
				$prodvars_total  = hs_get_prodvars_total($prod_id);
				$data['total']   = $prodvars_total;

				if ($var_addon_type == 'color' && not_empty($product_item)) {
					if ($prodvars_total >= 15) {
						$data['status']  =  400;
						$data['message'] =  hs_translate('You cannot add more than {%max%} color options to this type of product.',array(
							'max'        => 15
						));
					}
					else {
						if ($product_item['variation_type'] == 'color') {
							#Clear DB from orphan prod var items
							hs_clear_orphan_vars($prod_id);

							$var_id               =  $db->insert(T_PROD_VARS,array(
								'prod_id'         => $prod_id,
								'var_type'        => 'color',
								'activity_status' => 'orphan',
								'time'            => time()
							));

							if (is_numeric($var_id)) {

								$edit_product_data['var_id'] = $var_id;
								hs_session('edit_product_data',$edit_product_data);

								$data['status']  =  200;
								$data['html']    =  hs_loadpage('upsert_product/modals/add_col_variation',array(
									'prod_id'    => $hs['prod_id'],
									'var_id'     => $var_id,
									'sale_price' => $product_item['sale_price'],
									'reg_price'  => $product_item['reg_price'],
									'quantity'   => $product_item['quantity'],
		 						));
							}
						}
					}
				}

				else if($var_addon_type == 'size' && not_empty($product_item)) {
					if ($prodvars_total >= 15) {
						$data['status']  =  400;
						$data['message'] =  hs_translate('You cannot add more than {%max%} size options to this type of product.',array(
							'max'        => 15
						));
					}
					else {
						if ($product_item['variation_type'] == 'size') {
							if ($product_item['sizing_type'] == 'none') {
								$data['status']  = 400;
								$data['message'] = 'Request data error: Sizing type is not defined!';
							} 
							else {
								#Clear DB from orphan prod var items
								hs_clear_orphan_vars($prod_id);

								$var_id               =  $db->insert(T_PROD_VARS,array(
									'prod_id'         => $prod_id,
									'var_type'        => 'size',
									'activity_status' => 'orphan',
									'time'            => time()
								));

								$edit_product_data['var_id'] = $var_id;
								hs_session('edit_product_data',$edit_product_data);

								$hs['ps_type']   =  $hs['sizing_types'][$hs['pd_item']['sizing_type']];				
								$data['status']  =  200;
								$data['html']    =  hs_loadpage('upsert_product/modals/add_size_variation',array(
									'prod_id'    => $hs['pd_item']['id'],
									'var_id'     => $var_id,	
									'sale_price' => $product_item['sale_price'],
									'reg_price'  => $product_item['reg_price'],
									'quantity'   => $product_item['quantity'],
								));
							}
						}
					}
				} 

				else if($var_addon_type == 'color_size' && not_empty($product_item)) {
					if ($prodvars_total >= 150) {
						$data['status']  =  400;
						$data['message'] =  hs_translate('You cannot add more than {%max%} (Color & Size) options to this type of product.',array(
							'max'        => 150
						));
					}
					else {
						if ($product_item['sizing_type'] == 'none') {
							$data['status']  = 400;
							$data['message'] = hs_translate('Sizing type is not defined!');
						} 
						else {
							if ($product_item['variation_type'] == 'color_size') {
								#Clear DB from orphan prod var items
								hs_clear_orphan_vars($prod_id);

								
								/*$unset *******/ unset($edit_product_data['col_img']);
							    /*$unset *******/ unset($edit_product_data['col_hex']);
							    /*$unset *******/ unset($edit_product_data['var_ids']);
							    /*$session_set */ hs_session('edit_product_data',$edit_product_data);
								
								$hs['ps_type']  =  $hs['sizing_types'][$hs['pd_item']['sizing_type']];				
								$data['status'] =  200;
								$data['html']   =  hs_loadpage('upsert_product/modals/add_both_variation',array(
									'prod_id'   => $prod_id,
								));
							}
						}
					}	
				} 

				else if($var_addon_type == 'color_size_inner' && not_empty($product_item)) {
					if ($prodvars_total >= 150) {
						$data['status']  =  400;
						$data['message'] =  hs_translate('You cannot add more than {%max%} color options to this type of product.',array(
							'max'        => 150
						));
					}
					else {
						if ($product_item['sizing_type'] == 'none') {
							$data['status']  = 400;
							$data['message'] = hs_translate('Sizing type is not defined!');
						}

						else {
							if (not_empty($_GET['size_unit']) && $product_item['variation_type'] == 'color_size') {

								$hs['ps_type'] = $hs['sizing_types'][$hs['pd_item']['sizing_type']];
								$valid_size    = true;
								$size_unit     = hs_secure($_GET['size_unit']);

								if (not_empty($hs['ps_type']['expandable'])) {
									$regex = $hs['ps_type']['regex'];
									if (preg_match($regex, $size_unit) != true) {
										$valid_size = false;
									}
								} 

								else if (in_array($size_unit, $hs['ps_type']['size_units']) != true) {
									$valid_size = false;
								}

								if ($valid_size) {
									$var_id               =  $db->insert(T_PROD_VARS,array(
										'prod_id'         => $prod_id,
										'var_type'        => 'color_size',
										'activity_status' => 'orphan',
										'size'            => $size_unit,
										'time'            => time()
									));

									$data['status']  = 200;
									$data['var_id']  = $var_id;
									$var_color_img   = fetch_or_get($edit_product_data['col_img'],false);
									$var_color_hex   = fetch_or_get($edit_product_data['col_hex'],false);
									$hs['col_img']   = (($var_color_img) ? hs_get_media($var_color_img) : null);
									$hs['col_hex']   = (($var_color_hex) ? strval($var_color_hex) : null);

									$data['html']    =  hs_loadpage('upsert_product/modals/add_both_variation_inner',array(
										'var_id'     => $var_id,
										'size_unit'  => $size_unit,
										'sale_price' => $product_item['sale_price'],
										'reg_price'  => $product_item['reg_price'],
										'quantity'   => $product_item['quantity'],
									));

									if (isset($edit_product_data['var_ids']) != true) {
										$edit_product_data['var_ids'] = array();
									}

									array_push($edit_product_data['var_ids'], $var_id);
									hs_session('edit_product_data',$edit_product_data);
								}

								else {
									$data['status']  = 400;
									$data['message'] = hs_translate('The size you want to add is not valid!');
								}
							}
						}
					}	
				} 
			}
		}
	}

	else if($template_name == 'product_variations_list') {
		if(hs_session('edit_product_data')) {
			$edit_product_data = hs_session('edit_product_data');

			if (is_number($edit_product_data['prod_id'])) {
				$prod_id   = intval($edit_product_data['prod_id']);
				$db        = $db->where('id',$prod_id);
				$prod_item = $db->getOne(T_PRODUCTS);

				if (hs_queryset($prod_item,'object') != true || $prod_item->variation_type == 'single') {
					$data['status'] = 404;
					$data['error']  = 'Error: Invalid request data. Please try again later!';
				} 

				else {
					$product_vars  = hs_preview_prod_vars($prod_id);
					$prod_item     = hs_o2array($prod_item);

					if (not_empty($product_vars)) {
						$hs['prod_item'] = $prod_item;
						$hs['prod_vars'] = $product_vars;
						$data['html']    = hs_loadpage('upsert_product/modals/variations_list');
						$data['status']  = 200;	
					} 

					else {
						$data['status'] = 400;
						$data['error']  = 'Error: Invalid request data. The product has no vars yet!';
					}
				}
			}
		}
	}

	else if($template_name == 'edit_var_item') {
		$prod_id           = fetch_or_get($_GET['prod_id'],false);
		$var_id            = fetch_or_get($_GET['var_id'],false);
		$edit_product_data = hs_session('edit_product_data');

		if (not_num($prod_id) || hs_is_prodowner($me['id'], $prod_id) != true) {
			$data['status']  =  400;
			$data['message'] =  "Error: Invalid request data. Please check your details";
		}

		else if(not_num($var_id)) {
			$data['status']  =  400;
			$data['message'] =  "Error: Invalid request data. Please check your details";
		}

		else {
			$db->returnType = 'Array';
			$db             = $db->where('id',$var_id);
			$db             = $db->where('prod_id',$prod_id);
			$db             = $db->where('activity_status','active');
			$var_data       = $db->getOne(T_PROD_VARS);

			if (hs_queryset($var_data)) {
				$edit_product_data['var_id'] = $var_id;
				$data['status']              = 200;
				$hs['var_data']              = $var_data;
				$data['html']                = hs_loadpage('upsert_product/modals/edit_variation_data');

				hs_session('edit_product_data',$edit_product_data);
			}

			else {
				$data['status']  =  400;
				$data['message'] =  "Error: Invalid request data. The product option with such ID does not exists!";
			}
		}
	}
}

else if ($action == 'tdr') {
	$temp_data_type = fetch_or_get($_POST['type'],'none');	
	$temp_data_type = hs_secure($temp_data_type);
	$data['status'] = 400;

	if ($temp_data_type == 'session') {
		$data_key = fetch_or_get($_POST['key'],false);	
		$data_val = fetch_or_get($_POST['value'],false);

		if ($data_key == 'product_var_color' && not_empty($data_val)) {
			if (in_array($data_val, array_keys($hs['color_types']))) {
				$edit_product_data            = hs_session('edit_product_data');
				$edit_product_data['col_hex'] = $data_val;
				hs_session('edit_product_data',$edit_product_data);
				$data['status'] = 200;
			}
		}

		else if($data_key == 'product_var_color_image' && not_empty($_FILES['image'])) {
			if (not_empty($_FILES['image']['tmp_name'])) {
				$file_info    = array(
		            'file'    => $_FILES['image']['tmp_name'],
		            'size'    => $_FILES['image']['size'],
		            'name'    => $_FILES['image']['name'],
		            'type'    => $_FILES['image']['type'],
		            'allowed' => 'jpg,png,jpeg,gif,webp'
		        );

		        $file_upload = hs_upload($file_info);
		        
		        if (not_empty($file_upload['filename'])) {
		            $edit_product_data            = hs_session('edit_product_data');
					$edit_product_data['col_img'] = $file_upload['filename'];
					$data['status']               = 200;
					$data['url']                  = hs_get_media($file_upload['filename']);
					hs_session('edit_product_data',$edit_product_data);
		        }
			}
		}
	}
}

else if($action  == 'set_vartype' && hs_session('edit_product_data')) {
	$edit_product_data = hs_session('edit_product_data');
	$product_id        = intval($edit_product_data['prod_id']);

	if(hs_is_prodowner($me['id'],$product_id) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(empty($_POST['var_type']) || in_array($_POST['var_type'], array('single','color','size','color_size')) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$product_item  = hs_get_edited_product($product_id);
		$var_type      = hs_secure($_POST['var_type']);

		if (not_empty($product_item)) {
			$db         = $db->where('prod_id',$product_id);
			$db         = $db->where('activity_status','active');
			$vars_total = $db->getValue(T_PROD_VARS,'COUNT(*)');
			$vars_total = intval($vars_total);

			if (($var_type != $product_item['variation_type']) && $vars_total) {
				$message          = hs_translate('You can not change the variation type of your product, as you have already added ({%number%}) product variants of a different type. In order to change the variation type of this product, first you must to remove the previously added options',array('number' => $vars_total));
				$data['err_code'] = 3;
				$data['status']   = 300;
				$data['pvt']      = $product_item['variation_type'];
				$data['message']  = $message;
			} 
			else {
				$data['status'] = 200;
				if($var_type != $product_item['variation_type'])  {
					hs_setprod_val($product_id,array(
						'variation_type' => $var_type
					));
				}
				if(in_array($var_type, array('single','color')))  {
					if (not_empty($product_item['sizing_type'])) {
						hs_setprod_val($product_id,array(
							'sizing_type' => 'none'
						));
					}
				}
			}
		} 
		else {
			$data['err_code'] = 4;
			$data['status']   = 500;
			$data['message']  = "An error found while processing your request. Please try again later!";
		}
	}
}

else if($action  == 'set_sizing_type' && hs_session('edit_product_data')) {
	$edit_product_data = hs_session('edit_product_data');
	$product_id        = intval($edit_product_data['prod_id']);

	if(hs_is_prodowner($me['id'],$product_id) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(empty($_POST['type']) || !in_array($_POST['type'], array_keys($hs['sizing_types']))) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else {
		$product_item  = hs_get_edited_product($product_id);
		$sizing_type   = hs_secure($_POST['type']);

		if (!empty($product_item)) {		
			$db         = $db->where('prod_id',$product_id);
			$db         = $db->where('activity_status','active');
			$vars_total = $db->getValue(T_PROD_VARS,'COUNT(*)');
			$vars_total = intval($vars_total);

			if (($sizing_type != $product_item['sizing_type']) && $vars_total) {

			
				$data['err_code'] = 3;
				$data['status']   = 300;
				$data['pst']      = $product_item['sizing_type'];
				$data['message']  = hs_translate("You cannot change the variations sizing type of your product, as you have already added ({%vars_total%}) product variants of a different type. In order to change the type of product variation you first need to remove the previously added options",array('vars_total' => $vars_total));

			} 

			else if(in_array($product_item['variation_type'], array('size','color_size')) != true)  {
				$data['err_code'] = 4;
				$data['status']   = 400;
				$data['pst']      = $product_item['sizing_type'];
				$data['message']  = "An error found while processing your request. Please try again later!";
			} 

			else {
				if($sizing_type != $product_item['sizing_type'])  {
					$data['status'] = 200;
					hs_setprod_val($product_id,array(
						'sizing_type' => $sizing_type
					));
				}
			}
		} 

		else {
			$data['err_code'] = 4;
			$data['status']   = 500;
			$data['message']  = "An error found while processing your request. Please try again later!";
		}
	}
}

else if ($action == 'prod_upsert' && hs_session('edit_product_data')) {
	$data['err_code']    = 0;
	$data['status']      = 400;
	$edit_product_data   = hs_session('edit_product_data');

	if (not_num($edit_product_data['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details!";
	}

	else if(hs_is_prodowner($me['id'],$edit_product_data['prod_id']) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details!";
	}

	else {
		$product_id       = intval($edit_product_data['prod_id']);
		$db->returnType   = 'Array';
		$db               = $db->where('id',$product_id);
		$db               = $db->where('user_id',$me['id']);		
		$product_item     = $db->getOne(T_PRODUCTS);
		$db               = $db->where('prod_id',$product_id);
		$product_media    = $db->getValue(T_PROD_MEDIA,'COUNT(*)');
		$prod_data_fileds = array(
			1  => 'name',
			2  => 'condition',
			3  => 'category',
			4  => 'description',
			5  => 'reg_price',
			6  => 'sale_price',
			7  => 'quantity',
			8  => 'sku',
			9  => 'shipping_cost',
			10 => 'payment_method',
			11 => 'shipping_time',
			12 => 'origin',
			13 => 'brand',
			14 => 'model_number',
			15 => 'weight',
			16 => 'length',
			17 => 'width',
			18 => 'height',
			19 => 'poster',
			20 => 'media',
			21 => 'variation_type',
		);

		if (not_empty($product_item)) {
			foreach ($prod_data_fileds as $field) {
				if ($field == 'name') {
					$product_name      = fetch_or_get($_POST['name'],0);
					$product_name      = hs_secure($product_name,true);
					$prod_name_max_len = intval($hs['config']['prod_max_name']);
					$prod_name_min_len = intval($hs['config']['prod_min_name']);

					if (empty($product_name)) {
			            $data['message']  = hs_translate("You missed product name. Please enter product name");
			            $data['err_code'] = 3; break;
			        } 

			        else {
			        	if (len($product_name) < $prod_name_min_len) {
			        		$data['message']  = hs_translate("The product name you entered is too short. Please check your details");
			            	$data['err_code'] = 3; break;
			        	} 

			        	else if (len($product_name) > $prod_name_max_len) {
			        		$data['message']  = hs_translate("The product name you entered is too long. Please check your details");
			            	$data['err_code'] = 3; break;
			        	} 

			        	else if($product_name != $product_item['name']) {
			        		hs_setprod_val($product_id,array(
			        			'name' => $product_name
			        		));
			        	}
			        }
				}

				else if ($field == 'condition') {
					$product_condition = fetch_or_get($_POST['condition'],0);
					$product_condition = hs_secure($product_condition);
					$product_condition = strval($product_condition);

					if (not_num($product_condition)) {
			            $data['message']  = hs_translate("The product condition is required. Please check your details");
			            $data['err_code'] = 4; break;
			        } 

			        else {
			        	if (in_array($product_condition, array('1','2','3')) != true) {
			        		$data['message']  = hs_translate("The product condition you selected is not valid. Please check your details");
			            	$data['err_code'] = 4; break;
			        	} 

			        	else if($product_condition != $product_item['condition']) {
			        		hs_setprod_val($product_id,array(
			        			'condition' => $product_condition
			        		));	
			        	}
			        }
				}

				else if ($field == 'category') {
					$product_category = fetch_or_get($_POST['category'],0);
					$product_category = hs_secure($product_category);
					$product_category = strval($product_category);

					if (empty($product_category)) {
			            $data['message']  = hs_translate("The product category is required. Please check your details");
			            $data['err_code'] = 5; break;
			        } 

			        else {
			        	if (in_array($product_category, array_keys($hs['categories'])) != true) {
			        		$data['message']  = hs_translate("The product category you selected is not valid. Please check your details");
			            	$data['err_code'] = 5; break;
			        	} 

			        	else if($product_category != $product_item['category']) {
			        		hs_setprod_val($product_id,array(
			        			'category' => $product_category
			        		));
			        	}
			        }
				}

				else if ($field == 'description') {
					$product_description = fetch_or_get($_POST['description'],0);
					$product_description = trim($product_description);
					$prod_desc_max_len   = intval($hs['config']['prod_max_desc']);
					$prod_desc_min_len   = intval($hs['config']['prod_min_desc']);

					if (empty($product_description)) {
			            $data['message']  = hs_translate("The product description is field required. Please check your details");
			            $data['err_code'] = 6; break;
			        }

			        else {
			        	if (len($product_description) < $prod_desc_min_len) {
			        		$data['message']  = hs_translate("The product description you wrote is too short.");
			            	$data['err_code'] = 6; break;
			        	} 

			        	else if(len($product_description) > $prod_desc_max_len) {
			        		$data['message']  = hs_translate("The product description you wrote is too long.");
			            	$data['err_code'] = 6; break;
			        	} 

			        	else if(len($product_description) > 60000) {
			        		$data['message']  = hs_translate("The product description you wrote is too long.");
			            	$data['err_code'] = 6; break;
			        	} 

			        	else if($product_description != $product_item['description']) {
			        		hs_setprod_val($product_id,array(
			        			'description' => hs_secure($product_description,true)
			        		));	
			        	}
			        }
				}

				else if ($field == 'reg_price') {
					$product_reg_price = fetch_or_get($_POST['reg_price'],0);
					$product_reg_price = hs_secure($product_reg_price);
					$product_reg_price = floatval($product_reg_price);
					$min_sale_price    = $config['min_sale_price'];
					$max_sale_price    = $config['max_sale_price'];

					if (not_num($product_reg_price)) {
			            $data['message']  = hs_translate("The product regular price field is required.");
			            $data['err_code'] = 7; break;
			        } 
			        else {
			        	if ($product_reg_price < $min_sale_price) {
			        		$data['message']  = hs_translate("The product regular price you entered is too low.");
			            	$data['err_code'] = 7; break;
			        	} 

			        	else if($product_reg_price > $max_sale_price) {
			        		$data['message']  = hs_translate("The product regular price you entered is too high.");
			            	$data['err_code'] = 7; break;
			        	} 

			        	else if($product_reg_price != $product_item['reg_price']) {
			        		hs_setprod_val($product_id,array(
			        			'reg_price' => $product_reg_price
			        		));	
			        	}
			        }
				}

				else if ($field == 'sale_price') {
					$product_sale_price = fetch_or_get($_POST['sale_price'],0);
					$product_sale_price = hs_secure($product_sale_price);
					$product_sale_price = floatval($product_sale_price);

					$product_reg_price  = fetch_or_get($_POST['reg_price'],0);
					$product_reg_price  = hs_secure($product_reg_price);
					$product_reg_price  = floatval($product_reg_price);

					$min_sale_price     = $config['min_sale_price'];
					$max_sale_price     = $config['max_sale_price'];

					if (not_num($product_sale_price)) {
			            $data['message']  = hs_translate("The product sale price field is required!");
			            $data['err_code'] = 8; break;
			        } 
			        else {
			        	if ($product_sale_price < $min_sale_price) {
			        		$data['message']  = hs_translate("The product sale price you entered is too low.");
			            	$data['err_code'] = 8; break;
			        	} 

			        	else if($product_sale_price > $max_sale_price) {
			        		$data['message']  = hs_translate("The product sale price you entered is too high.");
			            	$data['err_code'] = 8; break;
			        	}  

			        	else if($product_sale_price == $product_reg_price) {
			        		$data['message']  = hs_translate("The regular price of the goods and the sale price cannot be the same!");
			            	$data['err_code'] = 8; break;
			        	} 

			        	else if($product_sale_price != $product_item['sale_price']) {
			        		hs_setprod_val($product_id,array(
			        			'sale_price' => $product_sale_price
			        		));	
			        	}
			        }
				}

				else if ($field == 'quantity') {
					$product_quantity = fetch_or_get($_POST['quantity'],0);
					$product_quantity = hs_secure($product_quantity);

					if (not_num($product_quantity)) {
			            $data['message']  = hs_translate("The product quantity field is required!");
			            $data['err_code'] = 9; break;
			        } 

			        else {
			        	if($product_quantity > 9999999) {
			        		$data['message']  = hs_translate("The product quantity you entered is too high. Max quantity value is 1200");
			            	$data['err_code'] = 9; break;
			        	} 

			        	else if($product_quantity != $product_item['quantity']) {
			        		hs_setprod_val($product_id,array(
			        			'quantity' => $product_quantity
			        		));	
			        	}
			        }
				}

				else if ($field == 'sku') {
					$product_sku = fetch_or_get($_POST['sku'],'');
					$product_sku = hs_secure($product_sku);

			        if(not_empty($product_sku) || len($product_sku) > 0) {
			        	if(len($product_sku) > 22) {
			        		$data['message']  = hs_translate("The product SKU value you entered is too long!");
			            	$data['err_code'] = 25; break;
			        	}

			        	else if(hs_product_sku_exists($product_id,$product_sku) == true) {
			        		$data['message']  = hs_translate("The product with such SKU ID alerady exists!");
			            	$data['err_code'] = 25; break;
			        	} 

			        	else if($product_sku != $product_item['sku']) {
			        		hs_setprod_val($product_id,array(
			        			'sku' => $product_sku
			        		));	
			        	}
			        }
			        else {
			        	hs_setprod_val($product_id,array('sku' => ''));	
			        }
				}

				else if ($field == 'shipping_cost') {
					$product_shipping_cost = fetch_or_get($_POST['shipping_cost'],0);
					$product_shipping_fee  = fetch_or_get($_POST['shipping_fee'],0);
					$product_shipping_cost = hs_secure($product_shipping_cost);

					if (empty($product_shipping_cost)) {
			            $data['message']  = hs_translate("The product shipping cost field is required!");
			            $data['err_code'] = 10; break;
			        } 
			        else {
			        	if (in_array($product_shipping_cost, array('free','paid')) != true) {
			        		$data['message']  = hs_translate("The product shipping cost you selected is not valid.");
			            	$data['err_code'] = 10; break;
			        	}
			        	else if($product_shipping_cost == 'paid' && not_num($product_shipping_fee)) {
			        		$data['message']  = hs_translate("The product shipping fee amount is missing or invalid. Please check your details");
			            	$data['err_code'] = 11; break;
			        	}
			        	else {
			        		if($product_shipping_cost != $product_item['shipping_cost']) {
				        		hs_setprod_val($product_id,array(
				        			'shipping_cost' => $product_shipping_cost
				        		));	
				        	}
				        	if ($product_shipping_cost == 'paid' && $product_shipping_fee != $product_item['shipping_fee']) {
				        		hs_setprod_val($product_id,array(
				        			'shipping_fee' => $product_shipping_fee
				        		));	
				        	}
			        	}
			        }
				}

				else if ($field == 'payment_method') {
					$product_payment_method = fetch_or_get($_POST['payment_method'],'none');
					$product_payment_method = hs_secure($product_payment_method);

					if (empty($product_payment_method)) {
			            $data['message']  = "Error: Invalid request data. Please check your details!";
			            $data['err_code'] = 23; break;
			        } 
			        else {
			        	if (in_array($product_payment_method, array('cod_payments','pre_payments','all_payments')) != true) {
			        		$data['message']  = "Error: Invalid request data. Please check your details!";;
			            	$data['err_code'] = 23; break;
			        	}
			        	else {
			        		if($product_payment_method != $product_item['payment_method']) {
				        		hs_setprod_val($product_id,array(
				        			'payment_method' => $product_payment_method
				        		));

				        		if ($product_payment_method == 'pre_payments') {
				        			if ($product_item['status'] == 'blocked') {
				        				hs_setprod_val($product_id,array(
						        			'status' => $product_item['last_status']
						        		));
				        			}
				        		}
				        	}
			        	}
			        }
				}

				else if ($field == 'shipping_time') {
					$product_shipping_time = fetch_or_get($_POST['shipping_time'],0);
					$product_shipping_time = hs_secure($product_shipping_time);

					if (empty($product_shipping_time)) {
			            $data['message']  = hs_translate("The product shipping time is required!");
			            $data['err_code'] = 12; break;
			        } 
			        else {
			        	if (in_array($product_shipping_time, array('1_bd','2_3_bd','4_7_bd','8_15_bd','within_1_month','within_2_months','within_3_months')) != true) {
			        		$data['message']  = hs_translate("The product shipping time estimate you selected is not valid.");
			            	$data['err_code'] = 12; break;
			        	} 

			        	else if($product_shipping_time != $product_item['shipping_time']) {
			        		hs_setprod_val($product_id,array(
			        			'shipping_time' => $product_shipping_time
			        		));	
			        	}
			        }
				}

				else if ($field == 'origin') {
					$product_origin = fetch_or_get($_POST['origin'],'');
					$product_origin = hs_secure($product_origin);

			        if (not_empty($product_origin) || len($product_origin) > 0) {
			        	if (len_between($product_origin,5,30) != true) {
			        		$data['message']  = hs_translate("The product origin country name you entered is not valid.");
			            	$data['err_code'] = 13; break;
			        	} 

			        	else if($product_origin != $product_item['origin']) {
			        		hs_setprod_val($product_id,array(
			        			'origin' => $product_origin
			        		));	
			        	}
			        }

			        else {
			        	hs_setprod_val($product_id,array('origin' => ''));	
			        }
				}

				else if ($field == 'brand') {
					$product_brand = fetch_or_get($_POST['brand'],'');
					$product_brand = hs_secure($product_brand);
 
			        if (not_empty($product_brand) || len($product_brand) > 0) {
			        	if (len_between($product_brand,2,50) != true) {
			        		$data['message']  = hs_translate("The product brand name you entered is not valid!");
			            	$data['err_code'] = 14; break;
			        	} 

			        	else if($product_brand != $product_item['brand']) {
			        		hs_setprod_val($product_id,array(
			        			'brand' => $product_brand
			        		));	
			        	}
			        }
			        else {
			        	hs_setprod_val($product_id,array('brand' => ''));	
			        }
				}

				else if ($field == 'model_number') {
					$product_model_number = fetch_or_get($_POST['model_number'],'');
					$product_model_number = hs_secure($product_model_number);

			        if (not_empty($product_model_number) || len($product_model_number) > 0) {
			        	if (len_between($product_model_number,5,50) != true) {
			        		$data['message']  = hs_translate("The product model number you entered is not valid!");
			            	$data['err_code'] = 15; break;
			        	} 
			        	else if($product_model_number != $product_item['model_number']) {
			        		hs_setprod_val($product_id,array(
			        			'model_number' => $product_model_number
			        		));	
			        	}
			        }
			        else {
			        	hs_setprod_val($product_id,array('model_number' => ''));	
			        }
				}

				else if ($field == 'weight') {
					$product_weight = fetch_or_get($_POST['weight'],'');
					$product_weight = hs_secure($product_weight);
					$product_weight = preg_replace('/(\s|\n|\r|\t|\n\r)/is', '', $product_weight);

			        if (not_empty($product_weight) || len($product_weight) > 0) {
			        	if (empty(preg_match('/^(?:[0]{1,3}\.[0]{1,3}|[0]{1,3})(?:mg|g|kg|lb|oz)$/i', $product_weight)) != true) {
			        		$data['message']  = hs_translate("The product package weight value you entered is not valid!");
			            	$data['err_code'] = 16; break;
			        	} 
			        	elseif (empty(preg_match('/^(?:[0-9]{1,3}\.[0-9]{1,3}|[0-9]{1,3})(?:mg|g|kg|lb|oz)$/i', $product_weight))) {
			        		$data['message']  = hs_translate("The product package weight value you entered is not valid!");
			            	$data['err_code'] = 16; break;
			        	} 
			        	else if($product_weight != $product_item['weight']) {
			        		hs_setprod_val($product_id,array(
			        			'weight' => $product_weight
			        		));	
			        	}
			        }

			        else {
			        	hs_setprod_val($product_id,array('weight' => ''));	
			        }
				}

				else if ($field == 'length') {
					$product_length = fetch_or_get($_POST['length'],'');
					$product_length = hs_secure($product_length);
					$product_length = preg_replace('/(\s|\n|\r|\t|\n\r)/is', '', $product_length);

			        if (not_empty($product_length) || len($product_length) > 0) {
			        	if (empty(preg_match('/^(?:[0]{1,3}\.[0]{1,3}|[0]{1,3})(?:mm|cm|m|kl|in)$/i', $product_length)) != true) {
			        		$data['message']  = hs_translate("The product package length value you entered is not valid!");
			            	$data['err_code'] = 17; break;
			        	} 
			        	elseif (empty(preg_match('/^(?:[0-9]{1,3}\.[0-9]{1,3}|[0-9]{1,3})(?:mm|cm|m|kl|in)$/i', $product_length))) {
			        		$data['message']  = hs_translate("The product package length value you entered is not valid!");
			            	$data['err_code'] = 17; break;
			        	} 
			        	else if($product_length != $product_item['length']) {
			        		hs_setprod_val($product_id,array(
			        			'length' => $product_length
			        		));	
			        	}
			        }

			        else {
			        	hs_setprod_val($product_id,array('length' => ''));
			        }
				}

				else if ($field == 'width') {
					$product_width = fetch_or_get($_POST['width'],'');
					$product_width = hs_secure($product_width);
					$product_width = preg_replace('/(\s|\n|\r|\t|\n\r)/is', '', $product_width);
 
			        if (not_empty($product_width) || len($product_width) > 0) {
			        	if (empty(preg_match('/^(?:[0]{1,3}\.[0]{1,3}|[0]{1,3})(?:mm|cm|m|kl|in)$/i', $product_width)) != true) {
			        		$data['message']  = hs_translate("The product package width value you entered is not valid!");
			            	$data['err_code'] = 18; break;
			        	} 
			        	elseif (empty(preg_match('/^(?:[0-9]{1,3}\.[0-9]{1,3}|[0-9]{1,3})(?:mm|cm|m|kl|in)$/i', $product_width))) {
			        		$data['message']  = hs_translate("The product package width value you entered is not valid!");
			            	$data['err_code'] = 18; break;
			        	} 
			        	else if($product_width != $product_item['width']) {
			        		hs_setprod_val($product_id,array(
			        			'width' => $product_width
			        		));	
			        	}
			        }
			        else {
			        	hs_setprod_val($product_id,array('width' => ''));	
			        }
				}

				else if ($field == 'height') {
					$product_height = fetch_or_get($_POST['height'],'');
					$product_height = hs_secure($product_height);
					$product_height = preg_replace('/(\s|\n|\r|\t|\n\r)/is', '', $product_height);

			        if (not_empty($product_height) || len($product_height) > 0) {
			        	if (empty(preg_match('/^(?:[0]{1,3}\.[0]{1,3}|[0]{1,3})(?:mm|cm|m|kl|in)$/i', $product_height)) != true) {
			        		$data['message']  = hs_translate("The product package height value you entered is not valid!");
			            	$data['err_code'] = 19; break;
			        	} 

			        	elseif (empty(preg_match('/^(?:[0-9]{1,3}\.[0-9]{1,3}|[0-9]{1,3})(?:mm|cm|m|kl|in)$/i', $product_height))) {
			        		$data['message']  = hs_translate("The product package height value you entered is not valid!");
			            	$data['err_code'] = 19; break;
			        	} 

			        	else if($product_height != $product_item['height']) {
			        		hs_setprod_val($product_id,array(
			        			'height' => $product_height
			        		));	
			        	}
			        }
			        else {
			        	hs_setprod_val($product_id,array('height' => ''));
			        }
				}

				else if ($field == 'poster') {
					if (empty($product_item['poster'])) {
			            $data['message']  = hs_translate("The product poster image is required. Please select product main image!");
			            $data['err_code'] = 20; break;
			        } 
				}

				else if ($field == 'media') {
					$prod_media_min = intval($hs['config']['prod_min_images']);
					$prod_media_max = intval($hs['config']['prod_max_images']);

					if (empty($product_media)) {
			            $data['message']  =  hs_translate("The product gallery images are required. Please add product gallery from {%min%} to {%max%} images",array(
			            	'min'         => $prod_media_min,
			            	'max'         => $prod_media_max,
			            ));
			            $data['err_code'] = 21; break;
			        } 

			        else if($product_media < $prod_media_min) {
			        	$data['message']  =  hs_translate("Please add product gallery from {%min%} to {%max%} images",array(
			            	'min'         => $prod_media_min,
			            	'max'         => $prod_media_max,
			            ));
			            $data['err_code'] = 21; break;
			        } 

			        else if($product_media > $prod_media_max) {
			        	$data['message']  =  hs_translate("Please add product gallery from {%min%} to {%max%} images",array(
			            	'min'         => $prod_media_min,
			            	'max'         => $prod_media_max,
			            ));
			            $data['err_code'] = 21; break;
			        }
				}

				else if ($field == 'variation_type') {
					$product_variation_type = fetch_or_get($_POST['variation_type']);
					$product_variation_type = hs_secure($product_variation_type);

					if (empty($product_variation_type)) {
			            $data['message']  = hs_translate("The product variation type is missing or invalid.");
			            $data['err_code'] = 22; break;
			        } 

			        else {
			        	if (in_array($product_variation_type,array('single','color','size','color_size')) != true) {
			        		$data['message']  = hs_translate("The product variation type you selected is not valid!");
			            	$data['err_code'] = 22; break;
			        	} 

			        	else if (in_array($product_variation_type, array('color','size','color_size'))){
			        		if(empty(hs_get_prodvars_total($product_id))) {
			        			$data['message']  = hs_translate("The variable product type must have at least one variant!");
			            		$data['err_code'] = 22; break;
			        		}
			        	} 

			        	else if($product_variation_type != $product_item['variation_type']) {
			        		hs_setprod_val($product_id,array(
			        			'variation_type' => $product_variation_type
			        		));	
			        	}
			        }
				}
			}

			if (empty($data['err_code'])) {
				$update_prod_data     =  array(
					'status'          => 'active',
					'activity_status' => 'active',
					'editing_stage'   => 'saved',
					'time'            => time(),
					'approved'        => (($hs['is_admin']) ? 'Y' : 'N')
				);

				$db              = $db->where('id',$product_id);
				$up              = $db->update(T_PRODUCTS,$update_prod_data);
				$data['status']  = 200;
				$data['url']     = hs_link("product/$product_id");
				$modification    = fetch_or_get($_POST['save_changes'],false);
				$up              = hs_block_unblock_seller_products($me['id']);

				if ($hs['is_admin']) {
					if (not_empty($modification)) {
						$data['message'] = hs_translate("Your changes to this product has been successfully saved!");
					}

					else {
						$data['message'] = hs_translate("Your new product item has been successfully created!");
					}
				}
				else {
					if (not_empty($modification)) {
						$data['message'] = hs_translate("Your changes to this product item has been successfully saved and waiting for moderator approval!");
					}
					
					else {
						$data['message'] = hs_translate("Your new product item has been successfully created and waiting for moderator approval!");
					}
				}
			}
		}
		else {
			$data['status']  = 404;
			$data['message'] = "Error: Product item with such ID does not exists!";
		}
	}
}

else if ($action == 'cvar_upsert' && hs_session('edit_product_data')) {
	$data['err_code']    = 0;
	$data['status']      = 400;
	$edit_product_data   =  hs_session('edit_product_data');
	$variation_id        = $edit_product_data['var_id'];
	$product_id          = $edit_product_data['prod_id'];
	$prodvars_total      = hs_get_prodvars_total($product_id);

	if ($prodvars_total >= 15) {
		$data['err_code'] =  'system_error';
		$data['status']   =  406;
		$data['message']  =  hs_translate('You cannot add more than {%max%} color options to this type of product.',array(
			'max'         => 15
		));
	}

	else if (not_num($variation_id) || not_num($product_id) || hs_is_prodowner($me['id'],$product_id) != true) {
		$data['err_code'] = 1;
		$data['status']   = 406;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		$db               = $db->where('id',$variation_id);
		$db               = $db->where('prod_id',$product_id);
		$colvar_item      = $db->getOne(T_PROD_VARS);	
		$product_item     = hs_get_edited_product($product_id);
		if (not_empty($colvar_item) && not_empty($product_item) && $product_item['variation_type'] == 'color') {

			$colvar_item     = hs_o2array($colvar_item);
			$var_data_fileds = array(
				'col_img'    => fetch_or_get($colvar_item['col_img'],false),
				'col_name'   => fetch_or_get($_POST['col_name'],false),
				'reg_price'  => fetch_or_get($_POST['reg_price'],false),
				'sale_price' => fetch_or_get($_POST['sale_price'],false),
				'quantity'   => fetch_or_get($_POST['quantity'],false),
				'sku'        => fetch_or_get($_POST['sku'],''),
			);

			foreach ($var_data_fileds as $field_name => $field_val) {
				if ($field_name == 'col_img') {
					if (empty($colvar_item['col_img'])) {
			            $data['message']  = hs_translate("Please select a color-coded image for your product variation!");
			            $data['err_code'] = 1; break;
			        } 

			        else if(file_exists($colvar_item['col_img']) != true) {
			        	$data['message']  = hs_translate("The product variation color-coded image file not found or invalid!");
			            $data['err_code'] = 1; break;
			        }
				}

				else if ($field_name == 'col_name') {
					if (empty($field_val)) {
			            $data['message']  = hs_translate("The color name is required. Please select color name!");
			            $data['err_code'] = 2; break;
			        } 

			        else {
			        	if (in_array($field_val, array_keys($hs['color_types'])) != true) {
			        		$data['message']  = hs_translate("The color name you selected is not valid!");
			            	$data['err_code'] = 2; break;
			        	} 
			        	else if($field_val != $colvar_item['col_hex']) {
			        		hs_setprodvar_val($variation_id,array(
			        			'col_name' => $hs['color_types'][$field_val]
			        		));

			        		hs_setprodvar_val($variation_id,array(
			        			'col_hex' => hs_secure($field_val)
			        		));
			        	}
			        }
				}

				else if ($field_name == 'reg_price') {			
					$max_price     = intval($config['max_sale_price']);
			        $min_price     = intval($config['min_sale_price']);

					if (not_num($field_val)) {
			            $data['message']  = hs_translate("The regular price field is required!");
			            $data['err_code'] = 3; break;
			        } 
			        else {
			        	if ($field_val < $min_price) {
			        		$data['message']  = hs_translate("The regular price you entered is too low.");
			            	$data['err_code'] = 3; break;
			        	} 

			        	else if($field_val > $max_price) {
			        		$data['message']  = hs_translate("The regular price you entered is too high.");
			            	$data['err_code'] = 3; break;
			        	} 

			        	else if($field_val != $colvar_item['reg_price']) {
			        		hs_setprodvar_val($variation_id,array(
			        			'reg_price' => hs_secure($field_val)
			        		));	
			        	}
			        }
				}

				else if ($field_name == 'sale_price') {
					$max_price      = intval($config['max_sale_price']);
			        $min_price      = intval($config['min_sale_price']);
			        $regular_price  = fetch_or_get($var_data_fileds['reg_price']);

					if (not_num($field_val)) {
			            $data['message']  = hs_translate("The sale price field is required!");
			            $data['err_code'] = 4; break;
			        } 
			        else {
			        	if ($field_val < $min_price) {
			        		$data['message']  = hs_translate("The sale price you entered is too low.");
			            	$data['err_code'] = 4; break;
			        	} 
			        	else if($field_val > $max_price) {
			        		$data['message']  = hs_translate("The sale price you entered is too high.");
			            	$data['err_code'] = 4; break;
			        	}  
			        	else if($field_val == $regular_price) {
			        		$data['message']  = hs_translate("The regular price of the goods and the sale price cannot be the same!");
			            	$data['err_code'] = 4; break;
			        	} 
			        	else if($field_val != $colvar_item['sale_price']) {
			        		hs_setprodvar_val($variation_id,array(
			        			'sale_price' => $field_val
			        		));	
			        	}
			        }
				}

				else if ($field_name == 'quantity') {
					if (not_num($field_val)) {
			            $data['message']  = hs_translate("The product quantity field is required!");
			            $data['err_code'] = 5; break;
			        } 
			        else {
			        	if($field_val > 1200) {
			        		$data['message']  = hs_translate("Max quantity value is 1200");
			            	$data['err_code'] = 5; break;
			        	} 	 

			        	else if($field_val != $colvar_item['quantity']) {
			        		hs_setprodvar_val($variation_id,array(
			        			'quantity' => intval($field_val)
			        		));	
			        	}
			        }
				}

				else if ($field_name == 'sku') {
			        if (not_empty($field_val)) {
			        	if(len($field_val) > 22) {
			        		$data['message']  = hs_translate("The product SKU value you entered is too long!");
			            	$data['err_code'] = 6; break;
			        	} 

			        	else if(hs_product_var_sku_exists($product_id,$variation_id,$field_val) == true) {
			        		$data['message']  = hs_translate("The product with such SKU ID alerady exists!");
			            	$data['err_code'] = 6; break;
			        	}

			        	else if($field_val != $colvar_item['sku']) {
			        		hs_setprodvar_val($variation_id,array(
			        			'sku' => hs_secure($field_val)
			        		));	
			        	}
			        }
				}
			}

			if (empty($data['err_code'])) {
				hs_setprodvar_val($variation_id,array('activity_status' => 'active'));

				$data['status']  = 200;
				$data['total']   = hs_get_prodvars_total($product_id);
				$data['message'] = hs_translate("Your new product variation item has been successfully added!");
			}
		} 

		else {
			$data['err_code'] = 14;
			$data['status']   = 406;
			$data['message']  = "Error: Invalid request data. Please check your details";
		}
	}
}

else if ($action == 'svar_upsert' && hs_session('edit_product_data')) {
	$data['err_code']  = 0;
	$data['status']    = 400;
	$edit_product_data = hs_session('edit_product_data');
	$product_id        = fetch_or_get($edit_product_data['prod_id'],false);
	$var_id            = fetch_or_get($edit_product_data['var_id'],false);
	$prodvars_total    = hs_get_prodvars_total($product_id);

	if ($prodvars_total >= 15) {
		$data['err_code'] =  14;
		$data['status']   =  400;
		$data['message']  =  hs_translate('You cannot add more than {%max%} size options to this type of product.',array(
			'max'         => 15
		));
	}

	else if (not_num($product_id) || not_num($var_id)) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		$db           = $db->where('id',$var_id);
		$db           = $db->where('prod_id',$product_id);
		$sizevar_item = $db->getOne(T_PROD_VARS);
		$db           = $db->where('id',$product_id);
		$product_item = $db->getOne(T_PRODUCTS);

		if (hs_queryset($sizevar_item,'object') && hs_queryset($product_item,'object') && $product_item->variation_type == 'size') {
			$sizevar_item    =  hs_o2array($sizevar_item);
			$product_item    =  hs_o2array($product_item);
			$var_data_fileds =  array(
				'size'       => fetch_or_get($_POST['size'],false),
				'reg_price'  => fetch_or_get($_POST['reg_price'],false),
				'sale_price' => fetch_or_get($_POST['sale_price'],false),
				'quantity'   => fetch_or_get($_POST['quantity'],false),
				'sku'        => fetch_or_get($_POST['sku'],''),
			);

			foreach ($var_data_fileds as $field_name => $field_val) {
				if ($field_name == 'size') {
			        $hs['ps_type']        = $hs['sizing_types'][$product_item['sizing_type']];
			        $item_exists          = array(
			        	'prod_id'         => $product_id,
			        	'var_type'        => 'size',
			        	'activity_status' => 'active',
			        	'status'          => 'active',
			        	'size'            => $field_val
			        );

					if (empty($field_val)) {
			            $data['message']  = hs_translate("The size is required Please select product size");
			            $data['err_code'] = 2; break;
			        } 
			        else {
			        	if (hs_prod_type_exists($item_exists)) {
			        		$data['message']  = hs_translate("This size option already exists!");
			            	$data['err_code'] = 2; break;
			        	}
			        	else if (not_empty($hs['ps_type']['expandable'])) {
							$regex = $hs['ps_type']['regex'];
							if (preg_match($regex, $field_val) != true) {
								$data['message']  = hs_translate("The size value you selected is not valid!");
			            		$data['err_code'] = 2; break;
							}
						}
						else if(in_array($field_val, $hs['ps_type']['size_units']) != true) {
							$data['message']  = hs_translate("The size value you selected is not valid!");
			            	$data['err_code'] = 2; break;
						} 
						if($field_val != $sizevar_item['size']) {
			        		hs_setprodvar_val($var_id,array(
			        			'size' => hs_secure($field_val)
			        		));
			        	}
			        }
				}

				else if ($field_name == 'reg_price') {
					$max_sale_price = intval($config['max_sale_price']);
					$min_sale_price = intval($config['min_sale_price']);

					if (not_num($field_val)) {
			            $data['message']  = hs_translate("The regular price field is required!");
			            $data['err_code'] = 3; break;
			        } 
			        else {
			        	if ($field_val < $min_sale_price) {
			        		$data['message']  = hs_translate("The regular price you entered is too low.");
			            	$data['err_code'] = 3; break;
			        	} 
			        	else if($field_val > $max_sale_price) {
			        		$data['message']  = hs_translate("The regular price you entered is too high.");
			            	$data['err_code'] = 3; break;
			        	} 
			        	else if($field_val != $sizevar_item['reg_price']) {
			        		hs_setprodvar_val($var_id,array(
			        			'reg_price' => hs_secure($field_val)
			        		));	
			        	}
			        }
				}

				elseif ($field_name == 'sale_price') {
					$max_sale_price = intval($config['max_sale_price']);
					$min_sale_price = intval($config['min_sale_price']);
					$var_reg_price  = fetch_or_get($var_data_fileds['reg_price']);

					if (not_num($field_val)) {
			            $data['message']  = hs_translate("The sale price field is required!");
			            $data['err_code'] = 4; break;
			        } 
			        else {
			        	if ($field_val < $min_sale_price) {
			        		$data['message']  = hs_translate("The sale price you entered is too low.");
			            	$data['err_code'] = 4; break;
			        	} 
			        	else if($field_val > $max_sale_price) {
			        		$data['message']  = hs_translate("The sale price you entered is too high.");
			            	$data['err_code'] = 4; break;
			        	} 
			        	else if($field_val == $var_reg_price) {
			        		$data['message']  = hs_translate("The regular price of the goods and the sale price cannot be the same!");
			            	$data['err_code'] = 4; break;
			        	} 
			        	else if($field_val != $sizevar_item['sale_price']) {
			        		hs_setprodvar_val($var_id,array(
			        			'sale_price' => hs_secure($field_val)
			        		));	
			        	}
			        }
				}

				elseif ($field_name == 'quantity') {
					if (not_num($field_val)) {
			            $data['message']  = hs_translate("The product quantity field is required!");
			            $data['err_code'] = 5; break;
			        } 
			        else {
			        	if($field_val > 1200) {
			        		$data['message']  = hs_translate("Max quantity value is 1200");
			            	$data['err_code'] = 5; break;
			        	} 
			        	else if($field_val != $sizevar_item['quantity']) {
			        		hs_setprodvar_val($var_id,array(
			        			'quantity' => intval($field_val)
			        		));	
			        	}
			        }
				}

				elseif ($field_name == 'sku') {
			        if (not_empty($field_val)) {
			        	if(len($field_val) > 22) {
			        		$data['message']  = hs_translate("The product SKU value you entered is too long!");
			            	$data['err_code'] = 6; break;
			        	} 

			        	else if(hs_product_var_sku_exists($product_id,$var_id,$field_val) == true) {
			        		$data['message']  = hs_translate("The product with such SKU ID alerady exists!");
			            	$data['err_code'] = 6; break;
			        	}

			        	else if($field_val != $sizevar_item['sku']) {
			        		hs_setprodvar_val($var_id,array(
			        			'sku' => hs_secure($field_val)
			        		));	
			        	}
			        }
				}
			}

			if (empty($data['err_code'])) {

				hs_setprodvar_val($var_id,array('activity_status' => 'active'));

				$data['status']  = 200;
				$data['total']   = hs_get_prodvars_total($product_id);
				$data['message'] = hs_translate("Your new product variation item has been successfully added!");
			}
		} 

		else {
			$data['err_code'] = 14;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Please check your details";
		}
	}
}

else if ($action == 'csvar_upsert' && hs_session('edit_product_data')) {
	$data['err_code']  = 0;
	$data['status']    = 400;
	$edit_product_data = hs_session('edit_product_data');
	$prod_id           = fetch_or_get($edit_product_data['prod_id'],0);
	$var_ids           = fetch_or_get($edit_product_data['var_ids'],array());
	$var_items_data    = fetch_or_get($_POST['var_items_data'],array());
	$prodvars_total    = hs_get_prodvars_total($prod_id);

	if ($prodvars_total >= 150) {
		$data['err_code'] =  14;
		$data['status']   =  406;
		$data['message']  =  hs_translate('You cannot add more than {%max%} (Color & Size) options to this type of product.',array(
			'max'         => 150
		));
	}

	else if(not_num($prod_id) || empty($var_ids)) {
		$data['err_code'] = "fatal_error";
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else if(hs_is_prodowner($me['id'],$prod_id) != true) {
		$data['err_code'] = "fatal_error";
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(empty($var_items_data) || is_array($var_items_data) != true) {
		$data['err_code'] = "fatal_error";
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(all_numeric(array_keys($var_items_data)) != true) {
		$data['err_code'] = "fatal_error";
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		$db         = $db->where('prod_id',$prod_id);
		$db         = $db->where('var_type','color_size');
		$var_items  = $db->get(T_PROD_VARS);
		$var_items  = ((hs_queryset($var_items)) ? hs_o2array($var_items) : null);
		$variations = $var_items_data;
		$db         = $db->where('id',$prod_id);
		$db         = $db->where('user_id',$me['id']);
		$prod_item  = $db->getOne(T_PRODUCTS);
		$prod_item  = ((hs_queryset($prod_item,'object')) ? hs_o2array($prod_item) : null);
		$var_it_ids = array();

		if (count($var_items_data) >= 20) {
			$data['message']   = hs_translate('You cannot add more than 20 size options to one color option!');        
			$data['err_code']  = 'vars_total_error';
		}
		else {
			if(not_empty($var_items) && not_empty($prod_item)) {
				if ($prod_item['variation_type'] == 'color_size') {
					foreach ($var_items as $cs_var_item) {
						if (in_array($cs_var_item['id'], array_keys($variations))) {
							$var_id            = $cs_var_item['id'];
							$var_it_ids[]      = $var_id;
							$var_data_fileds   = array(
								'reg_price'    => fetch_or_get($_POST['var_items_data'][$var_id]['reg_price'],false),
								'sale_price'   => fetch_or_get($_POST['var_items_data'][$var_id]['sale_price'],false),
								'quantity'     => fetch_or_get($_POST['var_items_data'][$var_id]['quantity'],false),
								'sku'          => fetch_or_get($_POST['var_items_data'][$var_id]['sku'],false),
							);

							foreach ($var_data_fileds as $field_name => $field_val) {
								if ($field_name == 'reg_price') {
									$max_sale_price = intval($config['max_sale_price']);
									$min_sale_price = intval($config['min_sale_price']);

									if (not_num($field_val) || pos_int($field_val) != true) {
							            $data['message']   = hs_translate("The regular price field is required!");	            
							            $data['err_field'] = $field_name;
							            $data['err_code']  = $var_id; break;
							        } 
							        else {
							        	if ($field_val < $min_sale_price) {
							        		$data['message']   = hs_translate("The regular price you entered is too low.");
							            	$data['err_field'] = $field_name;
							            	$data['err_code']  = $var_id; break;
							        	} 

							        	else if($field_val > $max_sale_price) {
							        		$data['message']   = hs_translate("The regular price you entered is too high.");  	
							            	$data['err_field'] = $field_name;
							            	$data['err_code']  = $var_id; break;
							        	} 

							        	else if($field_val != $cs_var_item['reg_price']) {
							        		hs_setprodvar_val($var_id,array(
							        			'reg_price' => hs_secure($field_val)
							        		));	
							        	}
							        }
								}

								else if ($field_name == 'sale_price') {
									$max_sale_price = intval($config['max_sale_price']);
									$min_sale_price = intval($config['min_sale_price']);
									$prod_reg_price = fetch_or_get($_POST['var_items_data'][$var_id]['reg_price'],0);

									if (not_num($field_val) || pos_int($field_val) != true) {
							            $data['message']   = hs_translate("The sale price field is required!");
							            $data['err_field'] = $field_name;
							            $data['err_code']  = $var_id; break;
							        } 
							        else {
							        	if ($field_val < $min_sale_price) {
							        		$data['message']   = hs_translate("The sale price you entered is too low.");
							            	$data['err_field'] = $field_name;
							            	$data['err_code']  = $var_id; break;
							        	} 
							        	else if($field_val > $max_sale_price) {
							        		$data['message']   = hs_translate("The sale price you entered is too high.");
							            	$data['err_field'] = $field_name;
							           		$data['err_code']  = $var_id; break;
							        	}  
							        	else if($field_val == $prod_reg_price) {
							        		$data['message']   = hs_translate("The regular price of the goods and the sale price cannot be the same!");
							            	$data['err_field'] = $field_name;
							           		$data['err_code']  = $var_id; break;
							        	} 
							        	else if($field_val != $cs_var_item['sale_price']) {
							        		hs_setprodvar_val($var_id,array(
							        			'sale_price' => hs_secure($field_val)
							        		));	
							        	}
							        }
								}

								else if ($field_name == 'quantity') {
									if (not_num($field_val) || pos_int($field_val) != true) {
							            $data['message']   = hs_translate("The product quantity field is required!");
							            $data['err_field'] = $field_name;
							            $data['err_code']  = $var_id; break;
							        }
							        else {	
							        	if($field_val > 1200) {
							        		$data['message']   = hs_translate("Max quantity value is 1200");
							            	$data['err_field'] = $field_name;
							            	$data['err_code']  = $var_id; break;
							        	} 

							        	else if($field_val != $cs_var_item['quantity']) {
							        		hs_setprodvar_val($var_id,array(
							        			'quantity' => intval($field_val)
							        		));	
							        	}
							        }
								}

								else if ($field_name == 'sku') {
							        if (not_empty($field_val)) {	
							        	if(len($field_val) > 22) {
							        		$data['message']   = hs_translate("The product SKU value you entered is too long!");
							            	$data['err_field'] = $field_name;
							            	$data['err_code']  = $var_id; break;
							        	} 

							        	else if(hs_product_var_sku_exists($prod_id,$var_id,$field_val) == true) {
							        		$data['message']   = hs_translate("The product with such SKU ID alerady exists!");
							        		$data['err_field'] = $field_name;
							            	$data['err_code']  = $var_id; break;
							        	}

							        	else if($field_val != $cs_var_item['quantity']) {
							        		hs_setprodvar_val($var_id,array(
							        			'sku' => hs_secure($field_val)
							        		));	
							        	}
							        }
								}
							}
						}
					}

					if (empty($data['err_code'])) {
						$var_color_img   = fetch_or_get($edit_product_data['col_img'],0);
						$var_color_hex   = fetch_or_get($edit_product_data['col_hex'],0);
						$var_color_thumb = hs_thumbnail($var_color_img,'120x120');

						if (empty($var_color_img)) {
							$data['status']    = 400;
							$data['message']   = hs_translate("Please select a color variation image for your product.");
							$data['err_field'] = 1;
							$data['err_code']  = 'col_img';
						} 

						else if(empty($var_color_hex)) {
							$data['status']    = 400;
							$data['message']   = hs_translate("Please select a color name for this product variant.");
							$data['err_field'] = 1;
							$data['err_code']  = 'col_hex';
						}

						else {

							/*$unset *******/ unset($edit_product_data['col_img']);
							/*$unset *******/ unset($edit_product_data['col_hex']);
							/*$unset *******/ unset($edit_product_data['var_ids']);
							/*$session_set */ hs_session('edit_product_data',$edit_product_data);

							$col_name             =  $hs['color_types'][$var_color_hex];
							$update_data          =  array(
								'col_img'         => $var_color_img,
								'col_thumb'       => $var_color_thumb,
								'col_hex'         => $var_color_hex,
								'col_name'        => $col_name,
								'activity_status' => 'active',
							);

							$db              = $db->where('id',$var_it_ids,"IN");
							$update          = $db->update(T_PROD_VARS,$update_data);
							$data['status']  = 200;
							$data['total']   = hs_get_prodvars_total($prod_id);
							$data['message'] = hs_translate("Your new product variation item has been successfully added!");
						}
					}
				}
			}
		}
	}
}

else if ($action == 'edit_prod_variation_data' && hs_session('edit_product_data')) {
	$data['err_field'] = false;
	$data['status']    = 400;
	$edit_product_data = hs_session('edit_product_data');
	$product_id        = fetch_or_get($edit_product_data['prod_id'],false);
	$var_id            = fetch_or_get($edit_product_data['var_id'],false);

	if (not_num($product_id) || not_num($var_id)) {
		$data['err_field'] = 'none';
		$data['status']    = 400;
		$data['message']   = "Error: Invalid request data. Please check your details";
	} 

	else {
		$var_data_fileds =  array(
			'reg_price'  => fetch_or_get($_POST['reg_price'],false),
			'sale_price' => fetch_or_get($_POST['sale_price'],false),
			'quantity'   => fetch_or_get($_POST['quantity'],false),
			'sku'        => fetch_or_get($_POST['sku'],''),
		);

		foreach ($var_data_fileds as $field_name => $field_val) {
			if ($field_name == 'reg_price') {
				$max_sale_price = intval($config['max_sale_price']);
				$min_sale_price = intval($config['min_sale_price']);

				if (not_num($field_val)) {
		            $data['message']   = hs_translate("The regular price field is required!");
		            $data['err_field'] = $field_name; break;
		        } 
		        else {
		        	if ($field_val < $min_sale_price) {
		        		$data['message']   = hs_translate("The regular price you entered is too low.");
		            	$data['err_field'] = $field_name; break;
		        	} 
		        	else if($field_val > $max_sale_price) {
		        		$data['message']   = hs_translate("The regular price you entered is too high.");
		            	$data['err_field'] = $field_name; break;
		        	} 
		        	else {
		        		hs_setprodvar_val($var_id,array(
		        			'reg_price' => $field_val
		        		));	
		        	}
		        }
			}

			elseif ($field_name == 'sale_price') {
				$max_sale_price = intval($config['max_sale_price']);
				$min_sale_price = intval($config['min_sale_price']);
				$var_reg_price  = fetch_or_get($var_data_fileds['reg_price']);

				if (not_num($field_val)) {
		            $data['message']   = hs_translate("The sale price field is required!");
		            $data['err_field'] = $field_name; break;
		        } 
		        
	        	else if ($field_val < $min_sale_price) {
	        		$data['message']   = hs_translate("The sale price you entered is too low.");
	            	$data['err_field'] = $field_name; break;
	        	} 

	        	else if($field_val > $max_sale_price) {
	        		$data['message']   = hs_translate("The sale price you entered is too high.");
	            	$data['err_field'] = $field_name; break;
	        	} 

	        	else if($field_val == $var_reg_price) {
	        		$data['message']   = hs_translate("The regular price of the goods and the sale price cannot be the same!");
	            	$data['err_field'] = $field_name; break;
	        	} 

	        	else {
	        		hs_setprodvar_val($var_id,array(
	        			'sale_price' => $field_val
	        		));	
	        	}
			}

			elseif ($field_name == 'quantity') {
				if (not_num($field_val)) {
		            $data['message']   = hs_translate("The product quantity field is required!");
		            $data['err_field'] = $field_name; break;
		        } 
		        else {
		        	if($field_val > 1200) {
		        		$data['message']   = hs_translate("Max quantity value is 1200");
		            	$data['err_field'] = $field_name; break;
		        	} 
		        	else {
		        		hs_setprodvar_val($var_id,array(
		        			'quantity' => intval($field_val)
		        		));	
		        	}
		        }
			}

			else if ($field_name == 'sku') {
		        if (not_empty($field_val)) {
		        	if(len($field_val) > 22) {
		        		$data['message']   = hs_translate("The product SKU value you entered is too long!");
		            	$data['err_field'] = $field_name; break;
		        	}

		        	else if(hs_product_var_sku_exists($var_id,$field_val) == true) {
		        		$data['message']   = hs_translate("The product with such SKU ID alerady exists!");
		            	$data['err_field'] = $field_name; break;
		        	} 

		        	else {
		        		hs_setprodvar_val($var_id,array(
		        			'sku' => $field_val
		        		));	
		        	}
		        }
		        else {
		        	hs_setprodvar_val($var_id,array('sku' => ''));
		        }
			}
		}

		if (empty($data['err_field'])) {
			$data['status']  = 200;
			$data['message'] = hs_translate("Your changes to this product has been successfully saved!");
		}
	}
}

else if ($action == 'upload_poster') {
	$data['err_code'] = 0;	
    if (empty($_FILES['poster']) || empty($_POST['prod_id']) || is_numeric($_POST['prod_id']) != true) {
        $data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 1;
        $data['status']   = 400;
    }

    if (empty($data['err_code'])) {
    	$prod_id     = hs_secure($_POST['prod_id']);
    	$update_data = array();

        if (not_empty($_FILES['poster']['tmp_name']) && hs_is_prodowner($me['id'],$prod_id)) {
            $file_info = array(
                'file' => $_FILES['poster']['tmp_name'],
                'size' => $_FILES['poster']['size'],
                'name' => $_FILES['poster']['name'],
                'type' => $_FILES['poster']['type'],
                'crop' => array('width' => 2800, 'height' => 2800),
                'allowed' => 'jpg,png,jpeg,gif'
            );

            $file_upload = hs_upload($file_info);
            if (not_empty($file_upload['filename'])) {
            	$prod_old_poster = hs_get_prod_fields($prod_id,array('poster','thumb'));
                if ($prod_old_poster) {
                	hs_delete_image($prod_old_poster['poster']);
                	hs_delete_image($prod_old_poster['thumb']);
                }

                $update_data['poster'] = $file_upload['filename'];
                $update_data['thumb']  = hs_thumbnail($file_upload['filename'],'120x120');
                $db                    = $db->where('id', $prod_id);
                $update_poster         = $db->update(T_PRODUCTS, $update_data);
                $data['status']        = 200;
                $data['url']           = hs_get_media($file_upload['filename']); 
            } 

            else{
            	$data['status']  = 500;
            	$data['message'] = "Error found while processing your request, please try again later.";
            }
        }
    }
}

else if ($action == 'additional_image' && hs_session('edit_product_data')) {
	$data['err_code']    = 0;	
	$edit_product_data   = hs_session('edit_product_data');

    if (empty($_FILES['image']) || not_num($edit_product_data['prod_id'])) {
        $data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 1;
        $data['status']   = 400;
    }

    if (empty($data['err_code'])) {
    	$prod_id        = intval($edit_product_data['prod_id']);
    	$max_upload     = array('prod_id' => $prod_id);
		$prod_media_max = intval($hs['config']['prod_max_images']);

    	if (hs_count_total(T_PROD_MEDIA,$max_upload) >= $prod_media_max) {
    		$data['message']  = hs_translate("You can not attach more than {%max%} images to this product!",array('max' => $prod_media_max));
	        $data['err_code'] = 2;
	        $data['status']   = 400;
    	}

        else if (not_empty($_FILES['image']['tmp_name']) && hs_is_prodowner($me['id'],$prod_id)) {
            $file_info    =  array(
                'file'    => $_FILES['image']['tmp_name'],
                'size'    => $_FILES['image']['size'],
                'name'    => $_FILES['image']['name'],
                'type'    => $_FILES['image']['type'],
                'crop'    => array('width' => 800, 'height' => 800),
                'allowed' => 'jpg,png,jpeg,gif'
            );

            $file_upload = hs_upload($file_info);
            if (not_empty($file_upload['filename'])) {
                $media_id        =  $db->insert(T_PROD_MEDIA, array(
                	"prod_id"    => $prod_id,
                	"src"        => $file_upload['filename'],
                	'thumb'      => hs_thumbnail($file_upload['filename'],'120x120')
                ));
                $data['status']  = 200;
                $data['url']     = hs_get_media($file_upload['filename']);
                $data['id']      = $media_id;
            } 
            else{
            	$data['status']  = 500;
            	$data['message'] = "Error found while processing your request, please try again later.";
            }
        }
    }
}

else if ($action == 'upload_pcv_img' && hs_session('edit_product_data')) {
	$data['err_code']    = 0;
	$edit_product_data = hs_session('edit_product_data');

	if (not_num($edit_product_data['prod_id']) || not_num($edit_product_data['var_id'])) {
		$data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 2;
        $data['status']   = 400;
	}

    else if (empty($_FILES['image'])) {
        $data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 2;
        $data['status']   = 400;
    } 

    else if(empty($_FILES['image']['tmp_name'])){
    	$data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 2;
        $data['status']   = 400;
    } 

    else if(hs_is_prodowner($me['id'],$edit_product_data['prod_id']) != true) {
    	$data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 2;
        $data['status']   = 400;
    } 

    else{

    	$prod_id   = intval($edit_product_data['prod_id']);
    	$var_id    = intval($edit_product_data['var_id']);
    	$db        = $db->where('id',$var_id);
    	$db        = $db->where('prod_id',$prod_id);

    	$prod_var  = $db->getOne(T_PROD_VARS);
    	$prod_item = hs_get_edited_product($prod_id);

    	if($prod_item['variation_type'] != 'color') {
    		$data['message']  = "Error: Invalid request data. Please try again later!";
	        $data['err_code'] = 2;
	        $data['status']   = 400;
    	} 

    	else {
            $file_info    = array(
                'file'    => $_FILES['image']['tmp_name'],
                'size'    => $_FILES['image']['size'],
                'name'    => $_FILES['image']['name'],
                'type'    => $_FILES['image']['type'],
                'crop'    => array('width' => 500, 'height' => 500),
                'allowed' => 'jpg,png,jpeg,gif'
            );

            $file_upload = hs_upload($file_info);
            if (not_empty($file_upload['filename'])) {  	
            	hs_delete_image($prod_var->col_img);
            	hs_delete_image($prod_var->col_thumb);

            	$db             =  $db->where('id',$var_id);
                $update         =  $db->update(T_PROD_VARS, array(
                	"col_img"   => $file_upload['filename'],
                	"col_thumb" => hs_thumbnail($file_upload['filename'],'120x120')
                ));

                $data['status']  = 200;
                $data['url']     = hs_get_media($file_upload['filename']);
            } 

            else{
            	$data['status']  = 406;
            	$data['message'] = "Error found while processing your request, please try again later.";
            }
    	}
    }
}

else if ($action == 'delete_product') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$product_id       = fetch_or_get($_POST['prod_id'],null);

	if (not_num($product_id)) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(hs_is_prodowner($me['id'],$product_id) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		if ($hs['is_admin']) {
			$data['status']  = 200; hs_ap_delete_product($product_id);
			$data['message'] = hs_translate("The product item has been successfully deleted!");
		}
		else{
			$db        = $db->where('user_id',$me['id']);
			$db        = $db->where('id',$product_id);
			$prod_item = $db->getOne(T_PRODUCTS);
			
			if (hs_queryset($prod_item,'object')) {	
				$update          = $db->where('id',$product_id)->update(T_PRODUCTS,array('status' => 'deleted'));
				$data['status']  = 200;
				$data['message'] = hs_translate("The product removal request has been successfully sent and awaiting for admin review!");

				#Delete product from wishlists
		        $db           = $db->where('prod_id',$product_id);
		        $prod_wishls  = $db->delete(T_WLS_ITEMS);

		        #Delete product from basket
		        $db           = $db->where('prod_id',$product_id);
		        $prod_basket  = $db->delete(T_BASKET);

		        $insert_data  = array(
					'user_id' => $me['id'],
					'title'   => 'Your request has been submitted!',
					'message' => 'Your product removal request has been sent successfully and awaiting review by the administrator.',
					'type'    => 'info',
					'static'  => 'N',
					'time'    => time()
				); $db->insert(T_ANNOUNC,$insert_data);
			} 

			else {
				$data['err_code'] = 3;
				$data['status']   = 400;
				$data['message']  = "Error: Invalid request data. Please check your details";
			}
		}
	}
}

else if ($action == 'toggle_prod_status') {
	$data['err_code'] = 0;
	$data['status']   = 400;
	$product_id       = fetch_or_get($_POST['id'],null);
	$status           = fetch_or_get($_POST['status'],'none');

	if (not_num($product_id)) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(hs_is_prodowner($me['id'],$product_id) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}

	else if(in_array($status, array('on','off')) != true) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		$db->returnType  = 'Array';
		$db              = $db->where('id',$product_id);
		$prod_item       = $db->getOne(T_PRODUCTS);

		if (empty($prod_item) || in_array($prod_item['status'], array('deleted','blocked'))) {
			$data['err_code'] = 4;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Please check your details";
		}
		else {
			$up              = hs_toggle_prod_status($product_id,$status,$prod_item['last_status']);
			$data['status']  = 200;
			$data['message'] = hs_translate("Product status has been successfully changed!");
		}	
	}
}

else if ($action == 'delete_prod_var' && hs_session('edit_product_data')) {
	$data['err_code']    = 0;
	$data['status']      = 400;
	$edit_product_data   = hs_session('edit_product_data');
	$product_id          = fetch_or_get($edit_product_data['prod_id'],0);
	$var_id              = fetch_or_get($_POST['id'],false);

	if (not_num($product_id) || not_num($var_id)) {
		$data['err_code'] = 1;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(hs_is_prodowner($me['id'],$product_id) != true) {
		$data['err_code'] = 2;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		$db           = $db->where('id',$var_id);
		$db           = $db->where('prod_id',$product_id);
		$prodvar_item = $db->getOne(T_PROD_VARS);

		if (hs_queryset($prodvar_item,'object')) {

			if ($prodvar_item->var_type == 'color') {
				hs_delete_image($prodvar_item->col_img);
			} 

			else if($prodvar_item->var_type == 'color_size') {
				$db   = $db->where('prod_id',$product_id);
				$db   = $db->where('var_type','color_size');
				$db   = $db->where('col_img',$prodvar_item->col_img);
				$used = $db->getValue(T_PROD_VARS,'COUNT(*)');

				if ($used < 2) {
					hs_delete_image($prodvar_item->col_img);
				}
			}


			$db              = $db->where('id',$var_id);
			$deleted         = $db->delete(T_PROD_VARS);
			$data['status']  = 200;
			$data['total']   = hs_get_prodvars_total($product_id);
		} 

		else {

			$data['err_code'] = 3;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Please check your details";
		}
	}
}

else if ($action == 'remove_gallery_image') {
	if (empty($_POST['image_id']) || is_numeric($_POST['image_id']) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(empty($_POST['prod_id']) || is_numeric($_POST['prod_id']) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else if(hs_is_prodowner($me['id'],intval($_POST['prod_id'])) != true) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 

	else {
		$pid = intval($_POST['prod_id']);
		$mid = intval($_POST['image_id']);
		$db  = $db->where('id',$mid);
		$db  = $db->where('prod_id',$pid);
		$img = $db->getOne(T_PROD_MEDIA);

		if (hs_queryset($img,'object')) {

			hs_delete_image($img->src);
			hs_delete_image($img->thumb);

			$db  = $db->where('id',$mid);
			$db  = $db->where('prod_id',$pid);
			$del = $db->delete(T_PROD_MEDIA);

			if ($del) {
				$data['status'] = 200;
			} 

			else {
				$data['err_code'] = 4;
				$data['status']   = 400;
				$data['message']  = "Error: Invalid request data. Please check your details";
			}
		} 

		else {
			$data['err_code'] = 4;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Please check your details";
		}
	}
}

else if ($action == 'clear_prod_media') {
	$prod_id = hs_secure($_GET['prod_id']);
	$media   = $db->where('prod_id',$prod_id)->get(T_PROD_MEDIA);

	if ($media) {
		foreach ($media as $row) {
			hs_delete_image($row->src);
		}

		$db->where('prod_id',$prod_id)->delete(T_PROD_MEDIA);
	}
}

else if ($action == 'add_p2_basket') {
	if (not_num($_POST['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details!";
	} 

	else if (not_num($_POST['quantity'])) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details!";
	} 

	else if (int_between($_POST['quantity'],1,20) != true) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details!";
	} 

	elseif (hs_is_prodowner($_POST['prod_id'])) {
		$data['err_code'] = 4;
		$data['status']   = 400;
		$data['message']  = "Error: You can not add your product to the basket!";
	} 

	elseif ($me['basket_val'] > 30) {
		$data['err_code'] = 4;
		$data['status']   = 400;
		$data['message']  = hs_translate('You can not have more than 30 products at a time. Please, first remove or pay products from your basket!');
	} 

	else {
		$prod_id   = intval($_POST['prod_id']);
		$quantity  = intval($_POST['quantity']);
		$user_id   = $me['id'];
		$db        = $db->where('id',$prod_id);
		$prod_item = $db->getOne(T_PRODUCTS);
		$prod_item = ((hs_queryset($prod_item,'object')) ? hs_o2array($prod_item) : array());

		if (not_empty($prod_item)) {
			if (fetch_or_get($prod_item['approved'],false) != 'Y') {
				$data['status']  = 400;
				$data['message'] = "Error: The product item with such id does not exists or not active!";
			}

			else if(fetch_or_get($prod_item['editing_stage'],false) != 'saved') {
				$data['status']  = 400;
				$data['message'] = "Error: The product item with such id does not exists!";
			}

			else if(fetch_or_get($prod_item['status'],false) != 'active') {
				$data['status']  = 400;
				$data['message'] = "Error: The product item with such id does not exists!";
			}

			else {
				if ($prod_item['variation_type'] == 'single') {
					$check_order_exists = array(
						'buyer_id' => $user_id,
						'prod_id'  => $prod_id,
						'var_type' => 'single',
					);

					if($quantity > intval($prod_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("This product item is not available in the desired quantity. Available quantity is {%qty%}",array(
							'qty'        => $prod_item['quantity']
						));
					}

					else if (hs_where_exists(T_ORDERS,$check_order_exists) == true) {
						$data['err_code'] = 5;
						$data['status']   = 400;
						$data['message']  = hs_translate("You have already ordered this product from this seller, so you cannot order it twice from the same seller");
					}

					else {
						$upsert_item   = hs_basket_upsert(array(
							'user_id'  => $user_id,
							'prod_id'  => $prod_id,
							'quantity' => $quantity,
							'var_type' => 'single',
							'time'     => time(),
						));

						if (in_array($upsert_item, array('update','insert'))) {
							$data['status']   = 200;
							$data['total']    = hs_basket_items_total($user_id);
							$data['message']  = hs_translate("This item has been added to the basket!");
						} 

						else {
							$data['err_code'] = 6;
							$data['status']   = 400;
							$data['message']  ="Error: Something went wrong, Please try again later!";
						}
					}
				}

				else if ($prod_item['variation_type'] == 'color' && is_number($_POST['var_color'])) {
					$check_order_exists = array(
						'buyer_id'      => $user_id,
						'prod_id'       => $prod_id,
						'var_id'        => intval($_POST['var_color']),
						'var_type'      => 'color',
					);

					$db             = $db->where('id', intval($_POST['var_color']));
					$db             = $db->where('prod_id', $prod_id);
					$db             = $db->where('var_type', 'color');
					$db             = $db->where('status', 'active');
					$db             = $db->where('activity_status', 'active');
					$db->returnType = 'Array';
					$var_item       = $db->getOne(T_PROD_VARS);

					if(hs_queryset($var_item) != true) {
						$data['err_code'] = 7;
						$data['status']   = 400;
						$data['message']  = "Error: Invalid request data. Please check your details!";
					}

					else if(empty($var_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("Sorry, this product option is out of stock and can not be ordered!");
					}

					else if($quantity > intval($var_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("This product item is not available in the desired quantity. Available quantity is {%qty%}",array(
							'qty'        => $var_item['quantity']
						));
					}

					else if (hs_where_exists(T_ORDERS,$check_order_exists) == true) {
						$data['err_code'] = 8;
						$data['status']   = 400;
						$data['message']  = hs_translate("You have already ordered this product from this seller, so you cannot order it twice from the same seller");
					}

					else {	
						$upsert_item   = hs_basket_upsert(array(
							'user_id'  => $user_id,
							'prod_id'  => $prod_id,
							'var_id'   => intval($_POST['var_color']),
							'quantity' => $quantity,
							'var_type' => 'color',
							'time'     => time(),
						));

						if (in_array($upsert_item, array('update','insert'))) {
							$data['status']   = 200;
							$data['total']    = hs_basket_items_total($user_id);
							$data['message']  = hs_translate("This item has been added to the basket!");
						} 

						else {
							$data['err_code'] = 9;
							$data['status']   = 400;
							$data['message']  = "Error: Something went wrong, please try again later!";
						}
					}
				}

				else if ($prod_item['variation_type'] == 'color_size' && is_number($_POST['var_id'])) {
					$check_order_exists = array(
						'buyer_id'      => $user_id,
						'prod_id'       => $prod_id,
						'var_id'        => intval($_POST['var_id']),
						'var_type'      => 'color_size',
					);

					$db             = $db->where('id', intval($_POST['var_id']));
					$db             = $db->where('prod_id', $prod_id);
					$db             = $db->where('var_type', 'color_size');
					$db             = $db->where('status', 'active');
					$db             = $db->where('activity_status', 'active');
					$db->returnType = 'Array';
					$var_item       = $db->getOne(T_PROD_VARS);

					if(hs_queryset($var_item) != true) {
						$data['err_code'] = 7;
						$data['status']   = 400;
						$data['message']  = "Error: Invalid request data. Please check your details!";
					}

					else if(empty($var_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("Sorry, this product option is out of stock and can not be ordered!");
					}

					else if($quantity > intval($var_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("This product item is not available in the desired quantity. Available quantity is {%qty%}",array(
							'qty'        => $var_item['quantity']
						));
					}

					else if(hs_where_exists(T_ORDERS,$check_order_exists) == true) {
						$data['err_code'] = 11;
						$data['status']   = 400;
						$data['message']  = hs_translate("You have already ordered this product from this seller, so you cannot order it twice from the same seller");
					}

					else {
						$upsert_item   = hs_basket_upsert(array(
							'user_id'  => $user_id,
							'prod_id'  => $prod_id,
							'var_id'   => intval($_POST['var_id']),
							'quantity' => $quantity,
							'var_type' => 'color_size',
							'time'     => time(),
						));

						if (in_array($upsert_item, array('update','insert'))) {
							$data['status']   = 200;
							$data['total']    = hs_basket_items_total($user_id);
							$data['message']  = hs_translate("This item has been added to the basket!");
						} 
						else {
							$data['err_code'] = 12;
							$data['status']   = 400;
							$data['message']  = "Error: Something went wrong, please try again later!";
						}
					}
				}

				else if ($prod_item['variation_type'] == 'size' && is_number($_POST['var_id'])) {
					$check_order_exists = array(
						'buyer_id'      => $user_id,
						'prod_id'       => $prod_id,
						'var_id'        => intval($_POST['var_id']),
						'var_type'      => 'size',
					);

					$db             = $db->where('id', intval($_POST['var_id']));
					$db             = $db->where('prod_id', $prod_id);
					$db             = $db->where('var_type', 'size');
					$db             = $db->where('status', 'active');
					$db             = $db->where('activity_status', 'active');
					$db->returnType = 'Array';
					$var_item       = $db->getOne(T_PROD_VARS);

					if(hs_queryset($var_item) != true) {
						$data['err_code'] = 7;
						$data['status']   = 400;
						$data['message']  = "Error: Invalid request data. Please check your details!";
					}

					else if(empty($var_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("Sorry, this product option is out of stock and can not be ordered!");
					}

					else if($quantity > intval($var_item['quantity'])) {
						$data['status']  = 400;
						$data['message'] = hs_translate("This product item is not available in the desired quantity. Available quantity is {%qty%}",array(
							'qty'        => $var_item['quantity']
						));
					}

					else if(hs_where_exists(T_ORDERS,$check_order_exists) == true) {
						$data['err_code'] = 14;
						$data['status']   = 400;
						$data['message']  = hs_translate("You have already ordered this product from this seller, so you cannot order it twice from the same seller");
					}

					else {
						$upsert_item   = hs_basket_upsert(array(
							'user_id'  => $user_id,
							'prod_id'  => $prod_id,
							'var_id'   => intval($_POST['var_id']),
							'quantity' => $quantity,
							'var_type' => 'size',
							'time'     => time(),
						));

						if (in_array($upsert_item, array('update','insert'))) {
							$data['status']   = 200;
							$data['total']    = hs_basket_items_total($user_id);
							$data['message']  = hs_translate("This item has been added to the basket");
						} 

						else {
							$data['err_code'] = 15;
							$data['status']   = 400;
							$data['message']  = "Error: Something went wrong, please try again later!";
						}
					}
				}
			}
		}
		else {
			$data['status']  = 400;
			$data['message'] = "Error: The product item with such id does not exists!";
		}
	}
}

else if ($action == 'rm_bitem') {
	if (not_num($_POST['item_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	} 
	else {
		$user_id   = $me['id'];
		$item_id   = intval($_POST['item_id']);
		$db        = $db->where('id',$item_id);
		$db        = $db->where('user_id',$user_id);
		$bskt_item = $db->getOne(T_BASKET);

		if (hs_queryset($bskt_item,'object') == true) {			
			$data['status']       = 200;	
			$remove_item          = hs_delete_basket_items($user_id,array($item_id));
			$data['code']         = (empty($remove_item)) ? 0 : 1;
			$data['tl_items']     = hs_basket_items_total($user_id);
			$data['tl_items_num'] = ((is_number($data['tl_items'])) ? intval($data['tl_items']) : 0);
			$data['message']      = hs_translate("This item was successfully deleted from your card");	
			
		} 
		else {
			$data['err_code'] = 2;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Please check your details";
		}
	}
}

else if ($action == 'rm_bitems') {
	if (empty($_POST['items']) || is_array($_POST['items']) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please sdscheck your details";
	} 
	else if(hs_all($_POST['items'],'numeric') != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}
	else {
		$user_id              = $me['id'];
		$data['status']       = 200;
		$item_ids             = array_values($_POST['items']);
		$remove_item          = hs_delete_basket_items($user_id,$item_ids);
		$data['code']         = (empty($remove_item)) ? 0 : 1;		
		$data['tl_items']     = hs_basket_items_total($user_id);
		$data['tl_items_num'] = ((is_number($data['tl_items'])) ? intval($data['tl_items']) : 0);
		$data['message']      = hs_translate("The items was successfully deleted from your card");
	}
}

else if ($action == 'get_bo_summary') {
	if (empty($_POST['items']) || is_array($_POST['items']) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please sdscheck your details";
	} 
	else if(hs_all($_POST['items'],'numeric') != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Please check your details";
	}
	else {	
		$user_id          = $me['id'];
		$item_ids         = array_values($_POST['items']);
		$db               = $db->where('user_id',$user_id);
		$db               = $db->where('id',$item_ids,"NOT IN");
		$set_inactive     = $db->update(T_BASKET,array('status' => 'inactive'));
		$db               = $db->where('user_id',$user_id);
		$db               = $db->where('id',$item_ids,"IN");
		$set_active       = $db->update(T_BASKET,array('status' => 'active'));
		$summary_info     = hs_get_order_summary($user_id,$item_ids);
		$data['status']   = 200;
		$data['info']     = $summary_info;
	}
}

else if ($action == 'reset_bo_summary') {
	if(empty($hs['is_logged'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: This action requires authentication!";
	}
	else {	
		$user_id      = $me['id'];
		$db           = $db->where('user_id',$user_id);
		$set_inactive = $db->update(T_BASKET,array('status' => 'inactive'));
	}
}

else if($action == 'items_checkout') {
	if (empty($_POST['payment_method']) || in_array($_POST['payment_method'], array('card','qiwi','yandmoney','paypal','wallet','cod')) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Payment method is missing or invalid";
	}
	elseif (hs_is_valid_address($me['address']) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = hs_translate("In order to place an order, you first need to add or choose from the list, delivery address");
	}
	else {
		$payment_method  = strval($_POST['payment_method']);
		$user_id         = $me['id'];
		$items_data      = hs_get_order_items_data($user_id);
		$line_items      = array();
		$prepaym_prods   = 0;
		$codpaym_prods   = 0;
		$prepaym_methods = array('card','paypal','qiwi','yandmoney','wallet'); 

		if (not_empty($items_data)) {

			foreach ($items_data as $row) {
				if ($row['payment_method'] == 'pre_payments') {
					$prepaym_prods += 1;
				}

				else if($row['payment_method'] == 'cod_payments') {
					$codpaym_prods += 1;
				}
			}

			if ((in_array($payment_method, $prepaym_methods) && not_empty($codpaym_prods)) || ($payment_method == 'cod' && not_empty($prepaym_prods))) {
				$data['status']   = 400;
				$data['message']  = "You have selected products that are not available for payment by your chosen payment method!";
			}

			else {
				if ($payment_method == 'card' && not_empty($items_data)) {
					if ($hs['config']['stripe_gateway_status'] == 'on') {
						foreach ($items_data as $item_row) {
							$price_amount     =  (($item_row['sale_price'] * $item_row['qt']) * 100);
							$price_amount     =  ($price_amount + (($item_row['ship_cost'] == 'paid') ? (floatval($item_row['ship_fee']) * 100) : 0));
							$line_items[]     =  array(
								'name'        => $item_row['prod_name'],
								'amount'      => $price_amount,
								'currency'    => $config['market_currency'],
								'quantity'    => $item_row['qt'],
							);
						}

						try{
							$stripe_session            = \Stripe\Checkout\Session::create(array(
							    'payment_method_types' => array('card'),
							    'line_items'           => $line_items,
								'success_url'          => hs_link('paid/card/success'),
								'cancel_url'           => hs_link('paid/card/cancel'),
							));
					
							if (not_empty($stripe_session) && hs_queryset($stripe_session,'object')) {
								$data['status']  = 200;
								$data['sess_id'] = $stripe_session['id'];
								$data['message'] = hs_translate("The payment was successfully created. Please wait..");
								hs_session('payment_data',array('stripe_payment_session_id' => $stripe_session['id']));
							} 

							else{
								$data['status']  = 400;
								$data['message'] = "An error found while processing your request. Please try again later!";
							}
						} 

						catch (Exception $e) {
							$data['err_code'] = 3;
							$data['status']   = 500;
							$data['message']  = $e->getMessage();
						}
					}

					else {
						$data['status']  = 400;
						$data['message'] = "Error: This payment method is not available yet.";
					}
				}

				else if($payment_method == 'paypal') {
					if ($hs['config']['paypal_gateway_status'] == 'on') {
						try {
							$currency      = strtoupper($config['market_currency']);
							$payer         = new \PayPal\Api\Payer();
							$itemList      = new \PayPal\Api\ItemList();
							$details       = new \PayPal\Api\Details();
							$amount        = new \PayPal\Api\Amount();
							$transaction   = new \PayPal\Api\Transaction();
							$redirectUrls  = new \PayPal\Api\RedirectUrls();
							$payment       = new \PayPal\Api\Payment();
							$inputFields   = new \PayPal\Api\InputFields();
							$webProfile    = new \PayPal\Api\WebProfile();

							$payer         = $payer->setPaymentMethod('paypal');
							$subtotal      = 0;
							$items_line    = array();

							$inputFields   = $inputFields->setAllowNote(true);
							$inputFields   = $inputFields->setNoShipping(1);
							$inputFields   = $inputFields->setAddressOverride(0);
							
							$webProfile    = $webProfile->setName(uniqid());
					        $webProfile    = $webProfile->setInputFields($inputFields);
					        $webProfile    = $webProfile->setTemporary(true);

							$createProfile = $webProfile->create($paypal);
							$profileID     = $createProfile->getId();
							$payment       = $payment->setExperienceProfileId($profileID); 

							#Set redirect URLS
							$redirectUrls  = $redirectUrls->setReturnUrl(hs_link('paid/paypal/success'));
							$redirectUrls  = $redirectUrls->setCancelUrl(hs_link('')); 

							foreach ($items_data as $item_row) {
								$sale_price   = floatval($item_row['sale_price']);
								$sale_price   = ($sale_price + (($item_row['ship_cost'] == 'paid') ? floatval($item_row['ship_fee']) : 0));
								$line_item    = new \PayPal\Api\Item();
								$line_item    = $line_item->setName($item_row['prod_name']);
								$line_item    = $line_item->setQuantity($item_row['qt']);
								$line_item    = $line_item->setPrice($sale_price);
								$line_item    = $line_item->setCurrency($currency);
								$items_line[] = $line_item;
								$subtotal    += ($item_row['sale_price'] * $item_row['qt']);

								if ($item_row['ship_cost'] == 'paid') {
									$subtotal += floatval($item_row['ship_fee']);
								}
							}

							#Set items to be purchased
							$itemList    = $itemList->setItems($items_line); 
							$details     = $details->setSubtotal($subtotal);

							#Set amount
							$amount      = $amount->setCurrency($currency);
							$amount      = $amount->setTotal($subtotal);
							$amount      = $amount->setDetails($details);
							$transaction = $transaction->setAmount($amount);
							$transaction = $transaction->setItemList($itemList);
							$transaction = $transaction->setDescription('Pay HighExpress');
							$transaction = $transaction->setInvoiceNumber(time());
							$payment     = $payment->setIntent('sale');
							$payment     = $payment->setPayer($payer);
							$payment     = $payment->setRedirectUrls($redirectUrls);
							$payment     = $payment->setTransactions(array($transaction));

						    try {
						        $payment->create($paypal);
						        $data['status']  = 200;
						        $data['url']     = $payment->getApprovalLink();
						        $data['message'] = hs_translate("Redirection to the Paypal, please wait...");
						    }

						    catch (Exception $e) {
								$data['status']  = 400;
								$data['message'] = "An error found while processing your request. Please try again later!";
						    }
						} 

						catch (Exception $e) {
							$data['status']  = 400;
							$data['message'] = "An error found while processing your request. Please try again later!";
						}
					}

					else {
						$data['status']  = 400;
						$data['message'] = "Error: This payment method is not available yet.";
					}
				}

				else if($payment_method == 'qiwi') {
					$data['err_code'] = 1;
					$data['status']   = 400;
					$data['message']  = "Error: This payment method is not available yet.";
				}

				else if($payment_method == 'yandmoney') {
					$data['err_code'] = 1;
					$data['status']   = 400;
					$data['message']  = "Error: This payment method is not available yet.";
				}

				else if($payment_method == 'wallet') {
					if ($hs['config']['wallet_gateway_status'] == 'on') {
						$password = ((not_empty($_POST['pass'])) ? hs_secure($_POST['pass']) : '');

						if (password_verify($password, $me['password'])) {
							$subtotal  = 0;
							$my_id     = $me['id'];
							$my_wallet = $me['wallet_val'];

							foreach ($items_data as $item_row) {
								$subtotal += ($item_row['sale_price'] * $item_row['qt']);

								if ($item_row['ship_cost'] == 'paid') {
									$subtotal += floatval($item_row['ship_fee']);
								}
							}

							if ($subtotal <= $my_wallet) {
								$payment_id     = sha1(rand(11111, 99999)) . time() . md5(microtime() . $user_id . time() . md5(time()));
								$data['status'] = 200;
								$data['url']    = hs_link(sprintf("paid/wallet/success?payment_id=%s",$payment_id));
								$db             = $db->where('id',$my_id);
								$db             = $db->update(T_USERS,array(
									'wallet'    => ($me['wallet_val'] - $subtotal)
								));	hs_session('payment_data',array('user_wallet_payment_id' => $payment_id));
							}
							else {
								$data['status']  = 400;
								$data['message'] = hs_translate("Your account does not have sufficient funds to pay for goods!");
							}
						}
						else {
							$data['status']   = 400;
							$data['message']  = hs_translate("The password you entered is not valid. Please check your details");
						}
					}

					else {
						$data['status']  = 400;
						$data['message'] = "Error: This payment method is not available yet.";
					}
				}

				else if($payment_method == 'cod') {
					if ($hs['config']['cod_gateway_status'] == 'on') {
						$user_id        = $me['id'];
						$payment_id     = sha1(rand(11111, 99999)) . time() . md5(microtime() . $user_id . time() . md5(time()));
						$data['status'] = 200;
						$data['url']    = hs_link(sprintf("paid/cod/success?payment_id=%s",$payment_id));

						hs_session('payment_data',array(
							'user_cod_payment_id' => $payment_id
						));
					}

					else {
						$data['status']  = 400;
						$data['message'] = "Error: This payment method is not available yet.";
					}
				}
			}
		}
		else {
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Your basket is empty!";
		}
	}
}

else if ($action == 'var_info') {
	if (not_num($_GET['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product ID is missing or Invalid";
	} 

	else if (hs_is_product_presentable(intval($_GET['prod_id'])) != true) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product with such ID does not exists or not active!";
	} 

	else if (not_num($_GET['quantity'])) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Quantity value is not valid";
	} 

	else if (intval($_GET['quantity']) > 20) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Quantity value is too high";
	}

	elseif (not_num($_GET['var_id'])) {
		$data['err_code'] = 4;
		$data['status']   = 400;
		$data['message']  = "Error: Variant ID is not valid or missing";
	} 
	
	else {
		$prod_id   = intval($_GET['prod_id']);
		$quantity  = intval($_GET['quantity']);
		$var_id    = intval($_GET['var_id']);
		$user_id   = $me['id'];
		$db        = $db->where('id',$prod_id);
		$prod_item = $db->getOne(T_PRODUCTS);
		$prod_item = ((hs_queryset($prod_item,'object')) ? hs_o2array($prod_item) : array());

		if (not_empty($prod_item)) {
			if ($prod_item['variation_type'] == 'color') {
				$db       = $db->where('id',$var_id);
				$db       = $db->where('activity_status','active');
				$db       = $db->where('prod_id',$prod_id);
				$db       = $db->where('var_type','color');
				$var_item = $db->getOne(T_PROD_VARS);

				if (hs_queryset($var_item,'object') == true) {
					$var_item       = hs_o2array($var_item);
					$data['sp']     = hs_money($var_item['sale_price'] * $quantity);
					$data['rp']     = hs_money($var_item['reg_price'] * $quantity);	
					$data['disc']   = hs_price_discount($var_item['reg_price'],$var_item['sale_price']);	
					$data['status'] = 200;
				}
				else {
					$data['err_code'] = 5;
					$data['status']   = 400;
					$data['message']  = "Error: Invalid request data. Please check your details!";
				}
			}

			if ($prod_item['variation_type'] == 'color_size') {
				$db       = $db->where('id',$var_id);
				$db       = $db->where('prod_id',$prod_id);
				$db       = $db->where('activity_status','active');
				$db       = $db->where('var_type','color_size');
				$var_item = $db->getOne(T_PROD_VARS);

				if (hs_queryset($var_item,'object') == true) {
					$var_item       = hs_o2array($var_item);
					$data['sp']     = hs_money($var_item['sale_price'] * $quantity);
					$data['rp']     = hs_money($var_item['reg_price'] * $quantity);	
					$data['disc']   = hs_price_discount($var_item['reg_price'],$var_item['sale_price']);
					$data['status'] = 200;
				}
				else {
					$data['err_code'] = 5;
					$data['status']   = 400;
					$data['message']  = "Error: Invalid request data. Please check your details!";
				}
			}

			if ($prod_item['variation_type'] == 'size') {
				$db       = $db->where('id',$var_id);
				$db       = $db->where('prod_id',$prod_id);
				$db       = $db->where('activity_status','active');
				$db       = $db->where('var_type','size');
				$var_item = $db->getOne(T_PROD_VARS);

				if (hs_queryset($var_item,'object') == true) {
					$var_item       = hs_o2array($var_item);
					$data['sp']     = hs_money($var_item['sale_price'] * $quantity);
					$data['rp']     = hs_money($var_item['reg_price'] * $quantity);	
					$data['disc']   = hs_price_discount($var_item['reg_price'],$var_item['sale_price']);
					$data['status'] = 200;
				}
				else {
					$data['err_code'] = 5;
					$data['status']   = 400;
					$data['message']  = "Error: Invalid request data. Please check your details!";
				}
			}
		}
	}
}

else if ($action == 'get_csv_svars') {
	if (not_num($_GET['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product id is missing";
	} 

	else if (empty($_GET['color_hex']) || in_array($_GET['color_hex'], array_keys($hs['color_types'])) != true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Color hex code is invalid";
	} 

	else {
		$prod_id   = intval($_GET['prod_id']);
		$col_hex   = strval($_GET['color_hex']);
		$db        = $db->where('prod_id',$prod_id);
		$db        = $db->where('col_hex',$col_hex);
		$db        = $db->where('col_hex',$col_hex);
		$db        = $db->where('var_type','color_size');
		$var_ites  = $db->get(T_PROD_VARS,20,array('id','size'));

		if (hs_queryset($var_ites) == true) {
			$var_ites = hs_o2array($var_ites);
			$html_arr = array();

			foreach ($var_ites as $var_item) {
				array_push($html_arr,hs_loadpage('product/includes/temps/var_both/size_unit',array(
					'id'   => $var_item['id'],
					'size' => $var_item['size'],
				)));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		} 
		else {
			$data['err_code'] = 3;
			$data['status']   = 400;
			$data['message']  = "Error: Something went wrong, please try again later!";
		}
	}
}

else if($action == 'catalog_items') {
	if(not_empty($_GET['catg_id']) && in_array($_GET['catg_id'], array_keys($hs['categories']))) {
		$fiter_data            =  array(
			'limit'            => 20,
			'offset'           => fetch_or_get($_GET['offset'],0),
			'catg_id'          => fetch_or_get($_GET['catg_id'],'none'),
			'seller_countries' => null
		);

		if (not_empty($_GET['brand']) && len_between($_GET['brand'],2,50)) {
			$fiter_data['brand'] = hs_secure($_GET['brand']);
		}

		if (not_empty($_GET['keyword']) && len_between($_GET['keyword'],3,50)) {
			$fiter_data['keyword'] = hs_secure($_GET['keyword']);
		}

		if (not_empty($_GET['ship_cost']) && in_array($_GET['ship_cost'], array('paid','free'))) {
			$fiter_data['ship_cost'] = strval($_GET['ship_cost']);
		}

		if (not_empty($_GET['sortby']) && in_array($_GET['sortby'], array('price_up','price_down','newest','rating','sales'))) {
			$fiter_data['sortby'] = strval($_GET['sortby']);
		}

		if (not_empty($_GET['condition']) && in_array($_GET['condition'], array('1','2','3'))) {
			$fiter_data['condition'] = strval($_GET['condition']);
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
		$hs['prod_items'] = hs_get_catalog_items($fiter_data);
		$html_arr         = array();

		if (not_empty($hs['prod_items'])) {
			foreach ($hs['prod_items'] as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('catalog/includes/prod_item'));
			}
			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);	
		}

		else if(empty($hs['prod_items']) && empty($fiter_data['offset'])) {
			$data['html'] = hs_loadpage('catalog/includes/filter_404');
		}
	}
}

else if($action == 'write_review') {
	if (not_num($_POST['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product ID is missing";
	}

	else if(hs_is_prodowner($me['id'],intval($_POST['prod_id'])) == true) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "You cant not review your own product!";
	}

	else {
		$prod_id              = intval($_POST['prod_id']);	
		$user_purchase_exists = array(
			'buyer_id'        => $me['id'],
			'prod_id'         => $prod_id
		);
	
		if (hs_is_product_presentable($prod_id) != true) {
			$data['err_code'] = 4;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Product with such ID does not exists or not active!";
		}

		else if(hs_where_exists(T_ORDERS,$user_purchase_exists) != true) {
			$data['err_code'] = 4;
			$data['status']   = 400;
			$data['message']  = "In order to leave your feedback about this product you must first order and pay for it";
		}
		else {
			$db             = $db->where('user_id',$me['id']);
			$db             = $db->where('prod_id',$prod_id);
			$db             = $db->where('activity_status','orphan');
			$orphan_reviews = $db->get(T_PROD_RATINGS);
			if (hs_queryset($orphan_reviews)) {
				foreach ($orphan_reviews as $old_rev) {
					hs_delete_prod_review($old_rev->id);
				}
			}

			$insert_data          = array(
				'user_id'         => $me['id'],
				'prod_id'         => $prod_id,
				'activity_status' => 'orphan',
				'time'            => time(),
			); $insert_id         = $db->insert(T_PROD_RATINGS,$insert_data);

			
			if (is_number($insert_id)) {
				$data['status'] = 200;
				$data['review'] = $insert_id;

				hs_session('review_id',$insert_id);
			}
			else {
				$data['err_code'] = 4;
				$data['status']   = 500;
				$data['message']  = "An error found while processing your request. Please try again later!";
			}  
		}
	}
}

else if($action == 'save_review') {
	$data['err_code'] = 0;
	$review_id        = hs_session('review_id');
	if (not_num($_POST['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product ID is missing";
	}

	else if (not_num($review_id)) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Review ID is missing";
	}

	else if(not_num($_POST['valuation']) || int_between($_POST['valuation'],1,5) != true) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = hs_translate("No rating selected. Please select a review rating");
	}

	else if(not_empty($_POST['review']) && len($_POST['review']) < 2) {
		$data['err_code'] = 4;
		$data['status']   = 400;
		$data['message']  = hs_translate("The review you wrote is too short");
	}

	else if(not_empty($_POST['review']) && len($_POST['review']) > 1000) {
		$data['err_code'] = 5;
		$data['status']   = 400;
		$data['message']  = hs_translate("The review you wrote is too long");
	}

	else if(hs_is_prodowner($me['id'],intval($_POST['prod_id'])) == true) {
		$data['err_code'] = 6;
		$data['status']   = 400;
		$data['message']  = "You cant not review your own product.";
	}

	else if(hs_is_product_presentable(intval($_POST['prod_id'])) != true) {
		$data['err_code'] = 7;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product with such ID does not exists or not active!";
	}

	else {
		$prod_id              = intval($_POST['prod_id']);
		$ophan_review_exists  = array(
			'id'              => $review_id,
			'user_id'         => $me['id'],
			'prod_id'         => $prod_id,
			'activity_status' => 'orphan',
		);

		if (hs_where_exists(T_PROD_RATINGS,$ophan_review_exists) != true) {
			$data['err_code'] = 7;
			$data['status']   = 400;
			$data['message']  = "Error: Product review with sush id does not exists!";
		}

		else {
			$review_text          = ((not_empty($_POST['review'])) ? hs_secure($_POST['review']) : "");
			$update_data          = array(
				'valuation'       => intval($_POST['valuation']),
				'review'          => $review_text,
				'activity_status' => 'active',
				'time'            => time(),
			);
			$db                    = $db->where('id',$review_id);
			$update                = $db->update(T_PROD_RATINGS,$update_data);
			if ($update) {	
				$data['review']    = $review_id;
				$html_arr          = array();
				$revs              = hs_get_prod_reviews(array(
					'prod_id'      => $prod_id,
					'by_ids'       => array($review_id)
				));

				foreach ($revs as $hs['review_data']) {
					array_push($html_arr, hs_loadpage("product/includes/list_items/review_li"));
				}

				$data['status'] = 200;
				$data['html']   = implode('', $html_arr);

				#Delete previous sent review
				$db = $db->where('id',$review_id,'<>');
				$db = $db->where('user_id',$me['id']);
				$db = $db->where('prod_id',$prod_id);
				$pr = $db->getOne(T_PROD_RATINGS);
				if (hs_queryset($pr,'object')) {
					$data['pr'] = $pr->id;
					$db->where('id',$pr->id)->delete(T_PROD_RATINGS);
				}

				#Update prod review 
				$prod_rating   = hs_get_prod_rating($prod_id);
				$prod_rating   = number_format($prod_rating, 1, '.', ' ');
				$update_rating = $db->where('id',$prod_id)->update(T_PRODUCTS,array('rating' => $prod_rating));

				#Notify product owner about customer's feedback
				$db              = $db->where('id',$prod_id);
				$db->returnType  = 'Array';
				$prod_data       = $db->getOne(T_PRODUCTS);

				if (not_empty($prod_data)) {
					hs_notify(array(
						'notifier_id'  => $me['id'],
						'recipient_id' => $prod_data['user_id'],
						'subject'      => 'product_review',
						'message'      => 'has reviewed your product',
						'status'       => '0',
						'time'         => time(),
						'url'          => hs_link(sprintf('product/%d',$prod_id))
					),false);
				}
			}
			else {
				$data['err_code'] = 8;
				$data['status']   = 500;
				$data['message']  = "An error found while processing your request. Please try again later!";
			}  
		}
	}
}

else if($action == 'attach_review_image') {
	$data['err_code'] = 0;	
	$review_id        = hs_session('review_id');
    if (empty($_FILES['image']) || not_num($review_id)) {
        $data['message']  = "Error: Invalid request data. Please try again later!";
        $data['err_code'] = 1;
        $data['status']   = 400;
    }

    if (empty($data['err_code'])) {
    	$db                   =  $db->where('review_id',$review_id);
    	$db                   =  $db->where('user_id',$me['id']);
    	$max                  =  $db->getValue(T_PROD_RATING_MEDIA,'COUNT(*)');
    	$review_owner         =  array(
    		'id'              => $review_id,
    		'user_id'         => $me['id'],
    		'activity_status' => 'orphan'
    	);

    	if ($max < 5) {
	        if (not_empty($_FILES['image']['tmp_name']) && hs_where_exists(T_PROD_RATINGS,$review_owner)) {
	            $file_info    = array(
	                'file'    => $_FILES['image']['tmp_name'],
	                'size'    => $_FILES['image']['size'],
	                'name'    => $_FILES['image']['name'],
	                'type'    => $_FILES['image']['type'],
	                'crop'    => array('width' => 600, 'height' => 600),
	                'allowed' => 'jpg,png,jpeg,gif'
	            );

	            $file_upload = hs_upload($file_info);
	            if (not_empty($file_upload['filename'])) {
	            	$insert_data    =  array(
	            		'user_id'   => $me['id'],
	            		'review_id' => $review_id,
	            		'file_path' => $file_upload['filename'],
	            		'time'      => time(),
	            	);

	                $rev_media_id = $db->insert(T_PROD_RATING_MEDIA, $insert_data);

	                if (is_number($rev_media_id)) {
	                	$hs['rev_media'] = array(
	                		'src' => hs_get_media($file_upload['filename']),
	                		'id' => $rev_media_id,
	                	);

	                	$data['status']  = 200;
		                $data['html']    = hs_loadpage('product/includes/list_items/review_img_li');
		                $data['url']     = hs_get_media($file_upload['filename']);

		            }
	                else {
	                	$data['status']   = 500;
	                	$data['err_code'] = 2;
	            		$data['message']  = "Error found while processing your request, please try again later.";
	                }
	            } 
	            else{
	            	$data['status']   = 500;
	            	$data['err_code'] = 3;
	            	$data['message']  = "Error found while processing your request, please try again later.";
	            }
	        }
        }
        else {
        	$data['status']   = 400;
	        $data['err_code'] = 4;
	        $data['message']  = hs_translate("You cannot attach more than 5 images to this review.");
        }
    }
}

else if($action == 'delete_review') {
	$data['err_code'] = 0;	
	if (not_num($_POST['review_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Review id is missing";
	}
	else {
		$review_id            = intval($_POST['review_id']);
		$review_owner         = array(
    		'id'              => $review_id,
    		'user_id'         => $me['id'],
    		'activity_status' => 'active'
    	);

    	if (hs_where_exists(T_PROD_RATINGS,$review_owner)) {
    		$data['status']  = 200; hs_delete_prod_review($review_id);
    	}
    	else {
    		$data['err_code'] = 1;
			$data['status']   = 400;
			$data['message']  = "Error: Product review with sush id does not exists!";
    	}
	}
}

else if($action == 'delete_review_image') {
	$data['err_code'] = 0;	
	if (not_num($_POST['img_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Image id is missing";
	}
	else {
		$image_id   = intval($_POST['img_id']);
		$image_data = $db->where('id',$image_id)->getOne(T_PROD_RATING_MEDIA);

    	if (hs_queryset($image_data,'object')) {
    		if ($image_data->user_id == $me['id']) {
    			$del_file       = hs_delete_image($image_data->file_path);
    			$del_img        = $db->where('id',$image_id)->delete(T_PROD_RATING_MEDIA);
    			$data['status'] = 200;
    		}
    	}
    	else {
    		$data['err_code'] = 2;
			$data['status']   = 400;
			$data['message']  = "Error: Review  image with sush id does not exists!";
    	}
	}
}
 
else if($action == 'load_more_reviews') {
	$data['err_code'] = 0;
	if (not_num($_POST['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product id is missing";
	}

	else if(not_num($_POST['offset'])) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Offset id is missing";
	}

	else {
		$prod_id      =  intval($_POST['prod_id']);
		$offset       =  intval($_POST['offset']);
		$sortby       =  (empty($_POST['sortby']) || in_array($_POST['sortby'], array(1,2,3,4,5)) ? hs_secure($_POST['sortby']) : null);
		$html         =  array();
		$reviews      =  hs_get_prod_reviews(array(
			'prod_id' => $prod_id,
			'offset'  => $offset,
			'sortby'  => $sortby,
			'limit'   => 10,
		));

		if (not_empty($reviews)) {
			foreach ($reviews as $hs['review_data']) {
				array_push($html, hs_loadpage("product/includes/list_items/review_li"));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html);
		}
		else {
			$data['status']  = 404;
			$data['message'] = hs_translate("You have reached the end of this list");
		}
	}
}
 
else if($action == 'load_reviews') {
	$data['err_code'] = 0;
	if (not_num($_POST['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product id is missing";
	}

	else {
		$prod_id = intval($_POST['prod_id']);
		$sortby  = (empty($_POST['sortby']) || in_array($_POST['sortby'], array(1,2,3,4,5)) ? hs_secure($_POST['sortby']) : null);
		$html    = array();
		$reviews = hs_get_prod_reviews(array(
			'prod_id' => $prod_id,
			'sortby' => $sortby,
			'limit' => 10,
		));

		if (not_empty($reviews)) {
			foreach ($reviews as $hs['review_data']) {
				array_push($html, hs_loadpage("product/includes/list_items/review_li"));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html);
		}
		else {
			$data['status']  = 404;
			$data['message'] = hs_translate('Nothing found');
		}
	}
}

else if($action == 'report_product') {
	$data['err_code'] = 0;
	if (not_num($_POST['prod_id'])) {
		$data['err_code'] = 1;
		$data['status']   = 400;
		$data['message']  = "Error: Invalid request data. Product id is missing";
	}
	else if (empty($_POST['report'])) {
		$data['err_code'] = 2;
		$data['status']   = 400;
		$data['message']  = hs_translate("There is no report description. Please describe the reason of your report.");
	}
	else if (len($_POST['report']) < 32) {
		$data['err_code'] = 3;
		$data['status']   = 400;
		$data['message']  = hs_translate("The description is too short. Please check your details.");
	}
	else if (len($_POST['report']) > 1200) {
		$data['err_code'] = 4;
		$data['status']   = 400;
		$data['message']  = hs_translate("The report you wrote is too long. Maximum report length should be no more than 1200 characters");
	}
	else {
		$product_id           = intval($_POST['prod_id']);
		$has_reported         = array('user_id' => $me['id'],'prod_id' => $product_id);
		$report_text          = hs_secure($_POST['report']);
		
		if (hs_is_product_presentable($product_id) != true) {
			$data['err_code'] = 5;
			$data['status']   = 400;
			$data['message']  = "Error: Invalid request data. Product with such id does not exists";
		}
		else {
			#Delete previous sent report
			if (hs_where_exists(T_PROD_REPORTS,$has_reported)) {
				$db = $db->where('prod_id',$product_id);
				$db = $db->where('user_id',$me['id']);
				$rm = $db->delete(T_PROD_REPORTS);
			}

			$insert_data  = array(
				'user_id' => $me['id'],
				'prod_id' => $product_id,
				'report'  => $report_text,
				'time'    => time()
			);

			$insert_id = $db->insert(T_PROD_REPORTS,$insert_data);
			if (is_number($insert_id)) {
				$data['status']   = 200;
				$data['message']  = hs_translate('Thank you for your report. We have received your feedback, and will soon be reviewing it.');
			}
			else {
				$data['err_code'] = 7;
				$data['status']   = 500;
				$data['message']  = "Error found while processing your request, please try again later.";
			}
		}
	}
}

else if($action == 'load_popular_items' && is_number($_GET['offset'])) {
	$offset           =  fetch_or_get($_GET['offset'],0);
	$offset           =  ((is_number($offset)) ? $offset : 0);
	$data['status']   =  404;
	$html_arr         =  array();
	$hs['prod_items'] =  hs_get_products(array(
		'limit'       => 100,
		'approved'    => true,
		'offset'      => $offset,
		'nf_dgt'      => 0,
	));
	
	if (not_empty($hs['prod_items'])) {
		foreach ($hs['prod_items'] as $hs['prod_item']) {
			array_push($html_arr, hs_loadpage('home/includes/prod_item'));
		}
		
		$data['status'] = 200;
		$data['html']   = implode('', $html_arr);	
	}
}

else if($action == 'search_prods' && not_empty($_GET['keyword'])) {
	$keyword          = fetch_or_get($_GET['keyword'],'');
	$keyword          = hs_secure($keyword);
	$keyword          = ((len_between($keyword,1,60)) ? $keyword : false);
	$data['status']   = 404;
	$html_arr         = array();
	$hs['prod_items'] = hs_search_products(array(
		'limit'       => 100,
		'keyword'     => $keyword,
	));

	if (not_empty($hs['prod_items'])) {
		$data['status'] = 200;
		$data['result'] = $hs['prod_items'];	
	}
}
?>