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

namespace Twist\Core\Models\Email;
use \Twist\Core\Models\String\Html2Text;

/**
 * Generate and send full multi-lingual multi-part HTML emails with the ability to add attachments. Fully customisable with Char encoding, message encoding, priority and sensitivity.
 * @package Twist\Core\Models\Email
 */
class Create{

	protected $blMultipartEncoding = '7bit';
	protected $strCharEncoding = 'ISO-8859-1';
	protected $intSenderLevel = 0;
	protected $arrEmailData = array();

	public function __construct(){
		$this->reset();
	}

	protected function reset(){

		$this->arrEmailData = array();
		$this->arrEmailData['subject'] = '(no subject)';

		$this->blMultipartEncoding = \Twist::framework()->setting('EMAIL_MULTIPART_ENCODING');
		$this->strCharEncoding = \Twist::framework()->setting('EMAIL_CHAR_ENCODING');
	}

	/**
	 * options = 7bit, base64
	 * @param string $strEncoding
	 */
	public function setEncoding($strEncoding = '7bit'){
		$this->blMultipartEncoding = $strEncoding;
	}

	public function setCharEncoding($strCharEncoding = 'ISO-8859-1'){
		$this->strCharEncoding = $strCharEncoding;
	}

	/**
	 * Add the To address for the email, you can add as many To fields as you need.
	 * Optionally you can put the persons full name in the second parameter if you know it.
	 * @param string $strEmailAddress
	 * @param string $strName
	 */
	public function addTo($strEmailAddress,$strName = ''){

		if(!array_key_exists('to',$this->arrEmailData)){
			$this->arrEmailData['to'] = array();
		}

		$this->arrEmailData['to'][$strEmailAddress] = $strName;
	}

	/**
	 * Add the Cc (Carbon Copy) address for the email, you can add as many Cc fields as you need.
	 * Optionally you can put the persons full name in the second parameter if you know it.
	 * @param string $strEmailAddress
	 * @param string $strName
	 */
	public function addCc($strEmailAddress,$strName = ''){

		if(!array_key_exists('cc',$this->arrEmailData)){
			$this->arrEmailData['cc'] = array();
		}

		$this->arrEmailData['cc'][$strEmailAddress] = $strName;
	}

	/**
	 * Add the Bcc (Blind Carbon Copy) address for the email, you can add as many Bcc fields as you need.
	 * Optionally you can put the persons full name in the second parameter if you know it. Note that Bcc will
	 * send a copy of the email to the user but none of the addressed To and Cc users will be aware of this.
	 * @param string $strEmailAddress
	 * @param string $strName
	 */
	public function addBcc($strEmailAddress,$strName = ''){

		if(!array_key_exists('bcc',$this->arrEmailData)){
			$this->arrEmailData['bcc'] = array();
		}

		$this->arrEmailData['bcc'][$strEmailAddress] = $strName;
	}

	/**
	 * Set the From email address, this the is the address the email will be sent from.
	 * Optionally you can set a name for the from address in the second parameter.
	 * @param string $strEmailAddress
	 * @param string $strName
	 */
	public function setFrom($strEmailAddress,$strName = ''){
		$this->arrEmailData['from_name'] = $strName;
		$this->arrEmailData['from_email'] = $strEmailAddress;
	}

	/**
	 * Setup a different reply to address so that when a receiver hits reply in their mail client
	 * the email will be sent to the reply address rather than the from address.
	 * @param string $strEmailAddress
	 */
	public function setReplyTo($strEmailAddress){
		$this->arrEmailData['reply_to'] = $strEmailAddress;
	}

	/**
	 * Set the subject of the email, this is the brief line of text that a receiver would read in
	 * their client inbox when receiving a new email.
	 * @param string $strSubject
	 */
	public function setSubject($strSubject){
		$this->arrEmailData['subject'] = $strSubject;
	}

	/**
	 * Set the plain text body of the email, not required if you have set a HTML body the plain text can be auto
	 * generated. This will happen if no plain text alternative has been entered.
	 * @param string $strBody
	 */
	public function setBodyPlain($strBody){
		$this->arrEmailData['body_plain'] = $strBody;
	}

	/**
	 * Set the HTML body of the email, you can have a plain text only email if required. Full HTML and CSS support, bear in mind that
	 * different mail clients will display HTML in different ways. Testing is key here.
	 * @param string $strBody
	 */
	public function setBodyHTML($strBody){
		$this->arrEmailData['body_html'] = $strBody;
	}

