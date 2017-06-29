<?php

	namespace App\Controllers;

	use Twist\Core\Controllers\BaseAJAX;

	class DEMO_AJAX123 extends BaseAJAX {

		public function knock() {
			sleep( 2 );
			$this -> _ajaxFail();
			$this -> _ajaxMessage( 'The knock was too quiet' );
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
			$this -> _ajaxMessage( 'Please POST your age rather than GET' );
			return $this -> _ajaxRespond();
		}

		public function POSTage() {
			$this -> _ajaxMessage( 'Wow! You\'re old!' );
			$objResponse = array(
				'yourDob' => $this -> _posted( 'dob' ),
				'yourAge' => \Twist::DateTime() -> getAge( strtotime( $this -> _posted( 'dob' ) ) )
			);
			return $this -> _ajaxRespond( $objResponse );
		}

	}