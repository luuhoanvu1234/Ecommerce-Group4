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

if($action == 'get_prods') {
	$data['status'] = 404;
	$html_arr       = array();

	if (is_number($_GET['offset'])) {
		$offset      = intval($_GET['offset']);
		$hs['prods'] = hs_get_preview_products(array('offset' => $offset));
		if (not_empty($hs['prods'])) {
			foreach ($hs['prods'] as $hs['prod_item']) {
				array_push($html_arr, hs_loadpage('auth/includes/prod_item'));
			}

			$data['status'] = 200;
			$data['html']   = implode('', $html_arr);
		}
	}
}

else if ($action == 'register_user') {
	$data['err_field'] =  null;
	$user_data_fileds  =  array(
		'fname'        => fetch_or_get($_POST['fname'],null),
		'lname'        => fetch_or_get($_POST['lname'],null),
		'email'        => fetch_or_get($_POST['email'],null),
		'password'     => fetch_or_get($_POST['password'],null),
		'conf_pass'    => fetch_or_get($_POST['conf_pass'],null),
	);

	foreach ($user_data_fileds as $field_name => $field_val) {
		if ($field_name == 'fname') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The first name field is required!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,3, 32) != true) {
	            $data['message']   = hs_translate("The first name field length must be between 3 / 25 length characters!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (preg_match('/^[\w\s]+$/', $field_val) != true) {
	            $data['message']   = hs_translate("The first name has invalid characters. Allowed characters are a-z!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'lname') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The last name field is required!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,3, 32) != true) {
	            $data['message']   = hs_translate("The last name field length must be between 3 / 25 length characters!");
	            $data['err_field'] = $field_name; break;
	        }


			else if (preg_match('/^[\w\s]+$/', $field_val) != true) {
	            $data['message']   = hs_translate("The last name has invalid characters. Allowed characters are a-z!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'email') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The email address field is required!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (hs_email_exists($field_val)) {
	            $data['message']   = hs_translate("This e-mail is already taken!");
	            $data['err_field'] = $field_name; break;
	        }

	        else if (filter_var($field_val, FILTER_VALIDATE_EMAIL) != true || len($field_val) > 30) {
	            $data['message']   = hs_translate("This e-mail is invalid");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'password') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The password field is required!");
	            $data['err_field'] = $field_name; break;
	        }

	        else if (len($field_val) < 6) {
	        	$data['message']   = hs_translate("The password must be at least 6 characters!");
	            $data['err_field'] = $field_name; break;
	        }

	        else if (len($field_val) > 20) {
	        	$data['message']   = hs_translate("The password you entered is too long!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if($field_name == 'conf_pass') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("Please confirm your password!");
	            $data['err_field'] = $field_name; break;
	        }

	        else if ($field_val != $user_data_fileds['password']) {
	            $data['message']   = hs_translate("Passwords don't match");
	        	$data['err_field'] = $field_name; break;
	        }
		}
	}

	if (empty($data['err_field'])) {
		$active_user       =  (($hs['config']['acc_validation'] == 'on') ? '0' : '1');
        $email_code        =  sha1(time() + rand(111,999));
        $password_hashed   =  password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_ip           =  get_ip();
        $user_ip           =  ((filter_var($user_ip, FILTER_VALIDATE_IP) == true) ? $user_ip : '0.0.0.0');
        $insert_data       =  array(
            'fname'        => hs_secure($_POST['fname']),
            'lname'        => hs_secure($_POST['lname']),
            'password'     => $password_hashed,
            'email'        => hs_secure($_POST['email']),
            'active'       => $active_user,
            'em_code'      => $email_code,
            'last_active'  => time(),
            'joined'       => time(),
            'ipv4_address' => $user_ip,
            'language'     => $hs['config']['language'],
        ); $user_id        =  $db->insert(T_USERS, $insert_data);


        if (is_number($user_id)) {
        	hs_create_user_session($user_id,'web');
            $data['status'] = 200;
            $u_fname        = $insert_data['fname'];
            $u_lname        = $insert_data['lname'];
            $u_email        = $insert_data['email'];
            $fname          = strtolower(preg_replace('/[\s]/', '',$u_fname));
            $lname          = strtolower(preg_replace('/[\s]/', '',$u_lname));

            hs_update_user_data($user_id,array(
            	'username' => (sprintf("%s_%s@%d",$fname,$lname,$user_id)),
            ));
            
            require_once('core/controllers/event_handlers/on_user_signup.php');

            if ($hs['config']['acc_validation'] == 'on') {
            	$send_email_data     =  array(
	           		'from_email'     => $hs['config']['email'],
	           		'from_name'      => $hs['config']['name'],
	           		'to_email'       => $u_email,
	           		'to_name'        => sprintf("%s %s",$u_fname,$u_lname),
	           		'subject'        => hs_translate("Activate your account"),
	           		'charSet'        => 'UTF-8',
	           		'is_html'        => true,
	           		'message_body'   => hs_loadpage('emails/activate_account', array(
	           			"name"       => sprintf("%s %s",$u_fname,$u_lname),
	           			"em_code"    => $email_code,
	           		)),
	           	); hs_send_mail($send_email_data);
            }
        }
    }
}

