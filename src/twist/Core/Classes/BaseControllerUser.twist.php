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

namespace Twist\Core\Classes;
use \Twist\Core\Models\User\SessionHandler;

class BaseControllerUser extends BaseController{

	protected $resUser = null;

	public function __construct(){

		$this->resUser = \Twist::User();

		$this -> _aliasURI( 'change-password', 'changePassword' );
		$this -> _aliasURI( 'forgotten-password', 'forgottenPassword' );
		$this -> _aliasURI( 'verify-account', 'verifyAccount' );
		$this -> _aliasURI( 'device-manager', 'deviceManager' );
	}

	public function login(){
		return $this->resUser->viewExtension('login_form');
	}

	public function forgottenPassword(){
		return $this->resUser->viewExtension('forgotten_password_form');
	}

	public function postForgottenPassword(){

		//Process the forgotten password request
		if(array_key_exists('forgotten_email',$_POST) && $_POST['forgotten_email'] != ''){
			$arrUserData = $this->resUser->getByEmail($_POST['forgotten_email']);

			//Now if the email exists send out the reset password email.
			if(is_array($arrUserData) && count($arrUserData) > 0){

				$resUser = $this->resUser->get($arrUserData['id']);
				$resUser->resetPassword(true);
				$resUser->commit();

				\Twist::Session()->data('site-login_message','A temporary password has been emailed to you');
				\Twist::redirect('./');
			}
		}
	}

	public function changePassword(){
		return $this->resUser->viewExtension('change_password_form');
	}

	public function postChangePassword(){

		if(array_key_exists('password',$_POST) && array_key_exists('confirm_password',$_POST)){

			if($this->resUser->loggedIn()){

				if($_POST['password'] === $_POST['confirm_password']){

					if(\Twist::Session()->data('user-temp_password') === '0'){

						if(array_key_exists('current_password',$_POST)){

							$strNewPassword = $_POST['password'];

							//Change the users password and re-log them in (Only for none-temp password users)
							$this->resUser->changePassword(\Twist::Session()->data('user-id'),$strNewPassword,$_POST['current_password'],true);

							//Remove the two posted password vars
							unset($_POST['password']);
							unset($_POST['current_password']);

							$this->resUser->authenticate(\Twist::Session()->data('user-email'),$strNewPassword,$this->resUser->strLoginUrl,true);
							\Twist::redirect('./');
						}
					}else{

						$strNewPassword = $_POST['password'];

						//Change the users password and re-log them in
						$this->resUser->updatePassword(\Twist::Session()->data('user-id'),$strNewPassword);

						//Remove the posted password and reset the session var
						unset($_POST['password']);
						\Twist::Session()->data('user-temp_password','0');

						$this->resUser->authenticate(\Twist::Session()->data('user-email'),$strNewPassword,$this->resUser->strLoginUrl,true);
						\Twist::redirect('./');
					}

				}else{
					\Twist::Session()->data('site-error_message','The passwords you entered do not match');
					\Twist::redirect('?change');
				}
			}
		}
	}

	public function verifyAccount(){
		return $this->resUser->viewExtension('account_verification');
	}

	public function postVerifyAccount(){

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

	public function deviceManager(){
		return $this->resUser->viewExtension('devices_form');
	}

	public function postDeviceManager(){

		$objUserSessionHandler = new SessionHandler();

		if(array_key_exists('save-device',$_GET) && array_key_exists('device-name',$_GET)){
			$objUserSessionHandler->editDevice($this->resUser->currentID(),$_GET['save-device'],$_GET['device-name']);
		}

		if(array_key_exists('forget-device',$_GET)) {
			$objUserSessionHandler->forgetDevice($this->resUser->currentID(), $_GET['forget-device']);
		}
	}
}