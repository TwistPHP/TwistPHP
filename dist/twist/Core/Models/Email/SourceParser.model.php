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

	protected $arrTempData = array();

	/**
	 * Pass in the full raw source of an email and it will parse and return as a simple usable array of data.
	 * @param string $strEmailSource
	 * @return array
	 */
	public function processEmailSource($strEmailSource){

		$arrBoundaries = $this->splitBoundaries($strEmailSource);
		$arrEmailData = $this->parseBoundaries($arrBoundaries);

		//$arrEmailData['source'] = $strEmailSource;

		//Get the subject form the email
		preg_match("#\nSubject:(.*)\n#i",$strEmailSource,$arrResults);
		$arrEmailData['subject'] = (is_array($arrResults) && count($arrResults) > 1) ? trim($arrResults[1]) : '';

		//Get the from address from the email
		preg_match("#\nFrom:(.*)\n#i",$strEmailSource,$arrResults);
		$arrEmailData['from'] = (is_array($arrResults) && count($arrResults) > 1) ? trim($arrResults[1]) : '';
		$arrEmailData['from_name'] = '';

		if(strstr($arrEmailData['from'],"<")){
			preg_match("#([a-z0-9\-\_\s]+)\<([^\>]+)#i",$arrEmailData['from'],$strFromMatch);
			$arrEmailData['from'] = $strFromMatch[2];
			$arrEmailData['from_name'] = $strFromMatch[1];
		}

		//Get the to address from the email
		preg_match("#\nTo:(.*)\n#i",$strEmailSource,$arrResults);
		$arrEmailData['to'] = (is_array($arrResults) && count($arrResults) > 1) ? trim($arrResults[1]) : '';

		if(strstr($arrEmailData['to'],"<")){
			preg_match("#([a-z0-9\-\_\s]+)\<([^\>]+)#i",$arrEmailData['to'],$strToMatch);
			$arrEmailData['to'] = $strToMatch[2];
			$arrEmailData['to_name'] = $strToMatch[1];
		}

		//Get the Cc address from the email
		preg_match("#\nCc:(.*)\n#i",$strEmailSource,$arrResults);
		$arrEmailData['cc'] = (is_array($arrResults) && count($arrResults) > 1) ? trim($arrResults[1]) : '';

		if(strstr($arrEmailData['cc'],"<")){
			preg_match("#([a-z0-9\-\_\s]+)\<([^\>]+)#i",$arrEmailData['cc'],$strCcMatch);
			$arrEmailData['cc'] = $strCcMatch[2];
			$arrEmailData['cc_name'] = $strCcMatch[1];
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

		preg_match("#(content\-type|type)\:([^\;]+)\;#i",$strData,$arrContentType);
		//preg_match("#boundary\=\"([^\"]+)\"#i",$strData,$arrBoundaryInfo);
		preg_match("#boundary\=[\"]?([^\"|\n]+)#i",$strData,$arrBoundaryInfo);

		if(array_key_exists(1,$arrBoundaryInfo) && count($arrBoundaryInfo[1])){

			$strType = $arrContentType[2];
			$strBoundary = $arrBoundaryInfo[1];

			$strData = str_replace('--'.$strBoundary.'--','--'.$strBoundary,$strData);
			$arrDataParts = explode('--'.$strBoundary,$strData);
			array_shift($arrDataParts);
			array_pop($arrDataParts);

			if(count($arrDataParts)){

				$arrOut = array(
					'uid' => $strBoundary,
					'type' => $strType,
					'children' => array()
				);

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

			//Parse the headers into a formated array
			$arrHeaders = array();
			foreach($arrFoundHeaders[1] as $intKey => $strHeaderTag){
				$arrHeaders[trim(strtolower($strHeaderTag))] = trim(trim($arrFoundHeaders[2][$intKey]),'"');
			}

			if(array_key_exists('content-id',$arrHeaders)){
				$arrHeaders['content-id'] = trim($arrHeaders['content-id'],'<>');
			}

			$arrOut = array(
				'uid' => $strBoundary,
				'type' => $strType,
				'headers' => $arrHeaders,
				'data' => $strContent
			);

		}

		return $arrOut;
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