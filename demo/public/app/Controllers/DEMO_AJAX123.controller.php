<?php

	namespace App\Controllers;

	use Twist\Core\Controllers\BaseAJAX;

	class DEMO_AJAX123 extends BaseAJAX {

		private $arrPostedJSON = null;

		private function _getPostedJSONField( $strField ) {
			if( is_null( $this -> arrPostedJSON ) ) {
				$this -> arrPostedJSON = json_decode( file_get_contents( 'php://input' ), true );
			}
			return array_key_exists( $strField, $this -> arrPostedJSON ) ? $this -> arrPostedJSON[$strField] : null;
		}

		public function knock() {
			$this -> _ajaxFail();
			$this -> _ajaxMessage( 'The knock was quiet' );
			return $this -> _ajaxRespond();
		}

		public function ring() {
			$this -> _ajaxMessage( 'The doorbell made a noise' );
			$objResponse = array(
				'response' => 'Hello? Who\'s there?',
				'datetime' => date( 'Y-m-d H:i:s' )
			);
			return $this -> _ajaxRespond( $objResponse );
		}

		public function GETage() {
			$this -> _ajaxFail();
			$this -> _ajaxMessage( 'Please POST your age form' );
			return $this -> _ajaxRespond();
		}

		public function POSTage() {
			$this -> _ajaxMessage( 'Wow! You\'re old!' );

			$objResponse = array(
				'posted' => $this -> arrPostedJSON,
				'yourDob' => $this -> _getPostedJSONField( 'dob' ),
				'yourAge' => \Twist::DateTime() -> getAge( strtotime( $this -> _getPostedJSONField( 'dob' ) ) )
			);
			return $this -> _ajaxRespond( $objResponse );
		}

	}