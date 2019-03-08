<?php

	namespace Packages\notifications\Controllers;

	/**
	 * Class Manager Controller
	 * @package Packages\WebSockets\Controllers
	 */
	class Manager extends \Twist\Core\Controllers\Base{

		public function _index(){

			if(array_key_exists('retry',$_GET)){

				if($_GET['retry'] == 'all'){
					\Twist::Database()->query("UPDATE `notification_queue` SET `status` = 'new', `send_attempts` = `send_attempts` + 1 WHERE `status` = 'failed'");
				}else{
					$resNotification = \Twist::Database()->records('notification_queue')->get($_GET['retry']);
					$resNotification->set('status','new');
					$resNotification->increment('send_attempts');
					$resNotification->commit();
				}

				\Twist::redirect('notifications');
			}

			$arrTags = array('notifications' => '');
			$arrNotifications = \Twist::Database()->records('notification_queue')->all();

			foreach($arrNotifications as $arrEachNotification){

				$resUser = \Twist::User()->get($arrEachNotification['user_id']);

				$arrTags['notifications'] .= sprintf("<tr><td>%s</td><td>%s</td><td class='showHover'><strong>%s</strong></td><td>%s</td><td>%s</td><td>%s</td></tr>",
					$arrEachNotification['type'],
					$resUser->name().' ('.$arrEachNotification['user_id'].')',
					$arrEachNotification['title'],
					($arrEachNotification['status'] == 'failed') ? $arrEachNotification['status'].' (<a href="?retry='.$arrEachNotification['id'].'">retry</a>)' : $arrEachNotification['status'],
					$arrEachNotification['added'],
					$arrEachNotification['sent']
				);
			}

			if($arrTags['notifications'] == ''){
				$arrTags['notifications'] = '<tr><td colspan="6">Queue is currently empty, nice!</td></tr>';
			}

			$arrTags['no-fly-list'] = '';
			if(!is_null(\Twist::framework()->setting('NOTIFICATIONS_RESTRICTED')) && \Twist::framework()->setting('NOTIFICATIONS_RESTRICTED') !== ''){
				$arrTags['no-fly-list'] = '<p class="error"><strong>Notification Lockdown</strong>: Only listed users will receive notifications:<br><br>'.str_replace(',',', ',\Twist::framework()->setting('NOTIFICATIONS_RESTRICTED')).'</p>';
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
				$dirView = NOTIFICATIONS_VIEWS.'/'.$dirView;
			}

			return parent::_view($dirView,$arrViewTags,$blRemoveUnusedTags);
		}
	}