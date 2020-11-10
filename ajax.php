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
sleep(1);
header('Content-Type: application/json'); 
require_once("core/__init.php");

$app      = ((not_empty($_GET['app'])) ? $_GET['app'] : '');
$action   = ((not_empty($_GET['a'])) ? $_GET['a'] : '');
$rhandler = "xhr/$app.php";
$data     = array();
$errors   = array();
$root     = realpath(__DIR__); define('ROOT', $root);
$hash     = ((not_empty($_GET['hash'])) ? $_GET['hash'] : '');

if (empty($hash)) {
    $hash = ((not_empty($_POST['hash'])) ? $_POST['hash'] : '');
}

if (in_array($action, array('tup_balance_cancel','tup_balance_success')) != true) {
    if (empty($hash) || empty(hs_verify_csrf_token($hash))) {
        $data          =  array(
            'status'   => '400',
            'message'  => 'ERROR: Invalid or missing CSRF token'
        );

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
}

if (file_exists($rhandler) != true) {
    $data         = array(
        'status'  => '404',
        'message' => 'Error: Handler for Request not found'
    );

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit();
}

else {	
	require_once($rhandler);
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	exit();
}