<?php

	$arrTags = array();

	$objCodeScanner = new \Twist\Core\Models\Protect\Scanner();
	$arrTags['scanner'] = $objCodeScanner->getLastScan(TWIST_DOCUMENT_ROOT);
	$arrTags['pulse'] = \Twist\Core\Models\ScheduledTasks::pulseInfo();
	$arrTags['twistprotect-firewall'] = (\Twist::framework()->setting('TWISTPROTECT_FIREWALL') == '1') ? 'On' : 'Off';
	$arrTags['twistprotect-scanner'] = (\Twist::framework()->setting('TWISTPROTECT_SCANNER') == '1') ? 'On' : 'Off';

	$arrTags['score'] = 0;
	$arrTags['grade'] = 'F';

	$arrTags['score'] += (\Twist::framework()->setting('TWISTPROTECT_FIREWALL') == '1') ? 20 : 0;
	$arrTags['score'] += (\Twist::framework()->setting('TWISTPROTECT_SCANNER') == '1') ? 10 : 0;
	$arrTags['score'] += ($arrTags['pulse']['status'] == 'Active') ? 10 : 0;
	$arrTags['score'] += (true) ? 10 : 0;//scanner pass
	$arrTags['score'] += (true) ? 10 : 0;//Security Level
	$arrTags['score'] += (true) ? 20 : 0;//Core up-to-date
	$arrTags['score'] += (true) ? 10 : 0;//Packages up-to-date
	$arrTags['score'] += (true) ? 10 : 0;//Obscure Admin

	echo \Twist::View()->build('./security.tpl',$arrTags);