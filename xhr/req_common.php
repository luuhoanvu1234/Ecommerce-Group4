<?php 
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)									@
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ HighExpress - The Ultimate Modern Marketplace Platform                  @
# @ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
# @*************************************************************************@

if ($action == 'delete_announcement') {
	if (is_number($_GET['annc_id'])) {
		$annc_id = intval($_GET['annc_id']);
		$db      = $db->where('id',$annc_id);
		$db      = $db->where('user_id',$me['id']);
		$delete  = $db->delete(T_ANNOUNC);
	}
}