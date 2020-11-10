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

if ($action == 'general_settings') {
	$data['err_field'] =  null;
	$lang_names        =  array();
	$user_data_fields  =  array(
		'fname'        => fetch_or_get($_POST['fname']),
		'lname'        => fetch_or_get($_POST['lname']),
		'bio'          => fetch_or_get($_POST['bio']),
		'email'        => fetch_or_get($_POST['email']),
		'phone'        => fetch_or_get($_POST['phone']),
		'whatsapp'     => fetch_or_get($_POST['whatsapp']),
		'facebook'     => fetch_or_get($_POST['facebook']),
		'instagram'    => fetch_or_get($_POST['instagram']),
		'google_plus'  => fetch_or_get($_POST['google_plus']),
		'twitter'      => fetch_or_get($_POST['twitter']),
		'youtube'      => fetch_or_get($_POST['youtube']),
		'language'     => fetch_or_get($_POST['language']),
		'country_id'   => fetch_or_get($_POST['country_id']),
		'city'         => fetch_or_get($_POST['city']),
		'street'       => fetch_or_get($_POST['street']),
		'state'        => fetch_or_get($_POST['state']),
		'off_apt'      => fetch_or_get($_POST['off_apt']),
		'zip_postal'   => fetch_or_get($_POST['zip_postal']),
	);



    foreach ($lang_array as $lang_data) {
        array_push($lang_names, $lang_data['lang_name']);
    }

	foreach ($user_data_fields as $field_name => $field_val) {
		if ($field_name == 'fname') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("First name field is required for the user and should not be empty!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,3,25) != true) {
	            $data['message']   = hs_translate("The first name field length must be between 3 / 25 characters length");
	            $data['err_field'] = $field_name; break;
	        }

			else if (preg_match('/^[\w\s]+$/',$field_val) != true) {
	            $data['message']   = hs_translate("The first name contains invalid characters. Allowed characters are a-z");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'lname') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The last name field is required for the user and should not be empty!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,3,25) != true) {
	            $data['message']   = hs_translate("The last name field length must be between 3 / 25 characters length");
	            $data['err_field'] = $field_name; break;

	        }

			else if (preg_match('/^[\w\s]+$/',$field_val) != true) {
	            $data['message']   = hs_translate("The last name contains invalid characters. Allowed characters are a-z");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'bio') {
			if (not_empty($field_val)) {
	            if (len_between($field_val,12,215) != true) {
		            $data['message']   = hs_translate("The user bio field length must be between 12 / 220 characters length");
		            $data['err_field'] = $field_name; break;
		        }
	        }
		}

		else if ($field_name == 'email') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The e-mail address field is required for the user and should not be empty!");
	            $data['err_field'] = $field_name; break;
	        }

			else if ($me['email'] != $field_val && hs_email_exists($field_val)) {
	            $data['message']   = hs_translate("This E-mail is already taken!");
	            $data['err_field'] = $field_name; break;
	        }

	        else if (filter_var($field_val, FILTER_VALIDATE_EMAIL) != true || (len($field_val) > 30)) {
	            $data['message']   = hs_translate("This e-mail is invalid. Please enter a valid E-mail address!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'phone') {
			if (not_empty($field_val)) {
		        if (preg_match('/^[0-9\-\(\)\/\+\s]*$/',$field_val) != true) {
		            $data['message']   = hs_translate("The phone number you entered is not vlaid!");
		            $data['err_field'] = $field_name; break;
		        }
		        else if (len($field_val) < 7 || len($field_val) > 15) {
		            $data['message']   = hs_translate("The phone number you entered is not vlaid!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'whatsapp') {
			if (not_empty($field_val)) {
		        if (preg_match('/^[0-9\-\(\)\/\+\s]*$/',$field_val) != true) {
		            $data['message']   = hs_translate("The whatsapp number you entered is not vlaid.");
		            $data['err_field'] = $field_name; break;
		        }
		        else if (len($field_val) < 7 || len($field_val) > 15) {
		            $data['message']   = hs_translate("The whatsapp number you entered is not vlaid.");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if(in_array($field_name, array('youtube','twitter','google_plus','facebook','instagram'))) {
			if (not_empty($field_val) && is_url($field_val) != true) {
				$data['message']   = hs_translate("Please enter a valid url address!");
		        $data['err_field'] = $field_name; break;
			}
		}

		else if ($field_name == 'language') {
			if (not_empty($field_val)) {
		        if (in_array($field_val, $lang_names) != true) {
		            $data['message']   = hs_translate("The user language field value is not valid!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'country_id') {
			if (not_empty($field_val)) {
		        if (in_array($field_val,array_keys($hs['countries'])) != true) {
		            $data['message']   = hs_translate("The user country field value is not valid!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'city') {
			if (not_empty($field_val)) {
		        if (len_between($field_val,3,55) != true) {
		            $data['message']   = hs_translate("Invalid city name. Please check your details!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'state') {
			if (not_empty($field_val)) {
		        if (len_between($field_val,3,45) != true) {
		            $data['message']   = hs_translate("Invalid state name. Please check your details!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'street') {
			if (not_empty($field_val)) {
		        if (len_between($field_val,3,60) != true) {
		            $data['message']   = hs_translate("Invalid street name. Please check your details!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'off_apt') {
			if (not_empty($field_val)) {
		        if (len_between($field_val,4,60) != true) {
		            $data['message']   = hs_translate("Invalid office or apartment address. Please check your details!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}

		else if ($field_name == 'zip_postal') {
			if (not_empty($field_val)) {
		        if (not_num($field_val) || len_between($field_val,5,10) != true) {
		            $data['message']   = hs_translate("Invalid zip/postal code. Please check your details!");
		            $data['err_field'] = $field_name; break;
		        }
			}
		}
	}

	if (empty($data['err_field'])) {
		$user_id     = $me['id'];
        $update_data = array();

        foreach ($user_data_fields as $field_name => $field_val) {
        	if ($field_name == 'fname' || $field_name == 'lname') {
        		$field_val = trim($field_val);
        	}
        	
        	$field_val                = ((not_empty($field_val)) ? hs_secure($field_val) : '');
        	$update_data[$field_name] = $field_val;
        }

        $db     = $db->where('id',$user_id);
        $update = $db->update(T_USERS, $update_data);

        if (not_empty($update)) {
            $data         = array(
	            'status'  => 200,
	            'message' => hs_translate('Your changes have been successfully saved!'),
	        );
        }

        else{
        	$data         = array(
	            'status'  => 400,
	            'message' => 'Error found while processing your request, please try again later.',
	        );
        }
    }
}

else if ($action == 'avatar') {
	$data['message']   = "Error: Invalid request data. Please try again later!";
    $data['err_field'] = 'avatar';
    $data['status']    = 400;
    $update_data       = array();	

    if (not_empty($_FILES['avatar']) && not_empty($_FILES['avatar']['tmp_name'])) {
        $file_info    = array(
            'file'    => $_FILES['avatar']['tmp_name'],
            'size'    => $_FILES['avatar']['size'],
            'name'    => $_FILES['avatar']['name'],
            'type'    => $_FILES['avatar']['type'],
            'crop'    => array('width' => 120, 'height' => 120),
            'allowed' => 'jpg,png,jpeg,gif'
        );

        $file_upload  = hs_upload($file_info);
        if (not_empty($file_upload['filename'])) {
            $update_data['avatar'] = $file_upload['filename'];
            $db                    = $db->where('id', $me['id']);
            $up                    = $db->update(T_USERS, $update_data);
            $data['status']        = 200;
            $data['message']       = hs_translate("Your avatar has been successfully changed!");
            $data['url']           = hs_get_media($file_upload['filename']);
            hs_delete_image($me['avatar']);
        } 
        else{
        	$data['status']  = 500;
        	$data['message'] = "Error found while processing your request, please try again later.";
        }
    }
}

else if ($action == 'password') {
	$data['err_field']      = null;
	$user_data_fields       = array(
		'password'          => fetch_or_get($_POST['password'],null),
		'new_password'      => fetch_or_get($_POST['new_password'],null),
		'conf_new_password' => fetch_or_get($_POST['conf_new_password'],null),
	);

	foreach ($user_data_fields as $field_name => $field_val) {
		if ($field_name == 'password') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The old password field is required!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (password_verify($field_val, $me['password']) != true) {
	            $data['message']   = hs_translate("The currnet password you entered is not valid!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		else if ($field_name == 'new_password') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("The new password field is required!");
	            $data['err_field'] = $field_name; break;
	        }

			else if ($field_val == $user_data_fields['password']) {
	            $data['message']   = hs_translate("The new password is fully consistent with the old.");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len($field_val) < 8) {
	        	$data['message']   = hs_translate("The new password you entered must be at least 8 characters");
	            $data['err_field'] = $field_name; break;
	        }

	        else if (len($field_val) > 20) {
	        	$data['message']   = hs_translate("The new password you entered is too long. Maximum password length is 20 characters!");
	            $data['err_field'] = $field_name; break;
	        }

	        else if ($field_val != $user_data_fields['conf_new_password']) {
	            $data['message']   = hs_translate("Passwords don't match");
	            $data['err_field'] = 'conf_new_password'; break;
	        }
		}
	}

	if (empty($data['err_field'])) {
		$user_id       = $me['id'];
        $update_data   = array(
            'password' => password_hash(hs_secure($user_data_fields['new_password']), PASSWORD_DEFAULT),
        );
        $db     = $db->where('id', $user_id);
        $update = $db->update(T_USERS, $update_data);
        if (not_empty($update)) {
            $data         = array(
	            'status'  => 200,
	            'message' => hs_translate("Your new password have been successfully saved!")
	        );

	        $insert_data  = array(
				'user_id' => $me['id'],
				'title'   => 'The password has been changed!',
				'message' => 'The password of your account was just changed. Please be sure to memorize it or note it in a safe place!',
				'type'    => 'info',
				'static'  => 'Y',
				'time'    => time()
			); $db->insert(T_ANNOUNC,$insert_data);
        }
        else{
        	$data         = array(
	            'status'  => 400,
	            'message' => 'Error found while processing your request, please try again later.',
	        );
        }
    }
}

else if($action == 'delete_account') {
	if (empty($me['is_root'])) {
		$data['err_field']    =  null;
		$user_data_fields     =  array(
			'password'        => fetch_or_get($_POST['password']),
			'deletion_reason' => fetch_or_get($_POST['deletion_reason']),
			'reason_desc'     => fetch_or_get($_POST['reason_desc']),
		);

		foreach ($user_data_fields as $field_name => $field_val) {
			if ($field_name == 'password') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("User password is required. Please enter your current password!");
		            $data['err_field'] = $field_name; break;
		        }

				else if (password_verify($field_val, $me['password']) != true) {
		            $data['message']   = hs_translate("The password you entered is not valid. Please enter your account password!");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if ($field_name == 'deletion_reason') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("The account deletion reason field value is required. Please select the reason of your leave!");
		            $data['err_field'] = $field_name; break;
		        }

				else if (in_array($field_val, range(1, 5)) != true) {
		            $data['message']   = hs_translate("The account deletion reason field value is invalid!");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if ($field_name == 'reason_desc') {
				if (not_empty($field_val)) {
					if (len_between($field_val,1,590) != true) {
						$data['message']   = hs_translate("The account deletion message is too long. The maximum message length is 600!");
		            	$data['err_field'] = $field_name; break;
					}
		        }
			}
		}

		if (empty($data['err_field'])) {
			$user_id      = $me['id'];
			$req_exists   = array('user_id' => $user_id);
	        $insert_data  = array(
	        	'user_id' => $user_id,
	        	'time'    => time(),
	        );

	        if (hs_where_exists(T_ACC_DEL_REQS,$req_exists) != true) {
		        foreach ($user_data_fields as $field_name => $field_val) {
		        	$field_val = ((not_empty($field_val)) ? hs_secure($field_val) : '');
		        	
		        	if ($field_name == 'deletion_reason') {
		        		$insert_data['reason'] = $field_val;
		        	}
		        	elseif ($field_name == 'reason_desc') {
		        		$insert_data['message'] = $field_val;
		        	}
		        	
		        }; $insert = $db->insert(T_ACC_DEL_REQS, $insert_data);

		        if (is_number($insert)) {
		            $data         = array(
			            'status'  => 200,
			            'message' => hs_translate('Your account deletion request has been successfulyy sent.'),
			        );

			        $insert_data  =  array(
						'user_id' => $user_id,
						'title'   => 'Account deletion request sent!',
						'message' => 'You have successuflly entered an account deletion request. You will be notified by email or the administration will contact you, once we consider your request!',
						'type'    => 'warning',
						'static'  => 'Y',
						'time'    => time()
					); $db->insert(T_ANNOUNC,$insert_data);
		        }

		        else{
		        	$data         =  array(
			            'status'  => 400,
			            'message' => 'Error found while processing your request, please try again later.',
			        );
		        }
	        }
	        else {
	        	$data         =  array(
		            'status'  => 400,
		            'message' => 'You have already entered your account deletion request!',
		        );
	        }
	    }
	}

	else {
	    $data['status'] = 400;
	}
}

else if ($action == 'add_addr') {
	$data['err_field'] = 0;
	$data['status']    = 400;
	$user_data_fields  = array(
		'full_name'    => fetch_or_get($_POST['full_name'],null),
		'phone'        => fetch_or_get($_POST['phone'],null),
		'street'       => fetch_or_get($_POST['street'],null),
		'off_apt'      => fetch_or_get($_POST['off_apt'],null),
		'country_id'   => fetch_or_get($_POST['country_id'],null),
		'state'        => fetch_or_get($_POST['state'],null),
		'city'         => fetch_or_get($_POST['city'],null),
		'zip_postal'   => fetch_or_get($_POST['zip_postal'],null),
		'default'      => fetch_or_get($_POST['default'],null),
		'email'        => fetch_or_get($_POST['email'],null),
	);

	foreach ($user_data_fields as $field_name => $field_val) {
		if ($field_name == 'full_name') {
			if (empty($field_val)) {
	            $data['message']   = hs_translate("Please provide your name in English as in your ID (first name, last name, middle name)");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,6,32) != true) {
	            $data['message']   = hs_translate("The full name field length must be between 6 - 32 characters");
	            $data['err_field'] = $field_name; break;
	        }
		}

		if ($field_name == 'phone') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("The contact telephone field is required!");
	            $data['err_field'] = $field_name; break;
			}

			else if (preg_match('/^[0-9\-\(\)\/\+\s]*$/', $field_val) != true) {
	            $data['message']   = hs_translate("Phone number you entered is not vlaid!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,7,18) != true) {
	            $data['message']   = hs_translate("Phone number you entered is not vlaid!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		if ($field_name == 'street') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("The address street name field is required!");
	            $data['err_field'] = $field_name; break;
			}

			else if (len_between($field_val,6,60) != true) {
	            $data['message']   = hs_translate("The street name field length must be between 6 - 60 characters!");
	            $data['err_field'] = $field_name; break;
	        }
		}

		if ($field_name == 'off_apt') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("Please enter your apartment or office address");
	            $data['err_field'] = $field_name; break;
			}

			else if (len_between($field_val,6,60) != true) {
	            $data['message']   = hs_translate("The office/apartment field length must be between 6 - 60 characters");
	            $data['err_field'] = $field_name; break;
	        }
		}

		if ($field_name == 'country_id') {
			if (not_num($field_val)) {
				$data['message']   = hs_translate("Delivery country is requred. Please select the shipping country!");
	            $data['err_field'] = $field_name; break;
			}

			else if (in_array($field_val, array_keys($hs['countries'])) != true) {
		        $data['message']   = hs_translate("The country name you selected is not valid!");
		        $data['err_field'] = $field_name; break;
			}
		}

		if ($field_name == 'state') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("Please enter your country state name!");
	            $data['err_field'] = $field_name; break;
			}
			else if (len_between($field_val,6,32) != true) {
		        $data['message']   = hs_translate("The state name field length must be between 6 - 32 characters");
		        $data['err_field'] = $field_name; break;
			}
		}

		if ($field_name == 'city') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("The address city name field is required!");
	            $data['err_field'] = $field_name; break;
			}

			else if (len_between($field_val,4,60) != true) {
		        $data['message']   = hs_translate("The city name field length must be between 4 - 60 characters!");
		        $data['err_field'] = $field_name; break;
			}
		}

		if ($field_name == 'zip_postal') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("Please enter your postal (ZIP) code number!");
	            $data['err_field'] = $field_name; break;
			}
			else if (len_between($field_val,6,11) != true) {
		        $data['message']   = hs_translate("Invalid zip/postal code. Please check your details.");
		        $data['err_field'] = $field_name; break;
			}
		}

		if ($field_name == 'email') {
			if (empty($field_val)) {
				$data['message']   = hs_translate("Please eneter your contact E-Mail address!");
	            $data['err_field'] = $field_name; break;
			}

			else if (filter_var($field_val, FILTER_VALIDATE_EMAIL) != true) {
	            $data['message']  = hs_translate("The email address you entered is not valid!");
	            $data['err_field'] = $field_name; break;
	        }

			else if (len_between($field_val,6,30) != true) {
	            $data['message']   = hs_translate("The email address you entered is not valid!");
	            $data['err_field'] = $field_name; break;
	        }
		}
	}

	if (empty($data['err_field'])) {
		$user_id         = $me['id'];
        $insert_data     = array(
        	'full_name'  => hs_secure($user_data_fields['full_name']),
        	'phone'      => hs_secure($user_data_fields['phone']),
        	'street'     => hs_secure($user_data_fields['street']),
        	'country_id' => hs_secure($user_data_fields['country_id']),
        	'city'       => hs_secure($user_data_fields['city']),
        	'state'      => hs_secure($user_data_fields['state']),
        	'zip_postal' => hs_secure($user_data_fields['zip_postal']),
        	'off_apt'    => hs_secure($user_data_fields['off_apt']),
        	'email'      => hs_secure($user_data_fields['email']),
        	'user_id'    => intval($me['id']),
        ); $insert_id    = $db->insert(T_DELIV_ADDRS, $insert_data);

        

        if (is_number($insert_id)) {
            $data         = array(
	            'status'  => 200,
	            'message' => hs_translate('Your new delivery address has been successfully saved. Please wait'),
	        );

	        if (not_empty($_POST['default'])) {
	        	$db->where('id',$me['id'])->update(T_USERS,array(
	        		'deliv_addr' => $insert_id
	        	));
	        }
        }

        else{
        	$data         = array(
	            'status'  => 500,
	            'message' => 'Error found while processing your request, please try again later.',
	        );
        }
    }
}

else if($action == 'deliv_addr') {
	if (not_empty($_POST['addr_id']) && (is_number($_POST['addr_id']) || strval($_POST['addr_id']) == 'default')) {
		if (not_empty($_POST['act']) && in_array(strval($_POST['act']), array('set','del'))) {
			$addr_id = hs_secure($_POST['addr_id']);
			$act     = hs_secure($_POST['act']);

			if (is_number($addr_id)) {
				$deliv_addresses   = T_DELIV_ADDRS;
				$check_addr_exists = array(
					'id'           => $addr_id,
					'user_id'      => $me['id'],
				);

				if (hs_where_exists($deliv_addresses,$check_addr_exists)) {
					if ($act == 'set') {
						$db              = $db->where('id',$me['id']);
						$up              = $db->update(T_USERS,array('deliv_addr' => $addr_id));
						$data['status']  = 200;
						$data['message'] = hs_translate('Address has been changed successfully. Please wait...');
						$data['code']    = 'set';
					}
					else {
						$db              = $db->where('id',$addr_id);
						$rm              = $db->delete($deliv_addresses);
						$data['status']  = 200;
						$data['message'] = hs_translate('Address has been deleted successfully!');
						$data['code']    = 'del';
						$data['addr']    = $addr_id;
						$db              = $db->where('id',$me['id']);
						$up              = $db->update(T_USERS,array(
							'deliv_addr' => 'default'
						));
					}
				}
				else {
					$data['status']  = 400;
					$data['message'] = 'Error: 400 Invalid request data. Please check your details';
				}
			}
			else {
				$data['message'] = hs_translate('Address has been changed successfully. Please wait...');
				$db              = $db->where('id',$me['id']);
				$up              = $db->update(T_USERS,array(
					'deliv_addr' => 'default'
				));
				$data['status']  = 200;
				$data['code']    = 'set';
			}
		}
	}
}

else if($action == 'tup_balance') {
	if (not_num($_POST['amount'])) {
		$data['message']  = hs_translate("Please enter the top up amount");
	    $data['err_code'] = 1;
	    $data['status']   = 400;
	}

	else if($_POST['amount'] < $hs['config']['min_topup']) {
		$data['message']  = hs_translate("The amount you entered is less than the minimum amount to replenish the balance!");
	    $data['err_code'] = 2;
	    $data['status']   = 400;
	}

	else {
		$tup_amount    = intval($_POST['amount']);
		$currency      = strtoupper($hs['config']['market_currency']);
		$payer         = new \PayPal\Api\Payer();
		$itemList      = new \PayPal\Api\ItemList();
		$details       = new \PayPal\Api\Details();
		$amount        = new \PayPal\Api\Amount();
		$transaction   = new \PayPal\Api\Transaction();
		$redirectUrls  = new \PayPal\Api\RedirectUrls();
		$payment       = new \PayPal\Api\Payment();
		$line_item     = new \PayPal\Api\Item();
		$inputFields   = new \PayPal\Api\InputFields();
		$webProfile    = new \PayPal\Api\WebProfile();

		$payer         = $payer->setPaymentMethod('paypal');
		$subtotal      = $tup_amount;
		$url_success   = hs_str_form("{0}/req/main_settings/tup_balance_success",array($config['url']));
		$url_cancel    = hs_str_form("{0}/req/main_settings/tup_balance_cancel",array($config['url']));

		$inputFields   = $inputFields->setAllowNote(true);
		$inputFields   = $inputFields->setNoShipping(1);
		$inputFields   = $inputFields->setAddressOverride(0);
		
		$webProfile    = $webProfile->setName(uniqid());
        $webProfile    = $webProfile->setInputFields($inputFields);
        $webProfile    = $webProfile->setTemporary(true);

		$createProfile = $webProfile->create($paypal);
		$profileID     = $createProfile->getId();
		$payment       = $payment->setExperienceProfileId($profileID); 

		#Set redirect URLS
		$redirectUrls  = $redirectUrls->setReturnUrl($url_success);
		$redirectUrls  = $redirectUrls->setCancelUrl($url_cancel); 

		$line_item     = $line_item->setName(hs_translate('Top up your account balance'));
		$line_item     = $line_item->setQuantity(1);
		$line_item     = $line_item->setPrice($tup_amount);
		$line_item     = $line_item->setCurrency($currency);

		#Set items to be purchased
		$itemList      = $itemList->setItems(array($line_item)); 
		$details       = $details->setSubtotal($subtotal);

		#Set amount
		$amount        = $amount->setCurrency($currency);
		$amount        = $amount->setTotal($subtotal);
		$amount        = $amount->setDetails($details);

		$transaction   = $transaction->setAmount($amount);
		$transaction   = $transaction->setItemList($itemList);
		$transaction   = $transaction->setDescription(hs_translate('Pay to: {%site_name%}',array('site_name' => $hs['config']['name'])));
		$transaction   = $transaction->setInvoiceNumber(time());
		$payment       = $payment->setIntent('order');
		$payment       = $payment->setPayer($payer);
		$payment       = $payment->setRedirectUrls($redirectUrls);
		$payment       = $payment->setTransactions(array($transaction));

		try {
			$payment          = $payment->create($paypal);
			$data['url']      = $payment->getApprovalLink();
			$data['message']  = hs_translate('Payment created successfully. Please wait. Redirecting ...');
			$data['status']   = 200;
			$temp_save_amount = hs_temp_data_set('balance_tup_amount',$tup_amount);
		}

		catch (Exception $ex) {
			$data['error'] = $ex;
		}
	}
}

else if($action == 'tup_balance_success') {
	if (not_empty($_GET['paymentId']) && not_empty($_GET['token']) && not_empty($_GET['PayerID'])) {
		$paym_id  = hs_secure(strval($_GET['paymentId']));
		$paym_tok = hs_secure(strval($_GET['token']));
		$payer_id = hs_secure(strval($_GET['PayerID']));

		$payment  = \PayPal\Api\Payment::get($paym_id, $paypal);
	    $execute  = new \PayPal\Api\PaymentExecution();
	    $execute  = $execute->setPayerId($payer_id);

	    try{
	    	$top_up_amount  = hs_temp_data_get('balance_tup_amount');
	        $result         = $payment->execute($execute, $paypal);
	        $curr_balance   = ($me['wallet_val'] += $top_up_amount);
	        $insert_data    = array(
				'user_id'   => $me['id'],
				'title'     => 'Top-up completed successfully!',
				'message'   => 'Your account wallet has been successfully replenished, and now your balance is: {%balance%}',
				'type'      => 'success',
				'static'    => 'Y',
				'json_data' => json(array('balance' => $curr_balance),true),
				'time'      => time()
			); $db->insert(T_ANNOUNC,$insert_data);

			$up = $db->where('id',$me['id'])->update(T_USERS,array(
				'wallet' => $curr_balance
			)); 

			#Unblock seller blocked goods
			hs_block_unblock_seller_products($me['id']);

			hs_temp_data_delete('balance_tup_amount');
			hs_redirect('merchant_panel/wallet');
	    }

	    catch (Exception $e) {
			$insert_data  =  array(
				'user_id' => $me['id'],
				'title'   => 'Error: Top up transaction failed!',
				'message' => 'Your account balance replenishment failed. Please wait and try your request again later!',
				'type'    => 'error',
				'static'  => 'Y',
				'time'    => time()
			);

			$ins = $db->insert(T_ANNOUNC,$insert_data);
			hs_redirect('merchant_panel/wallet');
		}
	}
}

else if($action == 'tup_balance_cancel') {
	hs_temp_data_delete('balance_tup_amount');
	$data['status']  = 400;
	$data['message'] = 'Error found while processing your request, please try again later.';
}

else if ($action == 'request_verification') {
	$data['message']    =  "Error: Invalid request data. Please try again later!";
    $data['status']     =  400;
    $me['verif_status'] =  hs_get_verif_status($me['id']);
    $data_fields        =  array(
		'full_name'     => fetch_or_get($_POST['full_name']),
		'message2rev'   => fetch_or_get($_POST['message2rev']),
		'identity'      => null,
		'photo'         => null,
	);

	if ($me['verif_status'] == 'none') {
		foreach ($data_fields as $field_name => $field_val) {
			if ($field_name == 'full_name') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("Please enter your full name");
		            $data['err_field'] = $field_name; break;
		        }

				else if (len_between($field_val,6,32) != true) {
		            $data['message']   = hs_translate("Please enter your full name with a length of 6/32 characters");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if ($field_name == 'message2rev') {
				if (empty($field_val)) {
		            $data['message']   = hs_translate("Please enter a message for the reviewer");
		            $data['err_field'] = $field_name; break;
		        }

				else if (len_between($field_val,1,590) != true) {
		            $data['message']   = hs_translate("The message for the reviewer that you entered is too long. Maximum length 600 characters");
		            $data['err_field'] = $field_name; break;
		        }
			}

			else if($field_name == 'identity') {
				if (empty($_FILES['identity']) || empty($_FILES['identity']['tmp_name'])) {
					$data['message']   = hs_translate("Please select a photo of your passport/ID document");
		            $data['err_field'] = $field_name; break;
				}
			}

			else if($field_name == 'photo') {
				if (empty($_FILES['photo']) || empty($_FILES['photo']['tmp_name'])) {
					$data['message']   = hs_translate("Please select your personal photo.");
		            $data['err_field'] = $field_name; break;
				}
			}
		}

		if (empty($data['err_field'])) {
	        $file_upload1  =  hs_upload(array(
	            'file'     => $_FILES['identity']['tmp_name'],
	            'size'     => $_FILES['identity']['size'],
	            'name'     => $_FILES['identity']['name'],
	            'type'     => $_FILES['identity']['type'],
	            'allowed'  => 'jpg,png,jpeg,gif'
	        ));

	        $file_upload2  =  hs_upload(array(
	            'file'     => $_FILES['photo']['tmp_name'],
	            'size'     => $_FILES['photo']['size'],
	            'name'     => $_FILES['photo']['name'],
	            'type'     => $_FILES['photo']['type'],
	            'allowed'  => 'jpg,png,jpeg,gif'
	        ));

	        if (not_empty($file_upload1['filename']) && not_empty($file_upload2['filename'])) {
	        	$insert_data    =  array(
	        		'user_id'   => $me['id'],
	        		'full_name' => $data_fields['full_name'],
	        		'message'   => $data_fields['message2rev'],
	        		'id_photo'  => $file_upload1['filename'],
	        		'pr_photo'  => $file_upload2['filename'],
	        		'status'    => 'pending',
	        		'time'      => time(),
	        	);

	        	$insert_id = $db->insert(T_VERIF_REQS,$insert_data);

	        	if (is_number($insert_id)) {
	        		$data         =  array(
			            'status'  => 200,
			            'message' => hs_translate("Your verification request has been sent successfully!")
			        );

			        $insert_data  = array(
						'user_id' => $me['id'],
						'title'   => 'Verification request submitted!',
						'message' => 'Your verification request has been sent successfully. We will let you know as soon as we review your request for a verified badge.',
						'type'    => 'info',
						'static'  => 'Y',
						'time'    => time()
					); $db->insert(T_ANNOUNC,$insert_data);
	        	}
	        	else {
	        		$data['status']  = 400;
					$data['message'] = 'Error found while processing your request, please try again later.';
	        	}
	        }

	        else {
	        	if (not_empty($file_upload1['filename'])) {
	        		hs_delete_image($file_upload1['filename']);
	        	}

	        	if (not_empty($file_upload2['filename'])) {
	        		hs_delete_image($file_upload2['filename']);
	        	}
	        }
		}
	}
	else {
		$data['status']  = 401;
		$data['message'] = 'Error found while processing your request, please try again later.';
	}
}
