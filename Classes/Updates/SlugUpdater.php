<?php

namespace Blueways\BwGuild\Updates;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class SlugUpdater implements UpgradeWizardInterface
{
    private const TABLES = ['tx_bwguild_domain_model_offer' => 'slug', 'fe_users' => 'slug'];

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'bwGuildSlugUpdater';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return 'Updates slug fields for fe_users and offers';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Fills empty "slug" field of EXT:bw_guild offer records and fe_users';
    }

    /**
     * @inheritDoc
     */
    public function executeUpdate(): bool
    {
        foreach (self::TABLES as $table => $slugField) {

            // abort if no updated for this table is needed
            $elements = $this->getElementsForTable($table);
            if (!$elements->rowCount()) {
                continue;
            }

            // init SlugHelper for this table
            $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$slugField]['config'];
            /** @var SlugHelper $slugHelper */
            $slugHelper = GeneralUtility::makeInstance(
                SlugHelper::class,
                $table,
                $slugField,
                $fieldConfig
            );

            // init QueryBuilder
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connection->createQueryBuilder();

            while ($record = $elements->fetch()) {

                // generate unique slug for record
                $value = $slugHelper->generate($record, $record['pid']);
                $state = RecordStateFactory::forName($table)
                    ->fromArray($record, $record['pid'], $record['uid']);
                $slug = $slugHelper->buildSlugForUniqueInPid($value, $state);

                // update slug field of record
                $queryBuilder->update($table)
                    ->where(
                        $queryBuilder->expr()->eq(
                            'uid',
                            $queryBuilder->createNamedParameter($record['uid'], \PDO::PARAM_INT)
                        )
                    )
                    ->set($slugField, $slug);
                $queryBuilder->execute();
            }
        }

        return true;
    }

    /**
     * Gets element with empty or NULL slug field
     *
     * @param string $table
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    private function getElementsForTable(String $table)
    {
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(
                        self::TABLES[$table],
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->isNull(self::TABLES[$table])
                )
            )
            ->execute();
    }

    /**
     * @inheritDoc
     */
    public function updateNecessary(): bool
    {
        foreach (self::TABLES as $table => $slugField) {
            $elements = $this->getElementsForTable($table);
            if ($elements->rowCount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getPrerequisites(): array
    {
        return [];
    }
}