	/**
	 * Set the priority flag, most email clients will display a little icon next to priority emails.
	 * Level 1 = High
	 * Level 2 = Low
	 * Level 3 = Normal
	 * @param int $intPriority
	 */
	public function setPriority($intPriority = 3){

		//All 3 available priority levels
		$arrPriorityLevels = array(
			1 => array(
				'X-Priority' => '1 (High)',
				'X-MSMail-Priority' => 'High',
				'Importance' => 'High'),
			2 => array(
				'X-Priority' => '2 (Low)',
				'X-MSMail-Priority' => 'Low',
				'Importance' => 'Low'),
			3 => array(
				'X-Priority' => '3 (Normal)',
				'X-MSMail-Priority' => 'Normal',
				'Importance' => 'Normal')
		);

		$this->arrEmailData['headers'] .= sprintf("X-Priority: %s\r\n",$arrPriorityLevels[$intPriority]['X-Priority']);
		$this->arrEmailData['headers'] .= sprintf("X-MSMail-Priority: %s\r\n",$arrPriorityLevels[$intPriority]['X-MSMail-Priority']);
		$this->arrEmailData['headers'] .= sprintf("Importance: %s\r\n",$arrPriorityLevels[$intPriority]['Importance']);
	}

	/**
	 * Email sensitivity is not widely used but can be set if and when required.
	 * Level 1 = Company-Confidential
	 * Level 2 = Private
	 * Level 3 = Personal
	 * Level 4 = Normal
	 * @param int $intPriority
	 */
	public function setSensitivity($intPriority = 4){

		//All 3 available priority levels
		$arrPriorityLevels = array(
			1 => 'Company-Confidential',
			2 => 'Private',
			3 => 'Personal',
			4 => 'Normal'
		);

		$this->arrEmailData['headers'] .= sprintf("Sensitivity: %s\r\n",$arrPriorityLevels[$intPriority]);
	}

	/**
	 * Disable sender field checking, the sender field wil be set to the same as the from field.
	 * This will eliminate for the most part the Outlook warning: (from_name "sender" on behalf of "from_email")
	 * when the MX and SPF records of the domain don't match the servers IP.
	 * Alternatively you can set a custom sender, see "setCustomSender" function.
	 */
	function disableSenderChecking(){
		$this->intSenderLevel = 0;
	}

	/**
	 * Set a custom sender record if required
	 * @param null $strEmailAddress
	 * @param string $strName
	 */
	function setCustomSender($strEmailAddress = null,$strName = ''){
		$this->intSenderLevel = 2;
		$this->arrEmailData['sender_name'] = $strName;
		$this->arrEmailData['sender_email'] = $strEmailAddress;
	}

	/**
	 * Coming Soon, will add a view in browser link to the top of all HTML emails that are sent out.
	 */
	public function setViewInBrowser(){
		//Set a flag to say this email is to be cached as a view in browser email
		//A link will be returned to the view in browser file and a code attached
		//Expiry time for the view in browser should be set in the email config
		//Alternatively you should be able to pass in key that will set 1 cache for all emails sent.
	}

	/**
	 * Add local (on server) files to be attached to the email ass attachments
	 * @param string $strLocalFile
	 */
	public function addAttachment($strLocalFile){

		if(file_exists($strLocalFile) && is_file($strLocalFile)){

			//Attachment emails must be sent in base64
			$this->setEncoding('base64');

			if(!array_key_exists('attachments',$this->arrEmailData)){
				$this->arrEmailData['attachments'] = array();
			}

			$intFileSize = filesize($strLocalFile);

			$resFile = fopen($strLocalFile,"rb");
			$strFileData = fread($resFile,$intFileSize);
			fclose($resFile);

			$arrAttachment = array(
				'local_file' => $strLocalFile,
				'size' => $intFileSize,
				'name' => basename($strLocalFile),
				'data' => $strFileData
			);

			$this->arrEmailData['attachments'][] =$arrAttachment;
		}
	}

