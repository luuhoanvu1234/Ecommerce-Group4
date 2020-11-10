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

	req.`id`, 
	req.`user_id`,
	req.`full_name`,
	req.`message`,
	req.`id_photo`,
	req.`pr_photo`,
	req.`status`,
	req.`time`,

	user.`avatar`,
	user.`email`,
	user.`username`,
	user.`is_seller`,
	user.`last_active` AS last_seen

	FROM `{%t_acc_vrf%}` req 

	INNER JOIN `{%t_users%}` user ON req.`user_id` = user.`id`

	WHERE req.`status` IN ('pending','rejected')

	{%if ids%}	
		AND req.`id` IN ({%ids%})
	{%endif%}

	{%if offset%}		
		{%if offset_to == 'gt'%}
			AND req.`id` > {%offset%}
		{%endif%}	

		{%if offset_to == 'lt'%}
			AND req.`id` < {%offset%}
		{%endif%}
	{%endif%}

	ORDER BY req.`id` {%order%} 

{%if limit%}	
	LIMIT {%limit%}
{%endif%}