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
	prod.`reg_price`,
	prod.`sale_price`,
	prod.`rating`,
	prod.`sold` as sales

	FROM `{%t_prods%}` prod
	
	WHERE prod.`activity_status` = 'active'

	AND prod.`status` = 'active'

	AND prod.`editing_stage` = 'saved'

	AND prod.`approved` = 'Y'

	{%if offset%}		
		AND prod.`id` < {%offset%}
	{%endif%}

	ORDER BY prod.`id` DESC, prod.`sold` DESC

{%if limit%}	
	LIMIT {%limit%}
{%endif%}