	public function senderValidation(){

		switch($this->intSenderLevel){
			case'2':
				//Nothing to do a custom sender is being used
				break;
			case'1':
				// Validate the email and its MX / SPF records here and set sender accordingly
				$this->arrEmailData['sender_name'] = $_SERVER['HTTP_HOST'];
				$this->arrEmailData['sender_email'] = $_SERVER['SERVER_ADMIN'];
				break;
			case'0':
				//
				$this->arrEmailData['sender_name'] = $this->arrEmailData['from_name'];
				$this->arrEmailData['sender_email'] = $this->arrEmailData['from_email'];
				break;
		}
	}

	/**
	 * Get all the processed Email Data, Pass in a previously exported array of data
	 * @param null $arrEmailData
	 * @return array|null
	 */
	public function data($arrEmailData = null){

		if(!is_null($arrEmailData)){
			$this->arrEmailData = $arrEmailData;
		}

		return $this->arrEmailData;
	}

	/**
	 * Process and return all the source components that make up the Raw email
	 * @return array
	 */
	public function source(){

		$this->arrEmailData['headers'] = "MIME-Version: 1.0\r\n";
		$this->arrEmailData['headers'] .= "X-Mailer: TwistPHP ".\Twist::version()." (https://github.com/TwistPHP/TwistPHP)\r\n";

		$strEmailTo = $strListCC = $strListBCC = "";

		/** Add TO to the email headers */
		if(array_key_exists('to',$this->arrEmailData) && is_array($this->arrEmailData['to']) && count($this->arrEmailData['to']) > 0){

			$strEmailTo = 'To: ';

			foreach($this->arrEmailData['to'] as $strEmailAddress => $strName){
				$strEmailTo .= ($strName != '') ? sprintf("%s <%s>,",$this->convertEncodingHeader($strName),$strEmailAddress) : sprintf('%s,',$strEmailAddress);
			}

			$strEmailTo = rtrim($strEmailTo,',');
			$this->arrEmailData['headers'] .= sprintf("%s\r\n",$strEmailTo);
		}

		/** Add CC to the email headers if required */
		if(array_key_exists('cc',$this->arrEmailData) && is_array($this->arrEmailData['cc']) && count($this->arrEmailData['cc']) > 0){

			$strListCC = "Cc: ";

			foreach($this->arrEmailData['cc'] as $strEmailAddress => $strName){
				$strListCC .= ($strName != '') ? sprintf("%s <%s>,",$this->convertEncodingHeader($strName),$strEmailAddress) : sprintf('%s,',$strEmailAddress);
			}

			$strListCC = rtrim($strListCC,',');
			$this->arrEmailData['headers'] .= sprintf("%s\r\n",$strListCC);
		}

		/** Add BCC to the email headers if required */
		if(array_key_exists('bcc',$this->arrEmailData) && is_array($this->arrEmailData['bcc']) && count($this->arrEmailData['bcc']) > 0){

			$strListBCC = "Bcc: ";

			foreach($this->arrEmailData['bcc'] as $strEmailAddress => $strName){
				$strListBCC .= ($strName != '') ? sprintf("%s <%s>,",$this->convertEncodingHeader($strName),$strEmailAddress) : sprintf('%s,',$strEmailAddress);
			}

			$strListBCC = rtrim($strListBCC,',');
			$this->arrEmailData['headers'] .= sprintf("%s\r\n",$strListBCC);
		}

		$this->senderValidation();

		/** Add in the sender headers */
		if(array_key_exists('sender_email',$this->arrEmailData) && $this->arrEmailData['sender_email'] != ''){
			$this->arrEmailData['headers'] .= ($this->arrEmailData['sender_name'] != '') ? sprintf("Sender: %s <%s>\r\n",$this->convertEncodingHeader($this->arrEmailData['sender_name']),$this->arrEmailData['sender_email']) : sprintf("Sender: %s\r\n",$this->arrEmailData['sender_email']);
		}

		// Set the X-Sender to the same as the from address
		$this->arrEmailData['headers'] .= ($this->arrEmailData['from_name'] != '') ? sprintf("x-sender: %s <%s>\r\n",$this->convertEncodingHeader($this->arrEmailData['from_name']),$this->arrEmailData['from_email']) : sprintf("x-sender: %s\r\n",$this->arrEmailData['from_email']);

		/** Add FROM to the email headers */
		$this->arrEmailData['headers'] .= ($this->arrEmailData['from_name'] != '') ? sprintf("From: %s <%s>\r\n",$this->convertEncodingHeader($this->arrEmailData['from_name']),$this->arrEmailData['from_email']) : sprintf("From: %s\r\n",$this->arrEmailData['from_email']);

		if(array_key_exists('reply_to',$this->arrEmailData) && $this->arrEmailData['reply_to'] != ''){
			$this->arrEmailData['headers'] .= sprintf("Reply-To: %s\r\n",$this->arrEmailData['reply_to']);
			$this->arrEmailData['headers'] .= sprintf("Return-Path: %s\r\n",$this->arrEmailData['reply_to']);
		}

		//Encode the subject
		$this->arrEmailData['subject'] = $this->convertEncodingHeader($this->arrEmailData['subject']);
		$this->arrEmailData['headers'] .= sprintf("Subject: %s\r\n",$this->arrEmailData['subject']);

		$this->buildBody();

		return array(
			'headers' => $this->arrEmailData['headers'],
			'to' => ($strEmailTo != '') ? substr($strEmailTo,4) : '',
			'cc' => ($strListCC != '') ? substr($strListCC,4) : '',
			'bcc' => ($strListBCC != '') ? substr($strListBCC,5) : '',
			'subject' => $this->arrEmailData['subject'],
			'body' => $this->arrEmailData['body'],
			'raw' => $this->arrEmailData['headers'].$this->arrEmailData['body']
		);
	}

