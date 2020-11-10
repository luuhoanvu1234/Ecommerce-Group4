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

define("ROOTPATH", dirname(dirname(__FILE__)) );

if (file_exists("core/settings.php")) {
    require_once("core/settings.php");
    $main_conf   = array();
    $main_conf[] = empty($sql_db_host);
    $main_conf[] = empty($sql_db_user);
    $main_conf[] = empty($sql_db_pass);
    $main_conf[] = empty($sql_db_name);
    $main_conf[] = empty($site_url);

    if (in_array(true, $main_conf)) {
        echo('Error: The main configuration file content is invalid!');
        die();
    }
}

else {
    echo('Error: The main configuration file (./core/settings.php) is not readable or invalid!');
    die();
}

session_start();
require_once("core/database_tables.php");
require_once("core/global_context.php");
require_once("core/utils/main.php");
require_once("core/utils/utils.php");
require_once("core/libs/DB/vendor/autoload.php");
require_once("core/libs/MySQL-Dump/MySQLDump.php");
require_once("core/loadctrl.php");
require_once("core/utils/data_sessions.php");


// $mysqli        = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);
$mysqli        = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);
$server_errors = array();

if (mysqli_connect_errno()) {
    $server_errors[] = "Error: Failed to connect to MySQL Server: " . mysqli_connect_error();
}

if (not_empty($server_errors)) {
    foreach ($server_errors as $serv_error) {
        echo "<h3>{$serv_error}</h3>";
    }
    die();
}

$sqlConnect           = $mysqli;
$query                = $mysqli->query("SET NAMES utf8");
$set_charset          = $mysqli->set_charset('utf8mb4');
$set_charset          = $mysqli->query("SET collation_connection = utf8mb4_unicode_ci");
$db                   = new MysqliDb($mysqli);
$hs['sql_mode']       = hs_set_sql_mode();
$hs['is_logged']      = false;
$hs['is_admin']       = false;
$hs['theme_url']      = $site_url;
$hs['site_url']       = $hs['theme_url'];
$config               = hs_get_configurations();
$config['url']        = $site_url;
$hs['currencies']     = hs_get_currencies();
$config['currency']   = hs_currency($config['market_currency']);
$hs['config']         = $config;
$_SESSION['lang']     = (empty($_SESSION['lang'])) ? $hs['config']['language'] : $_SESSION['lang'];
$hs['language']       = fetch_or_get($_SESSION['lang'],'english');
$lang_array           = hs_get_languages('active');
$hs['lang_array']     = $lang_array;
$langs                = hs_get_langs($hs['language']);
$hs['categories']     = hs_get_product_categories('all','active');
$hs['featured_catgs'] = hs_get_product_categories(8,'active');
$categories           = $hs['categories'];

require_once('middleware/constant/request_start.php');

if ($hs['config']['as3_storage'] == 'on') {
    include_once('core/libs/s3/vendor/autoload.php');
}

if (hs_is_logged() == true) {
    $hs['is_logged']    = true;
    $session_id         = (not_empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : $_COOKIE['user_id'];
    $hs['user_session'] = hs_get_userfromsession_id($session_id);

    if (empty($hs['user_session']) && not_empty($_POST['access_token'])) {
        header('Content-Type: application/json');
        echo json(array('status' => 400,"error" => 'Invalid access token'),1);
        exit();
    }

    $hs['hash_session'] = $session_id;
    $user_data_         = hs_user_data($hs['user_session']);
    $me                 = $hs['me'] = ((empty($user_data_)) ? false : hs_o2array($user_data_));

    if (empty($me)) {
        header('Content-Type: application/json');
        echo json(array('status' => 400,"error" => 'Invalid access token'),1);
        exit();
    }

    else if($me['active'] == '0') {
        if (not_empty($_GET['resend_ace'])) {
            require_once('middleware/temp/account/resend_ace.php');
        }

        else if(not_empty($_GET['activation_token'])) {
            require_once('middleware/temp/account/activate_account.php');
        }

        else {
            require_once('middleware/temp/account/account_inactive.php');
        }
    }

    else if($me['active'] == '2') {
        require_once('middleware/temp/account/blocked_account.php');
    }

    else {
        $me['wallet_val']   = floatval($me['wallet']);
        $me['wallet']       = hs_money($me['wallet']);
        $me['wallet_all']   = hs_money_all($me['wallet_val']);
        $me['is_admin']     = ($me['admin'] == 1) ? true : false;
        $hs['is_admin']     = ($me['admin'] == 1) ? true : false;
        $me['basket']       = hs_basket_items_total($me['id']);
        $me['basket_val']   = ((is_numeric($me['basket'])) ? intval($me['basket']) : 0); 
        $me['data_session'] = hs_data_session_get();
        $hs['temp_session'] = hs_admin_temp_session($session_id);
 
        if ($me['last_active'] < (time() - (60 * 30))) {
            hs_update_user_data($me['id'], array(
                'last_active' => time()
            ));
        }

        if ($hs['language'] != $me['language']) {
            hs_session('lang',$me['language']);
        }
    }          
}

if (not_empty($_GET['language'])) {
    $lang_name  = hs_secure($_GET['language']);
    $lang_names = array();

    foreach ($lang_array as $lang_data) {
        array_push($lang_names, $lang_data['lang_name']);
    }

    if (in_array($lang_name, $lang_names)) {
        hs_session('lang',$lang_name);

        $db                   = $db->where('id', $me['id']);
        $set_lang             = $db->update(T_USERS, array('language' => $lang_name));
        $me['language']       = $lang_name;
        $hs['me']['language'] = $lang_name;
        $ref_url              = http_referer();

        if ($ref_url) {
            hs_location($ref_url);
        }
        else {
            hs_redirect_after('/',0.05);
        }
    }
}

$hs['language_type'] = 'ltr';
$hs['csrf_token']    = hs_generate_csrf_token(); 

define('IS_LOGGED', $hs['is_logged']);
define('IS_ADMIN', $hs['is_admin']);

require_once('stripe-config.php');
require_once('paypal-config.php');
require_once('php_scripts/scripts_launcher.php');
