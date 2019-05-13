<?php

	$arrTags = array();

	if(array_key_exists('development-mode',$_GET)){
		\Twist::framework()->setting('DEVELOPMENT_MODE',($_GET['development-mode'] === '1') ? '1' : '0');
	}elseif(array_key_exists('maintenance-mode',$_GET)){
		\Twist::framework()->setting('MAINTENANCE_MODE',($_GET['maintenance-mode'] === '1') ? '1' : '0');
	}elseif(array_key_exists('debug-bar',$_GET)){
		\Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR',($_GET['debug-bar'] === '1') ? '1' : '0');
	}elseif(array_key_exists('data-caching',$_GET)){
		\Twist::framework()->setting('CACHE_ENABLED',($_GET['data-caching'] === '1') ? '1' : '0');
	}elseif(array_key_exists('twistprotect-firewall',$_GET)){
		\Twist::framework()->setting('TWISTPROTECT_FIREWALL',($_GET['twistprotect-firewall'] === '1') ? '1' : '0');
	}elseif(array_key_exists('twistprotect-scanner',$_GET)){
		\Twist::framework()->setting('TWISTPROTECT_SCANNER',($_GET['twistprotect-scanner'] === '1') ? '1' : '0');
	}

	function setting_to_bytes($setting){
		static $short = array('k' => 0x400,
			'm' => 0x100000,
			'g' => 0x40000000);

		$setting = (string)$setting;
		if (!($len = strlen($setting))) return NULL;
		$last    = strtolower($setting[$len - 1]);
		$numeric = (int) $setting;
		$numeric *= isset($short[$last]) ? $short[$last] : 1;
		return $numeric;
	}

	$arrTags['development-mode'] = (\Twist::framework()->setting('DEVELOPMENT_MODE') == '1') ? 'On' : 'Off';
	$arrTags['maintenance-mode'] = (\Twist::framework()->setting('MAINTENANCE_MODE') == '1') ? 'On' : 'Off';
	$arrTags['debug-bar'] = (\Twist::framework()->setting('DEVELOPMENT_DEBUG_BAR') == '1') ? 'On' : 'Off';
	$arrTags['version'] = \Twist::version();
	$arrTags['server'] = $_SERVER["SERVER_SOFTWARE"];
	$arrTags['php_version'] = phpversion();
	$arrTags['php_memory'] = setting_to_bytes(ini_get('memory_limit'));
	$arrTags['php_upload_max'] = setting_to_bytes(ini_get('upload_max_filesize'));
	$arrTags['php_max_execution'] = ini_get('max_execution_time');

	echo \Twist::View()->build('./system.tpl',$arrTags);