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

error_reporting(0);
if (not_empty($config['server_mode'])) {
	if ($config['server_mode'] == 'debug') {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors',1);
		error_reporting(E_ALL);
	}
}

