<?php

	/**
	 * Twist Cache Management Cron, cleans the old cache data periodically
	 */
	echo "TwistProtect: Code Scanner";
	$blScanner = new Twist\Core\Models\Protect\Scanner();

	$arrSummary = $blScanner->scan(TWIST_PUBLIC_ROOT,true);

	print_r($arrSummary);