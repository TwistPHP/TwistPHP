<?php

	namespace Packages\users\Controllers;

	/**
	 * Class Manager Controller
	 * @package Packages\WebSockets\Controllers
	 */
	class Manager extends \Twist\Core\Controllers\Base{

		public function _index(){
		    $arrTags = array('users');
		    $arrUsers = \Twist::Database()->records('twist_users')->find(null,null,'id');
		    //$arrTags['users'] = \Twist::User()->getAll();

            if(array_key_exists('delete-user', $_GET)){
                \Twist::Database()->records('twist_users')->delete($_GET['delete-user'],'id');
                \Twist::redirect('/manager/users');
                //\Twist::noticeMessage('user deleted successfully');
            }

		    foreach ($arrUsers as $arrEachUser){
		        $arrTags['users'] .= $this->_view('manager/each_user.tpl',$arrEachUser);
            }

		    return $this->_view('manager/users.tpl',$arrTags);
		}
		public function create(){
		    return $this->_view('manager/create_user.tpl');
        }
        public function POSTcreate(){
            
		    $this->_required('email','email');
		    $this->_required('firstname','string');
		    $this->_required('surname','string');
		    $this->_required('password','string');
		    $this->_required('level','integer');

            if($this->_check()){

                $resRecord = \Twist::User()->create();
                $resRecord->id($resUser);
                $resRecord->email($_POST['email']);
                $resRecord->firstname($_POST['firstname']);
                $resRecord->surname($_POST['surname']);
                $resRecord->password($_POST['password']);
                $resRecord->level($_POST['level']);
                $addRecordID = $resRecord->commit();

                \Twist::redirect('/manager/users');
            } else {
                return $this->edit();
            }

        }
        public function edit(){
            $arrRoute = $this->_route();
            $intUserID = $arrRoute['parts']['1'];
            $arrTags = \Twist::User()->getData($intUserID);

            //Check to see that the userID is bigger than 0 and that we have user data
            if($intUserID > 0 && count(arrTags)){
                return $this->_view('manager/edit_user.tpl',$arrTags);
            }

            //If not then redirect the user back to the usesrs page
            \Twist::redirect('/manager/users');
        }

        public function POSTedit(){
            $arrRoute = $this->_route();
            $intUserID = $arrRoute['parts']['1'];

            $this->_required('firstname', 'string');
            $this->_required('surname', 'string');
            $this->_required('email', 'email');

            //Check the required fields
            if($this->_check()){
                $resUser= \Twist::User()->get($intUserID);
                $resUser->firstname($_POST['firstname']);
                $resUser->surname($_POST['surname']);
                $resUser->email($_POST['email']);
                $resUser->level($_POST['level']);
                $resUser->password($_POST['password']);

                //If the update was successful send an email
                if($resUser->commit()){

                    $resEmail = \Twist::Email()->create();
                    $resEmail->addTo($_POST['email']);
                    $resEmail->setSubject("Account Updates");
                    $resEmail->setFrom("noreply@twist.com");
                    $resEmail->setBodyHTML("<h3>Account details have been updated:</h3><p>if you didn't request these changes please contact support</p>");
                    $resEmail->send();
                }

                //Redirect back to the users page
                \Twist::redirect('/manager/users');
            }

            //If field validation fails output edit page, wont get here if gets into above IF
            return $this->edit();
		}
		/**
		 * Override the default view function to append the web sockets view path when required
		 * We do this rather than reset the view path as it has to work alongside the Manager which already has a view path set
		 * @param $dirView
		 * @param null $arrViewTags
		 * @param bool $blRemoveUnusedTags
		 * @return string
		 */
		protected function _view($dirView,$arrViewTags = null,$blRemoveUnusedTags = false){

			if(!file_exists($dirView) && substr($dirView,0,1) != '/' && substr($dirView,0,2) != './' && substr($dirView,0,3) != '../'){
				$dirView = ACCOUNTS_VIEWS.'/'.$dirView;
			}

			return parent::_view($dirView,$arrViewTags,$blRemoveUnusedTags);
		}
	}