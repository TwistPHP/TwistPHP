<?php

	namespace Twist\Core\Models\String;

	class SyntaxHighlight{

		/**
		 * Get the contents of a file and highlight the code within the file, set a range above and below a focus point (Line No)
		 * @param $dirFile
		 * @param string $strOutputType Choose from plain, em, table, dl (Plain puts line numbers in html comments)
		 * @param null|int $intFocusLineNo Adding a focus line no. will activate $intFocusRange
		 * @param int $intFocusRange Amount of lines to display above and below the focus line
		 * @return string
		 */
		public static function file($dirFile,$strOutputType = 'plain',$intFocusLineNo = null,$intFocusRange = 3){
			return (file_exists($dirFile)) ? self::processCode(file_get_contents($dirFile),$strOutputType,$intFocusLineNo,$intFocusRange) : '';
		}

		/**
		 * Hightlight some code that has been passed in, set a range above and below a focus point (Line No)
		 * @param $strCode
		 * @param string $strOutputType Choose from plain, em, table, dl (Plain puts line numbers in html comments)
		 * @param null|int $intFocusLineNo Adding a focus line no. will activate $intFocusRange
		 * @param int $intFocusRange Amount of lines to display above and below the focus line
		 * @return string
		 */
		public static function code($strCode,$strOutputType = 'plain',$intFocusLineNo = null,$intFocusRange = 3){
			return self::processCode($strCode,$strOutputType,$intFocusLineNo,$intFocusRange);
		}

		/**
		 * Process the code that has been passed in, highlight and output
		 * @param $strCode
		 * @param string $strOutputType Choose from plain, em, table, dl (Plain puts line numbers in html comments)
		 * @param null|int $intFocusLineNo Adding a focus line no. will activate $intFocusRange
		 * @param int $intFocusRange Amount of lines to display above and below the focus line
		 * @return string
		 */
		protected static function processCode($strCode,$strOutputType,$intFocusLineNo,$intFocusRange){

			$strOut = '';
			$intLineNo = 0;

			//Get all the lines of code as individual lines
			$arrCodeLines = self::explodeLines(highlight_string($strCode, true));

			$intStartLine = (!is_null($intFocusLineNo)) ? $intFocusLineNo-$intFocusRange : 0;
			$intEndLine = (!is_null($intFocusLineNo)) ? $intFocusLineNo+$intFocusRange : count($arrCodeLines);

			switch($strOutputType){

				case 'em':
					$strCodeContainer = "<code>%s</code>";
					$strLineContainer = "<em%s>%s</em>%s\n";
					break;

				case 'table':
					$strCodeContainer = "<table>\n%s</table>";
					$strLineContainer = "<tr%s><th>%s</th><td>%s</td></tr>";
					break;

				case 'dl':
					$strCodeContainer = "<dl>\n%s</dl>";
					$strLineContainer = "<dt%s>%s</dt><dd>%s</dd>";
					break;

				case 'plain':
				default:
					$strCodeContainer = "<code>%s</code>";
					$strLineContainer = "<!-- %s, Line: %s -->%s\n";
					break;
			}

			foreach($arrCodeLines as $strEachLine){
				$intLineNo++;

				//Only output lines that are within the correct range
				if($intLineNo >= $intStartLine && $intLineNo <= $intEndLine){

					$strFocusLine = ($intLineNo == $intFocusLineNo) ? ' class="codeFocus"' : '';
					$strOut .= sprintf($strLineContainer,$strFocusLine,$intLineNo,self::convertStyles($strEachLine));
				}
			}

			return sprintf($strCodeContainer,$strOut);
		}

		/**
		 * Explode the highlighted code that has been returned, fix any unclosed/unopened tags
		 * @param $strCode
		 * @return array Each line of highlighted code as an array
		 */
		protected static function explodeLines($strCode){

			//Pre-pair highlighted code
			$strCode = str_replace(array("<code>","</code>"),"",$strCode);
			$strCode = str_replace(array("<br>","<br />","<br >"),"\n",$strCode);

			$arrCodeLines = explode("\n",$strCode);

			//Loose the first and last 2 lines as they are the base color, we will re-add that to the main container
			array_shift($arrCodeLines);
			array_pop($arrCodeLines);
			array_pop($arrCodeLines);

			$strLastOpenTag = $strNextLastOpenTag = '';

			foreach($arrCodeLines as $intPosition => $strCodeLine){

				preg_match_all('#(<[\/]?span( style="[^\"]*")?>)#i', $strCodeLine, $arrSpanTags);

				$intCloses = $intOpens = 0;
				$strFirstTag = null;

				foreach($arrSpanTags[2] as $intSearchPosition => $strTagType){
					if($strTagType == ''){
						$intCloses++;
						$strFirstTag = (is_null($strFirstTag)) ? 'close' : $strFirstTag;
					}else{
						$intOpens++;
						$strFirstTag = (is_null($strFirstTag)) ? 'open' : $strFirstTag;
						$strNextLastOpenTag = $arrSpanTags[1][$intSearchPosition];
					}
				}

				if($intOpens != $intCloses){
					$strCodeLine = ($strFirstTag == 'open') ? $strCodeLine.'</span>' : $strLastOpenTag.$strCodeLine;
				}elseif($intOpens == 0 && $intCloses == 0 || $strFirstTag == 'close'){
					$strCodeLine = $strLastOpenTag.$strCodeLine.'</span>';
				}

				$strLastOpenTag = $strNextLastOpenTag;

				//Remove any empty span tags (Clean up essentially)
				$arrCodeLines[$intPosition] = preg_replace('#<span style="[^\"]*"></span>#i',"",$strCodeLine);
			}

			return $arrCodeLines;
		}

		/**
		 * Apply new styles to the code rather than the default ones
		 * @param $strFormattedCode
		 * @return mixed
		 */
		protected static function convertStyles($strFormattedCode){
			//@todo Write some code to detect the default styles and replace them with custom ones if required
			return $strFormattedCode;
		}
	}
