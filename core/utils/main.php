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

function hs_loadpage($page_url = '', $data = array(), $set_lang = true) {
    global $config,$hs,$lang_array,$me;

    $path1 = sprintf("views/%s/html/%s.phtml",$config['theme'],$page_url);;
    $path2 = sprintf("store/layout/%s.phtml",$page_url);
    $page  = null;

    if (file_exists($path1)) {
        $page = $path1;
    } 

    else if (file_exists($path2)) {
        $page = $path2;
    } 

    else {
        die("File does not Exists : $page_url");
    }

    $page_content = '';
    ob_start();
    require($page);
    $page_content = ob_get_contents();
    ob_end_clean();

    if ($set_lang == true) {
        $page_content = preg_replace_callback("/{{TR (.*?)}}/", function($m) use ($lang_array) {
            return hs_translate($m[1]);
        }, $page_content);
    }

    if (not_empty($data) && is_array($data)) {
        foreach ($data as $key => $replace) {
            if(is_array($replace) || is_object($replace) ){
                $arr          = explode('_',$key);
                $k            = strtoupper($arr[0]);
                $replace      = hs_o2array($replace);
                $page_content = preg_replace_callback(sprintf("/{{%s (.*?)}}/",$k), function($m) use ($replace) {
                    return (isset($replace[$m[1]])) ? $replace[$m[1]] : '';
                }, $page_content);
            } 

            else{
                $object_to_replace = sprintf("{{%s}}",$key);
                $page_content      = str_replace($object_to_replace, $replace, $page_content);
            }
        }
    }

    if ($hs['is_logged'] == true) {
        $replace      = hs_o2array($me);
        $page_content = preg_replace_callback("/{{ME (.*?)}}/", function($m) use ($replace) {
            return ((isset($replace[$m[1]])) ? $replace[$m[1]] : '');
        }, $page_content);
    }

    $page_content = preg_replace("/{{LINK (.*?)}}/", hs_link("$1"), $page_content);
    $page_content = preg_replace_callback("/{{CONFIG (.*?)}}/", function($m) use ($config) {
        return ((isset($config[$m[1]])) ? $config[$m[1]] : '');
    }, $page_content);
    return $page_content;
}

function hs_sqltepmlate($path = '', $data = array()){
    $temp_path = "core/controllers/sqltemps/$path.sql";
    $template  = $temp_path;
    if (file_exists($temp_path)) {
        $if   = '/(\{\%\s{0,1}if\s{1}(?P<key>[\w]+)\s{0,1}\%\}(?P<sq>.+?)\{\%\s{0,1}endif\s{0,1}\%\})/is';
        $ifeq = '/(\{\%\s{0,1}if\s{1}[\'\"]?(?P<key>[^\s]+?)[\'\"]?\s==\s[\'\"]?(?P<val>[^\s]+?)[\'\"]?\s{0,1}\%\}(?P<sq>.+?)\{\%\s{0,1}endif\s{0,1}\%\})/is';

        $template = file_get_contents($temp_path);

        foreach ($data as $key => $value) {
            $template = preg_replace_callback($ifeq, function($m) use($data) {
                if ($m && !empty($m['key']) && !empty($m['val']) && !empty($data[$m['key']]) && ($data[$m['key']] == $m['val'])) {
                    return (!empty($m['sq'])) ? $m['sq'] : '';
                }
                else{
                    return '';
                }
            },$template);

            $template = preg_replace_callback($if, function($m) use($data) {
                if ($m && !empty($m['key']) && !empty($data[$m['key']])) {
                        return (!empty($m['sq'])) ? $m['sq'] : '';
                }
                else{
                    return '';
                }

            },$template);

            $template = preg_replace("/\{\%\s{0,1}$key\s{0,1}\%\}/i",$value, $template);
            $template = preg_replace("/\{\@(.*?)\@\}/is",'', $template);
        }
    }
    else {
        exit("$template: Does not exists on the server!");
    }
    return $template;
}

function hs_server_message($page_url = '') {
    global $hs;
    $path = sprintf("views/server/%s.phtml",$page_url);;
    if (file_exists($path)) {
        $page_content = '';
        ob_start();
        require($path);
        $page_content = ob_get_contents();
        ob_end_clean();
        return $page_content;
    } 

    else {
        die("File does not exists : $path");
    }
}

function hs_html_el($tag_name = "html",$cont = "",$attrs = array()){
    $tag_attrs = "";
    if (not_empty($attrs) && is_array($attrs)) {
        foreach ($attrs as $attr => $value) {
            $tag_attrs .= " $attr=\"$value\"";
        }
    }
    
    return "<$tag_name$tag_attrs>$cont</$tag_name>";
}

