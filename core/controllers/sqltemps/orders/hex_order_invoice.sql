/*@*************************************************************************@
//@ @author Mansur Altamirov (Mansur_TL)									@
//@ @author_url 1: https://www.instagram.com/mansur_tl                      @
//@ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
//@ @author_email: highexpresstore@gmail.com                                @
//@*************************************************************************@
//@ HighExpress - The Ultimate Modern Marketplace Platform                  @
//@ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
//@*************************************************************************@*/

SELECT 

	o.`id`, 
	o.`seller_id`, 
	o.`buyer_id`, 
	o.`prod_id`, 
	o.`var_id`, 
	o.`var_type`, 
	o.`quantity`,
	o.`cancellation_time`,

	o.`prod_sc` as shipping_cost,
	o.`prod_sf` as shipping_fee,
	o.`prod_rp` as reg_price,
	o.`prod_sp` as sale_price,
	o.`paid_amount` as paid_amount,

	{@ Payment transaction @}
	
	t.`method` as payment_method,
	
	{@ Delivery address @}

	o.`cust_name`,
	CONCAT(o.`cust_country`, ' ', o.`cust_state`) as cust_origin,
	CONCAT(o.`cust_city`, ' ', o.`cust_zip`) as cust_city,
	CONCAT(o.`cust_street`, ' ', o.`cust_off_apt`) as cust_addr,

	o.`status`, 
	o.`cust_email`, 
	o.`time` as placed_date, 

	{@ Customer contacts @}

	CONCAT(u.`fname`,' ', u.`lname`) as seller_name,
	u.`email` as seller_email,
	u.`phone` as seller_phone,
	u.`username` as seller_username,
	CONCAT(u.`fname`,'_', u.`lname`) as seller_profile,

	{@ Product info @}
	p.`name` as prod_name,
	p.`poster` as prod_poster,
	p.`model_number` as prod_model,
	p.`shipping_time` as shipping_time,


	{@ Product var info @}
	v.`col_hex` AS var_color,
	v.`size` AS var_size,

	CASE 
		WHEN v.`id` IS NULL 

			THEN p.`sku` 

		ELSE v.`sku`
	END AS sku

	FROM `{%t_orders%}` o 

	INNER JOIN `{%t_users%}` u ON o.`seller_id` = u.`id`

	INNER JOIN `{%t_prods%}` p ON o.`prod_id` = p.`id`

	INNER JOIN `{%t_couts%}` t ON t.`order_id` = o.`id`

	LEFT OUTER JOIN `{%t_pvars%}` v ON o.`var_id` = v.`id`

	WHERE o.`id` = {%order_id%} AND o.`buyer_id` = {%buyer_id%}

	AND p.`activity_status` = 'active'
	
	AND p.`status` IN ('active','inactive','blocked')

	AND p.`approved` = 'Y'

	AND p.`editing_stage` = 'saved'

LIMIT 1