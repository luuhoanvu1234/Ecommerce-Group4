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
	ord_cnc.`id`,
	ord_cnc.`order_id`,
	ord_cnc.`trans_id`,
	ord_cnc.`prod_id`,
	ord_cnc.`seller_id`,
	ord_cnc.`buyer_id`,
	ord_cnc.`status`,
	ord_cnc.`time`,

	prods.`name` AS prod_name,

	CONCAT(buyers.`fname`, ' ',buyers.`lname`) AS cust_name,
	buyers.`avatar` AS cust_avatar,
	buyers.`username` AS cust_uname,
	buyers.`email` AS cust_email,
	buyers.`verified` AS cust_verified,
	

	CONCAT(seller.`fname`, ' ',seller.`lname`) AS seller_name,
	seller.`avatar` AS seller_avatar,
	seller.`username` AS seller_uname,
	seller.`email` AS seller_email,

	trans.`method` AS payment_method,
	trans.`amount` AS payment_amount,
	trans.`market_rate` AS payment_fee,
	trans.`stripe_pid`,
	trans.`paypal_pid`,
	trans.`wallet_pid`

	FROM `{%t_ord_cnc%}` ord_cnc 

	INNER JOIN `{%t_users%}` buyers ON ord_cnc.`buyer_id` = buyers.`id`

	INNER JOIN `{%t_users%}` seller ON ord_cnc.`seller_id` = seller.`id`

	INNER JOIN `{%t_prods%}` prods ON ord_cnc.`prod_id` = prods.`id`

	INNER JOIN `{%t_trans%}` trans ON ord_cnc.`trans_id` = trans.`id`

	WHERE ord_cnc.`id` = {%id%}

	AND prods.`activity_status` = 'active'

	AND prods.`status` IN ('active','inactive','blocked')

	AND prods.`approved` = 'Y'

	AND prods.`editing_stage` = 'saved'

	LIMIT 1