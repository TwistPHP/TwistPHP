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

namespace Twist\Core\Models;
use Twist\Classes\Error;

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
	 * @param string $strSystem
	 * @param string $strType
	 * @param mixed $mxdData
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
	 * @param array $arrCurrentRoute
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
				$arrEachItem['time_formatted'] = round($arrEachItem['time'],8).'s';
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
			$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : '-');
			$arrTags['current_route'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => is_array($strValue) ? sprintf('<pre>%s</pre>',print_r($strValue,true)) : htmlentities($strValue),'type' => gettype($strValue),'length' => $intLength));
		}

		$arrTags['routes'] = '';

		foreach(\Twist::Route()->getAll() as $strType => $arrItems){
			foreach($arrItems as $arrEachRoute){

				$arrEachRoute['highlight'] = ($arrEachRoute['registered_uri'] === $arrCurrentRoute['registered_uri']) ? 'highlight' : '';
				$arrEachRoute['item'] = (is_array($arrEachRoute['item'])) ? implode('->',$arrEachRoute['item']) : $arrEachRoute['item'];

				$arrRestriction = \Twist::Route()->currentRestriction(($arrEachRoute['registered_uri'] == '') ? '/' : $arrEachRoute['registered_uri']);
				$arrEachRoute['restricted'] = ($arrRestriction['restricted_uri']) ? 'Restricted ['.$arrRestriction['restricted_level'].']' : '';

				$arrTags['routes'] .=  $this->resTemplate->build('components/each-route.tpl',$arrEachRoute);
			}
		}

		$arrTags['get'] = '';
		foreach($_GET as $strKey => $strValue){
			$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
			$arrTags['get'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => $strValue,'type' => gettype($strValue),'length' => $intLength));
		}

		$arrTags['post'] = '';
		foreach($_POST as $strKey => $strValue){
			$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
			$arrTags['post'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => $strValue,'type' => gettype($strValue),'length' => $intLength));
		}

		$arrTags['twist_session'] = '';
		$arrTags['php_session'] = '';
		foreach($_SESSION as $strKey => $strValue){
			if($strKey == 'twist-session'){
				foreach($strValue as $mxdKey => $mxdValue){
					$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
					$arrTags['twist_session'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $mxdKey,'value' => $mxdValue,'type' => gettype($strValue),'length' => $intLength));
				}
			}else{
				$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
				$arrTags['php_session'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => $strValue,'type' => gettype($strValue),'length' => $intLength));
			}
		}

		$arrTags['cookie'] = '';
		foreach($_COOKIE as $strKey => $strValue){
			$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
			$arrTags['cookie'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => $strValue,'type' => gettype($strValue),'length' => $intLength));
		}

		$arrTags['request_headers'] = '';
		foreach(Error::apacheRequestHeaders() as $strKey => $strValue){
			$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
			$arrTags['request_headers'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => $strValue,'type' => gettype($strValue),'length' => $intLength));
		}

		$arrTags['server'] = '';
		foreach(Error::serverInformation() as $strKey => $strValue){
			$intLength = is_array($strValue) ? count($strValue) : (is_string($strValue) ? strlen($strValue) : null);
			$arrTags['server'] .= $this->resTemplate->build('components/table-row.tpl',array('key' => $strKey,'value' => $strValue,'type' => gettype($strValue),'length' => $intLength));
		}

		$arrTags['timeline'] = $arrTags['timeline_table'] = '';

		//\Twist::dump( $arrTimer );

		foreach($arrTimer['log'] as $strKey => $arrInfo){

			$arrTimelineTags = array(
				'time' => $arrInfo['time'],
				'time_pc' => ($arrInfo['time']/$arrTimer['total']) * 100,
				'time_formatted' => round($arrInfo['time']*1000,2).'ms',
				'title' => $arrInfo['title'],
				'memory_usage' => $arrInfo['memory'],
				'memory_usage_formatted' => \Twist::File()->bytesToSize($arrInfo['memory'])
			);

			$arrTags['timeline'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);
			$arrTags['timeline_table'] .= $this->resTemplate->build('components/timeline-table-entry.tpl',$arrTimelineTags);
		}

		$arrTimelineTags = array(
			'time' => $arrTimer['total'],
			'time_pc' => 100,
			'time_formatted' => ($arrTimer['total'] < 1) ? round($arrTimer['total']*1000,2).'ms' : round($arrTimer['total'],5).'s',
			'title' => 'Page loaded',
			'memory_usage' => $arrTimer['memory']['end'],
			'memory_usage_formatted' => \Twist::File()->bytesToSize($arrTimer['memory']['end'])
		);

		$arrTags['timeline'] .= $this->resTemplate->build('components/timeline-entry.tpl',$arrTimelineTags);
		$arrTags['timeline_table'] .= $this->resTemplate->build('components/timeline-table-entry.tpl',$arrTimelineTags);

		$arrTags['execution_time'] = $arrTimelineTags['time'];
		$arrTags['execution_time_formatted'] = ($arrTimer['total'] < 1) ? round($arrTimer['total']*1000).'ms' : round($arrTimer['total'],3).'s';

		return $this->resTemplate->build('_base.tpl',$arrTags);
	}

	public function catcher() {

		$resResource = new \Twist\Core\Models\Resources();
		$resResource -> viewResource( 'core-uri' );
		return sprintf('<script>%s</script>',file_get_contents(sprintf('%sCore/Resources/twist/debug/js/twistdebugcatcher.js',TWIST_FRAMEWORK)));
	}
}