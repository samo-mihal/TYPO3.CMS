<?php
declare(strict_types=1);
namespace TYPO3\CMS\Core\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

/**
 * Class LinkService
 * @package Digitalwerk\DwBoilerplate\Service
 */
class LinkService
{
    public static function getLink($parameter = '', $arguments = [])
    {
        $additionalParams = $arguments['additionalParams'];
        $useCacheHash = $arguments['useCacheHash'];
        $addQueryString = $arguments['addQueryString'];
        $addQueryStringMethod = $arguments['addQueryStringMethod'];
        $addQueryStringExclude = $arguments['addQueryStringExclude'];
        $absolute = $arguments['absolute'];

        $content = '';
        if ($parameter) {
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $content = $contentObject->typoLink_URL(
                [
                    'parameter' => self::createTypolinkParameterFromArguments($parameter, $additionalParams),
                    'useCacheHash' => $useCacheHash,
                    'addQueryString' => $addQueryString,
                    'addQueryString.' => [
                        'method' => $addQueryStringMethod,
                        'exclude' => $addQueryStringExclude
                    ],
                    'forceAbsoluteUrl' => $absolute
                ]
            );
        }

        return $parameter ? [
            'url' => $content,
            'isInternal' => strpos($parameter, 't3://') !== false || strpos($parameter, '#') !== false
        ] : null;
    }

    /**
     * Transforms ViewHelper arguments to typo3link.parameters.typoscript option as array.
     *
     * @param string $parameter Example: 19 _blank - "testtitle with whitespace" &X=y
     * @param string $additionalParameters
     *
     * @return string The final TypoLink string
     */
    protected static function createTypolinkParameterFromArguments($parameter, $additionalParameters = '')
    {
        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typolinkConfiguration = $typoLinkCodec->decode($parameter);

        // Combine additionalParams
        if ($additionalParameters) {
            $typolinkConfiguration['additionalParams'] .= $additionalParameters;
        }

        return $typoLinkCodec->encode($typolinkConfiguration);
    }
}
