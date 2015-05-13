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

	public function login(){
		return $this->resUser->templateExtension('login_form');
	}

	public function postLogin(){
		$this->resUser->authenticate();
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

					//AUTO_LOGIN
					if(\Twist::framework()->setting('USER_REGISTER_PASSWORD') && \Twist::framework()->setting('USER_EMAIL_VERIFICATION') == false && \Twist::framework()->setting('USER_AUTO_AUTHENTICATE')){

						//@todo redirect - test or work out best way of doing this
						//$this->resUser->afterLoginRedirect(); --- set the value that this function uses, authenticate will do the redirect

						//Authenticate the user (log them in)
						$this->resUser->authenticate($_POST['email'],$_POST['password']);

						//@todo redirect - test or work out best way of doing this
						$this->resUser->afterLoginRedirect();
					}else{
						\Twist::Session()->data('site-register_message','Thank you for your registration, your password has been emailed to you');
						unset( $_POST['email'] );
						unset( $_POST['firstname'] );
						unset( $_POST['lastname'] );
						unset( $_POST['register'] );
					}
				}else{
					\Twist::Session()->data('site-register_error_message','Failed to register user');
				}
			}
		}
	}
}