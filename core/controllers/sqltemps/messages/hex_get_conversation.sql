/*@*************************************************************************@
//@ @author Mansur Altamirov (Mansur_TL)									@
//@ @author_url 1: https://www.instagram.com/mansur_tl                      @
//@ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
//@ @author_email: highexpresstore@gmail.com                                @
//@*************************************************************************@
//@ HighExpress - The Ultimate Modern Marketplace Platform                  @
//@ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
//@*************************************************************************@*/

SELECT * FROM(

	SELECT * FROM `{%t_msgs%}` 

		WHERE ((`sent_by` = {%sent_by%} AND `sent_to` = {%sent_to%} AND `deleted_fs1` = 'N') OR (`sent_to` = {%sent_by%} AND `sent_by` = {%sent_to%} AND `deleted_fs2` = 'N'))

		{%if offset%} 
			{%if offset_to == 'gt'%}
				AND `id` >  {%offset%} 
			{%endif%}

			{%if offset_to == 'lt'%}
				AND `id` <  {%offset%} 
			{%endif%}
		{%endif%}

		{%if new%}
			AND `seen` = 0 
		{%endif%}

	ORDER BY `id` {%order_in%} LIMIT {%limit%}

) tw ORDER BY `id` {%order_out%};

