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
	prod.`name` AS prod_name,
	prod.`reg_price`,
	prod.`sale_price`,
	prod.`sold` as sales,
	prod.`activity_status`,
	prod.`time`,
	user.`avatar` AS seller_avatar,
	CONCAT(user.`fname`, ' ', user.`lname`) AS seller_name,

	(SELECT COUNT(*) FROM `{%t_orders%}` ord WHERE ord.`prod_id` = prod.`id`) AS total_orders

	FROM `{%t_prods%}` prod

	INNER JOIN `{%t_users%}` user ON prod.`user_id` = user.`id`
	
	WHERE prod.`activity_status` = 'active'

	AND prod.`status` = 'deleted'

	{%if offset%}		
		{%if offset_to == 'gt'%}
			AND prod.`id` > {%offset%}
		{%endif%}	

		{%if offset_to == 'lt'%}
			AND prod.`id` < {%offset%}
		{%endif%}
	{%endif%}

	{%if keyword%}
		AND prod.`name` LIKE '%{%keyword%}%'
	{%endif%}

	ORDER BY prod.`id` {%order%} 

{%if limit%}	
	LIMIT {%limit%}
{%endif%}