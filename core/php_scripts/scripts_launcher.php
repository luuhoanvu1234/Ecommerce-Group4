<?php 
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)                                    @
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ HighExpress - The Ultimate Modern Marketplace Platform                  @
# @ Copyright (c) 05.07.19 HighExpress. All rights reserved.                @
# @*************************************************************************@

$curr_time = time();
$db_lc     = fetch_or_get($hs['config']['db_lc'],$curr_time);

if ($curr_time >= $db_lc) {
	hs_ap_save_config('db_lc',($curr_time + 18000));
	try {
		require_once('tasks/clean_data_base.php');
	} 
	catch (Exception $e) {/* pass */}
}