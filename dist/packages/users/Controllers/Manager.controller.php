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
		    $resUser = \Twist::User()->current();
		    $resRecord = \Twist::Database()->records('twist_users')->create();
            $resRecord->set('id',$resUser);
		    $resRecord->set('email',$_POST['email']);
		    $resRecord->set('firstname',$_POST['firstname']);
		    $resRecord->set('surname',$_POST['surname']);
		    $resRecord->set('password',$_POST['password']);
		    $resRecord->set('level',$_POST['level']);
		    $addRecordID = $resRecord->commit();


		    \Twist::redirect('/manager/users');
        }
        public function edit(){
		    return $this->_view('manager/edit_user.tpl');
        }

        public function POSTedit(){
		    $resUser = \Twist::User()->current();
		    $resUser->firstname($_POST['firstname']);
		    $resUser->surname($_POST['surname']);
		    $resUser->email($_POST['email']);
		    $resUser->level($_POST['level']);
		    $resUser->password($_POST['password']);
		    $updateRecord = $resUser->commit();

		    \Twist::redirect('/manager/users');
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