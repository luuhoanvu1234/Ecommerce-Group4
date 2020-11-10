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

	COUNT(basket.`id`) AS total_items

	FROM `{%t_basket%}` basket 

	INNER JOIN `{%t_prods%}` prods ON basket.`prod_id` = prods.`id`

	WHERE basket.`user_id` = {%user_id%} AND prods.`activity_status` = 'active' AND prods.`status` = 'active' AND prods.`approved` = 'Y' AND prods.`editing_stage` = 'saved';