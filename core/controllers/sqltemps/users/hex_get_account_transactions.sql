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
	
	{@ Checkout Transaction Info @}

	ct.`id`,
	ct.`seller_id`,
	ct.`buyer_id`,
	ct.`amount`,
	ct.`status`,
	ct.`time`,

	{@ Accounts Info @}

	CONCAT(u1.`fname`,' ', u1.`lname`) as seller_name,
	CONCAT(u2.`fname`,' ', u2.`lname`) as buyer_name,

	u1.`avatar` as seller_avatar,
	u1.`verified` as seller_verified,
	u2.`avatar` as buyer_avatar,
	u2.`verified` as buyer_verified

	FROM `{%t_trans%}` ct

	INNER JOIN `{%t_users%}` u1 ON ct.`seller_id` = u1.`id`

	INNER JOIN `{%t_users%}` u2 ON ct.`buyer_id`  = u2.`id`
	
	WHERE (ct.`seller_id` = {%account_id%} OR ct.`buyer_id` = {%account_id%})

	{%if offset%}
		{%if offset_to == 'lt'%}		
			AND ct.`id` < {%offset%}
		{%endif%}

		{%if offset_to == 'gt'%}		
			AND ct.`id` > {%offset%}
		{%endif%}
	{%endif%}

	{%if keyword%}		
		AND ((u1.`fname` LIKE '%{%keyword%}%' OR u1.`lname` LIKE '%{%keyword%}%') OR (u2.`fname` LIKE '%{%keyword%}%' OR u2.`lname` LIKE '%{%keyword%}%'))
	{%endif%}

	ORDER BY ct.`id` {%order%}

{%if limit%}	
	LIMIT {%limit%}
{%endif%}