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

	{@ Reviews Data @}

	reviews.`id`,
	reviews.`user_id`,
	reviews.`prod_id`,
	reviews.`valuation`,
	reviews.`review`,
	reviews.`activity_status`,
	reviews.`time`,

	{@ Reviewer Data @}

	users.`avatar` as avatar,
	CONCAT(users.`fname`,' ',users.`lname`) as uname,
	users.`username`,
	users.`email`,
	users.`verified`,

	{@ Product Item Data @}
	prods.`name` as prod_name


	FROM `{%t_reviews%}` reviews

	INNER JOIN `{%t_users%}` users ON reviews.`user_id` = users.`id`

	INNER JOIN `{%t_prods%}` prods ON reviews.`prod_id` = prods.`id`
	
	WHERE reviews.`activity_status` = 'active'

	AND prods.`user_id` = {%user_id%}

	AND prods.`approved` = 'Y'

	AND prods.`activity_status` = 'active'

	AND prods.`status` = 'active'

	AND prods.`editing_stage` = 'saved'

	{%if ids%}	
		AND reviews.`id` IN ({%ids%})
	{%endif%}

	{%if offset%}		
		{%if offset_to == 'gt'%}
			AND reviews.`id` > {%offset%}
		{%endif%}	

		{%if offset_to == 'lt'%}
			AND reviews.`id` < {%offset%}
		{%endif%}
	{%endif%}

	{%if valuation%}
		AND reviews.`valuation` IN ({%valuation%})
	{%endif%}

	ORDER BY reviews.`id` {%order%}

{%if limit%}
	LIMIT {%limit%}
{%endif%}