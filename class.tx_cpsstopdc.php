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

class tx_cpsstopdc {

	/**
	*	Checks current url with those provided by typolink function (latest one)
	*
	*	@param	string				$theTable: Database table
	*	@param	string				$parentField: Database field to check with third parameter
	*	@param	mixed					$uids: Uids of items
	*	@return	string				An rootline array
	*
	*/
	function isOutputting($params, &$pObj) {

		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cps_stopdc']);

		// If any id was found so far
		if (intval($GLOBALS['TSFE']->id) > 0) {

			$this->cObj = t3lib_div::makeInstance('tslib_cObj');

			// Prepare array with query string information for typolink function
			// Strip id from array as it's an own parameter
			$queryArray = tx_cpsdevlib_div::queryStringToArray(t3lib_div::getIndpEnv('QUERY_STRING'));
			if (isset($queryArray['id'])) {
				unset($queryArray['id']);
			}

			// Prepare urls for comparison
			$currentUrlArray = parse_url(t3lib_div::getIndpEnv('REQUEST_URI'));
			if ($currentUrlArray['path'][0] != '/') $currentUrlArray['path'] = '/' . $currentUrlArray['path'];
			$latestUrl = $this->cObj->getTypoLink_URL($GLOBALS['TSFE']->id, $queryArray);
			$latestUrlArray = parse_url($latestUrl);
			if ($latestUrlArray['path'][0] != '/') $latestUrlArray['path'] = '/' . $latestUrlArray['path'];

			if ($currentUrlArray['path'] != $latestUrlArray['path']) {

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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cps_stopdc/class.tx_cpsstopdc.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cps_stopdc/class.tx_cpsstopdc.php']);
}
?>