<?php

	namespace Twist\Core\Models\Email;

	class Send{

		public static function protocolName(){
			return 'native';
		}

		/**
		 * @param \Twist\Core\Models\Email\Create $resEmail
		 * @return bool
		 */
		public static function protocolSend($resEmail){

			$arrSource = $resEmail->source();
			$arrData = $resEmail->data();

			$arrSource['headers'] = preg_replace('/\r\nTo\: .*\r\n/im', "\r\n", $arrSource['headers']);
			$arrSource['headers'] = preg_replace('/\r\nSubject\: .*\r\n/im', "\r\n", $arrSource['headers']);

			$blStatus = mail($arrSource['to'],$arrSource['subject'],$arrSource['body'],$arrSource['headers']);

			return $blStatus;
		}
	}