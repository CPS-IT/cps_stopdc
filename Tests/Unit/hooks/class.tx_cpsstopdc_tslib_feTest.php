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

require_once(__DIR__ . '/../../../hooks/class.tx_cpsstopdc_tslib_fe.php');

class tx_cpsstopdcTest extends tx_phpunit_testcase {

	/**
	 * Enable backup of global and system variables
	 *
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;

	/**
	 * Exclude TYPO3_DB from backup/ restore of $GLOBALS
	 * because resource types cannot be handled during serializing
	 *
	 * @var array
	 */
	protected $backupGlobalsBlacklist = array('TYPO3_DB');

	/**
	 * @var Tx_Phpunit_Interface_AccessibleObject|tx_cpsstopdc
	 */
	protected $fixture = NULL;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->fixture = $this->getAccessibleMock('tx_cpsstopdc', array('dummy'));
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @return array
	 */
	public function getCurrentUrlArrayReturnsArrayForRequestUriDataProvider() {
		return array(
			'url without query' => array(
				'/foo/bar',
				array(
					'path' => '/foo/bar'
				),
			),
			'url with query' => array(
				'foo/bar?foo=bar',
				array(
					'path' => '/foo/bar',
					'query' => 'foo=bar',
				),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider getCurrentUrlArrayReturnsArrayForRequestUriDataProvider
	 * @param string $queryString
	 * @param array $expectation
	 */
	public function getCurrentUrlArrayReturnsArrayForRequestUri($queryString, $expectation) {
		$_SERVER['REQUEST_URI'] = $queryString;
		$this->assertEquals($expectation, $this->fixture->_call('getCurrentUrlArray'));
	}

	/**
	 * @return array
	 */
	public function queryStringToArrayReturnsArrayForQueryStringDataProvider() {
		return array(
			'empty query string' => array(
				'',
				array(),
			),
			'simple query string' => array(
				'foo=1&bar=2',
				array(
					'foo' => 1,
					'bar' => 2,
				),
			),
			'nested query string' => array(
				'foo[bar]=1',
				array(
					'foo' => array(
						'bar' => 1
					),
				),
			),
			'complex query string' => array(
				'foo_bar=test&tx_foo_pi1[bar]=value&no_cache=1',
				array(
					'foo_bar' => 'test',
					'tx_foo_pi1' => array(
						'bar' => 'value',
					),
					'no_cache' => 1,
				),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider queryStringToArrayReturnsArrayForQueryStringDataProvider
	 * @param string $queryString
	 * @param array $expectation
	 */
	public function queryStringToArrayReturnsArrayForQueryString($queryString, $expectation) {
		$this->assertEquals($expectation, $this->fixture->_call('queryStringToArray', $queryString));
	}

	/**
	 * @return array
	 */
	public function needsRedirectReturnsTrueDataProvider() {
		return array(
			'different paths' => array(
				array(
					'path' => '/foo/bar',
				),
				array(
					'path' => '/bar/foo',
				),
			),
			'same paths and different query string' => array(
				array(
					'path' => '/foo/bar',
					'query' => 'foo=bar',
				),
				array(
					'path' => '/foo/bar',
					'query' => 'bar=foo',
				),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider needsRedirectReturnsTrueDataProvider
	 * @param array $currentUrlArray
	 * @param array $latestUrlArray
	 */
	public function needsRedirectReturnsTrue($currentUrlArray, $latestUrlArray) {
		$this->assertTrue($this->fixture->_call('needsRedirect', $currentUrlArray, $latestUrlArray));
	}

	/**
	 * @return array
	 */
	public function needsRedirectReturnsFalseDataProvider() {
		return array(
			'same paths' => array(
				array(
					'path' => '/foo/bar',
				),
				array(
					'path' => '/foo/bar',
				),
			),
			'same paths and query string' => array(
				array(
					'path' => '/foo/bar',
					'query' => 'foo=bar',
				),
				array(
					'path' => '/foo/bar',
					'query' => 'foo=bar',
				),
			),
			'same path and mixed query string' => array(
				array(
					'path' => '/foo/bar',
					'query' => 'foo=test&bar=value',
				),
				array(
					'path' => '/foo/bar',
					'query' => 'bar=value&foo=test',
				),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider needsRedirectReturnsFalseDataProvider
	 * @param array $currentUrlArray
	 * @param array $latestUrlArray
	 */
	public function needsRedirectReturnsFalse($currentUrlArray, $latestUrlArray) {
		$this->assertFalse($this->fixture->_call('needsRedirect', $currentUrlArray, $latestUrlArray));
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/cps_stopdc/class.tx_cpsstopdc.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/cps_stopdc/class.tx_cpsstopdc.php']);
}
?>