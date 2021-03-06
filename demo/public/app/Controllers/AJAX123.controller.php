<?php

	namespace App\Controllers;

	use Twist\Core\Controllers\BaseAJAX;

	class AJAX123 extends BaseAJAX {

		public function knock() {
			$this -> _ajaxFail();
			$this -> _ajaxMessage( 'The knock was too quiet' );
			return $this -> _ajaxRespond();
		}

		public function ring() {
			sleep( 30 );
			$this -> _ajaxMessage( 'The doorbell made a noise' );
			$objResponse = array(
				'response' => 'Hello? Who\'s there?',
				'datetime' => date( 'Y-m-d H:i:s' )
			);
			return $this -> _ajaxRespond( $objResponse );
		}

		public function POSTage() {
			$this -> _ajaxMessage( 'Your age was calculated' );
			$objResponse = array(
				//'yourDob' => $this -> _posted( 'dob' ),
				'yourAge' => \Twist::DateTime() -> getAge( strtotime( $this -> _posted( 'dob' ) ) )
			);
			return $this -> _ajaxRespond( $objResponse );
		}

	}