	/**
	 * Send the email once all the data ans emails addresses have been added, this by default will user PHP mail unless otherwise specified.
	 * @param bool $blClearCache
	 * @return bool
	 * @throws \Exception
	 */
	public function send($blClearCache = true){

		$blStatus = false;
		$blSend = true;

		if(\Twist::framework()->setting('EMAIL_PREVENT_SUSPENDED_USERS')){

			//Remove any suspended users from the email TO
			foreach($this->arrEmailData['to'] as $strEmail => $strName){
				$arrUser = \Twist::User()->getByEmail($strEmail);
				if(is_array($arrUser) && count($arrUser) && $arrUser['enabled'] == '0'){
					unset($this->arrEmailData['to'][$strEmail]);
				}
			}

			//Remove any suspended users from the email CC
			foreach($this->arrEmailData['cc'] as $strEmail => $strName){
				$arrUser = \Twist::User()->getByEmail($strEmail);
				if(is_array($arrUser) && count($arrUser) && $arrUser['enabled'] == '0'){
					unset($this->arrEmailData['cc'][$strEmail]);
				}
			}

			//Remove any suspended users from the email BCC
			foreach($this->arrEmailData['bcc'] as $strEmail => $strName){
				$arrUser = \Twist::User()->getByEmail($strEmail);
				if(is_array($arrUser) && count($arrUser) && $arrUser['enabled'] == '0'){
					unset($this->arrEmailData['bcc'][$strEmail]);
				}
			}
		}

		//Only send out the email if that is at-least one email in the To field
		if(count($this->arrEmailData['to'])){

			$arrPreProcessHooks = \Twist::framework()->hooks()->getAll('TWIST_EMAIL_PREPROCESS');

			foreach($arrPreProcessHooks as $strKey => $arrModel){
				$strEmailPreProcessModel = (string) $arrModel['model'];
				$blSend = $strEmailPreProcessModel::emailPreProcess($this);

				if(!$blSend){
					//A hook as requested the send not to happen, cancel the send
					break;
				}
			}

			if($blSend){
				//Get the send protocol and send out the email
				$strProtocol = \Twist::framework()->setting('EMAIL_PROTOCOL');
				$arrHooks = \Twist::framework()->hooks()->getAll('TWIST_EMAIL_PROTOCOLS');

				foreach($arrHooks as $strKey => $arrModel){
					if($strKey == $strProtocol){
						$strEmailModel = (string) $arrModel['model'];
						$blStatus = $strEmailModel::protocolSend($this);
						break;
					}
				}
			}

			if($blClearCache == true){
				//Clear the email data ready for the next email
				$this->reset();
			}
		}

		return $blStatus;
	}

	/**
	 * Generate a plain version of the HTML message using Html2Text
	 */
	protected function generatePlainMessage(){

		//Convert the HTML into formatted text, using a new model Html2Text, can expand on this later
		$resHtml2Text = new Html2Text($this->arrEmailData['body_html']);

		$this->arrEmailData['body_plain'] = $resHtml2Text->getText();
	}

