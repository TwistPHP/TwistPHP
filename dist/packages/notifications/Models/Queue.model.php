<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
	 *
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license    https://www.gnu.org/licenses/gpl.html GPL License
	 * @link       https://twistphp.com
	 */

	namespace Packages\notifications\Models;

	class Queue{

		protected $resTemplate = null;
		public $arrDebugLog = array();

		/**
		 * Spools up a queue processor, each processor watches the queue for 50 seconds, after this point it
		 * finished up the current notification being processed and will then die. A cron should be running every
		 * minute to spool up a new queue processor.
		 * @throws \Exception
		 */
		public static function processor(){

			$arrSettings = array(
				'restricted' => \Twist::framework()->setting('NOTIFICATIONS_RESTRICTED'),
				'per_cycle' => \Twist::framework()->setting('NOTIFICATIONS_PER_CYCLE'),
				'cooldown_after' => \Twist::framework()->setting('NOTIFICATIONS_COOLDOWN_AFTER'),
				'queue_wait' => \Twist::framework()->setting('NOTIFICATIONS_QUEUE_WAIT'),
				'auto_retry' => \Twist::framework()->setting('NOTIFICATIONS_AUTO_RETRY'),
				'retry_limit' => \Twist::framework()->setting('NOTIFICATIONS_RETRY_LIMIT'),
			);

			//Mark items as failed that have been processing for more than 1 minute
			\Twist::Database()->query("UPDATE `%snotification_queue` SET `status` = 'failed', `error` = 'Failed to send after processing for +1 minute' WHERE `status` = 'processing' AND (`started` IS NULL OR `started` < DATE_SUB(NOW(), INTERVAL -1 MINUTE))",TWIST_DATABASE_TABLE_PREFIX);

			//Delete all the records marked as delete
			\Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notification_queue')->delete('delete','status',null);

			//Update all the failed items that have reached their retry limit to be deleted on next run.
			\Twist::Database()->query("UPDATE `%snotification_queue` SET `status` = 'delete' WHERE  `send_attempts` >= %d",TWIST_DATABASE_TABLE_PREFIX,$arrSettings['retry_limit']);

			//Update all the sent items to be deleted on next run.
			\Twist::Database()->query("UPDATE `%snotification_queue` SET `status` = 'delete' WHERE `status` = 'sent'",TWIST_DATABASE_TABLE_PREFIX);

			//Update all the restricted items to be deleted on next run.
			\Twist::Database()->query("UPDATE `%snotification_queue` SET `status` = 'delete' WHERE `status` = 'restricted'",TWIST_DATABASE_TABLE_PREFIX);

			$intStart = time();
			$intRunningTimer = 0;

			//When auto retry is enabled the first cycle on any queue will retry failed notifications
			$strNextCycleProcess = ($arrSettings['auto_retry']) ? 'failed' : 'new';

			self::debug("Starting queue process for ".$arrSettings['cooldown_after']." seconds");

			//Allow a max running time of X seconds (This will minimise any overlap between the cron runs)
			while($intRunningTimer < $arrSettings['cooldown_after']){

				$arrNotifications = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notification_queue')->find($strNextCycleProcess,'status',null,'ASC',$arrSettings['per_cycle']);
				$arrNotifications = \Twist::framework()->tools()->arrayReindex($arrNotifications,'id');

				//Sometimes we might start with failed notifications first, Ensure that all subsequent sends will be for new notifications
				$strNextCycleProcess = 'new';

				if(count($arrNotifications) == 0){
					self::debug("Queue Empty, Waiting (".$arrSettings['queue_wait']." seconds)...");
					sleep($arrSettings['queue_wait']);
				}else{
					\Twist::Database()->query("UPDATE `%snotification_queue` SET `status` = 'processing',`send_attempts` = `send_attempts` + 1, `started` = NOW() WHERE `id` IN (%s)",TWIST_DATABASE_TABLE_PREFIX,implode(',',array_keys($arrNotifications)));

					foreach($arrNotifications as $arrEachSend){

						try{
							if($arrEachSend['user_id'] > 0){

								$arrDetails = \Twist::User()->getDetailsByID($arrEachSend['user_id']);
								self::debug("- SEND: {$arrEachSend['user_id']} [{$arrEachSend['type']}]");

								//Get the users notification options
								$arrOptions = array();
								if(array_key_exists('notification_options',$arrDetails)){
									$arrOptions = json_decode($arrDetails['notification_options'],true);
								}

								//Check to see if the notification is OK to go out
								if($arrEachSend['type'] == 'system' || array_key_exists($arrEachSend['type'],$arrOptions) && $arrOptions[$arrEachSend['type']] == '1'){

									$arrMethods = \Twist::framework()->hooks()->getAll('TWIST_NOTIFICATION_METHODS');

									foreach($arrMethods as $strEachMethod){
										$strEachMethod = (string) $strEachMethod;
										$blStatus = $strEachMethod::send($arrEachSend['user_id'], $arrEachSend['title'], $arrEachSend['html'], $arrEachSend['email_cc']);
									}
								}

							}else{

								self::debug("- SEND: {$arrEachSend['user_email']} [{$arrEachSend['type']}]");
								$blStatus = NotifyEmail::sendDirect($arrEachSend['user_email'], $arrEachSend['title'], $arrEachSend['html'], $arrEachSend['email_cc']);
							}

							//Log the result of the send
							$resQueue = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notification_queue')->get($arrEachSend['id'],'id');
							if($blStatus){
								$resQueue->set('status','sent');
								$resQueue->set('sent',date('Y-m-d H:i:s'));
							}else{
								$resQueue->set('status','failed');
								$resQueue->set('error','Failed to send, method returned false');
							}
							$resQueue->commit();

						}catch(\Exception $exception){
							self::debug("- # FAILED #");
							\Twist::Database()->query("UPDATE `%snotification_queue` SET `status` = 'failed', `error` = '%s'  WHERE `id` = %d",TWIST_DATABASE_TABLE_PREFIX,$arrEachSend['id'],$exception->getMessage());
						}
					}
				}

				//Get the total amount of seconds the script has been running for
				$intRunningTimer = time() - $intStart;
			}

			self::debug("Queue processes ended");
		}

		/**
		 * Send a notification email though the notification queue, log all notifications in the in-app notifications bar and hand push notifications when required
		 * - Add an email CC, this can be useful for testing purposes or in some cases such as contact requests may be handy for multiple receipts
		 * @param $intUserID
		 * @param $strTitle
		 * @param $strMessage
		 * @param $strEmailHTML
		 * @param $strSpecificURL
		 * @param $strType
		 * @param string $strEmailAddressCC
		 *
		 * @return bool|int
		 * @throws \Exception
		 */
		public static function send($intUserID, $strTitle, $strMessage, $strEmailHTML, $strType = 'system', $strSpecificURL = '', $strEmailAddressCC = ''){

			$blReturn = false;
			$arrUser = \Twist::User()->getData($intUserID);

			//Only send notifications to enabled users
			if(count($arrUser) && $arrUser['enabled'] == '1'){

				$strSiteURL = sprintf('%s://%s/',\Twist::framework()->setting('SITE_PROTOCOL'),\Twist::framework()->setting('SITE_HOST'));

				if($strSpecificURL == ''){
					$strSpecificURL = $strSiteURL;
				}elseif(substr($strSpecificURL,0,1) == '/'){
					$strSpecificURL = $strSiteURL.ltrim('/',$strSpecificURL);
				}

				//Remove any uncaptured double slashes and then fix the double slashes that are supposed to be their
				$strSpecificURL = str_replace('//','/',$strSpecificURL);
				$strSpecificURL = str_replace('http:/','http://',$strSpecificURL);
				$strSpecificURL = str_replace('https:/','https://',$strSpecificURL);

				$resQueue = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notification_queue')->create();
				$resQueue->set('type',$strType);
				$resQueue->set('user_id',$intUserID);
				$resQueue->set('email_cc',$strEmailAddressCC);
				$resQueue->set('title',$strTitle);
				$resQueue->set('message',$strMessage);
				$resQueue->set('html',$strEmailHTML);
				$resQueue->set('url',$strSpecificURL);
				$resQueue->set('added',date('Y-m-d H:i:s'));
				$blReturn = $resQueue->commit();

				//Log notification into database
				self::logNotification($strType, $intUserID, $strTitle, $strMessage, $strSpecificURL);
			}

			return $blReturn;
		}

		/**
		 * Send an email directly to a user via the notification queue
		 * - Add an email CC, this can be useful for testing purposes or in some cases such as contact requests may be handy for multiple receipts
		 * @param $strEmailAddress
		 * @param $strTitle
		 * @param $strEmailHTML
		 * @param string $strEmailAddressCC
		 *
		 * @return bool|int
		 * @throws \Exception
		 */
		public static function sendToEmail( $strEmailAddress, $strTitle, $strEmailHTML, $strEmailAddressCC = '' ){

			$resQueue = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notification_queue')->create();
			$resQueue->set('type','direct-email');
			$resQueue->set('user_email',$strEmailAddress);
			$resQueue->set('email_cc',$strEmailAddressCC);
			$resQueue->set('title',$strTitle);
			$resQueue->set('message','');
			$resQueue->set('html',$strEmailHTML);
			$resQueue->set('url','');
			$resQueue->set('added',date('Y-m-d H:i:s'));
			$blReturn = $resQueue->commit();

			return $blReturn;
		}

		public static function logNotification($strType, $intUserID, $strTitle, $strMessage, $strURL){

			$resNotification = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notifications')->create();

			$resNotification->set('user_id',$intUserID);
			$resNotification->set('type',$strType);
			$resNotification->set('title',$strTitle);
			$resNotification->set('message',$strMessage);
			$resNotification->set('url',$strURL);
			$resNotification->set('created',date('Y-m-d H:i:s'));

			$resNotification->commit();
		}

		public static function clear($intNotificationID){

			$resNotification = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'notifications')->get($intNotificationID);

			if($resNotification->get('user_id') == \Twist::User()->currentID()){
				$resNotification->set('read','1');
				return $resNotification->commit();
			}

			return false;
		}

		public static function clearAll(){
			return \Twist::Database()->query("UPDATE `%snotifications` SET `read` = '1' WHERE `user_id`= %d",
				TWIST_DATABASE_TABLE_PREFIX,
				\Twist::User()->currentID()
			)->status();
		}

		protected static function debug($strMessage){

			if(\Twist::framework()->setting('NOTIFICATIONS_DEBUG')){
				echo $strMessage."\n";
			}
		}
	}