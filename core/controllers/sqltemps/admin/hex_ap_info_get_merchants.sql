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

	u.`id`, 
	u.`email`,
	u.`avatar`,
	u.`last_active`,
	u.`username`,
	u.`verified`,
	u.`sales`,
	u.`country_id`,
	CONCAT(u.`fname`,' ', u.`lname`) as full_name,

	(SELECT COUNT(p.`id`) FROM `{%t_prods%}` p WHERE p.`user_id` = u.`id` AND p.`activity_status` = 'active' AND p.`editing_stage` = 'saved' AND p.`approved` = 'Y' AND p.`status` = 'active') AS selling_items,
 
	(SELECT payout.`time` FROM `{%t_payouts%}` payout WHERE payout.`user_id` = u.`id` AND payout.`status` = 'paid') AS lp_date,

	(SELECT payout.`amount` FROM `{%t_payouts%}` payout WHERE payout.`user_id` = u.`id` AND payout.`status` = 'paid') AS lp_amount,

	(SELECT payout.`currency` FROM `{%t_payouts%}` payout WHERE payout.`user_id` = u.`id` AND payout.`status` = 'paid') AS lp_currency

	FROM `{%t_users%}` u 

	WHERE u.`is_seller` = 'Y' AND u.`active` IN ('1','2')

	{%if offset%}		
		{%if offset_to == 'gt'%}
			AND u.`id` > {%offset%}
		{%endif%}	

		{%if offset_to == 'lt'%}
			AND u.`id` < {%offset%}
		{%endif%}
	{%endif%}

	{%if keyword%}
		AND (u.`fname` LIKE '%{%keyword%}%' OR u.`lname` LIKE '%{%keyword%}%' OR CONCAT(u.`fname`,' ',u.`lname`) LIKE '%{%keyword%}%')
	{%endif%}

	ORDER BY u.`id` {%order%} 

{%if limit%}	
	LIMIT {%limit%}
{%endif%}