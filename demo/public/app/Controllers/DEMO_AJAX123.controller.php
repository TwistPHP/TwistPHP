<?php

	namespace App\Controllers;

	use Twist\Core\Controllers\BaseAJAX;

	class DEMO_AJAX123 extends BaseAJAX {

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
				'post' => $_POST,
				'request' => $_REQUEST,
				'yourDob' => $_POST['dob'],
				'yourAge' => \Twist::DateTime() -> getAge( strtotime( $_POST['dob'] ) )
			);
			return $this -> _ajaxRespond( $objResponse );
		}

	}