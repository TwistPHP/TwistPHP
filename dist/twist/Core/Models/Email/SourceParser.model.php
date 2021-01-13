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

/**
 * The ability to parse raw email source and turn it into usable data using a single method call.
 * @package Twist\Core\Models\Email
 */
class SourceParser{

	protected $blShowHeaders = false;
	protected $arrTempData = array();

	/**
	 * Pass in the full raw source of an email and it will parse and return as a simple usable array of data.
	 * @param string $strEmailSource
	 * @param bool $blShowHeaders
	 * @return array
	 */
	public function processEmailSource($strEmailSource,$blShowHeaders = false){

		$this->blShowHeaders = $blShowHeaders;

		$arrBoundaries = $this->splitBoundaries($strEmailSource);
		$arrEmailData = $this->parseBoundaries($arrBoundaries);
		$arrEmailData['headers'] = $this->parseHeaders($arrBoundaries['headers']);

		//Get the from address from the email
		preg_match("#([^\<]+)\<(.*)\>#i",$arrEmailData['headers']['From'],$strFromMatch);
		$arrEmailData['from'] = array(
			'email' => (strstr($arrEmailData['headers']['From'],"<")) ? $strFromMatch[2] : $arrEmailData['headers']['From'],
			'name' => (strstr($arrEmailData['headers']['From'],"<")) ? $strFromMatch[1] : ''
		);

		//Get all the To addresses and their names
		$arrToAddress = explode(',',$arrEmailData['headers']['To']);
		foreach($arrToAddress as $strEachAddress){
			preg_match("#([^\<]+)\<(.*)\>#i",$strEachAddress,$strFromMatch);
			$arrEmailData['to'][] = array(
				'email' => (strstr($strEachAddress,"<")) ? $strFromMatch[2] : $strEachAddress,
				'name' => (strstr($strEachAddress,"<")) ? $strFromMatch[1] : ''
			);
		}

		//Get all the Cc addresses and their names
		if(array_key_exists('Cc',$arrEmailData['headers'])){
			$arrCcAddress = explode(',',$arrEmailData['headers']['Cc']);
			foreach($arrCcAddress as $strEachAddress){
				preg_match("#([^\<]+)\<(.*)\>#i",$strEachAddress,$strFromMatch);
				$arrEmailData['cc'][] = array(
					'email' => (strstr($strEachAddress,"<")) ? $strFromMatch[2] : $strEachAddress,
					'name' => (strstr($strEachAddress,"<")) ? $strFromMatch[1] : ''
				);
			}
		}

		//Get all the Bcc addresses and their names (Only shows if you are the Bcc)
		if(array_key_exists('Bcc',$arrEmailData['headers'])){
			preg_match("#([^\<]+)\<(.*)\>#i",$arrEmailData['headers']['Bcc'],$strFromMatch);
			$arrEmailData['bcc'] = array(
				'email' => (strstr($arrEmailData['headers']['Bcc'],"<")) ? $strFromMatch[2] : $arrEmailData['headers']['Bcc'],
				'name' => (strstr($arrEmailData['headers']['Bcc'],"<")) ? $strFromMatch[1] : ''
			);
		}

		//Get the subject form the email
		$arrEmailData['date'] = date('Y-m-d H:i:s',strtotime($arrEmailData['headers']['Date']));

		//Get the subject form the email
		$arrEmailData['subject'] = $arrEmailData['headers']['Subject'];

		if(!$this->blShowHeaders){
			unset($arrEmailData['headers']);
		}

		return $arrEmailData;
	}

	/**
	 * Parse the decoded boundaries, turn them into a usable data
	 * @param array $arrBoundaries
	 * @param bool $blReturnLog
	 * @return array
	 */
	protected function parseBoundaries($arrBoundaries,$blReturnLog = true){

		if($blReturnLog){
			$this->arrTempData = array(
				'headers' => array(),
				'from' => array(),
				'to' => array(),
				'cc' => array(),
				'bcc' => array(),
				'date' => '',
				'subject' => '',
				'body' => array(
					'plain' => '',
					'html' => ''
				),
				'attachments' => array()
			);
		}

		if(array_key_exists('children',$arrBoundaries) && count($arrBoundaries['children'])){
			foreach($arrBoundaries['children'] as $arrEachChild){
				$this->parseBoundaries($arrEachChild,false);
			}
		}else{

			if(array_key_exists('content-transfer-encoding',$arrBoundaries['headers']) && strstr($arrBoundaries['headers']['content-transfer-encoding'],'quoted-printable')){
				$arrBoundaries['data'] = $this->decodeQuotedPrintable($arrBoundaries['data']);
			}elseif(array_key_exists('content-transfer-encoding',$arrBoundaries['headers']) && strstr($arrBoundaries['headers']['content-transfer-encoding'],'base64')){
				$arrBoundaries['data'] = base64_decode($arrBoundaries['data']);
			}

			if($arrBoundaries['type'] == 'text/plain'){
				$this->arrTempData['body']['plain'] = $arrBoundaries['data'];
			}elseif($arrBoundaries['type'] == 'text/html'){
				$this->arrTempData['body']['html'] = $arrBoundaries['data'];
			}else{

				if(!$this->blShowHeaders){
					unset($arrBoundaries['uid']);
					unset($arrBoundaries['headers']);
				}

				$this->arrTempData['attachments'][] = $arrBoundaries;
			}
		}

		return $this->arrTempData;
	}