else if ($action == 'login') {
	$data['err_field'] = 0;
	$user_data_fileds  = array(
		'email'        => fetch_or_get($_POST['email'],null),
		'password'     => fetch_or_get($_POST['password'],null),
	);

	foreach ($user_data_fileds as $field_name => $field_val) {
		if ($field_name == 'email') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("Please enter your login!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len($field_val) > 32) {
	            $data['message']   = hs_translate("Invalid username or password!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		if ($field_name == 'password') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("Please enter your password!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len($field_val) > 20) {
	            $data['message']   = hs_translate("Invalid username or password!");
	            $data['err_field'] = $field_name; break;
	        }
		}
	}

	if (empty($data['err_field'])) {
        $email    = hs_secure($user_data_fileds['email']);
        $password = hs_secure($user_data_fileds['password']);

        $db       = $db->where("email",$email);
        $get_user = $db->getOne(T_USERS, array("password", "id", "active"));

        if (hs_queryset($get_user,'object') != true) {
        	$data['message']   = hs_translate("Invalid username or password!");
        	$data['err_field'] = 'password';
        } 

        else if (password_verify($password, $get_user->password) != true) {
        	$data['message']   = hs_translate("Invalid username or password!");
        	$data['err_field'] = 'password';
        } 

        if (empty($data['err_field'])) {   
        	$user_ip           = get_ip();
        	$user_ip           = ((filter_var($user_ip, FILTER_VALIDATE_IP) == true) ? $user_ip : '0.0.0.0');
            $db                = $db->where('user_id',$get_user->id);
            $db                = $db->where('platform','web');
	        $old_sessions      = $db->get(T_SESSIONS);
	        $session_id        = hs_create_user_session($get_user->id,'web');
	        $old_sessions      = ((hs_queryset($old_sessions)) ? hs_o2array($old_sessions) : array());
            $data['status']    = 200; hs_update_user_data($get_user->id,array('ipv4_address' => $user_ip));

	        foreach ($old_sessions as $sess_data) {
	        	$db  = $db->where('login_sess_id',$sess_data['id']);
	        	$tot = $db->getValue(T_ADMIN_SESSIONS,'COUNT(*)');
	        	if (empty($tot)) {
	        		$db->where('id',$sess_data['id'])->delete(T_SESSIONS);
	        	}
	        }
        }
    }
}

