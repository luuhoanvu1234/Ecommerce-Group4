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
	ct.`amount`,
	ct.`time`

	FROM `{%t_trans%}` ct

	WHERE ct.`seller_id` = {%account_id%}

	{%if date%}
		AND ct.`time` >= '{%date%}'
	{%endif%}