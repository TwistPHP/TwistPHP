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
 * @link       https://twistphp.com
 *
 */

namespace Twist\Core\Models;
use Twist\Core\Classes\Error;

/**
 * Debugging the framework and its modules, functionality to access debug data can be found here. Data will only be present if Debugging is enabled in your settings.
 */
final class Debug{

	protected $resTemplate = null;
	public $arrDebugLog = array();

	public function __construct(){

	}

	/**
	 * Log some debug data into the debug array, the debug data is shown on the debug window.
	 * @param $strSystem
	 * @param $strType
	 * @param $mxdData
	 */
	public function log($strSystem,$strType,$mxdData){

		if(!array_key_exists($strSystem,$this->arrDebugLog)){
			$this->arrDebugLog[$strSystem] = array();
		}

		if(!array_key_exists($strType,$this->arrDebugLog[$strSystem])){
			$this->arrDebugLog[$strSystem][$strType] = array();
		}

		$this->arrDebugLog[$strSystem][$strType][] = $mxdData;
	}

	/**
	 * Process the debug window to be output into the page.
	 * @param $arrCurrentRoute
	 * @return string
	 */
	public function window($arrCurrentRoute){

		//print_r($this->arrDebugLog);

		$arrTimer = \Twist::getEvents(true);

		$this->resTemplate = \Twist::View('TwistDebugBar');
		$this->resTemplate->setDirectory( sprintf('%sdebug/',TWIST_FRAMEWORK_VIEWS));

		$arrTags = array(
			'errors' => '',
			'warning_count' => 0,
			'notice_count' => 0,
			'other_count' => 0,
			'errors' => '',
			'database_queries' => '',
			'database_query_count' => 0,
			'views' => '',
			'timeline' => '',
			'execution_time' => '',
			'cache' => '',
			'memory' => $arrTimer['memory'],
			'memory_chart' => ''
		);

		if(array_key_exists('Error',$this->arrDebugLog)){
			foreach($this->arrDebugLog['Error']['php'] as $arrEachItem){

				if($arrEachItem['type'] === 'Warning'){
					$arrTags['warning_count']++;
				}elseif($arrEachItem['type'] === 'Notice'){
					$arrTags['notice_count']++;
				}else{
					$arrTags['other_count']++;
				}

				$arrTags['errors'] .= $this->resTemplate->build('components/php-error.tpl',$arrEachItem);
			}
		}

		if(array_key_exists('Database',$this->arrDebugLog)) {
			foreach ($this->arrDebugLog['Database']['queries'] as $arrEachItem) {

				$arrParts = explode(' ', trim($arrEachItem['query']));
				$arrEachItem['type'] = strtoupper($arrParts[0]);
				unset($arrParts);

				if ($arrEachItem['affected_rows'] < 0 || $arrEachItem['affected_rows'] === false) {
					$arrEachItem['affected_rows'] = 0;
				}

				if ($arrEachItem['status']) {
					$arrEachItem['response'] = 'success';
					if ($arrEachItem['type'] != 'INSERT' && ($arrEachItem['num_rows'] <= 0 && $arrEachItem['affected_rows'] <= 0)) {
						$arrEachItem['response'] = 'empty';
					}
				} else {
					$arrEachItem['response'] = 'fail';
				}

				$arrTags['database_queries'] .= $this->resTemplate->build('components/database-query.tpl', $arrEachItem);
			}
			$arrTags['database_query_count'] = count($this->arrDebugLog['Database']['queries']);
		}

		if(array_key_exists('View',$this->arrDebugLog)){
			foreach($this->arrDebugLog['View']['usage'] as $arrEachItem){

				if($arrEachItem['instance'] != 'TwistDebugBar'){
					$arrTags['views'] .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>",$arrEachItem['instance'],$arrEachItem['file'],implode("<br>",$arrEachItem['tags']));
				}
			}
		}

		$arrTags['current_route'] = '';
		foreach($arrCurrentRoute as $strKey => $strValue){
			$arrTags['current_route'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => is_array($strValue) ? sprintf('<pre>%s</pre>',print_r($strValue,true)) : htmlentities($strValue)));
		}


