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

namespace Twist\Core\Models\User;

class Auth{

    protected static $arrCurrentSession = array();

    public function __construct(){

        self::$arrCurrentSession = array(
            'status' => false,
            'message' => '',
            'diagnosis' => '',
            'session_key' => null,
            'user_id' => null
        );
    }

    /**
     * Log the user in and generate an active session (Stores session data into the browser)
     * @param $strEmail
     * @param $strPassword
     * @return array
     */
    public static function login($strEmail,$strPassword,$blRememberMeCookie = false){

        self::validate($strEmail,$strPassword);

        if(self::$arrCurrentSession['status']){

            //Create the session key or session cookie
            if($blRememberMeCookie){
                //self::$arrCurrentSession['session_key'] = self::$objUserSession->createCookie(self::$arrCurrentSession['user_id']);
            }else{
                //self::$arrCurrentSession['session_key'] = self::$objUserSession->createCode(self::$arrCurrentSession['user_id']);
            }

            self::processUserLoginSession(self::$arrCurrentSession['user_id'],self::$arrCurrentSession['session_key']);
        }

        return self::$arrCurrentSession;
    }

    /**
     * Validate a users credentials without logging the user into the system
     * @param $strEmail
     * @param $strPassword
     * @return array
     */
    public static function validate($strEmail,$strPassword){

        //If the user is still not valid then check email and password
        if(!is_null($strEmail) && !is_null($strPassword)){

            $objDB = \Twist::Database();

            $strSQL = sprintf("SELECT `id`,`password`,`enabled`,`verified`
									FROM `%s`.`%susers`
									WHERE `email` = '%s'
									LIMIT 1",
                TWIST_DATABASE_NAME,
                TWIST_DATABASE_TABLE_PREFIX,
                $objDB->escapeString($strEmail)
            );

            if($objDB->query($strSQL) && $objDB->getNumberRows()){
                $arrUserData = $objDB->getArray();

                if($arrUserData['password'] == sha1($strPassword)){
                    if($arrUserData['enabled'] == '1'){
                        self::$arrCurrentSession['status'] = true;
                        self::$arrCurrentSession['user_id'] = $arrUserData['id'];
                    }else{
                        self::$arrCurrentSession['message'] = 'Your account has been disabled';
                        self::$arrCurrentSession['diagnosis'] = 'The account has been set to disabled';
                    }
                }else{
                    self::$arrCurrentSession['message'] = 'Invalid login credentials, please try again';
                    self::$arrCurrentSession['diagnosis'] = 'Password does not match that of the requested account';
                }
            }else{
                self::$arrCurrentSession['message'] = 'Invalid login credentials, please try again';
                self::$arrCurrentSession['diagnosis'] = 'Email address not registered to a user';
            }
        }

        return self::$arrCurrentSession;
    }

    /**
     * Log the user out of the system
     * @return array
     */
    public static function logout(){


        return self::$arrCurrentSession;
    }



    /*** BELOW FUNCTIONS TO BE RE-WRITTEN FOR THE NEW AUTH MODEL ***/



    protected static function processUserLoginSession($intUserID,$strSessionKey){

        $arrUserData = self::processUserSession($intUserID);

        $objSession = \Twist::Session();

        $objSession->data('user-session_key',$strSessionKey);
        $objSession->data('user-logged_in',\Twist::DateTime()->time());

        if(self::$blActivityLogged == false){
            self::$resCurrentUser->lastLogin($_SERVER['REMOTE_ADDR']);
            self::$resCurrentUser->lastActive();
            self::$resCurrentUser->commit();
            self::$blActivityLogged = true;
        }

        self::$intUserID = $intUserID;

        if(\Twist::framework()->setting('USER_PASSWORD_CHANGE') == true){

            if($arrUserData['temp_password'] == '1'){
                $objSession->data('user-temp_password','1');
                $objSession->remove('site-login_redirect');

                //self::$goToPage( '?change', false );
                self::goToPage( sprintf('%s%s?change',\Twist::Route()->current('registered_uri'),self::$strLoginUrl), false );
            }

        }elseif($arrUserData['temp_password'] == '1'){
            self::$resCurrentUser->tempPassword(0);
        }

        //Destroy the var
        unset($objSession);

        self::afterLoginRedirect();
    }

    protected static function processUserSession($intUserID){

        $objSession = \Twist::Session();
        $arrUserData = self::$getData($intUserID);

        //Check that the account is enabled
        if($arrUserData['enabled'] == '1'){

            //Check that the account is verified if required
            if(self::$framework()->setting('USER_EMAIL_VERIFICATION') == false || (self::$framework()->setting('USER_EMAIL_VERIFICATION') && $arrUserData['verified'] == '1')){

                $objSession->data('user-id',$arrUserData['id']);
                $objSession->data('user-level',$arrUserData['level']);
                $objSession->data('user-email',$arrUserData['email']);;
                $objSession->data('user-name',sprintf('%s %s',$arrUserData['firstname'],$arrUserData['surname']));
                $objSession->data('user-firstname',$arrUserData['firstname']);
                $objSession->data('user-surname',$arrUserData['surname']);
                $objSession->data('user-temp_password',$arrUserData['temp_password']);

                self::$loadCurrentUser();

            }else{
                //If the user is not verified and password is correct show verification message
                $objSession->data('user-email',$arrUserData['email']);

                //self::$goToPage( '?verification', false );
                self::$goToPage( sprintf('%s%s?verification',\Twist::Route()->current('registered_uri'),self::$strLoginUrl), false );
            }
        }else{
            $objSession->remove();
            $objSession->data('site-login_error_message','Your account has been disabled');
        }

        //Destroy the var
        unset($objSession);

        return $arrUserData;
    }

}