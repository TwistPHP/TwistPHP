<?php
/**
 * This file is part of TwistPHP.
 *
 * TwistPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TwistPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       http://twistphp.com
 *
 */

namespace TwistPHP\Packages;

class EmailSMTP{

	protected $resConnection = null;
	protected $strLastMessage = '';
	protected $intTimeout = 90;
	protected $strBody = '';
	protected $blUseFromParameter = false;

	public function setTimeout($intTimeout = 90){
		$this->intTimeout = (is_null($intTimeout)) ? 90 : $intTimeout;
	}

	public function getLastMessage(){
		return $this->strLastMessage;
	}

	/**
	 * Open a new FTP connection
	 * @param $strHost
	 * @param $intPort
	 * @throws RuntimeException
	 */
	public function connect($strHost,$intPort = 25){

		/**
		 * May be a valid way to specify the IP address of the server when on a server with multiple IP addresses
		 */
		//$strInterfaceIP = '192.168.1.100'
		//$this->resConnection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		//socket_bind($this->resConnection, $strInterfaceIP);
		//socket_connect($this->resConnection, $strHost, $intPort);

		$this->resConnection = fsockopen($strHost, $intPort);

		stream_set_blocking($this->resConnection, true);
		stream_set_timeout($this->resConnection, $this->intTimeout);

		if($this->communicate() !== 220){
			return false;
		}

		return true;
	}

	public function connected(){ return $this->blConnected; }

	/**
	 * Disconnect the current session (connection)
	 */
	public function disconnect(){
		$this->communicate('QUIT');
		fclose($this->resConnection);
		$this->resConnection = null;
		$this->blConnected = false;
	}

	public function login($strEmailAddress,$strPassword){

		list($strLocalPart,$strEmailHost) = explode('@',$strEmailAddress);

		if($this->communicate(sprintf('EHLO %s',$strEmailHost)) === 250){
			if($this->communicate(sprintf('AUTH LOGIN')) === 334){
				return ($this->communicate(base64_encode($strEmailAddress)) === 334 && $this->communicate(base64_encode($strPassword)) === 235) ? true : false;
			}
		}

		return false;
	}

	public function useFromParam(){
		//Ignored for SMTP sending
	}

	public function from($strFromAddress){
		return ($this->communicate(sprintf('MAIL FROM: %s',$strFromAddress)) === 250) ? true : false;
	}

	public function to($strToAddress){
		return ($this->communicate(sprintf('RCPT TO: %s',$strToAddress)) === 250) ? true : false;
	}

	public function subject($strSubject){
		return true;
	}

	public function body($strBody){
		$this->strBody = $strBody;
		return true;
	}

	public function send($strEmailSource){
		if($this->communicate('DATA') === 354){
			return ($this->communicate(sprintf("%s%s.",$strEmailSource,$this->strBody)) === 250) ? true : false;
		}
	}

	/**
	 * Send and read data from the FTP connection, this function is used to send and receive all comms with the FTP server.
	 * @param null $strRequestString
	 * @return int
	 */
	protected function communicate($strRequestString = null){

		$intResponseCode = 0;
		$this->strLastMessage = '';

		//echo '> '.$strRequestString.'<br>';

		//Post the request data if required, otherwise get the response data only
		if(!is_null($strRequestString)){
			fputs($this->resConnection, $strRequestString."\r\n");
		}

		while(true){
			$mxdLine = fgets($this->resConnection, 1024);
			$this->strLastMessage .= $mxdLine;

			if(preg_match('#^([0-9]{3})\s#',$mxdLine,$arrMatches)){
				$intResponseCode = intval($arrMatches[0]);
				break;
			}
		}

		//Clean the message string
		$this->strLastMessage = trim($this->strLastMessage);

		//echo $this->strLastMessage.'<br>';

		return $intResponseCode;
	}
}