else if ($action == 'reset_password') {
	$data['err_field'] = 0;
	$data['status']    = 400;
	$email_addr        = fetch_or_get($_POST['email'],null);

    if (empty($email_addr)) {
        $data['message']   = hs_translate("Please enter your email address!");
	    $data['err_field'] = 'email';
    } 

    else if (filter_var($email_addr, FILTER_VALIDATE_EMAIL) != true) {
        $data['message']   = hs_translate("Please enter a valid email address!");
        $data['err_field'] = 'email';
    }

    else {

        $email = hs_secure($email_addr);
        $db    = $db->where("email",$email);
        $me    = $db->getOne(T_USERS, array("password", "id", "em_code","fname","lname"));

        if (empty($me)) {
        	$data['message']   = hs_translate("We cannot find an account with this email address!");
        	$data['err_field'] = 'email';
        }

        if (empty($data['err_field'])) {
        	$user_id             = $me->id;
            $email_code          = sha1(rand(11111, 99999) . $me->password);
            $db                  = $db->where('id', $me->id);
            $update              = $db->update(T_USERS, array('em_code' => $email_code));
            $me                  = hs_o2array($me);
            $me['em_code']       = $email_code;
            $send_email_data     = array(
           		'from_email'     => $hs['config']['email'],
           		'from_name'      => $hs['config']['name'],
           		'to_email'       => $email,
           		'to_name'        => sprintf("%s %s",$me['fname'],$me['lname']),
           		'subject'        => hs_translate("Reset your password"),
           		'charSet'        => 'UTF-8',
           		'is_html'        => true,
           		'message_body'   => hs_loadpage('emails/reset_password', array(
           			"name"       => sprintf("%s %s",$me['fname'],$me['lname']),
           			"em_code"    => $me['em_code'] ,
           		)),
           	); $hs['send_email'] = hs_send_mail($send_email_data);

            if (not_empty($hs['send_email'])) {
            	$data         = array(
		            'status'  => 200,
		            'html'    => hs_loadpage("auth/includes/reset_alert_success")
		        );
            } 
            else {
            	$data         = array(
		            'status'  => 500,
		            'html'    => hs_loadpage("auth/includes/reset_alert_error")
		        );
	        }
        }
    }
}

else if ($action == 'create_new_password') {
	$data['err_field']  = 0;
	$data['status']     = 400;
	$user_data_fileds   = array(
		'em_code'       => fetch_or_get($_POST['em_code'],null),
		'password'      => fetch_or_get($_POST['password'],null),
		'conf_password' => fetch_or_get($_POST['conf_password'],null),
	);

	foreach ($user_data_fileds as $field_name => $field_val) {
		if ($field_name == 'em_code') {
			if (empty($field_val) || len($field_val) > 130) {
	            $data['message']   = hs_translate("Invalid request data. Please check your details!");
	            $data['err_field'] = $field_name; break;
	        }
		} 

		else if ($field_name == 'password') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate('Please enter a new password!');
	            $data['err_field'] = $field_name; break;
	        }
	        else if (len($field_val) < 6) {
	        	$data['message']   = hs_translate("Password must be at least 6 characters");
	            $data['err_field'] = $field_name; break;
	        }
	        else if (len($field_val) > 20) {
	        	$data['message']   = hs_translate("Password you entered is too long!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'conf_password') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("Please confirm your new password!");
	            $data['err_field'] = $field_name; break;
	        }
	        else if ($user_data_fileds['password'] != $field_val) {
	            $data['message']   = hs_translate("Passwords don't match");
	            $data['err_field'] = $field_name; break;
	        }
		}
	}

    $password    = hs_secure($user_data_fileds['password']);
    $c_password  = hs_secure($user_data_fileds['conf_password']);
    $email_code  = hs_secure($user_data_fileds['em_code']);
    $passwd_hash = password_hash($password, PASSWORD_DEFAULT);

    if (empty($data['err_field'])) {
    	$db      = $db->where('em_code', $email_code);
    	$user_id = $db->getValue(T_USERS, "id");
    	if (is_number($user_id)) {
    		$data['status'] = 200;
	    	$email_code     = sha1(time() + rand(1111,9999));
	    	$update         = hs_update_user_data($user_id, array(
	    		'password'  => $passwd_hash, 
	    		'em_code'   => $email_code
	    	)); hs_create_user_session($user_id);
    	}
    	else{
    		$data         = array(
                'status'  => 500,
                'message' => 'An error occurred while processing your request. Please try again later!',
            );
    	}
    }
}
?>