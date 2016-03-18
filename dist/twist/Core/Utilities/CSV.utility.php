<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Copyright (C) 2016  Shadow Technologies Ltd.
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

	namespace Twist\Core\Utilities;

	/**
	 * Simply Create, Serve and Import CSV files. Create a CSV file from and array of data, database query results can be directly exported as a CSV file with. Import CSV files into a usable indexed array of data.
	 */
	class CSV extends Base{

		/**
		 * Create a CSV file on the server, pass in a multi-dimensional array of data containing keys and values, the keys will be used as the field names and the values will be each for in the CSV. By default the Delimiter, Enclosure and Escape are already set.
		 *
		 * @param $strLocalFile Full path to the local CSV file to be stored
		 * @param $arrData      Multi-dimensional array of data to be converted into a CSV
		 * @param $strDelimiter Delimiter to be used in creation of CSV data
		 * @param $strEnclosure Enclosure to be used in creation of CSV data
		 *
		 * @return mixed Returns the CSV data as a string
		 */
		public function export( $strLocalFile, $arrData, $strDelimiter = ',', $strEnclosure = '"' ) {

			$mxdOut = $this -> generateCSV( $arrData, $strDelimiter, $strEnclosure );

			//Create the CSV file on the server
			file_put_contents( $strLocalFile, $mxdOut );

			return $mxdOut;
		}

		/**
		 * Create a CSV file and serve to the user, pass in a multi-dimensional array of data containing keys and values, the keys will be used as the field names and the values will be each for in the CSV. By default the Delimiter, Enclosure and Escape are already set.
		 *
		 * @param $strFileName  Name of the file to be served as a downloadable file
		 * @param $arrData      Multi-dimensional array of data to be converted into a CSV
		 * @param $strDelimiter Delimiter to be used in creation of CSV data
		 * @param $strEnclosure Enclosure to be used in creation of CSV data
		 */
		public function serve( $strFileName, $arrData, $strDelimiter = ',', $strEnclosure = '"' ) {

			$strOut = $this -> generateCSV( $arrData, $strDelimiter, $strEnclosure );

			header( "Content-type: text/csv" );
			header( "Cache-Control: no-store, no-cache" );
			header( sprintf( 'Content-Disposition: attachment; filename="%s"', $strFileName ) );

			echo $strOut;
			die();
		}

		/**
		 * Generate the CSV data from a multi-dimensional array of data, ability to use a custom delimiter and enclosure.
		 *
		 * @param $arrData      Multi-dimensional array of data to be converted into a CSV
		 * @param $strDelimiter Delimiter to be used in creation of CSV data
		 * @param $strEnclosure Enclosure to be used in creation of CSV data
		 *
		 * @return mixed Returns the CSV data as a string
		 */
		protected function generateCSV( $arrData, $strDelimiter = ',', $strEnclosure = '"' ) {

			$resStream = fopen( 'php://temp/maxmemory', 'w+' );

			fputcsv( $resStream, array_keys( $arrData[0] ), $strDelimiter, $strEnclosure );

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
		 * @param $strLocalFile         Full path to the local CSV file that will be imported
		 * @param $strLineDelimiter     Expected delimiter for each line used in the CSV
		 * @param $intFieldDelimiter    Expected delimiter for each field used in the CSV
		 * @param $strEnclosure         Expected enclosure to be used in creation of CSV data
		 * @param $strEscape            String used to escape the CSV data (Only used if mbstring is enabled in PHP)
		 * @param $blUseFirstRowAsKeys  If TRUE, the first row of data is returned as the indexes for the array data
		 * @param $strEncoding          Output encoding (defaults to UTF-8)
		 * @param $strLocale            Expected inout locale (default of en_GB.UTF-8 ensures multibyte strings are safe)
		 *
		 * @return array Returns Multi-dimensional array of the CSV data
		 */
		public function import( $strLocalFile, $strLineDelimiter = "\n", $intFieldDelimiter = ',', $strEnclosure = '"', $strEscape = '\\', $blUseFirstRowAsKeys = false, $strEncoding = 'UTF-8', $strLocale = 'en_GB.UTF-8' ) {

			$arrOut = $arrHeaders = array();

			$strOriginalLocale = locale_get_default();
			setlocale(LC_ALL, $strLocale);

			$strImportedString = file_get_contents($strLocalFile);

			if( function_exists('mb_strlen')
					&& function_exists('mb_convert_encoding')
					&& mb_strlen($strImportedString) !== strlen($strImportedString) ) {
				$strImportedString = mb_convert_encoding($strImportedString, $strEncoding, mb_detect_encoding($strImportedString));
			}

			foreach( str_getcsv( $strImportedString, $strLineDelimiter ) as $intRow => $strRow ) {

				if( function_exists('mb_strlen')
						&& function_exists('mb_convert_encoding')
						&& mb_strlen($strRow) !== strlen($strRow) ) {
					$strRow = mb_convert_encoding($strRow, $strEncoding, mb_detect_encoding($strRow));
				}

				$arrRow = str_getcsv( $strRow, $intFieldDelimiter, $strEnclosure, $strEscape );

				if( $blUseFirstRowAsKeys ) {
					if( $intRow === 0 ) {
						$arrHeaders = $arrRow;
					} else {
						$arrRowIndexed = array();
						foreach( $arrRow as $intField => $mxdField ) {
							$arrRowIndexed[$arrHeaders[$intField]] = $mxdField;
						}
						$arrOut[] = $arrRowIndexed;
					}
				} else {
					$arrOut[] = $arrRow;
				}
			}

			setlocale(LC_ALL, $strOriginalLocale);

			return $arrOut;
		}

	}