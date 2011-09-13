<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Nicole Cordes <cordes@cps-it.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_content.php');

class tx_cpsstopdc {

	/**
	*	Checks current url with those provided by typolink function (latest one)
	*
	*	@param	array					$params: Parameter given from caller
	*	@param	array					$pObj: Parent object (tslib_fe)
	*	@return	void
	*
	*/
	function checkDataSubmission(&$pObj) {

		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cps_stopdc']);

		// If any id was found so far
		if (intval($GLOBALS['TSFE']->id) > 0) {

			$local_cObj = t3lib_div::makeInstance('tslib_cObj');

			// Prepare array with query string information for typolink function
			// Strip id from array as it's an own parameter
			$queryArray = tx_cpsdevlib_div::queryStringToArray(t3lib_div::getIndpEnv('QUERY_STRING'), 'id');

			// Prepare urls for comparison
			$currentUrlArray = parse_url(t3lib_div::getIndpEnv('REQUEST_URI'));
			if ($currentUrlArray['path'][0] != '/') $currentUrlArray['path'] = '/' . $currentUrlArray['path'];
			$latestUrl = $local_cObj->getTypoLink_URL($GLOBALS['TSFE']->id, $queryArray);
			$latestUrlArray = parse_url($latestUrl);
			if ($latestUrlArray['path'][0] != '/') $latestUrlArray['path'] = '/' . $latestUrlArray['path'];

			// Check for site root
			if (($GLOBALS['TSFE']->page['is_siteroot']) AND (!count($queryArray))) {
				$latestUrlArray['path'] = '/';
				$latestUrl = '/';
			}

			// Compare arrays
			$resultArray = array_diff_assoc($currentUrlArray, $latestUrlArray);

			// Redirect if there are any differences
			if (count($resultArray)) {

				// Send header from extension configuration
				if ($this->extConf['header']) {
					header($this->extConf['header']);
				}

				// Redirect
				header('Location: ' . t3lib_div::locationHeaderUrl($latestUrl));
				exit;
			}
		}

		// Extend realurl expiration dates
		if ($this->extConf['useRealurl'] == 1) {
			if (t3lib_extMgm::isLoaded('realurl')) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_pathcache', 'expire <= ' . time() . ' AND expire > 0', array('expire' => 'expire + ' . ($this->extConf['extendExpiration'] * 24 * 60 * 60)), 'expire');
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_uniqalias', 'expire <= ' . time() . ' AND expire > 0', array('expire' => 'expire + ' . ($this->extConf['extendExpiration'] * 24 * 60 * 60)), 'expire');
			}
		}

		// Extend CoolURI expiration dates
		if ($this->extConf['useCoolUri']) {
			if (t3lib_extMgm::isLoaded('cooluri')) {
				$GLOBALS['TYPO3_DB']->debugOutput = 1;
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('link_oldlinks', 'DATEDIFF(NOW(), tstamp) >= 0', array('tstamp' => 'DATE_ADD(tstamp, INTERVAL ' . $this->extConf['extendExpiration'] . ' DAY)'), 'tstamp');
			}
		}
	}



	/**
	*	Add canonical url if not already included
	*
	*	@param	array					$params: Parameter given from caller
	*	@param	array					$pObj: Parent object (tslib_fe)
	*	@return	void
	*
	*/
	function contentPostProc_output(&$params, &$pObj) {

		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cps_stopdc']);

		// Check only if it's enabled in extension manager
		if ($this->extConf['canonicalUrl']) {
			// Get header suppart
			$headerStart = strpos($pObj->content, ($pObj->pSetup['headTag'] ? $pObj->pSetup['headTag'] : '<head>'));
			$headerEnd = strpos($pObj->content, '</head>') + 7;
			$headerData = substr($pObj->content, $headerStart, $headerEnd - $headerStart);

			// Only add canonical url when not already exists
			if (strpos($headerData, 'rel="canonical"') === false) {

				// Use local cObj as cached pages haven't any cObj
				$local_cObj = t3lib_div::makeInstance('tslib_cObj');

				if ($this->extConf['removeVarsInCanonicalUrl'] == 'all') {
					$queryArray = array();
				} else {
					$queryArray = tx_cpsdevlib_div::queryStringToArray(t3lib_div::getIndpEnv('QUERY_STRING'), $this->extConf['removeVarsInCanonicalUrl']);
				}

				// Store mount point in temp variable
				$tempMP = $pObj->MP;
				$pObj->MP = '';

				// Store linkVars in temp variable
				$tempLinkVars = $pObj->linkVars;
				$pObj->linkVars = '';

				// Get id related to content page (to support content_from_pid)
				$id = $pObj->contentPid;

				// Get url and link tag
				$url = $local_cObj->getTypoLink_URL($id, $queryArray);
				$canonical = '<link rel="canonical" href="' . (($pObj->config['config']['baseURL']) ? $pObj->config['config']['baseURL'] : '') . $url . '" ' . tx_cpsdevlib_extmgm::getEndingSlash() . '>';

				// Restore mount point and link vars
				$pObj->MP = $tempMP;
				$pObj->linkVars = $tempLinkVars;

				// Replace </head> tag with canonical url
				$pObj->content = str_replace('</head>', $canonical . LF . '</head>', $pObj->content);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cps_stopdc/class.tx_cpsstopdc.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cps_stopdc/class.tx_cpsstopdc.php']);
}
?>