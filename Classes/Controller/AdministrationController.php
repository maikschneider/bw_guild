<?php

namespace Blueways\BwGuild\Controller;

use Blueways\BwGuild\Domain\Repository\UserRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use Blueways\BwGuild\Domain\Repository\OfferRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Blueways\BwGuild\Domain\Model\User;
use ReflectionClass;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AdministrationController extends ActionController
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var FrontendUserGroupRepository
     */
    protected $usergroupRepository;

    /**
     * @var OfferRepository
     */
    protected $offerRepository;

    public function indexAction(): ResponseInterface
    {
        $this->selectFirstView();

        $users = $this->userRepository->findAll();

        $this->view->assign('users', $users);
        return $this->htmlResponse();
    }

    public function injectOfferRepository(OfferRepository $offerRepository)
    {
        $this->offerRepository = $offerRepository;
    }

    public function injectUserRepository(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function injectUsergroupRepository(
        FrontendUserGroupRepository $usergroupRepository
    ) {
        $this->usergroupRepository = $usergroupRepository;
    }

    /**
     * @throws InvalidConfigurationTypeException
     * @throws StopActionException
     */
    protected function selectFirstView()
    {
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);
        $typoscript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $startView = $typoscript['module.']['tx_bwguild.']['settings.']['startView'];

        if ($startView && $startView !== 'index') {
            $this->forward($startView);
        }
    }

    public function offerAction(): ResponseInterface
    {
        $offers = $this->offerRepository->findAll();
        $offerGroups = $this->offerRepository->getGroupedOffers();

        $this->view->assign('offers', $offers);
        $this->view->assign('offerGroups', $offerGroups);
        return $this->htmlResponse();
    }

    /**
     * @throws RouteNotFoundException
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws InsufficientFolderWritePermissionsException
     * @throws NoSuchArgumentException
     */
    public function importerAction(): ResponseInterface
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $formAction = (string)$uriBuilder->buildUriFromRoute('tce_file');
        $formRedirect = (string)$uriBuilder->buildUriFromRoute('bwguild_csv_import');

        // get storage
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $storage = $resourceFactory->getDefaultStorage();

        // create upload folder
        if (!$storage->hasFolder($this->settings['importFolderPath'])) {
            $storage->createFolder($this->settings['importFolderPath']);
        }

        // get files in folder
        $folder = $storage->getFolder($this->settings['importFolderPath']);
        $formTarget = $storage->getUid() . ':' . $folder->getIdentifier();
        $files = $storage->getFilesInFolder($folder);

        // file was selected
        if ($this->request->hasArgument('file')) {
            $rows = $this->getCsvDataFromIdentifier($this->request->getArgument('file'));

            if ($rows) {
                $feFields = $this->getFeUserFields();
                $groups = $this->usergroupRepository->findAll();

                $this->view->assign('csvFields', $rows[0]);
                $this->view->assign('feFields', $feFields);
                $this->view->assign('fileIdentifier', $this->request->getArgument('file'));
                $this->view->assign('groups', $groups);

                $this->view->setTemplate('CsvImport');
            }
        }

        $this->view->assign('formAction', $formAction);
        $this->view->assign('formRedirect', $formRedirect);
        $this->view->assign('formTarget', $formTarget);
        $this->view->assign('files', $files);
        return $this->htmlResponse();
    }

    /**
     * @param $identifier
     * @return bool|mixed
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws InsufficientFolderWritePermissionsException
     */
    private function getCsvDataFromIdentifier(
        $identifier
    ) {
        $file = $this->getFileFromIdentifier($identifier);

        // check for csv extension
        if ($file->getExtension() !== 'csv') {
            return false;
        }

        return $this->getCsvDataFromCsvFile($file);
    }

    /**
     * @param string $identifier
     * @return bool|FileInterface
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws InsufficientFolderWritePermissionsException
     */
    private function getFileFromIdentifier(
        $identifier
    ) {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $storage = $resourceFactory->getDefaultStorage();

        // create upload folder
        if (!$storage->hasFolder($this->settings['importFolderPath'])) {
            $storage->createFolder($this->settings['importFolderPath']);
        }

        // check for file existence
        if (!$storage->hasFile($identifier)) {
            return false;
        }

        $file = $storage->getFile($identifier);

        // check for csv extension
        if ($file->getExtension() !== 'csv') {
            return false;
        }

        return $file;
    }

    /**
     * @param FileInterface $file
     * @return mixed
     */
    private function getCsvDataFromCsvFile(
        $file
    ) {
        $filePath = $file->getForLocalProcessing(false);

        // check if file can be read
        if (($handle = fopen($filePath, 'r')) === false) {
            return false;
        }

        $rows = [];
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $singleRow = [];
            for ($c = 0; $c < count($data); $c++) {
                $singleRow[$c] = iconv('ISO-8859-1', 'UTF-8', $data[$c]);
            }
            array_push($rows, $singleRow);
        }
        fclose($handle);

        if (!count($rows)) {
            return false;
        }

        return $rows;
    }

    /**
     * @return array|\ReflectionProperty[]
     */
    private function getFeUserFields()
    {
        $reflectionClass = $this->objectManager->get(ReflectionClass::class, User::class);

        $feFields = $reflectionClass->getProperties();
        $feFields = array_filter($feFields, function ($obj) {
            $excludeFields = [
                'pid',
                'lockToDomain',
                'image',
                'lastlogin',
                'uid',
                '_localizedUid',
                '_languageUid',
                '_versionedUid',
                'offers',
            ];
            return !in_array($obj->name, $excludeFields);
        });

        return $feFields;
    }

    public function csvAction(
        ServerRequest $request
    ) {
        return new HtmlResponse('Upload done');
    }

    /**
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws InsufficientFolderWritePermissionsException
     * @throws NoSuchArgumentException
     * @throws StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws InvalidPasswordHashException
     */
    public function csvImportAction()
    {
        if (!$this->request->hasArgument('file')) {
            $this->throwStatus(403, 'No CSV file selected');
        }

        $fileIdentifier = $this->request->getArgument('file');
        $rows = $this->getCsvDataFromIdentifier($fileIdentifier);

        if (!$rows) {
            $this->throwStatus(400, 'No data found in csv');
        }

        $formProtectionFactory = FormProtectionFactory::get();
        $formToken = $formProtectionFactory->generateToken('FE User action', 'import');
        $feFields = $this->getFeUserFields();
        $groups = $this->usergroupRepository->findAll();

        if ($this->request->hasArgument('csvMapping') && $this->request->hasArgument('fixValue')) {
            $users = $this->createUsersFromCsvDataMapping(
                $rows,
                (array)$this->request->getArgument('csvMapping'),
                (array)$this->request->getArgument('fixValue')
            );

            $this->addFlashMessage(
                'With the current mapping, ' . count($users) . ' users can be created from your CSV',
                count($users) . ' users found',
                AbstractMessage::INFO,
                false
            );

            $this->view->assign('users', $users);
            $this->view->assign('csvMapping', $this->request->getArgument('csvMapping'));
            $this->view->assign('fixValue', $this->request->getArgument('fixValue'));
        }

        if ($this->request->hasArgument('actionCreate')) {
            foreach ($users as $user) {
                $this->userRepository->add($user);
            }

            $this->addFlashMessage(
                count($users) . ' users have been created on this page',
                'Import success',
                AbstractMessage::OK,
                true
            );

            $this->redirect('importer', 'Administration', 'bw_guild');
        }

        $this->view->assign('groups', $groups);
        $this->view->assign('csvFields', $rows[0]);
        $this->view->assign('feFields', $feFields);
        $this->view->assign('fileIdentifier', $fileIdentifier);
        $this->view->assign('token', $formToken);
    }

    /**
     * @param array $rows
     * @param array $csvMappings
     * @param array $fixValues
     * @return array
     * @throws InvalidPasswordHashException
     */
    private function createUsersFromCsvDataMapping($rows, $csvMappings, $fixValues)
    {
        $users = [];

        // abort if no username or password mapping is given (or fixed password)
        if (
            $csvMappings['username'] == '-1'
            || ($csvMappings['password'] == '-1' && (!$fixValues['password'] || strlen($fixValues['password']) < 3))
        ) {
            return $users;
        }

        // remove csv header
        unset($rows[0]);

        // get list of usernames
        $usernames = $this->userRepository->getUsernames();
        if ($usernames && count($usernames)) {
            $usernames = array_map(function ($arr) {
                return $arr['username'];
            }, $usernames);
        }

        $hashInstance = $this->objectManager->get(PasswordHashFactory::class)->getDefaultHashInstance('FE');

        foreach ($rows as $key => $row) {
            // username is always required abort if not given
            $username = trim($row[$csvMappings['username']]);
            if (!$username || strlen($username) < 2 || in_array(strtolower($username), $usernames)) {
                continue;
            }

            // get password from mapping and override if given
            // @TODO: here a "0" is prepended due to csv removal of leading zero - need to delete!!
            $password = '0' . trim($row[$csvMappings['password']]);
            if ($fixValues['password'] && strlen(trim($fixValues['password'])) > 2) {
                $password = trim($fixValues['password']);
            }

            // check for required valid password (+3 characters)
            if (!$password || strlen($password) < 3) {
                continue;
            }

            // hash password
            $password = $hashInstance->getHashedPassword($password);

            // create users
            $user = new User($username, $password);

            // usergroup get uid
            $groupUid = (int)$csvMappings['usergroup'] > 0 ? (int)trim($row[$csvMappings['usergroup']]) : false;
            if ($fixValues['usergroup'] && (int)trim($fixValues['usergroup'])) {
                $groupUid = (int)trim($fixValues['usergroup']);
            }

            // get usergroup and add to user
            if ($groupUid) {
                $group = $this->usergroupRepository->findByUid($groupUid);
                if ($group) {
                    $groupStorage = new ObjectStorage();
                    $groupStorage->attach($group);
                    $user->setUsergroup($groupStorage);
                }
            }

            // set all properties from mapping (except username, password, -1, values outside from $row)
            foreach ($csvMappings as $propertyName => $mapping) {
                if ($propertyName == 'username' || $propertyName == 'password' || $propertyName == 'usergroup' || $mapping == '-1' || !(int)$mapping || !array_key_exists(
                    (int)$mapping,
                    $row
                )) {
                    continue;
                }

                $user->_setProperty($propertyName, $row[$mapping]);
            }

            // set all overrides from fixed values
            foreach ($fixValues as $propertyName => $value) {
                if ($propertyName == 'username' || $propertyName == 'password' || $propertyName == 'usergroup' || !$value || !strlen(trim($value))) {
                    continue;
                }

                $user->_setProperty($propertyName, trim($value));
            }

            $usernames[] = strtolower($user->getUsername());
            $users[] = $user;
        }

        return $users;
    }

    public function passwordRefreshAction(): ResponseInterface
    {
        $updated = 0;
        $users = $this->userRepository->findAll();

        if ($this->request->hasArgument('refresh')) {
            $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');

            /** @var User $user */
            foreach ($users as $user) {
                $password = $user->getZip();

                if (!$password) {
                    continue;
                }
                $hashedPassword = $hashInstance->getHashedPassword($password);

                if ($hashedPassword) {
                    $user->setPassword($hashedPassword);
                    $this->userRepository->update($user);
                    $updated++;
                }
            }
        }

        $this->view->assign('users', $users);
        $this->view->assign('updated', $updated);
        return $this->htmlResponse();
    }
}