	/**
	 * Split the email source into a multi-dimensional array by boundary ID
	 * @param string $strData
	 * @param string $strBoundary
	 * @return array
	 */
	protected function splitBoundaries($strData,$strBoundary = ''){

		$arrOut = array();
		$blFirstPart = ($strBoundary === '');

		preg_match("#(content\-type|type)\:([^\;]+)\;#i",$strData,$arrContentType);
		//preg_match("#boundary\=\"([^\"]+)\"#i",$strData,$arrBoundaryInfo);
		preg_match("#boundary\=[\"]?([^\"|\n]+)#i",$strData,$arrBoundaryInfo);

		if(array_key_exists(1,$arrBoundaryInfo) && count($arrBoundaryInfo[1])){

			$strType = $arrContentType[2];
			$strBoundary = $arrBoundaryInfo[1];

			$strData = str_replace('--'.$strBoundary.'--','--'.$strBoundary,$strData);
			$arrDataParts = explode('--'.$strBoundary,$strData);

			$strEmailHeaders = ($blFirstPart) ? $arrDataParts[0] : '';
			array_shift($arrDataParts);
			array_pop($arrDataParts);

			if(count($arrDataParts)){

				$arrOut = array(
					'uid' => $strBoundary,
					'type' => $strType,
					'headers' => $strEmailHeaders,
					'children' => array()
				);

				//We only want email headers from the very first boundary
				if(!$blFirstPart){
					unset($arrOut['headers']);
				}

				foreach($arrDataParts as $strNewData){
					$arrOut['children'][] = $this->splitBoundaries($strNewData,$strBoundary);
				}
			}

		}else{

			//Get the headers
			$arrHeaderParts = explode("\n\n",$strData);
			preg_match("#content\-type\:([^\;]+)\;#i",$strData,$arrContentType);
			preg_match_all("#([a-z\-\s]+)[\:\=]([^\n]+)\n#i",$arrHeaderParts[0]."\n",$arrFoundHeaders);

			$strType = trim($arrContentType[1]);

			//Return everything but the headers
			unset($arrHeaderParts[0]);
			$strContent = trim(implode("\n\n",$arrHeaderParts));

			//Parse the headers into a formatted array
			$arrHeaders = array();
			foreach($arrFoundHeaders[1] as $intKey => $strHeaderTag){
				$arrHeaders[trim(strtolower($strHeaderTag))] = trim($arrFoundHeaders[2][$intKey]);
			}

			$intContentID = '';
			if(array_key_exists('content-id',$arrHeaders)){
				$arrHeaders['content-id'] = trim($arrHeaders['content-id'],'<>');
				$intContentID = $arrHeaders['content-id'];
			}elseif(array_key_exists('x-content-id',$arrHeaders)){
				$arrHeaders['x-content-id'] = trim($arrHeaders['x-content-id'],'<>');
				$intContentID = $arrHeaders['x-content-id'];
			}

			$strDisposition = (array_key_exists('content-disposition',$arrHeaders)) ? explode(';',$arrHeaders['content-disposition'])[0] : 'attachment';

			if(array_key_exists('filename',$arrHeaders)){
				$strFilename = trim($arrHeaders['filename'],'"');
			}elseif(array_key_exists('content-disposition',$arrHeaders) && strstr($arrHeaders['content-disposition'],'filename')){
				list($strIgnore,$strFilename) = explode('filename="',trim($arrHeaders['content-disposition'],'"'));
			}elseif(array_key_exists('content-type',$arrHeaders) && strstr($arrHeaders['content-type'],'name')){
				list($strIgnore,$strFilename) = explode('name="',trim($arrHeaders['content-type'],'"'));
			}else{
				$arrInfo = \Twist::File()->mimeTypeInfoByMime($strType);
				$strFilename = $arrHeaders['content-id'].$arrInfo['extensions'];
			}

			$arrOut = array(
				'uid' => $strBoundary,
				'cid' => $intContentID,
				'type' => $strType,
				'filename' => $strFilename,
				'disposition' => $strDisposition,
				'headers' => $arrHeaders,
				'data' => $strContent
			);
		}

		return $arrOut;
	}

	/**
	 * Parse the email headers into a usable array of data
	 * @param $strHeaders
	 * @return array
	 */
	protected function parseHeaders($strHeaders){

		$strKey = '';
		$arrHeaders = array();

		$arrHeaderLines = explode("\n",$strHeaders);
		foreach($arrHeaderLines as $strEachLine){
			if(substr($strEachLine,0,1) === ' ' || substr($strEachLine,0,1) === "\t"){
				if(is_array($arrHeaders[$strKey])){
					$arrHeaders[$strKey][count($arrHeaders[$strKey])-1] .= "\n".$strEachLine;
				}else{
					$arrHeaders[$strKey] .= "\n" . $strEachLine;
				}
			}else{
				list($strKey,$strValue) = explode(':',$strEachLine,2);

				if(!empty($strKey)){
					if(array_key_exists($strKey,$arrHeaders) && !is_array($arrHeaders[$strKey])){
						$arrHeaders[$strKey] = array($arrHeaders[$strKey]);
						$arrHeaders[$strKey][] = trim($strValue);
					}else{
						$arrHeaders[$strKey] = trim($strValue);
					}
				}
			}
		}

		return $arrHeaders;
	}

	/**
	 * Decode QPrint helps with decoding emails from the system
	 * @param string $strData
	 * @return string
	 */
	protected function decodeQuotedPrintable($strData){
		$strData = imap_qprint($strData);
		return $strData;
	}

	/**
	 * Strip out all unwanted headers from email raw source
	 * @param string $strEmailSource
	 * @return mixed
	 */
	protected function stripEmailHeaders($strEmailSource){

		$arrParts = explode("\n\n",$strEmailSource);

		$arrParts[0] = null;

		$strEmailSource = implode("\n\n",$arrParts);
		$strEmailSource = str_replace("=\n","",$strEmailSource);

		return $strEmailSource;
	}
}