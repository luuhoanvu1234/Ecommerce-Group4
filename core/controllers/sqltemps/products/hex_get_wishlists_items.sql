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
	wli.`id`, 
	wli.`list_id`, 
	wli.`prod_id`, 
	wli.`user_id`, 
	pdi.`reg_price`,
	pdi.`sale_price`,
	pdi.`poster`,
	pdi.`rating`,
	pdi.`name` as prod_name,
	pdi.`sold` as sales

	FROM `{%t_wlist%}` wli 

	INNER JOIN `{%t_prods%}` pdi ON wli.`prod_id` = pdi.`id`

	INNER JOIN `{%t_users%}` usr ON pdi.`user_id` = usr.`id`

	WHERE wli.`user_id` = {%user_id%} 

	{%if list_id%}
		AND wli.`list_id` = '{%list_id%}'
	{%endif%}
	
	AND pdi.`activity_status` = 'active'

	AND pdi.`status` = 'active'
	
	AND pdi.`approved` = 'Y'

	AND pdi.`editing_stage` = 'saved'

	{%if brand%}		
		AND pdi.`brand` LIKE '%{%brand%}%'
	{%endif%}

	{%if keyword%}		
		AND (pdi.`name` LIKE '%{%keyword%}%' OR pdi.`description` LIKE '%{%keyword%}%')
	{%endif%}

	{%if ship_cost%}		
		AND pdi.`shipping_cost` = '{%ship_cost%}'
	{%endif%}

	{%if min_price%}		
		AND pdi.`sale_price` >= {%min_price%}
	{%endif%}

	{%if max_price%}		
		AND pdi.`sale_price` <= {%max_price%}
	{%endif%}

	{%if condition%}		
		AND pdi.`condition` = '{%condition%}'
	{%endif%}

	{%if ship_time%}		
		AND pdi.`shipping_time` = '{%ship_time%}'
	{%endif%}	

	{%if catg_id%}		
		AND pdi.`category` = '{%catg_id%}'
	{%endif%}	

	{%if offset%}		
		AND wli.`id` < {%offset%}
	{%endif%}

	{%if sell_cntr%}		
		AND usr.`country_id` IN ({%sell_cntr%})
	{%endif%}

	ORDER BY wli.`id` DESC
		{%if sortby == 'price_up'%} ,pdi.`sale_price` ASC {%endif%}	

		{%if sortby == 'price_down'%} ,pdi.`sale_price` DESC {%endif%}

		{%if sortby == 'price_down'%} ,pdi.`sale_price` ASC {%endif%}

		{%if sortby == 'rating'%} ,pdi.`rating` DESC {%endif%}

		{%if sortby == 'sales'%} ,pdi.`sold` DESC {%endif%}

		{%if sortby == 'newest'%} ,pdi.`id` DESC {%endif%}

{%if limit%}	
	LIMIT {%limit%}
{%endif%}