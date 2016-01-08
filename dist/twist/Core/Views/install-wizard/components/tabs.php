<?php

    $intRouteKey = 0;

    $arrRoute = \Twist::Route()->current();

	if(count($arrRoute['parts']) == 0){
		$arrRoute['parts'][$intRouteKey] = 'welcome';
	}

	$arrOptions = array(
		'licence' => array(
			'disabled' => ($arrRoute['parts'][$intRouteKey] != 'licence') ? 'disabled' : '',
			'current' => ($arrRoute['parts'][$intRouteKey] == 'licence') ? 'current' : '',
			'icon' => (in_array($arrRoute['parts'][$intRouteKey],array('welcome','licence'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		),
		'checks' => array(
			'disabled' => ($arrRoute['parts'][$intRouteKey] != 'checks') ? 'disabled' : '',
			'current' => ($arrRoute['parts'][$intRouteKey] == 'checks') ? 'current' : '',
			'icon' => (in_array($arrRoute['parts'][$intRouteKey],array('welcome','licence','checks'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		),
		'database' => array(
			'disabled' => ($arrRoute['parts'][$intRouteKey] != 'database') ? 'disabled' : '',
			'current' => ($arrRoute['parts'][$intRouteKey] == 'database') ? 'current' : '',
			'icon' => (in_array($arrRoute['parts'][$intRouteKey],array('welcome','licence','checks','database'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		),
		'settings' => array(
			'disabled' => ($arrRoute['parts'][$intRouteKey] != 'settings') ? 'disabled' : '',
			'current' => ($arrRoute['parts'][$intRouteKey] == 'settings') ? 'current' : '',
			'icon' => (in_array($arrRoute['parts'][$intRouteKey],array('welcome','licence','checks','database','settings'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		),
		'user' => array(
			'disabled' => ($arrRoute['parts'][$intRouteKey] != 'user') ? 'disabled' : '',
			'current' => ($arrRoute['parts'][$intRouteKey] == 'user') ? 'current' : '',
			'icon' => (in_array($arrRoute['parts'][$intRouteKey],array('welcome','licence','checks','database','settings','user'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		),
		'packages' => array(
			'disabled' => ($arrRoute['parts'][$intRouteKey] != 'packages') ? 'disabled' : '',
			'current' => ($arrRoute['parts'][$intRouteKey] == 'packages') ? 'current' : '',
			'icon' => (in_array($arrRoute['parts'][$intRouteKey],array('welcome','licence','checks','database','settings','user','packages'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		)
	);

	echo \Twist::View()->build('components/tabs.tpl',$arrOptions);