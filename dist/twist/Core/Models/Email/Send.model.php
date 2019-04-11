<?php

	namespace Twist\Core\Models\Email;

	class Send{

		public static function protocolName(){
			return 'Native';
		}

		/**
		 * @param \Twist\Core\Models\Email\Create $resEmail
		 * @return bool
		 */
		public static function protocolSend($resEmail){

			$strEmailSource = $resEmail->source();

			$strEmailSource = preg_replace('/\r\nTo\: .*\r\n/im', "\r\n", $strEmailSource);
			$strEmailSource = preg_replace('/\r\nSubject\: .*\r\n/im', "\r\n", $strEmailSource);

			$blStatus = mail($resEmail->strTo,$resEmail->strSubject,$resEmail->strBody,$strEmailSource);

			return $blStatus;
		}
	}