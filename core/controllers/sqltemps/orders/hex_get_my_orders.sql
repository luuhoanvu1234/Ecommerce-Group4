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
	o.`status`, 
	o.`time` as placed_date, 
	u.`avatar` as seller_avatar,
	u.`verified` as seller_verified,
	u.`username` as seller_uname,
	CONCAT(u.`fname`,' ', u.`lname`) as seller_name,
	p.`name` as prod_name,

	CASE 
		WHEN v.`id` IS NULL 

			THEN p.`reg_price` 

		ELSE v.`reg_price`
	END AS reg_price,

	CASE 
		WHEN v.`id` IS NULL 

			THEN p.`sale_price` 

		ELSE v.`sale_price`
	END AS sale_price

	FROM `{%t_orders%}` o 

	INNER JOIN `{%t_users%}` u ON o.`seller_id` = u.`id`

	INNER JOIN `{%t_prods%}` p ON o.`prod_id` = p.`id`

	LEFT OUTER JOIN `{%t_pvars%}` v ON o.`var_id` = v.`id`

	WHERE o.`buyer_id` = {%buyer_id%}

	AND p.`activity_status` = 'active'

	AND p.`status` IN ('active','inactive','blocked')

	AND p.`approved` = 'Y'

	AND p.`editing_stage` = 'saved'

	{%if offset%}
		{%if offset_to == 'lt'%}		
			AND o.`id` < {%offset%}
		{%endif%}

		{%if offset_to == 'gt'%}		
			AND o.`id` > {%offset%}
		{%endif%}
	{%endif%}

	{%if keyword%}
		AND (u.`fname` LIKE '%{%keyword%}%' OR u.`lname` LIKE '%{%keyword%}%' OR CONCAT(u.`fname`,' ', u.`lname`) LIKE '%{%keyword%}%')
	{%endif%}

	ORDER BY o.`id` {%order%}

{%if limit%}	
	LIMIT {%limit%}
{%endif%}