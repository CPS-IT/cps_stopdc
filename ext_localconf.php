<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

	// Use hook in class.tslib_fe.php to compare current url with latest one
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['isOutputting']['cps_stopdc'] = 'EXT:cps_stopdc/class.tx_cpsstopdc.php:&tx_cpsstopdc->isOutputting';
?>