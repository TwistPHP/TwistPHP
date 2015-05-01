<?php
/**
 * This file is part of TwistPHP.
 *
 * TwistPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TwistPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       https://twistphp.com
 *
 */

namespace Twist\Core\Controllers;
use Twist\Core\Classes\BaseController;

class User extends BaseController{

	protected $resUser = null;

	public function __construct(){
		$this->resUser = \Twist::User();
	}

	public function __default(){
		return $this->resUser->templateExtension('login_form');
	}

	public function forgotten(){
		return $this->resUser->templateExtension('forgotten_password_form');
	}

	public function postForgotten(){

		//Process the forgotten password request
		if(array_key_exists('forgotten_email',$_POST) && $_POST['forgotten_email'] != ''){
			$arrUserData = $this->resUser->getByEmail($_POST['forgotten_email']);

			//Now if the email exists send out the reset password email.
			if(is_array($arrUserData) && count($arrUserData) > 0){

				$resUser = $this->resUser->get($arrUserData['id']);
				$resUser->resetPassword(true);
				$resUser->commit();

				\Twist::Session()->data('site-login_message','A temporary password has been emailed to you');
				$this->resUser->goToPage('./', false );
			}
		}
	}

	public function change(){
		return $this->resUser->templateExtension('change_password_form');
	}

	public function postChange(){

		if(array_key_exists('password',$_POST) && array_key_exists('confirm_password',$_POST)){

			if($this->resUser->loggedIn()){

				if($_POST['password'] == $_POST['confirm_password']){

					if(\Twist::Session()->data('user-temp_password') == '0'){

						if(array_key_exists('current_password',$_POST)){

							$strNewPassword = $_POST['password'];

							//Change the users password and re-log them in (Only for none-temp password users)
							$this->resUser->changePassword(\Twist::Session()->data('user-id'),$strNewPassword,$_POST['current_password'],true);

							//Remove the two posted password vars
							unset($_POST['password']);
							unset($_POST['current_password']);

							$this->resUser->authenticate(\Twist::Session()->data('user-email'),$strNewPassword,$this->resUser->strLoginUrl,true);
							$this->resUser->goToPage('./',false);
						}
					}else{

						$strNewPassword = $_POST['password'];

						//Change the users password and re-log them in
						$this->resUser->updatePassword(\Twist::Session()->data('user-id'),$strNewPassword);

						//Remove the posted password and reset the session var
						unset($_POST['password']);
						\Twist::Session()->data('user-temp_password','0');

						$this->resUser->authenticate(\Twist::Session()->data('user-email'),$strNewPassword,$this->resUser->strLoginUrl,true);
						$this->resUser->goToPage('./',false);
					}

				}else{
					\Twist::Session()->data('site-error_message','The passwords you entered do not match');
					$this->resUser->goToPage('?change',false);
				}
			}
		}
	}

	public function verify(){
		return $this->resUser->templateExtension('account_verification');
	}

	public function postVerify(){

		//Resend a new verification code
		if(array_key_exists('verification_email',$_POST) && $_POST['verification_email'] != ''){
			$arrUserData = $this->resUser->getByEmail($_POST['verification_email']);

			//Now if the email exists send out the reset password email.
			if(is_array($arrUserData) && count($arrUserData) > 0){

				$resUser = $this->resUser->get($arrUserData['id']);
				$resUser->requireVerification();
				$resUser->commit();
			}
		}

		if(array_key_exists('verify',$_GET) && array_key_exists('verify',$_GET) && $_GET['verify'] != ''){
			$this->resUser->verifyEmail($_GET['verify']);
		}
	}

	public function register(){
		return $this->resUser->templateExtension('registration_form');
	}

	public function postRegister(){

		//Process the register user request
		if(array_key_exists('register',$_POST) && $_POST['register'] != ''){

			$resUser = $this->resUser->create();
			$resUser->email($_POST['email']);
			$resUser->firstname($_POST['firstname']);
			$resUser->surname($_POST['lastname']);
			$resUser->level(10);

			$blContinue = true;

			if(\Twist::framework()->setting('USER_REGISTER_PASSWORD')){

				if($_POST['password'] == $_POST['confirm_password']){
					$arrResponse = $resUser->password($_POST['password']);

					if($arrResponse['status'] == false){
						\Twist::Session()->data('site-register_error_message',$arrResponse['message']);
						$blContinue = false;
					}
				}else{
					\Twist::Session()->data('site-register_error_message','Your password and confirm password do not match');
					$blContinue = false;
				}
			}else{
				$resUser->resetPassword();
			}

			//If the password configuration has passed all checks then continue
			if($blContinue){
				$intUserID = $resUser->commit();

				if($intUserID > 0){
					\Twist::Session()->data('site-register_message','Thank you for your registration, your password has been emailed to you');
					unset( $_POST['email'] );
					unset( $_POST['firstname'] );
					unset( $_POST['lastname'] );
					unset( $_POST['register'] );
				}else{
					\Twist::Session()->data('site-register_error_message','Failed to register user');
				}
			}
		}
	}

	public function devices(){
		return $this->resUser->templateExtension('devices_form');
	}

	public function postDevices(){

		if(array_key_exists('save-device',$_GET) && array_key_exists('device-name',$_GET)){
			$this->resUser->objUserSession->editDevice($this->resUser->currentID(),$_GET['save-device'],$_GET['device-name']);
		}

		if(array_key_exists('forget-device',$_GET)) {
			$this->resUser->objUserSession->forgetDevice($this->resUser->currentID(), $_GET['forget-device']);
		}
	}
}