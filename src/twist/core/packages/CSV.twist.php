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
	 * @link       http://twistphp.com/
	 *
	 */

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;

	/**
	 * Simply Create, Serve and Import CSV files. Create a CSV file from and array of data, database query results can be directly exported as a CSV file with. Import CSV files into a usable indexed array of data.
	 */
	class CSV extends ModuleBase{

		public function __construct(){}

		/**
		 * Create a CSV file on the server, pass in a multi-dimensional array of data containing keys and values, the keys will be used as the field names and the values will be each for in the CSV. By default the Delimiter, Enclosure and Escape are already set.
		 *
		 * @param $strLocalFile Full path to the local CSV file to be stored
		 * @param $arrData Multi-dimensional array of data to be converted into a CSV
		 * @param $strDelimiter Delimiter to be used in creation of CSV data
		 * @param $strEnclosure Enclosure to be used in creation of CSV data
		 * @return mixed Returns the CSV data as a string
		 */
		public function export($strLocalFile,$arrData,$strDelimiter = ',',$strEnclosure = '"'){

			$mxdOut = $this->generateCSV($arrData,$strDelimiter,$strEnclosure);

			//Create the CSV file on the server
			file_put_contents($strLocalFile,$mxdOut);

			return $mxdOut;
		}

		/**
		 * Create a CSV file and serve to the user, pass in a multi-dimensional array of data containing keys and values, the keys will be used as the field names and the values will be each for in the CSV. By default the Delimiter, Enclosure and Escape are already set.
		 *
		 * @param $strFileName Name of the file to be served as a downloadable file
		 * @param $arrData Multi-dimensional array of data to be converted into a CSV
		 * @param $strDelimiter Delimiter to be used in creation of CSV data
		 * @param $strEnclosure Enclosure to be used in creation of CSV data
		 */
		public function serve($strFileName,$arrData,$strDelimiter = ',',$strEnclosure = '"'){

			$strOut = $this->generateCSV($arrData,$strDelimiter,$strEnclosure);

			header("Content-type: text/csv");
			header("Cache-Control: no-store, no-cache");
			header(sprintf('Content-Disposition: attachment; filename="%s"',$strFileName));

			echo $strOut;
			die();
		}

		/**
		 * Generate the CSV data from a multi-dimensional array of data, ability to use a custom delimiter and enclosure.
		 *
		 * @param $arrData Multi-dimensional array of data to be converted into a CSV
		 * @param $strDelimiter Delimiter to be used in creation of CSV data
		 * @param $strEnclosure Enclosure to be used in creation of CSV data
		 * @return mixed Returns the CSV data as a string
		 */
		protected function generateCSV($arrData, $strDelimiter = ',', $strEnclosure = '"' ){

			$resStream = fopen( 'php://temp/maxmemory', 'w+' );

			fputcsv( $resStream, array_keys($arrData[0]), $strDelimiter, $strEnclosure );

			foreach( $arrData as $arrRecord ) {
				fputcsv( $resStream, $arrRecord, $strDelimiter, $strEnclosure );
			}

			rewind( $resStream );
			$mxdOut = stream_get_contents( $resStream );
			fclose( $resStream );

			return $mxdOut;
		}

		/**
		 * Pass in the local file path to a CSV file, the CSV file will be parsed and turned into an array. By default the Delimiter, Enclosure and Escape are already set.
		 *
		 * @param $strLocalFile Full path to the local CSV file that will be imported
		 * @param $strDelimiter Expected delimiter used in the imported CSV
		 * @param $strEnclosure Expected enclosure to be used in creation of CSV data
		 * @param $strEscape String used to escape the CSV data
		 * @return array Returns Multi-dimensional array of the CSV data
		 */
		public function import($strLocalFile,$strDelimiter = ',',$strEnclosure = '"',$strEscape = '\\'){

			$arrOut = array();
			$strCSVData = file_get_contents($strLocalFile);

			$arrOut = $this->csvToArray($strCSVData,$strDelimiter,$strEnclosure,$strEscape);

			if(function_exists('str_getcsv')){
				//$arrOut = str_getcsv($strCSVData,$strDelimiter,$strEnclosure,$strEscape);
			}

			return $arrOut;
		}

		/**
		 * Process the imported CSV data into an array
		 *
		 * @note This function needs to be looked at further, possibly to be re-written
		 *
		 * @param $fileContent
		 * @param string $delimiter
		 * @param string $enclosure
		 * @param string $escape
		 * @return array
		 */
		protected function csvToArray($fileContent, $delimiter = ';', $enclosure = '"', $escape = '\\'){

			$lines = array();
			$fields = array();

			if($escape == $enclosure){
				$escape = '\\';
				$fileContent = str_replace(array('\\', $enclosure . $enclosure, "\r\n", "\r"),
					array('\\\\', $escape . $enclosure, "\\n", "\\n"), $fileContent);
			}else{
				$fileContent = str_replace(array("\r\n", "\r"), array("\\n", "\\n"), $fileContent);
			}

			$nb = strlen($fileContent);
			$field = '';
			$inEnclosure = false;
			$previous = '';

			for($i = 0; $i < $nb; $i++){
				$c = $fileContent[$i];

				if($c === $enclosure){

					if($previous !== $escape){
						$inEnclosure ^= true;
					}else{
						$field .= $enclosure;
					}

				}elseif($c === $escape){
					$next = $fileContent[$i + 1];

					if($next != $enclosure && $next != $escape){
						$field .= $escape;
					}

				}elseif($c === $delimiter){

					if($inEnclosure){
						$field .= $delimiter;
					}else{
						//end of the field
						$fields[] = $field;
						$field = '';
					}

				}elseif($c === "\n"){
					$fields[] = $field;
					$field = '';
					$lines[] = $fields;
					$fields = array();
				}else{
					$field .= $c;
				}

				$previous = $c;
			}
			//we add the last element
			if(true || $field !== ''){
				$fields[] = $field;
				$lines[] = $fields;
			}

			return $lines;
		}
	}