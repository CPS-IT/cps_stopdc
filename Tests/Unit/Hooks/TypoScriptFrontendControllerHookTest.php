<?php
namespace CPSIT\CpsStopdc\Tests\Unit\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Nicole Cordes <cordes@cps-it.de>
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

use CPSIT\CpsStopdc\Hooks\TypoScriptFrontendControllerHook;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class TypoScriptFrontendControllerHookTest extends UnitTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface|TypoScriptFrontendControllerHook
     */
    protected $subject;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->subject = $this->getAccessibleMock('CPSIT\\CpsStopdc\\Hooks\\TypoScriptFrontendControllerHook', array('dummy'));
    }

    /**
     * @return array
     */
    public function getCurrentUrlArrayReturnsArrayForRequestUriDataProvider()
    {
        return array(
            'url without query' => array(
                '/foo/bar',
                array(
                    'path' => '/foo/bar',
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
    public function getCurrentUrlArrayReturnsArrayForRequestUri($queryString, array $expectation)
    {
        $_SERVER['REQUEST_URI'] = $queryString;
        if (is_callable('TYPO3\\CMS\\Core\\Utility\\GeneralUtility::flushInternalRuntimeCaches')) {
            call_user_func('TYPO3\\CMS\\Core\\Utility\\GeneralUtility::flushInternalRuntimeCaches');
        }
        $this->assertEquals($expectation, $this->subject->_call('getCurrentUrlArray'));
    }

    /**
     * @return array
     */
    public function queryStringToArrayReturnsArrayForQueryStringDataProvider()
    {
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
                        'bar' => 1,
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
    public function queryStringToArrayReturnsArrayForQueryString($queryString, array $expectation)
    {
        $this->assertEquals($expectation, $this->subject->_call('queryStringToArray', $queryString));
    }

    /**
     * @return array
     */
    public function needsRedirectReturnsTrueDataProvider()
    {
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
    public function needsRedirectReturnsTrue(array $currentUrlArray, array $latestUrlArray)
    {
        $this->assertTrue($this->subject->_call('needsRedirect', $currentUrlArray, $latestUrlArray));
    }

    /**
     * @return array
     */
    public function needsRedirectReturnsFalseDataProvider()
    {
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
    public function needsRedirectReturnsFalse(array $currentUrlArray, array $latestUrlArray)
    {
        $this->assertFalse($this->subject->_call('needsRedirect', $currentUrlArray, $latestUrlArray));
    }
}
