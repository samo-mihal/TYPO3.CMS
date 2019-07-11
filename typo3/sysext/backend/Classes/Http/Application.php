<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Backend\Http;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\AbstractApplication;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Entry point for the TYPO3 Backend (HTTP requests)
 */
class Application extends AbstractApplication
{
    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @param RequestHandlerInterface $requestHandler
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(RequestHandlerInterface $requestHandler, ConfigurationManager $configurationManager)
    {
        $this->requestHandler = $requestHandler;
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->checkIfEssentialConfigurationExists()) {
            return $this->installToolRedirect();
        }
        // Set up the initial context
        $this->initializeContext();
        return parent::handle($request);
    }

    /**
     * Check if LocalConfiguration.php and PackageStates.php exist
     *
     * @return bool TRUE when the essential configuration is available, otherwise FALSE
     */
    protected function checkIfEssentialConfigurationExists(): bool
    {
        return file_exists($this->configurationManager->getLocalConfigurationFileLocation())
            && file_exists(Environment::getLegacyConfigPath() . '/PackageStates.php');
    }

    /**
     * Create a PSR-7 Response that redirects to the install tool
     *
     * @return ResponseInterface
     */
    protected function installToolRedirect(): ResponseInterface
    {
        return new RedirectResponse('./install.php', 302);
    }

    /**
     * Initializes the Context used for accessing data and finding out the current state of the application
     * Will be moved to a DI-like concept once introduced, for now, this is a singleton
     */
    protected function initializeContext()
    {
        GeneralUtility::makeInstance(Context::class, [
            'date' => new DateTimeAspect(new \DateTimeImmutable('@' . $GLOBALS['EXEC_TIME'])),
            'visibility' => new VisibilityAspect(true, true)
        ]);
    }
}