function hs_country($id = null) {
    global $hs;
    if (in_array($id, array_keys($hs['countries']))) {
        return hs_translate($hs['countries'][$id]);
    }
    return "Unknown";
}

function hs_link($path) {
    global $site_url;
    return (($path == '/') ? $site_url : "$site_url/$path");
}

function hs_o2array($obj) {
    if (is_object($obj)) {
        $obj = (array) $obj;
    }
        
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = hs_o2array($val);
        }
    } 

    else {
        $new = $obj;
    }
    
    return $new;
}

function hs_redirect($link = '') {
    header(sprintf("Location: %s", hs_link($link)));
    exit();
}

function hs_redirect_after($link = '', $seconds = 0) {
    header(sprintf("Refresh: %d; url=%s",$seconds,hs_link($link)));
    exit();
}

function hs_location($link = '') {
    header(sprintf("Location: %s", $link));
    exit();
}

function hs_str_form($str = "", $data = array()) {
    foreach ($data as $i => $val) {
        $str = preg_replace("/\{$i\}/i",$val, $str);
    }

    return $str;
}

function hs_banner($country = 'us') {
    global $config;
    $theme = $config['theme'];
    $path  = "views/$theme/statics/img/banners/$country.svg";
    $svg   = "";

    if (file_exists($path)) {
        $svg = file_get_contents($path);
    }
    else {
        $svg = file_get_contents("views/$theme/statics/img/banners/us.svg");
    }

    return $svg;
}

function hs_svg($icon = 'us') {
    global $config;
    $theme = $config['theme'];
    $icon  = "views/$theme/statics/svg/$icon.svg";
    $svg   = "";

    if (file_exists($icon)) {
        $svg = file_get_contents($icon);
    }

    return $svg;
}

function hs_png($icon = 'none') {
    global $config;
    $theme = $config['theme'];
    $icon  = "views/$theme/statics/img/png/$icon.png";
    $path  = "";

    if (file_exists($icon)) {
        $surl = $config['url'];
        $path = "$surl/$icon";
    }

    return $path;
}

function hs_currency($name = 'usd') {
    global $hs;
    $curr = '?';

    if (not_empty($name)) {
        foreach ($hs['currencies'] as $curr_data) {
            if ($curr_data['curr_code'] == $name) {
                $curr = $curr_data['curr_symbol']; break;
            }
        }
    }

    return $curr;
}

function hs_txtslug($str, $delimiter = '_'){
    $slug = trim(preg_replace("#(\p{P}|\p{C}|\p{S}|\p{Z})+#u", mb_strtolower($delimiter,'UTF-8'), $str), $delimiter);
    return mb_strtolower($slug);
}

function hs_randstr($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/mb_strlen($x)) )),1,$length);
}

function hs_import_image($url) {
    global $hs;
    if (empty($url) || is_url($url) != true) {
        return false;
    }

    try {
       if (file_exists(sprintf("upload/%s", date('Y'))) != true) {
            mkdir(sprintf("upload/%s", date('Y')), 0777, true);
        }

        if (file_exists(sprintf("upload/photos/%s/%s",date('Y'),date('m'))) != true) {
            mkdir(sprintf("upload/photos/%s/%s",date('Y'),date('m')), 0777, true);
        }

        $dir       = sprintf("upload/photos/%s/%s",date('Y'),date('m'));
        $file_dir  = sprintf("%s/%s-image-url.jpg",$dir,hs_randstr(10));
        $get_media = file_get_contents($url);
        if (not_empty($get_media)) {
            $import_image = file_put_contents($file_dir, $get_media);

            if ($hs['config']['as3_storage'] == 'on') {
                try {
                    hs_upload2s3($file_dir);
                } catch (Exception $e) { /* pass */ }
            }
        }

        return ((file_exists($file_dir)) ? $file_dir : false); 
    } 

    catch (Exception $e) {
        return false;
    }
}


function hs_minify_js($code = ''){
    $code = preg_replace('/(\r\n|\n|\t|\s{2,})/is', '', $code);
    return $code;
}

function hs_secure($text = "",$sslashes = false,$br = true) {
    global $mysqli;
    $text = trim($text);
    $text = strip_tags($text);
    $text = mysqli_real_escape_string($mysqli, $text);
    $text = htmlspecialchars($text, ENT_QUOTES);

    if ($br == true) {
        $text = str_replace('\r\n', " <br>", $text);
        $text = str_replace('\n\r', " <br>", $text);
        $text = str_replace('\r', " <br>", $text);
        $text = str_replace('\n', " <br>", $text);
    } 

    else {
        $text = str_replace('\r\n', "", $text);
        $text = str_replace('\n\r', "", $text);
        $text = str_replace('\r', "", $text);
        $text = str_replace('\n', "", $text);
    }

    if ($sslashes) {
        $text = stripslashes($text);
    }
    
    $text = str_replace('&amp;#', '&#', $text);
    $text = preg_replace("/{{(.*?)}}/", '', $text);
    return $text;
}

