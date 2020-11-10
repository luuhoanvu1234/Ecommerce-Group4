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
	prod.`id`,
	prod.`poster`, 
	prod.`thumb`, 
	prod.`name`,
	prod.`reg_price`,
	prod.`sale_price`,
	prod.`sku`,
	prod.`sold` as sales,
	prod.`activity_status`,
	prod.`profit`,
	prod.`variation_type`,
	prod.`editing_stage`,
	prod.`approved`,
	prod.`status`,

	CASE 
	
		WHEN prod.`variation_type` IN ('color','size','color_size') 

			THEN (SELECT SUM(var.`quantity`) FROM `{%t_pvars%}` var WHERE var.`prod_id` = prod.`id` AND var.`status` = 'active'  AND var.`activity_status` = 'active')

		ELSE prod.`quantity`

	END AS quantity

	FROM `{%t_prods%}` prod
	
	WHERE prod.`activity_status` = 'active'

	AND prod.`user_id` = {%user_id%}

	AND prod.`editing_stage` = 'saved'

	{%if prod_status%}

		{%if prod_status == 'active'%}
			AND prod.`status` = 'active'
		{%endif%}

		{%if prod_status == 'inactive'%}
			AND prod.`status` = 'inactive'
		{%endif%}

		{%if prod_status == 'blocked'%}
			AND prod.`status` = 'blocked'
		{%endif%}

	{%endif%}

	{%if var_type%}

		{%if var_type == 'single'%}
			AND prod.`variation_type` = 'single'
		{%endif%}

		{%if var_type == 'color'%}
			AND prod.`variation_type` = 'color'
		{%endif%}

		{%if var_type == 'size'%}
			AND prod.`variation_type` = 'size'
		{%endif%}
		
		{%if var_type == 'color_size'%}
			AND prod.`variation_type` = 'color_size'
		{%endif%}

	{%endif%}

	{%if sku%}
		AND prod.`sku` = '{%sku%}'
	{%endif%}

	{%if payment_method%}

		{%if payment_method == 'all_payments'%}
			AND prod.`payment_method` = 'all_payments'
		{%endif%}

		{%if payment_method == 'cod_payments'%}
			AND prod.`payment_method` = 'cod_payments'
		{%endif%}

		{%if payment_method == 'pre_payments'%}
			AND prod.`payment_method` = 'pre_payments'
		{%endif%}

	{%endif%}

	{%if approval_status%}

		{%if approval_status == 'approved'%}
			AND prod.`approved` = 'Y'
		{%endif%}

		{%if approval_status == 'not_approved'%}
			AND prod.`approved` = 'N'
		{%endif%}
		
	{%endif%}

	{%if category%}
		AND prod.`category` = '{%category%}'
	{%endif%}

	{%if keyword%}
		AND prod.`name` LIKE '%{%keyword%}%'
	{%endif%}

	{%if offset%}		
		{%if offset_to == 'gt'%}
			AND prod.`id` > {%offset%}
		{%endif%}	

		{%if offset_to == 'lt'%}
			AND prod.`id` < {%offset%}
		{%endif%}
	{%endif%}

	ORDER BY prod.`id` {%order%} 

{%if limit%}	
	LIMIT {%limit%}
{%endif%}