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

namespace TYPO3\CMS\Workspaces\Tests\Functional\DataHandling\IRRE\ForeignField\Modify;

use TYPO3\CMS\Workspaces\Tests\Functional\DataHandling\IRRE\ForeignField\AbstractActionTestCase;
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
    protected $assertionDataSetDirectory = 'typo3/sysext/workspaces/Tests/Functional/DataHandling/IRRE/ForeignField/Modify/DataSet/';

    /**
     * @var bool False as temporary hack
     */
    protected $assertCleanReferenceIndex = false;

    /**
     * @test
     */
    public function createParentContent()
    {
        parent::createParentContent();
        $this->assertAssertionDataSet('createParentContent');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function modifyParentContent()
    {
        parent::modifyParentContent();
        $this->assertAssertionDataSet('modifyParentContent');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * @test
     */
    public function deleteParentContent()
    {
        parent::deleteParentContent();
        $this->assertAssertionDataSet('deleteParentContent');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2'));
    }

    /**
     * @test
     */
    public function copyParentContent()
    {
        parent::copyParentContent();
        $this->assertAssertionDataSet('copyParentContent');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * @test
     */
    public function copyParentContentToDifferentPage()
    {
        parent::copyParentContentToDifferentPage();
        $this->assertAssertionDataSet('copyParentContentToDifferentPage');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageIdTarget),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * @test
     */
    public function localizeParentContentWithAllChildren()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::localizeParentContentWithAllChildren();
        $this->assertAssertionDataSet('localizeParentContentWAllChildren');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('[Translate to Dansk:] Hotel #1'));
    }

    /**
     * @test
     */
    public function changeParentContentSorting()
    {
        parent::changeParentContentSorting();
        $this->assertAssertionDataSet('changeParentContentSorting');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * @test
     */
    public function moveParentContentToDifferentPage()
    {
        parent::moveParentContentToDifferentPage();
        $this->assertAssertionDataSet('moveParentContentToDifferentPage');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageIdTarget),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * @test
     */
    public function moveParentContentToDifferentPageTwice()
    {
        parent::moveParentContentToDifferentPageTwice();
        $this->assertAssertionDataSet('moveParentContentToDifferentPageTwice');
    }

    /**
     * @test
     */
    public function moveParentContentToDifferentPageAndChangeSorting()
    {
        parent::moveParentContentToDifferentPageAndChangeSorting();
        $this->assertAssertionDataSet('moveParentContentToDifferentPageNChangeSorting');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageIdTarget),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Regular Element #2', 'Regular Element #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * Page records
     */

    /**
     * @test
     */
    public function modifyPage()
    {
        parent::modifyPage();
        $this->assertAssertionDataSet('modifyPage');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Page)->setField('title')->setValues('Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2'));
    }

    /**
     * @test
     */
    public function deletePage()
    {
        parent::deletePage();
        $this->assertAssertionDataSet('deletePage');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        self::assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function copyPage()
    {
        parent::copyPage();
        $this->assertAssertionDataSet('copyPage');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId($this->recordIds['newPageId']),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2', 'Hotel #1'));
    }

    /**
     * @test
     */
    public function copyPageWithHotelBeforeParentContent()
    {
        parent::copyPageWithHotelBeforeParentContent();
        $this->assertAssertionDataSet('copyPageWHotelBeforeParentContent');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId($this->recordIds['newPageId']),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2', 'Hotel #1'));
    }

    /**
     * IRRE Child Records
     */

    /**
     * @test
     * Publish, PublishAll and Discard currently not implemented - they make little sense
     */
    public function changeHotelSortingWithOfferNotWorkspaceAware()
    {
        parent::changeHotelSortingWithOfferNotWorkspaceAware();
        $this->assertAssertionDataSet('changeHotelSortingWithOfferNotWorkspaceAware');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Hotel . ':' . self::VALUE_HotelIdFirst)->setRecordField(self::FIELD_HotelOffer)
            ->setTable(self::TABLE_Offer)->setField('title')->setValues('Offer #1.1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Hotel . ':' . self::VALUE_HotelIdFirst)->setRecordField(self::FIELD_HotelOffer)
            ->setTable(self::TABLE_Offer)->setField('title')->setValues('Offer #1.2'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Hotel . ':' . self::VALUE_HotelIdSecond)->setRecordField(self::FIELD_HotelOffer)
            ->setTable(self::TABLE_Offer)->setField('title')->setValues('Offer #2.1'));
    }

    /**
     * @test
     */
    public function createParentContentWithHotelAndOfferChildren()
    {
        parent::createParentContentWithHotelAndOfferChildren();
        $this->assertAssertionDataSet('createParentContentNHotelNOfferChildren');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
    }

    /**
     * @test
     */
    public function createAndCopyParentContentWithHotelAndOfferChildren()
    {
        parent::createAndCopyParentContentWithHotelAndOfferChildren();
        $this->assertAssertionDataSet('createNCopyParentContentNHotelNOfferChildren');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1 (copy 1)'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['copiedContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Hotel . ':' . $this->recordIds['copiedHotelId'])->setRecordField(self::FIELD_HotelOffer)
            ->setTable(self::TABLE_Offer)->setField('title')->setValues('Offer #1'));
    }

    /**
     * @test
     */
    public function createAndLocalizeParentContentWithHotelAndOfferChildren()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::createAndLocalizeParentContentWithHotelAndOfferChildren();
        $this->assertAssertionDataSet('createNLocalizeParentContentNHotelNOfferChildren');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('[Translate to Dansk:] Hotel #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Hotel . ':' . $this->recordIds['newHotelId'])->setRecordField(self::FIELD_HotelOffer)
            ->setTable(self::TABLE_Offer)->setField('title')->setValues('[Translate to Dansk:] Offer #1'));
    }

    /**
     * @test
     * Test not implemented for Publish, PublishAll and Discard
     */
    public function createAndLocalizeParentContentWithHotelAndOfferChildrenWithoutSortByConfiguration()
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        parent::createAndLocalizeParentContentWithHotelAndOfferChildrenWithoutSortByConfiguration();
        $this->assertAssertionDataSet('createNLocalizeParentContentNHotelNOfferChildrenWOSortBy');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('[Translate to Dansk:] Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('[Translate to Dansk:] Hotel #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Hotel . ':' . $this->recordIds['newHotelId'])->setRecordField(self::FIELD_HotelOffer)
            ->setTable(self::TABLE_Offer)->setField('title')->setValues('[Translate to Dansk:] Offer #1'));
    }

    /**
     * @test
     */
    public function modifyOnlyHotelChild()
    {
        parent::modifyOnlyHotelChild();
        $this->assertAssertionDataSet('modifyOnlyHotelChild');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Testing #1'));
    }

    /**
     * @test
     */
    public function modifyParentAndChangeHotelChildrenSorting()
    {
        parent::modifyParentAndChangeHotelChildrenSorting();
        $this->assertAssertionDataSet('modifyParentNChangeHotelChildrenSorting');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #2', 'Hotel #1'));
    }

    /**
     * @test
     */
    public function modifyParentWithHotelChild()
    {
        parent::modifyParentWithHotelChild();
        $this->assertAssertionDataSet('modifyParentNHotelChild');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Testing #1'));
    }

    /**
     * @test
     */
    public function modifyParentAndAddHotelChild()
    {
        parent::modifyParentAndAddHotelChild();
        $this->assertAssertionDataSet('modifyParentNAddHotelChild');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1', 'Hotel #2'));
    }

    /**
     * @test
     */
    public function modifyParentAndDeleteHotelChild()
    {
        parent::modifyParentAndDeleteHotelChild();
        $this->assertAssertionDataSet('modifyParentNDeleteHotelChild');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionStructureHasRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #1'));
        self::assertThat($responseSections, $this->getRequestSectionStructureDoesNotHaveRecordConstraint()
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentHotel)
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Hotel #2'));
    }

    /**
     * @test
     */
    public function modifyAndDiscardAndModifyParentWithHotelChild()
    {
        parent::modifyAndDiscardAndModifyParentWithHotelChild();
        $this->assertAssertionDataSet('modifyNDiscardNModifyParentWHotelChild');

        $response = $this->executeFrontendRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections('Default', 'Extbase:list()');
        self::assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionDoesNotHaveRecordConstraint()
            ->setTable(self::TABLE_Hotel)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #2'));
        self::assertThat($responseSections, $this->getRequestSectionHasRecordConstraint()
            ->setTable(self::TABLE_Hotel)->setField('title')->setValues('Testing #2'));
    }
}
