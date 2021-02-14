<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Felogin\Tests\Unit\Controller;

use Prophecy\Argument;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Felogin\Controller\FrontendLoginController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class FrontendLoginControllerTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @var SiteLanguage
     */
    protected $language;

    public function setUp(): void
    {
        $site = new Site('test', 1, [
            'identifier' => 'test',
            'rootPageId' => 1,
            'base' => '/',
            'languages' => [
                [
                    'languageId' => 0,
                    'locale' => 'en_US',
                    'typo3Language' => 'en',
                    'base' => '/',
                ],
            ]
        ]);
        $this->language = $site->getLanguageById(0);
        parent::setUp();
    }

    /*************************
     * Test concerning getPreserveGetVars
     *************************/

    /**
     * @return array
     */
    public function getPreserveGetVarsReturnsCorrectResultDataProvider()
    {
        return [
            'special get var id is not preserved' => [
                [
                    'id' => 42,
                ],
                '',
                [],
            ],
            'simple additional parameter is not preserved if not specified in preservedGETvars' => [
                [
                    'id' => 42,
                    'special' => 23,
                ],
                '',
                [],
            ],
            'all params except ignored ones are preserved if preservedGETvars is set to "all"' => [
                [
                    'id' => 42,
                    'special1' => 23,
                    'special2' => [
                        'foo' => 'bar',
                    ],
                    'tx_felogin_pi1' => [
                        'forgot' => 1,
                    ],
                ],
                'all',
                [
                    'special1' => 23,
                    'special2' => [
                        'foo' => 'bar',
                    ],
                ]
            ],
            'preserve single parameter' => [
                [
                    'L' => 42,
                ],
                'L',
                [
                    'L' => 42,
                ],
            ],
            'preserve whole parameter array' => [
                [
                    'L' => 3,
                    'tx_someext' => [
                        'foo' => 'simple',
                        'bar' => [
                            'baz' => 'simple',
                        ],
                    ],
                ],
                'L,tx_someext',
                [
                    'L' => 3,
                    'tx_someext' => [
                        'foo' => 'simple',
                        'bar' => [
                            'baz' => 'simple',
                        ],
                    ],
                ],
            ],
            'preserve part of sub array' => [
                [
                    'L' => 3,
                    'tx_someext' => [
                        'foo' => 'simple',
                        'bar' => [
                            'baz' => 'simple',
                        ],
                    ],
                ],
                'L,tx_someext[bar]',
                [
                    'L' => 3,
                    'tx_someext' => [
                        'bar' => [
                            'baz' => 'simple',
                        ],
                    ],
                ],
            ],
            'preserve keys on different levels' => [
                [
                    'L' => 3,
                    'no-preserve' => 'whatever',
                    'tx_ext2' => [
                        'foo' => 'simple',
                    ],
                    'tx_ext3' => [
                        'bar' => [
                            'baz' => 'simple',
                        ],
                        'go-away' => '',
                    ],
                ],
                'L,tx_ext2,tx_ext3[bar]',
                [
                    'L' => 3,
                    'tx_ext2' => [
                        'foo' => 'simple',
                    ],
                    'tx_ext3' => [
                        'bar' => [
                            'baz' => 'simple',
                        ],
                    ],
                ],
            ],
            'preserved value that does not exist in get' => [
                [],
                'L,foo%5Bbar%5D',
                [],
             ],
        ];
    }

    /**
     * @test
     * @dataProvider getPreserveGetVarsReturnsCorrectResultDataProvider
     * @param array $getArray
     * @param string $preserveVars
     * @param string $expected
     */
    public function getPreserveGetVarsReturnsCorrectResult(array $getArray, $preserveVars, $expected)
    {
        $_GET = $getArray;
        $subject = $this->getAccessibleMock(FrontendLoginController::class, ['dummy'], ['_',  $this->createMock(TypoScriptFrontendController::class)]);
        $subject->cObj = $this->createMock(ContentObjectRenderer::class);
        $subject->conf['preserveGETvars'] = $preserveVars;
        self::assertSame($expected, $subject->_call('getPreserveGetVars'));
    }

    public function processUserFieldsRespectsDefaultConfigurationForStdWrapDataProvider()
    {
        return [
            'Simple casing' => [
                [
                    'username' => 'Holy',
                    'lastname' => 'Wood',
                ],
                [
                    'username.' => [
                        'case' => 'upper'
                    ]
                ],
                [
                    '###FEUSER_USERNAME###' => 'HOLY',
                    '###FEUSER_LASTNAME###' => 'Wood',
                    '###USER###' => 'HOLY'
                ]
            ],
            'Default config applies' => [
                [
                    'username' => 'Holy',
                    'lastname' => 'O" Mally',
                ],
                [
                    'username.' => [
                        'case' => 'upper'
                    ]
                ],
                [
                    '###FEUSER_USERNAME###' => 'HOLY',
                    '###FEUSER_LASTNAME###' => 'O&quot; Mally',
                    '###USER###' => 'HOLY'
                ]
            ],
            'Specific config overrides default config' => [
                [
                    'username' => 'Holy',
                    'lastname' => 'O" Mally',
                ],
                [
                    'username.' => [
                        'case' => 'upper'
                    ],
                    'lastname.' => [
                        'htmlSpecialChars' => '0'
                    ]
                ],
                [
                    '###FEUSER_USERNAME###' => 'HOLY',
                    '###FEUSER_LASTNAME###' => 'O" Mally',
                    '###USER###' => 'HOLY'
                ]
            ],
            'No given user returns empty array' => [
                null,
                [
                    'username.' => [
                        'case' => 'upper'
                    ],
                    'lastname.' => [
                        'htmlSpecialChars' => '0'
                    ]
                ],
                []
            ],
        ];
    }

    /**
     * @test
     * @dataProvider processUserFieldsRespectsDefaultConfigurationForStdWrapDataProvider
     */
    public function processUserFieldsRespectsDefaultConfigurationForStdWrap($userRecord, $fieldConf, $expectedMarkers)
    {
        $tsfe = $this->prophesize(TypoScriptFrontendController::class);
        $tsfe->fe_user = new \stdClass();
        $tsfe->fe_user->user = $userRecord;
        $tsfe->getLanguage(Argument::any())->willReturn($this->language);
        $conf = ['userfields.' => $fieldConf];
        $subject = $this->getAccessibleMock(FrontendLoginController::class, ['dummy'], [null, $tsfe->reveal()]);
        $subject->cObj = new ContentObjectRenderer();
        $subject->_set('conf', $conf);
        self::assertEquals($expectedMarkers, $subject->_call('getUserFieldMarkers'));
    }

    /**
     * @test
     */
    public function processRedirectReferrerDomainsMatchesDomains()
    {
        $tsfe = $this->prophesize(TypoScriptFrontendController::class);
        $tsfe->getLanguage(Argument::any())->willReturn($this->language);
        $conf = [
            'redirectMode' => 'refererDomains',
            'domains' => 'example.com'
        ];
        $subject = $this->getAccessibleMock(FrontendLoginController::class, ['dummy'], [null, $tsfe->reveal()]);
        $subject->_set('conf', $conf);
        $subject->_set('logintype', LoginType::LOGIN);
        $subject->_set('referer', 'http://www.example.com/snafu');
        $subject->_set('userIsLoggedIn', true);
        self::assertSame(['http://www.example.com/snafu'], $subject->_call('processRedirect'));
    }
}