function hs_html_secure($text = "",$sslashes = false,$br = true) {
    global $mysqli;
    $text = trim($text);
    $text = mysqli_real_escape_string($mysqli, $text);
    $text = htmlspecialchars($text, ENT_QUOTES);

    if ($br == true) {
        $text = str_replace('\r\n', " <br>", $text);
        $text = str_replace('\n\r', " <br>", $text);
        $text = str_replace('\r', " <br>", $text);
        $text = str_replace('\n', " <br>", $text);
    } 

    else {
        $text = str_replace('\r\n', "", $text);
        $text = str_replace('\n\r', "", $text);
        $text = str_replace('\r', "", $text);
        $text = str_replace('\n', "", $text);
    }

    if ($sslashes) {
        $text = stripslashes($text);
    }
    
    $text = str_replace('&amp;#', '&#', $text);
    $text = preg_replace("/{{(.*?)}}/", '', $text);
    return $text;
}

function hs_text_secure($text = "") {
    global $mysqli;
    $text = trim($text);
    $text = stripslashes($text);
    $text = strip_tags($text);
    $text = mysqli_real_escape_string($mysqli, $text);
    $text = htmlspecialchars($text, ENT_QUOTES);
    $text = str_replace('&amp;#', '&#', $text);
    $text = preg_replace("/{{(.*?)}}/", '', $text);
    return $text;
}

function hs_br_text($text = "") {
    $text = str_replace('\r\n', " <br>", $text);
    $text = str_replace('\n\r', " <br>", $text);
    $text = str_replace('\r', " <br>", $text);
    $text = str_replace('\n', " <br>", $text);

    return $text;
}
 
function hs_count_total($table = null,$data = array()) {
    global $db,$hs;

    if (empty($table) || empty($data) || is_array($data) != true) {
        return false;
    }

    foreach ($data as $col => $val) {
        $db->where($col,$val);
    }

    $count = $db->getValue($table,"COUNT(*)");

    return (is_numeric($count)) ? $count : 0;
}

function hs_translate($text = '',$data = array()) {
    global $langs,$hs,$db;
    $langkey  = hs_gen_lang_key($text);
    $servmode = fetch_or_get($hs['config']['server_mode'],'none');

    if (in_array($langkey, array_keys($langs))) {
        $text_val = $langs[$langkey];

        if (not_empty($data) && is_array($data)) {
            foreach ($data as $key => $val) {
                $text_val = preg_replace_callback("/\{\%(.*?)\%\}/", function($m) use ($data) {
                    return ((isset($data[$m[1]])) ? $data[$m[1]] : '');
                }, $text_val);
            }
        }

        if ($langkey == 'control_panel') {
            if (not_empty($hs['is_logged'])) {
                if (not_empty($hs['is_admin'])) {
                    $text_val = $langs['admin_panel'];
                }
                else {
                    $text_val = $langs['merchant_panel'];
                }
            }
        }

        return stripslashes($text_val);
    }

    // if ($servmode == 'debug') {
    //     $insert             =  $db->insert(T_LANGS, array(
    //         'lang_key'      => $langkey, 
    //         'english'       => hs_secure($text)
    //     ));$langs[$langkey] =  $text;
    // }

    // try {
    //     $insert          =  $db->insert('hex_untranslated_langs', array(
    //         'lang_key'   => $langkey, 
    //         'lang_value' => hs_secure($text),
    //         'time'       => time(),
    //     ));
    // } catch (Exception $e) { /* pass */ }

    return $text;
}

function hs_gen_lang_key($text = '') {
    if (empty($text) || is_string($text) != true) {
        return "";
    }

    $text     = trim($text);
    $langkey  = preg_replace('/\{\%(.*?)\%\}/', '', $text);
    $langkey  = strtolower(preg_replace('/[^a-zA-Z0-9-_\.\(\)]/','_', $langkey));
    $langkey  = hs_croptxt($langkey,60);
    return $langkey;
}

