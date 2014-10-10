<?php

    $intRouteKey = 0;

    if(count($_SERVER['TWIST_ROUTE_PARTS']) == 0){
        $_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] = 'welcome';
    }

    $arrOptions = array(
		'licence' => array(
			'disabled' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] != 'licence') ? 'disabled' : '',
			'current' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] == 'licence') ? 'current' : '',
			'icon' => (in_array($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey],array('welcome','licence'))) ? 'fa-circle-o' : 'fa-check-circle-o'
		),
        'checks' => array(
            'disabled' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] != 'checks') ? 'disabled' : '',
            'current' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] == 'checks') ? 'current' : '',
            'icon' => (in_array($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey],array('welcome','licence','checks'))) ? 'fa-circle-o' : 'fa-check-circle-o'
        ),
        'database' => array(
            'disabled' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] != 'database') ? 'disabled' : '',
            'current' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] == 'database') ? 'current' : '',
            'icon' => (in_array($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey],array('welcome','licence','checks','database'))) ? 'fa-circle-o' : 'fa-check-circle-o'
        ),
        'settings' => array(
            'disabled' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] != 'settings') ? 'disabled' : '',
            'current' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] == 'settings') ? 'current' : '',
            'icon' => (in_array($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey],array('welcome','licence','checks','database','settings'))) ? 'fa-circle-o' : 'fa-check-circle-o'
        ),
        'user' => array(
            'disabled' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] != 'user') ? 'disabled' : '',
            'current' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] == 'user') ? 'current' : '',
            'icon' => (in_array($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey],array('welcome','licence','checks','database','settings','user'))) ? 'fa-circle-o' : 'fa-check-circle-o'
        ),
        'interfaces' => array(
            'disabled' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] != 'interfaces') ? 'disabled' : '',
            'current' => ($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey] == 'interfaces') ? 'current' : '',
            'icon' => (in_array($_SERVER['TWIST_ROUTE_PARTS'][$intRouteKey],array('welcome','licence','checks','database','settings','user','interfaces'))) ? 'fa-circle-o' : 'fa-check-circle-o'
        )
    );

    echo \Twist::Template()->build('components/tabs.tpl',$arrOptions);