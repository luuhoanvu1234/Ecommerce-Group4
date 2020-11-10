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
	p.`id`, 
	p.`user_id`, 
	p.`reg_price`,
	p.`sale_price`,
	p.`poster`,
	p.`sold`,
	p.`rating`,
	p.`name` as name

	FROM `{%t_prods%}` p 

	INNER JOIN `{%t_users%}` u ON p.`user_id` = u.`id`

	WHERE p.`category` = '{%catg_id%}' 

	AND p.`activity_status` = 'active'

	AND p.`status` = 'active'

	AND p.`approved` = 'Y'

	AND p.`editing_stage` = 'saved'

	{%if brand%}		
		AND p.`brand` LIKE '%{%brand%}%'
	{%endif%}

	{%if keyword%}		
		AND (p.`name` LIKE '%{%keyword%}%' OR p.`description` LIKE '%{%keyword%}%')
	{%endif%}

	{%if ship_cost%}		
		AND p.`shipping_cost` = '{%ship_cost%}'
	{%endif%}

	{%if min_price%}		
		AND p.`sale_price` >= {%min_price%}
	{%endif%}

	{%if max_price%}		
		AND p.`sale_price` <= {%max_price%}
	{%endif%}

	{%if condition%}		
		AND p.`condition` = '{%condition%}'
	{%endif%}

	{%if ship_time%}		
		AND p.`shipping_time` = '{%ship_time%}'
	{%endif%}	

	{%if offset%}		
		AND p.`id` < {%offset%}
	{%endif%}

	{%if sell_cntr%}		
		AND u.`country_id` IN ({%sell_cntr%})
	{%endif%}


	ORDER BY p.`id` DESC
		{%if sortby == 'price_up'%} ,p.`sale_price` ASC {%endif%}	

		{%if sortby == 'price_down'%} ,p.`sale_price` DESC {%endif%}

		{%if sortby == 'price_down'%} ,p.`sale_price` ASC {%endif%}

		{%if sortby == 'rating'%} ,p.`rating` DESC {%endif%}

		{%if sortby == 'sales'%} ,p.`sold` DESC {%endif%}

{%if limit%}	
	LIMIT {%limit%}
{%endif%}