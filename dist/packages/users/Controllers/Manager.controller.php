<?php

	namespace Packages\users\Controllers;

	/**
	 * Class Manager Controller
	 * @package Packages\WebSockets\Controllers
	 */
	class Manager extends \Twist\Core\Controllers\Base{

		public function _index(){
		    $arrTags = array('users' => '');
		    $arrUsers = \Twist::User()->getAll();
		    //$arrTags['users'] = \Twist::User()->getAll();

		    foreach ($arrUsers as $arrEachUser){
		        $arrTags['users'] = $this->_view('manager/each_user.tpl',$arrEachUser);
            }

		    return $this->_view('manager/users.tpl',$arrTags);
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