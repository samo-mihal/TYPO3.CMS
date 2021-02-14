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

namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\Regular;

use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Migrations\TcaMigration;
use TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Functional test for the DataHandler
 */
abstract class AbstractActionTestCase extends AbstractDataHandlerActionTestCase
{
    const VALUE_PageIdParent = 88;
    const VALUE_PageId = 89;
    const VALUE_PageIdTarget = 90;
    const VALUE_PageIdWebsite = 1;
    const VALUE_ContentIdParent = 296;
    const VALUE_ContentIdFirst = 297;
    const VALUE_ContentIdSecond = 298;
    const VALUE_ContentIdThird = 299;
    const VALUE_ContentIdThirdLocalized = 300;
    const VALUE_LanguageId = 1;
    const VALUE_LanguageIdSecond = 2;

    const TABLE_Page = 'pages';
    const TABLE_Content = 'tt_content';

    /**
     * @var string
     */
    protected $scenarioDataSetDirectory = 'typo3/sysext/core/Tests/Functional/DataHandling/Regular/DataSet/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->importScenarioDataSet('LiveDefaultPages');
        $this->importScenarioDataSet('LiveDefaultElements');

        $this->setUpFrontendSite(1, $this->siteLanguageConfiguration);
        $this->setUpFrontendRootPage(1, ['typo3/sysext/core/Tests/Functional/Fixtures/Frontend/JsonRenderer.typoscript']);
        $this->setWorkspaceId(0);
    }

    /**
     * Content records
     */

    /**
     * Create a content record
     */
    public function createContents()
    {
        // Creating record at the beginning of the page
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Content, self::VALUE_PageId, ['header' => 'Testing #1']);
        $this->recordIds['newContentIdFirst'] = $newTableIds[self::TABLE_Content][0];
        // Creating record at the end of the page (after last one)
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Content, -self::VALUE_ContentIdSecond, ['header' => 'Testing #2']);
        $this->recordIds['newContentIdLast'] = $newTableIds[self::TABLE_Content][0];
    }

    /**
     * Creation of a content element with language set to all
     *
     * See DataSet/createContentForLanguageAll.csv
     */
    public function createContentForLanguageAll()
    {
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Content, self::VALUE_PageId, ['header' => 'Language set to all', 'sys_language_uid' => -1]);
        $this->recordIds['newContentLanguageAll'] = $newTableIds[self::TABLE_Content][0];
    }

    public function modifyContent()
    {
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, ['header' => 'Testing #1']);
    }

    public function hideContent()
    {
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, ['hidden' => '1']);
    }

    public function deleteContent()
    {
        $this->actionService->deleteRecord(self::TABLE_Content, self::VALUE_ContentIdSecond);
    }

    public function deleteLocalizedContentAndDeleteContent()
    {
        $this->actionService->deleteRecord(self::TABLE_Content, self::VALUE_ContentIdThirdLocalized);
        $this->actionService->deleteRecord(self::TABLE_Content, self::VALUE_ContentIdThird);
    }

    public function copyContent()
    {
        $copiedTableIds = $this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_PageId);
        $this->recordIds['copiedContentId'] = $copiedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
    }

    public function copyContentToLanguage()
    {
        $copiedTableIds = $this->actionService->copyRecordToLanguage(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $copiedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
    }

    /**
     * See DataSet/copyContentToLanguageWSynchronization.csv
     */
    public function copyContentToLanguageWithLanguageSynchronization()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['behaviour']['allowLanguageSynchronization'] = true;
        $copiedTableIds = $this->actionService->copyRecordToLanguage(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $copiedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, ['header' => 'Testing #1']);
    }

    /**
     * See DataSet/copyContentToLanguageWExclude.csv
     */
    public function copyContentToLanguageWithLocalizationExclude()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['l10n_mode'] = 'exclude';
        $copiedTableIds = $this->actionService->copyRecordToLanguage(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $copiedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, ['header' => 'Testing #1']);
    }

    /**
     * Free mode "translation" of a record in non default language
     */
    public function copyContentToLanguageFromNonDefaultLanguage()
    {
        $copiedTableIds = $this->actionService->copyRecordToLanguage(self::TABLE_Content, self::VALUE_ContentIdThirdLocalized, self::VALUE_LanguageIdSecond);
        $this->recordIds['localizedContentId'] = $copiedTableIds[self::TABLE_Content][self::VALUE_ContentIdThirdLocalized];
    }

    /**
     * See DataSet/copyPasteContent.csv
     */
    public function copyPasteContent()
    {
        $this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_PageId, ['header' => 'Testing #1']);
    }

    public function localizeContent()
    {
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
    }

    public function localizeContentWithHideAtCopy()
    {
        $GLOBALS['TCA'][self::TABLE_Content]['ctrl']['hideAtCopy'] = true;
        self::localizeContent();
        $this->actionService->modifyRecord(self::TABLE_Content, $this->recordIds['localizedContentId'], ['hidden' => 0]);
    }

    /**
     * @see \TYPO3\CMS\Core\Migrations\TcaMigration::sanitizeControlSectionIntegrity()
     */
    public function localizeContentWithEmptyTcaIntegrityColumns()
    {
        $integrityFieldNames = [
            'origin' => $GLOBALS['TCA'][self::TABLE_Content]['ctrl']['origUid'] ?? null,
            'language' => $GLOBALS['TCA'][self::TABLE_Content]['ctrl']['languageField'] ?? null,
            'languageParent' => $GLOBALS['TCA'][self::TABLE_Content]['ctrl']['transOrigPointerField'] ?? null,
            'languageSource' => $GLOBALS['TCA'][self::TABLE_Content]['ctrl']['translationSource'] ?? null,
        ];
        // explicitly unset integrity columns in TCA
        foreach ($integrityFieldNames as $integrityFieldName) {
            unset($GLOBALS['TCA'][self::TABLE_Content]['columns'][$integrityFieldName]);
        }
        // After TCA changes, refindex is not ok anymore for imported rows. Update it before performing other actions.
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
        $referenceIndex->updateIndex(false);

        // explicitly call TcaMigration (which was executed already earlier in functional testing bootstrap)
        $GLOBALS['TCA'] = (new TcaMigration())->migrate($GLOBALS['TCA']);
        // create translated page first
        $this->actionService->copyRecordToLanguage(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        // perform actions to be tested
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
    }

    public function localizeContentWithLanguageSynchronization()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, ['header' => 'Testing #1']);
    }

    public function localizeContentWithLanguageSynchronizationHavingNullValue()
    {
        $GLOBALS['TCA']['tt_content']['columns']['bodytext']['config']['eval'] = 'null';
        $GLOBALS['TCA']['tt_content']['columns']['bodytext']['config']['behaviour']['allowLanguageSynchronization'] = true;
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, ['bodytext' => null]);
        self::localizeContentWithLanguageSynchronization();
    }

    public function localizeContentFromNonDefaultLanguage()
    {
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdThirdLocalized, self::VALUE_LanguageIdSecond);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdThirdLocalized];
    }

    public function localizeContentFromNonDefaultLanguageWithLanguageSynchronizationDefault()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdThirdLocalized, self::VALUE_LanguageIdSecond);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdThirdLocalized];
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdThird, ['header' => 'Testing #1']);
    }

    public function localizeContentFromNonDefaultLanguageWithLanguageSynchronizationSource()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdThirdLocalized, self::VALUE_LanguageIdSecond);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdThirdLocalized];
        $this->actionService->modifyRecord(self::TABLE_Content, $this->recordIds['localizedContentId'], ['l10n_state' => ['header' => 'source']]);
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdThird, ['header' => 'Testing #1']);
    }

    public function createLocalizedContent()
    {
        $newContentIdDefault = StringUtility::getUniqueId('NEW');
        $newContentIdLocalized = StringUtility::getUniqueId('NEW');
        $dataMap = [
            self::TABLE_Content => [
                $newContentIdDefault => ['pid' => self::VALUE_PageId, 'header' => 'Testing'],
                $newContentIdLocalized => ['pid' => self::VALUE_PageId, 'header' => 'Localized Testing', 'sys_language_uid' => self::VALUE_LanguageId, 'l18n_parent' => $newContentIdDefault, 'l10n_source' => $newContentIdDefault],
            ]
        ];
        $this->actionService->invoke($dataMap, []);
        $this->recordIds['newContentIdDefault'] = $this->actionService->getDataHandler()->substNEWwithIDs[$newContentIdDefault];
        $this->recordIds['newContentIdLocalized'] = $this->actionService->getDataHandler()->substNEWwithIDs[$newContentIdLocalized];
    }

    public function createLocalizedContentWithLanguageSynchronization()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['behaviour']['allowLanguageSynchronization'] = true;
        self::createLocalizedContent();
    }

    public function createLocalizedContentWithLocalizationExclude()
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['l10n_mode'] = 'exclude';
        self::createLocalizedContent();
    }

    public function changeContentSorting()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, -self::VALUE_ContentIdSecond);
    }

    public function changeContentSortingAfterSelf()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, -self::VALUE_ContentIdFirst);
    }

    public function moveContentToDifferentPage()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_PageIdTarget);
    }

    /**
     * See DataSet/movePasteContentToDifferentPage.csv
     */
    public function movePasteContentToDifferentPage()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_PageIdTarget, ['header' => 'Testing #1']);
    }

    public function moveContentToDifferentPageAndChangeSorting()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, -self::VALUE_ContentIdSecond);
    }

    public function moveContentToDifferentPageAndHide()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_PageIdTarget, ['hidden' => '1']);
    }

    /**
     * Page records
     */

    /**
     * Create a page
     */
    public function createPage()
    {
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Page, self::VALUE_PageId, ['title' => 'Testing #1', 'hidden' => 0, 'nav_title' => 'Nav Testing #1']);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][0];
    }

    public function createPageAndSubPageAndSubPageContent()
    {
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Page, self::VALUE_PageId, ['title' => 'Testing #1', 'hidden' => 0]);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][0];
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Page, $this->recordIds['newPageId'], ['title' => 'Testing #1 #1', 'hidden' => 0]);
        $this->recordIds['newSubPageId'] = $newTableIds[self::TABLE_Page][0];
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Content, $this->recordIds['newSubPageId'], ['header' => 'Testing #1 #1', 'hidden' => 0]);
        $this->recordIds['newSubPageContentId'] = $newTableIds[self::TABLE_Content][0];
    }

    /**
     * This test creates a page on pid=88 (unlike other tests) and moves the new draft page on that exact level,
     * in order to only modify the "sorting" and not the "pid" setting.
     */
    public function createPageAndChangePageSorting()
    {
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Page, 88, ['title' => 'Testing #1', 'hidden' => 0, 'nav_title' => 'Nav Testing #1']);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][0];
        $this->actionService->moveRecord(self::TABLE_Page, $this->recordIds['newPageId'], -self::VALUE_PageId);
    }

    /**
     * This change creates a page on pid=89 and moves the page one level up (= we check the pid value of both placeholder + versioned record).
     */
    public function createPageAndMoveCreatedPage()
    {
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Page, self::VALUE_PageId, ['title' => 'Testing #1', 'hidden' => 0, 'nav_title' => 'Nav Testing #1']);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][0];
        $this->actionService->moveRecord(self::TABLE_Page, $this->recordIds['newPageId'], -self::VALUE_PageId);
    }

    public function createPageAndContentWithTcaDefaults()
    {
        $newTableIds = $this->actionService->createNewRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Page => ['title' => 'Testing #1'],
                self::TABLE_Content => ['pid' => '__previousUid', 'header' => 'Testing #1']
            ]
        );
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][0];
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][0];
    }

    public function modifyPage()
    {
        $this->actionService->modifyRecord(self::TABLE_Page, self::VALUE_PageId, ['title' => 'Testing #1']);
    }

    public function deletePage()
    {
        $this->actionService->deleteRecord(self::TABLE_Page, self::VALUE_PageId);
    }

    public function copyPage()
    {
        $newTableIds = $this->actionService->copyRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->recordIds['newContentIdFirst'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdFirst];
        $this->recordIds['newContentIdLast'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
    }

    public function copyPageFreeMode()
    {
        $newTableIds = $this->actionService->copyRecord(self::TABLE_Page, self::VALUE_PageIdTarget, self::VALUE_PageIdTarget);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][self::VALUE_PageIdTarget];
    }

    public function localizePage()
    {
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
    }

    public function localizePageWithLanguageSynchronization()
    {
        unset($GLOBALS['TCA'][self::TABLE_Page]['columns']['title']['l10n_mode']);
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['title']['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->actionService->modifyRecord(self::TABLE_Page, self::VALUE_PageId, ['title' => 'Testing #1']);
    }

    public function localizePageAndContentsAndDeletePageLocalization()
    {
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
        // Deleting the localized page should also delete its localized records
        $this->actionService->deleteRecord(self::TABLE_Page, $this->recordIds['localizedPageId']);
    }

    public function localizeNestedPagesAndContents()
    {
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageIdParent, self::VALUE_LanguageId);
        $this->recordIds['localizedParentPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageIdParent];
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdParent, self::VALUE_LanguageId);
        $this->recordIds['localizedParentContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdParent];
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdSecond, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][self::VALUE_ContentIdSecond];
        // Have another localized content element on page 88 to verify it's translation is also properly discarded in workspaces
        $this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdParent, self::VALUE_PageIdParent);
    }

    public function changePageSorting()
    {
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, -self::VALUE_PageIdTarget);
    }

    public function changePageSortingAfterSelf()
    {
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, -self::VALUE_PageId);
    }

    public function movePageToDifferentPage()
    {
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
    }

    public function movePageToDifferentPageTwice()
    {
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdWebsite);
    }

    /**
     * Create page localization, then move default language page to different pages twice.
     * Verifies the page localization is moved together with the default language page.
     * In workspaces, the page localization will be a "new" overlay that is moved around.
     */
    public function movePageLocalizedToDifferentPageTwice()
    {
        // Localize page first. In workspaces, this localization is created within ws, creating a "new" t3ver_state=-1 record
        $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdWebsite);
    }

    /**
     * Create page localization in live, then move default language page in workspaces to different pages twice.
     * Verifies the page localization is moved together with the default language page.
     * This should create "move" a overlay for the localization.
     *
     * No ext:core implementation of this test since it is identical with
     * moveLocalizedPageToDifferentPageTwice() in non-workspace
     */
    public function movePageLocalizedInLiveToDifferentPageTwice()
    {
        $this->setWorkspaceId(0);
        $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->setWorkspaceId(static::VALUE_WorkspaceId);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdWebsite);
    }

    /**
     * Create page localization in live, then change the localization in workspace,
     * then move default language page in workspaces to different pages twice.
     * Verifies the page localization is moved together with the default language page, and that the
     * "changed" t3ver_state=0 record is turned into a move placeholder when default language page is moved.
     *
     * No ext:core implementation of this test since it is identical with
     * moveLocalizedPageToDifferentPageTwice() in non-workspace
     */
    public function movePageLocalizedInLiveWorkspaceChangedToDifferentPageTwice()
    {
        $this->setWorkspaceId(0);
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->setWorkspaceId(static::VALUE_WorkspaceId);
        $this->actionService->modifyRecord(self::TABLE_Page, $this->recordIds['localizedPageId'], ['title' => 'Testing #1']);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdWebsite);
    }

    /**
     * Create page localization in live, then delete the localization in workspace,
     * then move default language page in workspaces to different pages twice.
     * Verifies the page localization is moved together with the default language page.
     *
     * @todo The "deleted" t3ver_state=2 record is turned into a move placeholder so the "marked for delete" information is lost.
     *
     * No ext:core implementation of this test since it is identical with
     * moveLocalizedPageToDifferentPageTwice() in non-workspace
     */
    public function movePageLocalizedInLiveWorkspaceDeletedToDifferentPageTwice()
    {
        $this->setWorkspaceId(0);
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->setWorkspaceId(static::VALUE_WorkspaceId);
        $this->actionService->deleteRecord(self::TABLE_Page, $this->recordIds['localizedPageId']);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdWebsite);
    }

    public function movePageToDifferentPageAndChangeSorting()
    {
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageIdTarget, self::VALUE_PageIdWebsite);
        $this->actionService->moveRecord(self::TABLE_Page, self::VALUE_PageId, -self::VALUE_PageIdTarget);
    }
}