function hs_get_langs($lang = 'english') {
    global $hs, $db;

    $avail_langs = array();

    foreach ($hs['lang_array'] as $lang_data) {
        array_push($avail_langs, $lang_data['lang_name']);
    }

    $active_lang = hs_get_first_active_langname();
    $lang        = ((in_array($lang,array_values($avail_langs))) ? $lang : $active_lang);
    $data        = array();
    $query       = $db->get(T_LANGS,null,array('lang_key',$lang));

    if (hs_queryset($query)) {
        foreach ($query as $row) {
            $row->$lang           = stripcslashes($row->$lang);
            $row->$lang           = html_entity_decode($row->$lang, ENT_QUOTES | ENT_HTML5);
            $row->$lang           = htmlspecialchars_decode($row->$lang);
            $data[$row->lang_key] = $row->$lang;
        }
    }
    
    return $data;
}

function hs_get_configurations() {
    global $db;
    $data  = array();
    $configs = $db->get(T_CONFIG);
    foreach ($configs as $config) {
        if ($config->name == 'google_analytics') {
            $config->value = htmlspecialchars_decode($config->value);
        }

        $data[$config->name] = $config->value;
    }

    return $data;
}

function hs_get_db_languages($tt = null) {
    global $hs, $db;
    $data   = array();
    $t_lang = T_LANGS;
    $query  = $db->rawQuery("DESCRIBE `$t_lang`");
    foreach ($query as $column) {
        $data[] = ($tt == 'up') ? ucfirst($column->Field) : lcfirst($column->Field);
    }
    unset($data[0]);
    unset($data[1]);
    return $data;
}

function hs_gen_key($minlength = 20, $maxlength = 20, $uselower = true, $useupper = true, $usenumbers = true, $usespecial = false) {
    $charset = '';
    if ($uselower) {
        $charset .= "abcdefghijklmnopqrstuvwxyz";
    }
    if ($useupper) {
        $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    if ($usenumbers) {
        $charset .= "123456789";
    }
    if ($usespecial) {
        $charset .= "~@#$%^*()_+-={}|][";
    }
    if ($minlength > $maxlength) {
        $length = mt_rand($maxlength, $minlength);
    } else {
        $length = mt_rand($minlength, $maxlength);
    }
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $charset[(mt_rand(0, mb_strlen($charset) - 1))];
    }
    return $key;
}

function hs_get_multifiles_array(&$files){
    $_files       = array();
    $_files_count = count($files['name']);
    $_files_keys  = array_keys($files);
    for ($i = 0; $i < $_files_count; $i++) {
        foreach ($_files_keys as $key){
            $_files[$i][$key] = $files[$key][$i];
        }
    }
    return $_files;
}

function hs_cropimg($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
    $imgsize = @getimagesize($source_file);
    $width   = $imgsize[0];
    $height  = $imgsize[1];
    $mime    = $imgsize['mime'];
    switch ($mime) {
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image        = "imagegif";
            break;
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image        = "imagepng";
            break;
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image        = "imagejpeg";
            break;
        default:
            return false;
            break;
    }
    $dst_img    = @imagecreatetruecolor($max_width, $max_height);
    $src_img    = $image_create($source_file);
    $width_new  = ($height * $max_width / $max_height);
    $height_new = ($width * $max_height / $max_width);

    if ($width_new > $width) {
        $h_point = (($height - $height_new) / 2);
        @imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    } 

    else {
        $w_point = (($width - $width_new) / 2);
        @imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }

    @imagejpeg($dst_img, $dst_dir, $quality);
    if ($dst_img) {
        @imagedestroy($dst_img);
    }     
    if ($src_img) {
        @imagedestroy($src_img);
    }      
}

function hs_compress_img($source_url, $destination_url, $quality) {
    $info = getimagesize($source_url);
    if ($info['mime'] == 'image/jpeg') {
        $image = @imagecreatefromjpeg($source_url);
        @imagejpeg($image, $destination_url, $quality);
    } 

    elseif ($info['mime'] == 'image/gif') {
        $image = @imagecreatefromgif($source_url);
        @imagegif($image, $destination_url, $quality);
    } 

    elseif ($info['mime'] == 'image/png') {
        $image = @imagecreatefrompng($source_url);
        @imagepng($image, $destination_url);
    }
}

function hs_get_media($media = '', $is_upload = false){
    global $config;
    if (empty($media)) {
        return '';
    }
    
    if ($config['as3_storage'] == 'on') {
        $as3_bucket = $config['as3_bucket_name'];
        $media_url  = sprintf("https://%s.s3.amazonaws.com/%s",$as3_bucket,$media);
        return $media_url;
    }
    else {
        $media_url = sprintf("%s/%s",$config['url'],$media);
        return $media_url;
    }
}

