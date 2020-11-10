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
	CONCAT(u.`fname`,' ', u.`lname`) as full_name

	FROM `{%t_users%}` u 

	WHERE u.`active` IN ('1','2')

	AND u.`id` IN (SELECT `user_id` FROM `{%t_admins%}`)

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