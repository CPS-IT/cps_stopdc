<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Use hook in class.tslib_fe.php to compare current url with latest one
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkDataSubmission']['cps_stopdc'] = 'CPSIT\\CpsStopdc\\Hooks\\TypoScriptFrontendControllerHook';

// Add canonical url to content
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['cps_stopdc'] = 'CPSIT\\CpsStopdc\\Hooks\\TypoScriptFrontendControllerHook->contentPostProc_output';
