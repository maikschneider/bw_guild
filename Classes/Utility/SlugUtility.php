<?php

namespace Blueways\BwGuild\Utility;

use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class SlugUtility
{

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * SlugUtility constructor.
     *
     * @param string $fieldName
     */
    public function __construct(string $fieldName = 'slug')
    {
        $this->fieldName = $fieldName;
    }

    public function getSlug(AbstractEntity $object): string
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // get table name
        $dataMapper = $objectManager->get(DataMapper::class);
        $tableName = $dataMapper->getDataMap(get_class($object))->getTableName();

        $fieldConfig = $GLOBALS['TCA'][$tableName]['columns'][$this->fieldName]['config'];

        /** @var SlugHelper $helper */
        $helper = GeneralUtility::makeInstance(
            SlugHelper::class,
            $tableName,
            $this->fieldName,
            $fieldConfig
        );

        // Convert extbase object to array
        $reflectionService = $objectManager->get(ReflectionService::class);
        $objectProperties = $reflectionService->getClassSchema($object)->getProperties();
        foreach ($objectProperties as $propertyName => $value) {
            $objectProperties[$propertyName] = $object->_getProperty($propertyName);
        }

        // generate unique slug for pid of record
        $value = $helper->generate($objectProperties, $object->getPid());
        $state = RecordStateFactory::forName($tableName)
            ->fromArray($objectProperties, $object->getPid(), $object->getUid() ?: 'NEW');
        return $helper
            ->buildSlugForUniqueInPid(
                $value,
                $state
            );
    }
}
