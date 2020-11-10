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
	users.`verified`,

	{@ Profile Data @}
	prods.`user_id` as profile_id

	FROM `{%t_reviews%}` reviews

	INNER JOIN `{%t_users%}` users ON reviews.`user_id` = users.`id`

	INNER JOIN `{%t_prods%}` prods ON reviews.`prod_id` = prods.`id`
	

	WHERE prods.`user_id` = {%prof_id%}

	AND prods.`approved` = 'Y'

	AND prods.`activity_status` = 'active'

	AND prods.`status` = 'active'

	AND prods.`editing_stage` = 'saved'

	{%if offset%}
		AND reviews.`id` < {%offset%}
	{%endif%}

	{%if sortby%}
		AND reviews.`valuation` IN ({%sortby%})
	{%endif%}

	ORDER BY reviews.`id` DESC, reviews.`valuation` DESC

{%if limit%}
	LIMIT {%limit%}
{%endif%}