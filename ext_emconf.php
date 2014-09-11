<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "cps_stopdc".
 *
 * Auto generated 11-09-2014 19:23
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Stop Duplicate Content',
	'description' => 'Forces TYPO3 to use the latest url for any page. This helps to avoid duplicate content.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Nicole Cordes',
	'author_email' => 'cordes@cps-it.de',
	'author_company' => 'CPS-IT GmbH (http://www.cps-it.de)',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.7-5.5.99',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:7:{s:9:"ChangeLog";s:4:"78d1";s:21:"ext_conf_template.txt";s:4:"a460";s:12:"ext_icon.gif";s:4:"3c44";s:17:"ext_localconf.php";s:4:"d6e8";s:13:"locallang.xml";s:4:"6902";s:52:"Tests/Unit/hooks/class.tx_cpsstopdc_tslib_feTest.php";s:4:"f8a1";s:37:"hooks/class.tx_cpsstopdc_tslib_fe.php";s:4:"330d";}',
	'suggests' => array(
	),
);

?>