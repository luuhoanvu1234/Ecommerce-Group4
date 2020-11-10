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

	trans.`id`, 
	trans.`seller_id`, 
	trans.`buyer_id`, 
	trans.`amount`, 
	trans.`prod_id`, 
	trans.`status`, 
	trans.`method`, 
	trans.`market_rate`, 
	trans.`time`, 
	seller.`avatar` AS seller_avatar,
	seller.`username` AS seller_uname,
	seller.`verified` AS seller_verified,
	buyer.`username` AS buyer_uname,
	buyer.`avatar` AS buyer_avatar,
	buyer.`verified` AS buyer_verified,

	CONCAT(seller.`fname`, ' ',seller.`lname`) AS seller_name,
	CONCAT(buyer.`fname`, ' ',buyer.`lname`) AS buyer_name

	FROM `{%t_couts%}` trans 

	INNER JOIN `{%t_users%}` seller ON trans.`seller_id` = seller.`id`

	INNER JOIN `{%t_users%}` buyer ON trans.`buyer_id` = buyer.`id`

	WHERE trans.`id` > 0

	{%if offset%}		
		{%if offset_to == 'gt'%}
			AND trans.`id` > {%offset%}
		{%endif%}	

		{%if offset_to == 'lt'%}
			AND trans.`id` < {%offset%}
		{%endif%}
	{%endif%}

	{%if status%}		
		AND trans.`status` = '{%status%}'
	{%endif%}

	{%if customer%}		
		AND (buyer.`fname` LIKE '%{%customer%}%' OR buyer.`lname` LIKE '%{%customer%}%' OR CONCAT(buyer.`fname`,' ',buyer.`lname`) LIKE '%{%customer%}%')
	{%endif%}

	{%if seller%}		
		AND (seller.`fname` LIKE '%{%seller%}%' OR seller.`lname` LIKE '%{%seller%}%' OR CONCAT(seller.`fname`,' ',seller.`lname`) LIKE '%{%seller%}%')
	{%endif%}
	
	{%if amount%}		
		AND trans.`amount` = {%amount%}
	{%endif%}
	
	{%if order_id%}		
		AND trans.`order_id` = {%order_id%}
	{%endif%}
	
	{%if method%}		
		AND trans.`method` = '{%method%}'
	{%endif%}

	ORDER BY trans.`id` {%order%} 

{%if limit%}	
	LIMIT {%limit%}
{%endif%}