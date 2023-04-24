<?php

namespace Blueways\BwGuild\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class BackendAjaxController
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly FileRepository $fileRepository
    ) {
    }

    public function previewAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $record = BackendUtility::getRecord($params['table'], $params['uid'], '*');

        /** @var BackendUserAuthentication $beUser */
        $beUser = $GLOBALS['BE_USER'];
        $access = $beUser->doesUserHaveAccess($record, 1);

        if (!$access) {
            return new JsonResponse(['success' => false], 403);
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths(['EXT:bw_guild/Resources/Private/Templates/Backend/']);
        $view->setTemplate('Preview');
        $view->assign('record', $record);

        if ($record['images'] ?? '') {
            $files = $this->fileRepository->findByRelation($params['table'], 'images', $params['uid']);
            $view->assign('files', $files);
        }

        $html = $view->render();

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($html);
        return $response;
    }
}
