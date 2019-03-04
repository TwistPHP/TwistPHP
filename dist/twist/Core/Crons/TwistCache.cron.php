<?php

	/**
	 * Twist Cache Management Cron, cleans the old cache data periodically
	 */
	echo "Cleaning Twist Cache";
	\Twist::Cache()->clean();