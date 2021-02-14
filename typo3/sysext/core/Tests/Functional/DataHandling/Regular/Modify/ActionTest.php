<?php

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

namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\Regular\Modify;

use TYPO3\CMS\Core\Tests\Functional\DataHandling\Regular\AbstractActionTestCase;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\ResponseContent;

/**
 * Functional test for the DataHandler
 */
class ActionTest extends AbstractActionTestCase
{
    /**
     * @var string
     */
    protected $assertionDataSetDirectory = 'typo3/sysext/core/Tests/Functional/DataHandling/Regular/Modify/DataSet/';

    /**
     * @test
     */
    public function verifyCleanReferenceIndex()
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    /**
     * @test
     * See DataSet/createContentRecords.csv
     */
    public function createContents()
    {
        parent::createContents();
        $this->assertAssertionDataSet('createContents');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1', 'Testing #2'));
    }

    /**
     * @test
     * See DataSet/createContentForLanguageAll.csv
     */
    public function createContentForLanguageAll()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageIdSecond);
        parent::createContentForLanguageAll();

        $this->assertAssertionDataSet('createContentForLanguageAll');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Language set to all', '[Translate to Deutsch:] [Translate to Dansk:] Regular Element #1'));
    }

    /**
     * @test
     * See DataSet/modifyContentRecord.csv
     */
    public function modifyContent()
    {
        parent::modifyContent();
        $this->assertAssertionDataSet('modifyContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     * See DataSet/deleteContentRecord.csv
     */
    public function deleteContent()
    {
        parent::deleteContent();
        $this->assertAssertionDataSet('deleteContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
        self::assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/deleteLocalizedContentNDeleteContent.csv
     */
    public function deleteLocalizedContentAndDeleteContent()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);

        parent::deleteLocalizedContentAndDeleteContent();
        $this->assertAssertionDataSet('deleteLocalizedContentNDeleteContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #3', '[Translate to Dansk:] Regular Element #3', 'Regular Element #1'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/copyContentRecord.csv
     */
    public function copyContent()
    {
        parent::copyContent();
        $this->assertAssertionDataSet('copyContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2 (copy 1)'));
    }

    /**
     * @test
     * See DataSet/copyContentToLanguage.csv
     */
    public function copyContentToLanguage()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::copyContentToLanguage();
        $this->assertAssertionDataSet('copyContentToLanguage');

        // Set up "dk" to not have overlays
        $languageConfiguration = $this->siteLanguageConfiguration;
        $languageConfiguration[self::VALUE_LanguageId]['fallbackType'] = 'free';
        $this->setUpFrontendSite(1, $languageConfiguration);
        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #3', '[Translate to Dansk:] Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/copyContentToLanguageWSynchronization.csv
     */
    public function copyContentToLanguageWithLanguageSynchronization()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::copyContentToLanguageWithLanguageSynchronization();
        $this->assertAssertionDataSet('copyContentToLanguageWSynchronization');

        // Set up "dk" to not have overlays
        $languageConfiguration = $this->siteLanguageConfiguration;
        $languageConfiguration[self::VALUE_LanguageId]['fallbackType'] = 'free';
        $this->setUpFrontendSite(1, $languageConfiguration);
        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #3', '[Translate to Dansk:] Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/copyContentToLanguageWExclude.csv
     */
    public function copyContentToLanguageWithLocalizationExclude()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::copyContentToLanguageWithLocalizationExclude();
        $this->assertAssertionDataSet('copyContentToLanguageWExclude');

        // Set up "dk" to not have overlays
        $languageConfiguration = $this->siteLanguageConfiguration;
        $languageConfiguration[self::VALUE_LanguageId]['fallbackType'] = 'free';
        $this->setUpFrontendSite(1, $languageConfiguration);
        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #1', '[Translate to Dansk:] Regular Element #3', 'Regular Element #2 (copy 1)'));
    }

    /**
     * @test
     * See DataSet/copyContentToLanguageFromNonDefaultLanguage.csv
     */
    public function copyContentToLanguageFromNonDefaultLanguage()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageIdSecond);
        parent::copyContentToLanguageFromNonDefaultLanguage();
        $this->assertAssertionDataSet('copyContentToLanguageFromNonDefaultLanguage');

        // Set up "de" to not have overlays
        $languageConfiguration = $this->siteLanguageConfiguration;
        $languageConfiguration[self::VALUE_LanguageIdSecond]['fallbackType'] = 'free';
        $this->setUpFrontendSite(1, $languageConfiguration);
        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Deutsch:] [Translate to Dansk:] Regular Element #3'));
    }

    /**
     * @test
     * See DataSet/copyContentRecord.csv
     */
    public function copyPasteContent()
    {
        parent::copyPasteContent();
        $this->assertAssertionDataSet('copyPasteContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     * See DataSet/localizeContentRecord.csv
     */
    public function localizeContent()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::localizeContent();
        $this->assertAssertionDataSet('localizeContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #1', '[Translate to Dansk:] Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/localizeContentWithEmptyTcaIntegrityColumns.csv
     * @see \TYPO3\CMS\Core\Migrations\TcaMigration::sanitizeControlSectionIntegrity()
     */
    public function localizeContentWithEmptyTcaIntegrityColumns()
    {
        parent::localizeContentWithEmptyTcaIntegrityColumns();
        $this->assertAssertionDataSet('localizeContentWithEmptyTcaIntegrityColumns');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #1', '[Translate to Dansk:] Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/localizeContentWSynchronization.csv
     */
    public function localizeContentWithLanguageSynchronization()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::localizeContentWithLanguageSynchronization();
        $this->assertAssertionDataSet('localizeContentWSynchronization');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #1', 'Testing #1'));
    }

    /**
     * @test
     * See DataSet/localizeContentWSynchronizationHNull.csv
     */
    public function localizeContentWithLanguageSynchronizationHavingNullValue()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::localizeContentWithLanguageSynchronizationHavingNullValue();
        $this->assertAssertionDataSet('localizeContentWSynchronizationHNull');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Regular Element #1', 'Testing #1'));
    }

    /**
     * @test
     * See DataSet/localizeContentFromNonDefaultLanguage.csv
     */
    public function localizeContentFromNonDefaultLanguage()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageIdSecond);
        parent::localizeContentFromNonDefaultLanguage();

        $this->assertAssertionDataSet('localizeContentFromNonDefaultLanguage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Deutsch:] [Translate to Dansk:] Regular Element #1', '[Translate to Deutsch:] [Translate to Dansk:] Regular Element #3'));
    }

    /**
     * @test
     * See DataSet/localizeContentFromNonDefaultLanguageWSynchronizationDefault.csv
     */
    public function localizeContentFromNonDefaultLanguageWithLanguageSynchronizationDefault()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageIdSecond);
        parent::localizeContentFromNonDefaultLanguageWithLanguageSynchronizationDefault();

        $this->assertAssertionDataSet('localizeContentFromNonDefaultLanguageWSynchronizationDefault');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Deutsch:] [Translate to Dansk:] Regular Element #1', 'Testing #1'));
    }

    /**
     * @test
     * See DataSet/localizeContentFromNonDefaultLanguageWSynchronizationSource.csv
     */
    public function localizeContentFromNonDefaultLanguageWithLanguageSynchronizationSource()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageIdSecond);
        parent::localizeContentFromNonDefaultLanguageWithLanguageSynchronizationSource();

        $this->assertAssertionDataSet('localizeContentFromNonDefaultLanguageWSynchronizationSource');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Deutsch:] [Translate to Dansk:] Regular Element #1', 'Testing #1'));
    }

    /**
     * @test
     * See DataSet/createLocalizedContent.csv
     */
    public function createLocalizedContent()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::createLocalizedContent();

        $this->assertAssertionDataSet('createLocalizedContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Localized Testing'));
    }

    /**
     * @test
     * See DataSet/createLocalizedContentWSynchronization.csv
     */
    public function createLocalizedContentWithLanguageSynchronization()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::createLocalizedContentWithLanguageSynchronization();

        $this->assertAssertionDataSet('createLocalizedContentWSynchronization');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing'));
    }

    /**
     * @test
     * See DataSet/createLocalizedContentWExclude.csv
     */
    public function createLocalizedContentWithLocalizationExclude()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::createLocalizedContentWithLocalizationExclude();

        $this->assertAssertionDataSet('createLocalizedContentWExclude');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageIdSecond));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing', '[Translate to Dansk:] Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/changeContentSorting.csv
     */
    public function changeContentSorting()
    {
        parent::changeContentSorting();
        $this->assertAssertionDataSet('changeContentSorting');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/changeContentSortingAfterSelf.csv
     */
    public function changeContentSortingAfterSelf()
    {
        parent::changeContentSortingAfterSelf();
        $this->assertAssertionDataSet('changeContentSortingAfterSelf');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/moveContentRecordToDifferentPage.csv
     */
    public function moveContentToDifferentPage()
    {
        parent::moveContentToDifferentPage();
        $this->assertAssertionDataSet('moveContentToDifferentPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSectionsSource = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSectionsSource, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageIdTarget));
        $responseSectionsTarget = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSectionsTarget, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/movePasteContentToDifferentPage.csv
     */
    public function movePasteContentToDifferentPage()
    {
        parent::movePasteContentToDifferentPage();
        $this->assertAssertionDataSet('movePasteContentToDifferentPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSectionsSource = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSectionsSource, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1'));
        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageIdTarget));
        $responseSectionsTarget = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSectionsTarget, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     * See DataSet/moveContentRecordToDifferentPageAndChangeSorting.csv
     */
    public function moveContentToDifferentPageAndChangeSorting()
    {
        parent::moveContentToDifferentPageAndChangeSorting();
        $this->assertAssertionDataSet('moveContentToDifferentPageNChangeSorting');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageIdTarget));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }

    /**
     * Page records
     */

    /**
     * @test
     * See DataSet/createPageRecord.csv
     */
    public function createPage()
    {
        parent::createPage();
        $this->assertAssertionDataSet('createPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId($this->recordIds['newPageId']));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function createPageAndSubPageAndSubPageContent()
    {
        parent::createPageAndSubPageAndSubPageContent();
        $this->assertAssertionDataSet('createPageAndSubPageAndSubPageContent');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId($this->recordIds['newSubPageId']));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1 #1'));
    }

    /**
     * @test
     * See DataSet/createPageRecordWithSlugOverrideConfiguration.csv
     */
    public function createPageWithSlugOverrideConfiguration(): void
    {
        // set default configuration
        $GLOBALS['TCA']['pages']['columns']['slug']['config']['generatorOptions'] = [
            'fields' => [
                'title',
            ],
            'fieldSeparator' => '-',
            'prefixParentPageSlug' => true,
        ];
        // set override for doktype default
        $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPE_DEFAULT]['columnsOverrides'] = [
            'slug' => [
                'config' => [
                    'generatorOptions' => [
                        'fields' => [
                            'nav_title'
                        ],
                        'fieldSeparator' => '-',
                        'prefixParentPageSlug' => true,
                    ]
                ]
            ]
        ];
        parent::createPage();
        $this->assertAssertionDataSet('createPageWithSlugOverrideConfiguration');
    }

    /**
     * @test
     * See DataSet/createPageNContentWDefaults.csv
     */
    public function createPageAndContentWithTcaDefaults()
    {
        parent::createPageAndContentWithTcaDefaults();
        $this->assertAssertionDataSet('createPageNContentWDefaults');

        // first, assert that page cannot be opened without using backend user (since it's hidden)
        $response = $this->executeFrontendRequest(
            (new InternalRequest())
                ->withPageId($this->recordIds['newPageId'])
        );
        self::assertSame(404, $response->getStatusCode());

        // then, assert if preview is possible using a backend user
        $response = $this->executeFrontendRequest(
            (new InternalRequest())
                ->withPageId($this->recordIds['newPageId']),
            (new InternalRequestContext())
                ->withBackendUserId(self::VALUE_BackendUserId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())
            ->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     * See DataSet/modifyPage.csv
     */
    public function modifyPage()
    {
        parent::modifyPage();
        $this->assertAssertionDataSet('modifyPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1'));
    }

    /**
     * @test
     * See DataSet/deletePageRecord.csv
     */
    public function deletePage()
    {
        parent::deletePage();
        $this->assertAssertionDataSet('deletePage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        self::assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     * See DataSet/copyPage.csv
     */
    public function copyPage()
    {
        parent::copyPage();
        $this->assertAssertionDataSet('copyPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId($this->recordIds['newPageId']));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
    }

    /**
     * Copy page (id 90) containing content elements translated in "free mode".
     * Values in l10n_source field are remapped to ids of newly copied records
     * e.g. record 314 has l10n_source = 315 and record 313 has l10n_source = 314
     * also note that 314 is NOT a record in the default language
     *
     * @test
     * See DataSet/copyPageFreeMode.csv
     */
    public function copyPageFreeMode()
    {
        $this->importScenarioDataSet('LivePageFreeModeElements');
        parent::copyPageFreeMode();
        $this->assertAssertionDataSet('copyPageFreeMode');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId($this->recordIds['newPageId']));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Target'));
    }

    /**
     * @test
     * See DataSet/localizePageRecord.csv
     */
    public function localizePage()
    {
        parent::localizePage();
        $this->assertAssertionDataSet('localizePage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('[Translate to Dansk:] Relations'));
    }

    /**
     * @test
     * See DataSet/localizeNCopyPage.csv
     */
    public function localizeAndCopyPage()
    {
        parent::localizePage();
        parent::copyPage();
        $this->assertAssertionDataSet('localizeNCopyPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId($this->recordIds['newPageId'])->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('[Translate to Dansk:] Relations'));
    }

    /**
     * @test
     * See DataSet/localizePageWSynchronization.csv
     */
    public function localizePageWithLanguageSynchronization()
    {
        parent::localizePageWithLanguageSynchronization();
        $this->assertAssertionDataSet('localizePageWSynchronization');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1'));
    }

    /**
     * @test
     * See DataSet/localizeNCopyPageWSynchronization.csv
     */
    public function localizeAndCopyPageWithLanguageSynchronization()
    {
        parent::localizePageWithLanguageSynchronization();
        parent::copyPage();
        $this->assertAssertionDataSet('localizeNCopyPageWSynchronization');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function localizePageAndContentsAndDeletePageLocalization()
    {
        // Create localized page and localize content elements first
        parent::localizePageAndContentsAndDeletePageLocalization();
        $this->assertAssertionDataSet('localizePageAndContentsAndDeletePageLocalization');
    }

    /**
     * @test
     */
    public function localizeNestedPagesAndContents()
    {
        parent::localizeNestedPagesAndContents();
        $this->assertAssertionDataSet('localizeNestedPagesAndContents');
    }

    /**
     * @test
     * See DataSet/changePageSorting.csv
     */
    public function changePageSorting()
    {
        parent::changePageSorting();
        $this->assertAssertionDataSet('changePageSorting');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/changePageSortingAfterSelf.csv
     */
    public function changePageSortingAfterSelf()
    {
        parent::changePageSortingAfterSelf();
        $this->assertAssertionDataSet('changePageSortingAfterSelf');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     * See DataSet/movePageRecordToDifferentPage.csv
     */
    public function movePageToDifferentPage()
    {
        parent::movePageToDifferentPage();
        $this->assertAssertionDataSet('movePageToDifferentPage');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }

    /**
     * @test
     */
    public function movePageToDifferentPageTwice()
    {
        parent::movePageToDifferentPageTwice();
        $this->assertAssertionDataSet('movePageToDifferentPageTwice');
    }

    /**
     * @test
     */
    public function movePageLocalizedToDifferentPageTwice()
    {
        parent::movePageLocalizedToDifferentPageTwice();
        $this->assertAssertionDataSet('movePageLocalizedToDifferentPageTwice');
    }

    /**
     * @test
     * See DataSet/movePageRecordToDifferentPageAndChangeSorting.csv
     */
    public function movePageToDifferentPageAndChangeSorting()
    {
        parent::movePageToDifferentPageAndChangeSorting();
        $this->assertAssertionDataSet('movePageToDifferentPageNChangeSorting');

        $response = $this->executeFrontendRequest((new InternalRequest())->withPageId(self::VALUE_PageId));
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Relations'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #1', 'Regular Element #2'));
    }
}