function hs_upload($data = array()) {
    global $hs, $mysqli;
    if (empty($data) || !isset($data['name'])) {
        return false;
    }

    $upload_dirs = array(
        sprintf("upload/photos/%s/%s",date('Y'), date('m')),
        sprintf("upload/photos/thumbs/%s/%s",date('Y'),date('m'))
    );

    foreach ($upload_dirs as $upload_dir) {
        if (file_exists($upload_dir) !== true) {
            @mkdir($upload_dir, 0777, true);
        }
    }

    $allowed = 'jpg,png,jpeg,gif,webp';

    if (not_empty($data['allowed'])) {
        $allowed  = $data['allowed'];
    }

    $new_string        = pathinfo($data['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
    $extension_allowed = explode(',', $allowed);
    $file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);

    if (in_array($file_extension, $extension_allowed) != true) {
        return array(
            'error' => 'File format not supported'
        );
    }

    if (in_array($file_extension, array('jpg','jpeg','png','gif'))) {
        $folder   = 'photos';
        $fileType = 'image';
    } 

    else {
        return false;
    }

    $img_mime_types = array('image/png','image/jpeg','image/gif');

    if (in_array($data['type'], $img_mime_types) != true) {
        return array(
            'error' => 'File format not supported'
        );
    }

    $dir         = "upload/{$folder}/" . date('Y') . '/' . date('m');
    $filename    = $dir . '/' . hs_gen_key() . '_' . date('d') . '_' . md5(time()) . "_{$fileType}.{$file_extension}";
    $file_ext    = pathinfo($filename, PATHINFO_EXTENSION);
    $last_data   = array();
    if (move_uploaded_file($data['file'], $filename)) {
        if (in_array($file_ext, array('gif','png','jpeg','jpg')) == true) {
            if (not_empty($data['crop'])) {
                $crop_image = hs_cropimg($data['crop']['width'], $data['crop']['height'], $filename, $filename, 60);
            }

            try {
                @hs_compress_img($filename, $filename, 90);
            } catch (Exception $e) { /* pass */ }
        }

        if ($hs['config']['as3_storage'] == 'on') {
            try {
                hs_upload2s3($filename);
            } catch (Exception $e) { /* pass */ }
        }

        $last_data['filename'] = $filename;
        $last_data['name']     = $data['name'];
        return $last_data;
    }
}

function hs_upload2s3($filename = null) {
    global $hs,$db;
    
    if ($hs['config']['as3_storage'] == 'off') {
        return false;
    }
    else {
        if (empty($hs['config']['as3_api_key'])) {
            return false;
        }

        else if(empty($hs['config']['as3_api_secret_key'])) {
            return false;
        }

        else if(empty($hs['config']['as3_bucket_region'])) {
            return false;
        }

        else if(empty($hs['config']['as3_bucket_name'])) {
            return false;
        }

        else {
            try {
                $amazon_s3        =  new \Aws\S3\S3Client(array(
                    'version'     => 'latest',
                    'region'      => $hs['config']['as3_bucket_region'],
                    'credentials' => array(
                        'key'     => $hs['config']['as3_api_key'],
                        'secret'  => $hs['config']['as3_api_secret_key'],
                    )
                ));
                $up_aws_object     =  $amazon_s3->putObject(array(
                    'Bucket'       => $hs['config']['as3_bucket_name'],
                    'Key'          => $filename,
                    'Body'         => fopen($filename, 'r+'),
                    'ACL'          => 'public-read',
                    'CacheControl' => 'max-age=3153600',
                ));

                if ($hs['config']['as3_onup_delete'] == 'yes') {
                    if ($amazon_s3->doesObjectExist($hs['config']['as3_bucket_name'], $filename)) {
                        $db->insert(T_TEMP_MEDIA,array(
                            'file_path' => $filename,
                            'time' => time()
                        )); return true;
                    }
                } 

                else {
                    return true;
                }
            } 
            catch (Exception $e) {
                return false;
            }
        }
    } 
}

function hs_delete_from_s3($filename = null) {
    global $hs;
    if ($hs['config']['as3_storage'] == 'off') {
        return false;
    }
    else {
        if (empty($hs['config']['as3_api_key'])) {
            return false;
        }

        else if(empty($hs['config']['as3_api_secret_key'])) {
            return false;
        }

        else if(empty($hs['config']['as3_bucket_region'])) {
            return false;
        }

        else if(empty($hs['config']['as3_bucket_name'])) {
            return false;
        }

        try {
            $amazon_s3        = new \Aws\S3\S3Client(array(
                'version'     => 'latest',
                'region'      => $hs['config']['as3_bucket_region'],
                'credentials' => array(
                    'key'     => $hs['config']['as3_api_key'],
                    'secret'  => $hs['config']['as3_api_secret_key'],
                )
            ));

            $rm_aws_object =  $amazon_s3->deleteObject(array(
                'Bucket'   => $hs['config']['as3_bucket_name'],
                'Key'      => $filename
            ));

            if ($amazon_s3->doesObjectExist($hs['config']['as3_bucket_name'], $filename) != true) {
                return true;
            }

            else {
                return false;
            }
        } 
        catch (Exception $e) {
            return false;
        }
    }
}

function hs_upload_logos($data = array(),$type = 'logo') {
    global $hs, $db;

    if (empty($data['file']) || empty($data['name'])) {
        return false;
    }

    else if(in_array($type, array('logo','favicon')) != true) {
        return false;
    }

    $allowed           = 'jpg,png,jpeg';
    $file_path         = pathinfo($data['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
    $extension_allowed = explode(',', $allowed);
    $file_extension    = pathinfo($file_path, PATHINFO_EXTENSION);
    if (in_array($file_extension, $extension_allowed) != true) {
        return false;
    }

    $filename = sprintf("upload/images/%s","{$type}.{$file_extension}");
    try {
        if (move_uploaded_file($data['file'], $filename)) {
            return $filename;
        }
        else {
            return false;
        }  
    } 
    catch (Exception $e) {
        return false;
    }
}

function hs_croptxt($text = "", $len = 100,$end = "") {
    if (empty($text) || is_string($text) != true || not_num($len) || $len < 1) {
        return "";
    }
    if (mb_strlen($text) > $len) {
        $text = mb_substr($text, 0, $len, "UTF-8") . $end;
    }
    return $text;
}


function hs_image_name($path = null) {
    if (empty($path) || is_string($path) != true) {
        return false; 
    }

    $pttr  = "/(?P<name>[a-z0-9]{20,30}_{1}[0-9]{0,3}_{1}[a-z0-9]{20,32}_image)\.(?P<ext>[a-z]{0,5})/i";
    $match = preg_match($pttr, $path,$matches);
    return (not_empty($match)) ? $matches : false;
}


function hs_thumbnail($path = null,$size = "100x100") {
    if (empty($path) || file_exists($path) != true) {
        return false;
    } 

    else if (empty(preg_match('/^[0-9]{0,6}x[0-9]{0,6}$/', $size))) {
        return false;
    } 

    elseif (empty(hs_image_name($path))) {
        return false;
    }

    $upload_dirs = array(
        sprintf("upload/photos/%s/%s",date('Y'), date('m')),
        sprintf("upload/photos/thumbs/%s/%s",date('Y'),date('m'))
    );

    foreach ($upload_dirs as $upload_dir) {
        if (file_exists($upload_dir) !== true) {
            @mkdir($upload_dir, 0777, true);
        }
    }

    $path_info  = hs_image_name($path);
    $thumb_size = explode('x', $size);
    $image_name = $path_info['name'];
    $image_ext  = $path_info['ext'];
    $thumb_file = sprintf("upload/photos/thumbs/%s/%s/%s_%s_thumbnail.%s",date('Y'),date('m'),$image_name,$size,$image_ext);
    if (file_exists($thumb_file)) {
        return $thumb_file;
    } 

    elseif (hs_copy_file($path,$thumb_file) == false) {
        return false;
    } 

    else {
        hs_cropimg($thumb_size[0], $thumb_size[1], $thumb_file, $thumb_file, 60);
        hs_compress_img($thumb_file, $thumb_file, 90);
        hs_upload2s3($thumb_file);
        return $thumb_file;
    }
}

function hs_copy_file($src = null,$dest = null) {
    if (empty($src) || file_exists($src) != true) {
        return false;
    } 

    else if(is_string($dest) != true) {
        return false;
    }

    try {
        if (copy($src, $dest)) {
            return true;
        } 
        elseif (rename($src, $dest)) {
            return true;
        } 
        else {
            return false;
        }
    } 

    catch (Exception $e) {
        return false;
    }
}

function hs_delete_image($path = null) {
    global $hs;

    if (not_empty($path) && file_exists($path)) {
        try {
            $placeholders = array(
                'upload/users/user-avatar.png',
                'upload/images/as3-do-not-delete.png',
            );
            
            if (in_array($path, $placeholders) != true) {
                @unlink($path);
            }
            
            if ($hs['config']['as3_storage'] == 'on') {
                hs_delete_from_s3($path);
            }
        } catch (Exception $e) {/*pass*/ }
    }

    else if ($hs['config']['as3_storage'] == 'on') {
        try {
            hs_delete_from_s3($path);
        } catch (Exception $e) {/*pass*/ }
    }
}

function hs_set_sql_mode() {
    global $db;
    $sql   = "SELECT @@sql_mode as sql_modes";
    $modes = $db->rawQueryOne($sql);
    $mdset = false;
    if (hs_queryset($modes,'object') && not_empty($modes->sql_modes)) {
        $sql_md = array(
            'STRICT_TRANS_TABLES',
            'NO_ZERO_IN_DATE',
            'NO_ZERO_DATE',
            'ERROR_FOR_DIVISION_BY_ZERO',
            'NO_AUTO_CREATE_USER',
            'NO_ENGINE_SUBSTITUTION',
        );

        $match  = preg_match('/ONLY_FULL_GROUP_BY/i', $modes->sql_modes);
        $sql_md = implode(',', $sql_md);
        $mdset  = true;
        if ($match) {
            try {
                $db->rawQuery("SET GLOBAL sql_mode = '{$sql_md}'");
            } 

            catch (Exception $e) { 
                die('Cannot disable SQL mode ONLY_FULL_GROUP_BY. Please check your details!'); 
            }

            echo hs_server_message("wait/mysql_mode_setting");
            header("Refresh: 5;"); 
            exit();
        }
    } 

    return $mdset;
}

function hs_temp_data_get($key = '') {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return false;
    }

    $key = hs_secure($key);
    $db  = $db->where('user_id',$me['id']);
    $db  = $db->where('name',$key);
    $val = $db->getOne(T_TEMP_DATA,'value');
    $val = ((hs_queryset($val,'object') == true) ? $val->value : false);
    return $val;
}

function hs_temp_data_set($key = '',$val = '',$predelete = false) {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return false;
    }

    if ($predelete) {
        hs_temp_data_delete($key);
    }
    
    $key = hs_secure($key);
    $val = hs_secure($val);
    $ins = array(
        'user_id' => $me['id'],
        'name'    => $key,
        'value'   => $val,
    );

    $res = $db->insert(T_TEMP_DATA,$ins);
    return (is_numeric($res));
}

function hs_temp_data_delete($key = '') {
    global $db,$hs,$me;
    if (empty($hs['is_logged'])) {
        return false;
    }

    $key = hs_secure($key);
    $db  = $db->where('user_id',$me['id']);
    $db  = $db->where('name',$key);
    $res = $db->delete(T_TEMP_DATA);
    return $res;
}

function hs_rating_stars($rating = 0) {
    $stars = array();
    if (is_numeric($rating)) {
        $rating  = floor($rating);
        $inline  = $rating;
        $outline = (5 - $inline);

        for ($i=0; $i < $inline; $i++) { 
            array_push($stars, hs_svg('star'));
        }

        for ($i=0; $i < $outline; $i++) { 
            array_push($stars, hs_svg('star-outline'));
        }
    }

    else {
        for ($i=0; $i < 5; $i++) { 
            array_push($stars, hs_svg('star-outline'));
        }
    }

    return $stars;
}

function hs_price($price = '0.00',$digits = 0) {
    global $hs;
    if (is_numeric($price) != true) {
        if ($hs['config']['curr_symbol_position'] == 'after') {
            return sprintf("%s0.00",$hs['config']['currency']);
        }
        else {
            return sprintf("0.00%s",$hs['config']['currency']);
        }
    }

    else {
        if (len($price) > 9) {
            $money = hs_number_count($price);
            if ($hs['config']['curr_symbol_position'] == 'after') {
                return sprintf("%s%s",$hs['config']['currency'],$money);
            }
            else {
                return sprintf("%s%s",$money,$hs['config']['currency']);
            }
        }
        else {
            $money = number_format($price,$digits, '.', ', ');
            if ($hs['config']['curr_symbol_position'] == 'after') {
                return sprintf("%s%s",$hs['config']['currency'],$money);
            }
            else {
                return sprintf("%s%s",$money,$hs['config']['currency']);
            }
        }
    }
}

function hs_money($money = '0.00',$digits = 0) {
    return hs_price($money,$digits);
}

function hs_money_all($money = '0.00',$digits = 0) {
    global $hs;

    if (is_numeric($money) != true) {
        if ($hs['config']['curr_symbol_position'] == 'after') {
            return sprintf("%s0.00",$hs['config']['currency']);
        }
        else {
            return sprintf("0.00%s",$hs['config']['currency']);
        }
    }

    else {
        $money = number_format($money,$digits, '.', ', ');
        if ($hs['config']['curr_symbol_position'] == 'after') {
            return sprintf("%s%s",$hs['config']['currency'],$money);
        }
        else {
            return sprintf("%s%s",$money,$hs['config']['currency']);
        }
    }
}

function hs_number_count($number = 0) {
    if (not_num($number)) {
        return '0';
    }

    $number  = intval($number);
    $units   = array('', 'K', 'M', 'B');
    $power   = (($number > 0) ? floor(log($number, 1000)) : 0);

    if($power > 0) {
        $pow = fetch_or_get($units[$power],'');
        $num = hs_str_form("{0} {1}",array(number_format($number / pow(1000, $power), 0, ',', ' '),$pow));
        return  $num;
    }
        
    else {
        return number_format($number / pow(1000, $power), 0, ',', ' ');
    }    
}

function hs_file($path = "",$data = array()) {
    global $hs;
    if (file_exists($path) != true) {
        die("File not Exists: $path");
    } 

    $page_content = ''; ob_start(); require($path);
    $page_content = ob_get_contents(); ob_end_clean();
    
    if (not_empty($data) && is_array($data)) {
        foreach ($data as $key => $replace) {
            if(is_array($replace) || is_object($replace) ){
                $arr          = explode('_',$key);
                $k            = strtoupper($arr[0]);
                $replace      = hs_o2array($replace);
                $page_content = preg_replace_callback(sprintf("/{{%s (.*?)}}/",$k), function($m) use ($replace) {
                    return (isset($replace[$m[1]])) ? $replace[$m[1]] : '';
                }, $page_content);
            } 

            else{
                $object_to_replace = sprintf("{{%s}}",$key);
                $page_content      = str_replace($object_to_replace, $replace, $page_content);
            }
        }
    }

    return $page_content;
}

function strip_brs($content = "") {
    return preg_replace('/(<br\s{0,}\/{0,}>\s{0,}){3,}/i', '<br/><br/>', $content);
}

function hs_generate_csrf_token() {
    if (not_empty($_SESSION['csrf'])) {
        return $_SESSION['csrf'];
    }
    
    $hash = substr(sha1(rand(1111, 9999)), 0, 70);
    $slat = time();
    $hash = sprintf('%d:%s',$slat,$hash);

    $_SESSION['csrf'] = $hash;

    return $hash;
}

function hs_verify_csrf_token($hash = '') {
    if (empty($_SESSION['csrf']) || empty($hash)) {
        return false;
    }

    return ($hash == $_SESSION['csrf']) ? true : false;
}

function hs_linkify_urls($text = "") {
    if (empty($text) || is_string($text) != true) {
        return $text;
    }

    else {
        try {
            $text = preg_replace_callback('/(?P<url>https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/is', function($m) {
                if (isset($m['url'])) {
                    return sprintf('<a href="%s" target="_blank">%s</a>',$m['url'],$m['url']);
                }
            }, $text);

            return $text;
        } catch (Exception $e) { /*pass*/ }

        return $text;
    }
}

function hs_get_product_categories($limit = "all", $type = "all") {
    global $db,$hs;

    $status         = in_array($type, array('active','inactive'));
    $db->returnType = 'Array';
    $limit          = (is_number($limit)) ? $limit : null;
    $db             = ($status) ? $db->where('status',$type) : $db;
    $db             = $db->orderBy('sort_order','ASC');
    $categories     = $db->get(T_PROD_CATS,$limit);
    $data           = array();

    if (hs_queryset($categories)) {
        foreach ($categories as $catg_data) {
            $data[$catg_data['catg_id']] = $catg_data['catg_name'];
        }
    }

    return $data;
}

function hs_get_languages($type = "all") {
    global $db,$hs;

    $status         = in_array($type, array('active','inactive'));
    $db->returnType = 'Array';
    $db             = ($status) ? $db->where('status',$type) : $db;
    $db             = $db->orderBy('sort_order','ASC');
    $languages      = $db->get(T_LANGUAGES);
    $data           = array();

    if (hs_queryset($languages)) {
        $data = $languages;
    }

    return $data;
}

function hs_get_currencies($limit = null, $data = null) {
    global $db,$hs;

    $db->returnType = 'Array';
    $limit          = (is_number($limit)) ? $limit : null;
    $data           = (is_array($data)) ? $data : null;
    $currencies     = $db->get(T_CURRENCIES,$limit,$data);
    $data           = array();

    if (hs_queryset($currencies)) {
        $data = $currencies;
    }

    return $data;
}

function hs_br2nl($st) {
    $breaks = array(
        "<br />",
        "<br>",
        "<br/>"
    );
    return str_ireplace($breaks, "\r\n", $st);
}

function hs_inactive_payment_gateways() {
    global $hs;

    $total = 0;

    if ($hs['config']['stripe_gateway_status'] == 'off') {
        $total += 1;
    }

    if ($hs['config']['paypal_gateway_status'] == 'off') {
        $total += 1;
    }

    if ($hs['config']['wallet_gateway_status'] == 'off') {
        $total += 1;
    }

    if ($hs['config']['cod_gateway_status'] == 'off') {
        $total += 1;
    }

    return $total;
}