		$arrTags['routes'] = '';

		foreach(\Twist::Route()->getAll() as $strType => $arrItems){
			foreach($arrItems as $arrEachRoute){
				$arrEachRoute['highlight'] = ($arrEachRoute['registered_uri'] === $arrCurrentRoute['registered_uri']) ? 'highlight' : '';
				$arrEachRoute['item'] = (is_array($arrEachRoute['item'])) ? implode('->',$arrEachRoute['item']) : $arrEachRoute['item'];
				$arrTags['routes'] .=  $this->resTemplate->build('components/each-route.tpl',$arrEachRoute);
			}
		}

		$arrTags['get'] = '';
		foreach($_GET as $strKey => $strValue){
			$arrTags['get'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => $strValue));
		}

		$arrTags['post'] = '';
		foreach($_POST as $strKey => $strValue){
			$arrTags['post'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => $strValue));
		}

		$arrTags['twist_session'] = '';
		$arrTags['php_session'] = '';
		foreach($_SESSION as $strKey => $strValue){
			if($strKey == 'twist-session'){
				foreach($strValue as $mxdKey => $mxdValue){
					$arrTags['twist_session'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $mxdKey,'value' => $mxdValue));
				}
			}else{
				$arrTags['php_session'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => $strValue));
			}
		}

		$arrTags['cookie'] = '';
		foreach($_COOKIE as $strKey => $strValue){
			$arrTags['cookie'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => $strValue));
		}

		$arrTags['request_headers'] = '';
		foreach(Error::apacheRequestHeaders() as $strKey => $strValue){
			$arrTags['request_headers'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => $strValue));
		}

		$arrTags['server'] = '';
		foreach(Error::serverInformation() as $strKey => $strValue){
			$arrTags['server'] .= $this->resTemplate->build('components/dt-item.tpl',array('key' => $strKey,'value' => $strValue));
		}

		$intTotalTime = $arrTimer['end']-$arrTimer['start'];
		$intTotalPercentage = $intUsedTime = 0;

		foreach($arrTimer['log'] as $strKey => $arrInfo){

			$intCurrentTimeUsage = $arrInfo['time']-$intUsedTime;

			$intPercentage = ($intCurrentTimeUsage/$intTotalTime)*100;
			$intTotalPercentage += $intPercentage;

			$arrTimelineTags = array(
				'total_percentage' => round($intTotalPercentage,2),
				'percentage' => round($intPercentage,2),
				'time' => ($intCurrentTimeUsage < 1) ? round($intCurrentTimeUsage*1000).'ms' : round($intCurrentTimeUsage,3).'s',
				'title' => $arrInfo['title']
			);

			$arrTags['timeline'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);

			$arrTimelineTags['title'] = \Twist::File()->bytesToSize($arrInfo['memory']).' - '.$arrInfo['title'];
			$arrTags['memory_chart'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);
			$intUsedTime += $intCurrentTimeUsage;
		}

		$arrTimelineTags = array(
			'total_percentage' => 100,
			'percentage' => round(100-$intTotalPercentage,2),
			'time' => ($intTotalTime < 1) ? round($intTotalTime*1000).'ms' : round($intTotalTime,3).'s',
			'title' => 'Page Loaded'
		);

		$arrTags['timeline'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);

		$arrTimelineTags['title'] = \Twist::File()->bytesToSize($arrTimer['memory']['end']).' - Page Loaded';
		$arrTags['memory_chart'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);

		$arrTags['execution_time'] = round($intTotalTime,4);
		$arrTags['execution_time_formatted'] = ($intTotalTime < 1) ? round($intTotalTime*1000).'ms' : round($intTotalTime,3).'s';

		return $this->resTemplate->build('_base.tpl',$arrTags);
	}
}