	/**
	 * Sanitise the plain message to ensure no HTML is present, use Html2Text to do this
	 */
	protected function sanitisePlainMessage(){

		if($this->stripTags($this->arrEmailData['body_plain']) !== $this->arrEmailData['body_plain']){

			//Convert the HTML into formatted text, using a new model Html2Text, can expand on this later
			$resHtml2Text = new Html2Text($this->arrEmailData['body_plain']);

			$this->arrEmailData['body_plain'] = $resHtml2Text->getText();
		}
	}

	/**
	 * Remove all the style tags, html tags and then decode any HTML entities
	 * @param string $strHtmlContent
	 * @return mixed
	 */
	protected function stripTags($strHtmlContent){
		$strHtmlContent = preg_replace("#<style\b[^>]*>(.*?)</style>#s", "", $strHtmlContent);
		$strHtmlContent = strip_tags($strHtmlContent);
		return html_entity_decode($strHtmlContent);
	}

	/**
	 * Generate a HTML version of a plain message
	 */
	protected function generateHTMLMessage(){
		$this->arrEmailData['body_html'] = str_replace("\n","<br />",$this->arrEmailData['body_plain']);
	}

	/**
	 * Convert the header encoding, if no multibyte support on PHP installation ignore the encoding and output a warning
	 * @param string $strData
	 * @return string
	 */
	protected function convertEncodingHeader($strData){

		if(function_exists('mb_encode_mimeheader')){
			$strData = mb_encode_mimeheader(mb_convert_encoding($strData,$this->strCharEncoding,"AUTO"));
		}else{
			trigger_error('TwistPHP, skipping Email->convertEncodingHeader as multi-byte (mbstring) support not enabled in PHP installation',E_USER_WARNING);
		}

		return $strData;
	}

	/**
	 * Convert the body encoding, if no multibyte support on PHP installation ignore the encoding and output a warning
	 * @param string $strData
	 * @return string
	 */
	protected function convertEncodingBody($strData){

		if(function_exists('mb_encode_mimeheader')){
			$strData = mb_convert_encoding($strData,$this->strCharEncoding,"AUTO");
		}else{
			trigger_error('TwistPHP, skipping Email->convertEncodingBody as multi-byte (mbstring) support not enabled in PHP installation',E_USER_WARNING);
		}

		return $strData;
	}

	/**
	 * Build the email body and sent the relevant data headers
	 */
	protected function buildBody(){

		$blBodyPlain = (array_key_exists('body_plain',$this->arrEmailData) && $this->arrEmailData['body_plain'] != '');
		$blBodyHTML = (array_key_exists('body_html',$this->arrEmailData) && $this->arrEmailData['body_html'] != '');
		$blAttachments = (array_key_exists('attachments',$this->arrEmailData) && count($this->arrEmailData['attachments']) > 0);

		//If both html and plain are set then do multipart or if attachments do multipart anyway
		if(($blBodyHTML && $blBodyPlain) || $blAttachments || $blBodyHTML){

			//Convert the HTML into plain text best that can be done
			if($blBodyPlain == false){
				$this->generatePlainMessage();
			}

			//Convert the plain text to HTML best that can be done
			if($blBodyHTML == false){
				$this->generateHTMLMessage();
			}

			//Build the multipart encoding depending on the encoding type
			$this->encodeMultipartMessage();

			$strBody = $this->arrEmailData['body_multipart'];
		}else{

			if($blBodyHTML){
				$this->arrEmailData['headers'] .= sprintf("Content-Type: text/html; charset=%s\r\n",$this->strCharEncoding);
				$strBody = $this->arrEmailData['body_html'];
			}elseif($blBodyPlain){

				$this->sanitisePlainMessage();

				$this->arrEmailData['headers'] .= sprintf("Content-Type: text/plain; charset=%s\r\n",$this->strCharEncoding);
				$strBody = $this->arrEmailData['body_plain'];
			}else{
				$this->arrEmailData['headers'] .= "Content-type: text/plain; charset=utf-8\r\n";
				$strBody = "";
			}

			$this->arrEmailData['headers'] .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n",$this->blMultipartEncoding);
		}

		$this->arrEmailData['body'] = $this->convertEncodingBody($strBody);
	}

