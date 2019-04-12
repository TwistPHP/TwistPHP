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

			$arrData = $resEmail->data();
			$arrSource = $resEmail->source();

			$arrSource['raw'] = preg_replace('/\r\nTo\: .*\r\n/im', "\r\n", $arrSource['raw']);
			$arrSource['raw'] = preg_replace('/\r\nSubject\: .*\r\n/im', "\r\n", $arrSource['raw']);

			$blStatus = mail($arrSource['to'],$arrSource['subject'],$arrData['body_plain'],$arrSource['raw']);

			return $blStatus;
		}
	}