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
	prod.`name`,
	prod.`rating`,
	prod.`reg_price`,
	prod.`sale_price`,
	prod.`sold` as sales

	FROM `{%t_prods%}` prod
	
	WHERE prod.`activity_status` = 'active'

	AND prod.`status` = 'active'

	AND prod.`editing_stage` = 'saved'

	{%if approved%}
		AND prod.`approved` = 'Y'
	{%endif%}

	{%if not_appr%}
		AND prod.`approved` = 'N'
	{%endif%}
	
	{%if user_id%}		
		AND prod.`user_id` = {%user_id%}
	{%endif%}

	{%if catg_id%}		
		AND prod.`category` = '{%catg_id%}'
	{%endif%}

	{%if exclude%}		
		AND prod.`id` NOT IN ({%exclude%})
	{%endif%}

	{%if offset%}		
		AND prod.`id` < {%offset%}
	{%endif%}

	ORDER BY prod.`id` DESC {%if ob_sales%} ,prod.`sold` DESC {%endif%} {%if ob_rating%} ,prod.`rating` DESC {%endif%} 

{%if limit%}	
	LIMIT {%limit%}
{%endif%}