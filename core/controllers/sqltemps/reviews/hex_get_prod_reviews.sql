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
	users.`verified`

	FROM `{%t_reviews%}` reviews

	INNER JOIN `{%t_users%}` users ON reviews.`user_id` = users.`id`

	WHERE reviews.`activity_status` = 'active'

	AND reviews.`prod_id` = {%prod_id%}

	{%if by_ids%}
		AND reviews.`id` IN ({%by_ids%})
	{%endif%}

	{%if offset%}
		AND reviews.`id` < {%offset%}
	{%endif%}

	{%if sortby%}
		AND reviews.`valuation` = {%sortby%}
	{%endif%}

	ORDER BY reviews.`id` DESC

{%if limit%}
	LIMIT {%limit%}
{%endif%}