	/**
	 * Encode the multi-part email with all the correct headers and boundaries
	 */
	protected function encodeMultipartMessage(){

		$strRandomString = md5(\Twist::DateTime()->time());

		$strBoundary = sprintf("==MULTIPART_BOUNDARY_%s",$strRandomString);
		$strBoundaryHeader = chr(34).$strBoundary.chr(34);

		$strBoundaryMixed = sprintf("==MULTIPART_BOUNDARY_MIXED_%s",$strRandomString);
		$strBoundaryMixedHeader = chr(34).$strBoundaryMixed.chr(34);

		//Check if attachments are used
		$blAttachments = (array_key_exists('attachments',$this->arrEmailData) && count($this->arrEmailData['attachments']) > 0);

		//tell e-mail client this e-mail contains//alternate versions
		if($blAttachments){
			$this->arrEmailData['headers'] .= sprintf("Content-Type: multipart/mixed; boundary=%s\r\n\r\n",$strBoundaryMixedHeader);
		}else{
			$this->arrEmailData['headers'] .= sprintf("Content-Type: multipart/alternative; boundary=%s\r\n\r\n",$strBoundaryHeader);
		}

		//Start of the multipart body
		if($blAttachments){
			$this->arrEmailData['body_multipart'] = sprintf("\r\n\r\n--%s\r\n",$strBoundaryMixed);
			$this->arrEmailData['body_multipart'] .= sprintf("Content-Type: multipart/alternative; boundary=%s",$strBoundaryHeader);
		}else{
			$this->arrEmailData['body_multipart'] = "Multipart Message coming up";
		}

		//plain text version of message
		$this->arrEmailData['body_multipart'] .= sprintf("\r\n\r\n--%s\r\n",$strBoundary);
		$this->arrEmailData['body_multipart'] .= sprintf("Content-Type: text/plain; charset=%s\r\n",$this->strCharEncoding);
		$this->arrEmailData['body_multipart'] .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n",$this->blMultipartEncoding);

		$this->arrEmailData['body_multipart'] .= ($this->blMultipartEncoding == '7bit') ? $this->arrEmailData['body_plain'] : chunk_split(base64_encode($this->arrEmailData['body_plain']));

		//HTML version of message
		$this->arrEmailData['body_multipart'] .= sprintf("\r\n\r\n--%s\r\n",$strBoundary);
		$this->arrEmailData['body_multipart'] .= sprintf("Content-Type: text/html; charset=%s\r\n",$this->strCharEncoding);
		$this->arrEmailData['body_multipart'] .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n",$this->blMultipartEncoding);

		$this->arrEmailData['body_multipart'] .= ($this->blMultipartEncoding == '7bit') ? $this->arrEmailData['body_html'] : chunk_split(base64_encode($this->arrEmailData['body_html']));

		if($blAttachments){
			//Close the multi-part alternative
			$this->arrEmailData['body_multipart'] .= sprintf("\r\n--%s--\r\n",$strBoundary);
			$this->arrEmailData['body_multipart'] .= $this->attachmentHeaders($strBoundaryMixed);
			$this->arrEmailData['body_multipart'] .= sprintf("\r\n--%s--\r\n",$strBoundaryMixed);
		}else{
			//Close the multi-part body
			$this->arrEmailData['body_multipart'] .= sprintf("\r\n--%s--\r\n",$strBoundary);
		}
	}

	/**
	 * Set all the required attachment headers for each attachment.
	 * @param string $strBoundary
	 * @return string
	 */
	protected function attachmentHeaders($strBoundary){

		$strAttachmentHeaders = "";

		foreach($this->arrEmailData['attachments'] as $arrAttachment){

			$strAttachmentHeaders .= sprintf("\r\n\r\n--%s\r\n",$strBoundary);
			$strAttachmentHeaders .= sprintf("Content-Type: application/octet-stream; name=%s\r\n",$arrAttachment['name']);
			$strAttachmentHeaders .= sprintf("Content-Description: %s\r\n",$arrAttachment['name']);
			$strAttachmentHeaders .= sprintf("Content-Disposition: attachment; filename=%s; size=%s;\r\n",$arrAttachment['name'],$arrAttachment['size']);
			$strAttachmentHeaders .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n",$this->blMultipartEncoding);
			$strAttachmentHeaders .= ($this->blMultipartEncoding == '7bit') ?  $arrAttachment['data'] : chunk_split(base64_encode($arrAttachment['data']));
		}

		return $strAttachmentHeaders;
	}
}
