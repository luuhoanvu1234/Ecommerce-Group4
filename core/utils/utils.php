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

function val(&$var) {
    return (isset($var) == true) ? $var : '';
}

function not_num(&$var) {
    return (empty($var) || is_numeric($var) != true || $var < 1) ? true : false;
}

function empty_array(&$var) {
    return (empty($var) || is_array($var) != true) ? true : false;
}

function is_number(&$var) {
    return (not_empty($var) && is_numeric($var) == true && $var >= 1) ? true : false;
}

function c2val($v1 = null,$v2 = null, $ret = null) {
    if($v1 == $v2) {
        return $ret;
    }
    
    return false;
}

function pos_int($int = null) {
    return (is_numeric($int) && $int > 0) ? true : false;
}

function not_empty(&$var) {
    return (!empty($var));
}

function hs_queryset($data = null,$type = 'array') {
    $query = false;
    if ($type == 'object') {
        $query = (is_object($data) && not_empty($data));
    } 

    else {
        $query = (is_array($data) && not_empty($data));
    }
    return $query;
}

function hs_session($key = null,$val = null) { 
    if (not_empty($key) && is_string($key)) {
        if ($key && $val) {
            $_SESSION[$key] = $val;
            return true;
        } 
        else {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
        }
    }

    return false;
}

function hs_session_unset($key = null) { 
    if (not_empty($key) && isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

function len($string = '') {
    return mb_strlen($string);
}

function len_between($string = '',$s = 0, $e = 0) {
    return ((mb_strlen($string) >= $s) && (mb_strlen($string) <= $e));
}

function int_between($int = 0,$min = 0, $max = 0) {
    return ((intval($int) >= intval($min)) && (intval($int) <= intval($max)));
}

function is_num($var = null,$max_len = 0) {
	if ($max_len && is_numeric($max_len)) {
		return (is_numeric($var) && mb_strlen((string)$var) <= $max_len);
	} else {
		return is_numeric($var);
	}
}

function all_numeric($numbers_array = array()){
    if(!is_array($numbers_array)){
        return false;
    }
    else{
        foreach($numbers_array as $num){
            if(!is_numeric($num)){
                return false;
            }
        }
        
        return true;
    }
}

function hs_all($arr = array(), $type = none) {
    if (empty($arr) || is_array($arr) != true) {
        return false;
    }  else if(empty($type) || in_array($type, array('numeric','string')) != true) {
        return false;
    }

    if ($type == 'numeric') {
        foreach ($arr as $val) {
            if (is_numeric(($val)) != true) {
                return false;
            }
        }
    } 

    else if ($type == 'string') {
        foreach ($arr as $val) {
            if (is_string(($val)) != true) {
                return false;
            }
        }
    }

    return true;
}

function is_url($url = null) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

function num2digs($num = 0) {
    return number_format($num,2,'.','');
}

function json($array = array(), $seril = null) {
    if ($seril) {
        return json_encode($array,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
    else {
        return json_decode($array,true);
    }
}

function fetch_or_get(&$var,$alt = null) {
    if (not_empty($var)) {
        return $var;
    }
    else {
        return $alt;
    }
}

function hs_price_discount($rp = 0,$sp = 0) {
    if (not_num($rp) || not_num($sp)) {
        return 0;
    }

    return( ($sp < $rp) ? floor((100 - ($sp / $rp * (100)))) : 0);
}

function http_referer() {
    $ref = fetch_or_get($_SERVER['HTTP_REFERER']);

    return $ref;
}

function filesize_formatted($size = 0) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = ($size > 0 ? floor(log($size, 1024)) : 0);
    return sprintf("%s %s",number_format($size / pow(1024, $power), 2, '.', ','), $units[$power]);
}


function get_ip() {
    if (not_empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    
    if (not_empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)){
                    return $ip;
                }
            }
        } 

        else {
            if (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)){
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
    }

    if (not_empty($_SERVER['HTTP_X_FORWARDED']) && filter_var($_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_X_FORWARDED'];
    }
        
    if (not_empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && filter_var($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
        
    if (not_empty($_SERVER['HTTP_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
        
    if (not_empty($_SERVER['HTTP_FORWARDED']) && filter_var($_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_FORWARDED'];
    }
        
    return $_SERVER['REMOTE_ADDR'];
}

function pre($val = "",$exit = false) {
    echo "<pre>";
    print_r($val);
    echo "</pre>";
    if ($exit) {
        exit();
    }
}