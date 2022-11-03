<?php

namespace Blueways\BwGuild\Domain\Repository;

use Blueways\BwGuild\Domain\Model\Dto\UserDemand;
use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserRepository
 */
class UserRepository extends AbstractDemandRepository
{
    public function setConstraints($demand): void
    {
        parent::setConstraints($demand);

        /** @var UserDemand $demand */
        $this->setPublicProfileConstraint();
        $this->setFeatureConstraint($demand);
        $this->setFeGroupConstraint($demand);
    }

    protected function setLanguageConstraint(): void
    {
        // override since fe_users do not have sys_language_uid set
    }

    protected function setFeatureConstraint(UserDemand $demand): void
    {
        if (!$demand->feature) {
            return;
        }

        $this->queryBuilder->join(
            $demand::TABLE,
            'tx_bwguild_feature_feuser_mm',
            'mm',
            $this->queryBuilder->expr()->eq(
                'mm.uid_foreign',
                $this->queryBuilder->quoteIdentifier($demand::TABLE . '.uid')
            )
        );
        $this->queryBuilder->join(
            'mm',
            'tx_bwguild_domain_model_feature',
            'f',
            $this->queryBuilder->expr()->eq('f.uid', $this->queryBuilder->quoteIdentifier('mm.uid_local'))
        );

        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->like(
                'f.name',
                $this->queryBuilder->createNamedParameter('%' . $demand->feature . '%', \PDO::PARAM_STR)
            )
        );
    }

    protected function setFeGroupConstraint(UserDemand $demand): void
    {
        if (!$demand->feGroup) {
            return;
        }

        $searchSplittedParts = GeneralUtility::trimExplode(' ', $demand->feGroup, true);
        $searchFields = $demand->getFeGroupSearchFields();
        $constraints = [];

        foreach ($searchSplittedParts as $searchSplittedPart) {
            foreach ($searchFields as $cleanProperty) {
                $searchSplittedPart = trim($searchSplittedPart);
                if ($searchSplittedPart) {
                    $constraints[] = $this->queryBuilder->expr()->like(
                        'g.' . $cleanProperty,
                        $this->queryBuilder->createNamedParameter('%' . $searchSplittedPart . '%')
                    );
                }
            }
        }

        $this->queryBuilder->innerJoin(
            $demand::TABLE,
            'fe_groups',
            'g',
            $this->queryBuilder->expr()->andX(
                $this->queryBuilder->expr()->inSet(
                    $demand::TABLE . '.usergroup',
                    'g.uid'
                )
            )
        );

        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->orX(
                ...$constraints
            )
        );

        $this->queryBuilder->groupBy($demand::TABLE . '.uid');
    }

    private function setPublicProfileConstraint(): void
    {
        $this->queryBuilder->andWhere(
            $this->queryBuilder->expr()->eq(
                'public_profile',
                $this->queryBuilder->createNamedParameter(1, \PDO::PARAM_BOOL)
            )
        );
    }

    public function getUsernames()
    {
        $query = $this->createQuery();
        $query->statement('select username from fe_users');
        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));

        return $query->execute(true);
    }

    public function deleteAllUserLogos(int $userId)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_reference')->createQueryBuilder();

        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('sys_file_reference')
            ->set('deleted', 1)
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter('fe_users', PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('logo', PDO::PARAM_STR)),
                    $queryBuilder->expr()->eq(
                        'uid_foreign',
                        $queryBuilder->createNamedParameter($userId, PDO::PARAM_INT)
                    )
                )
            );

        return $queryBuilder->execute();
    }

    public function addBookmarkForUser(int $userId, string $tableName, int $recordUid)
    {
        $bookmarkName = $tableName . '_' . $recordUid;
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $sql = 'update fe_users set bookmarks=CONCAT(bookmarks, ",", "' . $bookmarkName . '") where uid=' . $userId . ' and bookmarks not regexp "' . $bookmarkName . ',| . ' . $bookmarkName . '$";';
        $connection->executeQuery($sql);
    }

    public function removeBookmarkForUser(int $userId, string $tableName, int $recordUid)
    {
        $bookmarkName = $tableName . '_' . $recordUid;
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $bookmarks = $connection->executeQuery('select bookmarks from fe_users where uid=' . $userId . ';')->fetchOne();
        $bookmarks = GeneralUtility::trimExplode(',', $bookmarks, true);
        $bookmarks = array_filter($bookmarks, function ($bookmark) use ($bookmarkName) {
            return $bookmark !== $bookmarkName;
        });
        $sql = 'update fe_users set bookmarks="' . implode(',', $bookmarks) . '" where uid=' . $userId . ';';
        $connection->executeQuery($sql);
    }

    /**
     * @param array<string, string> $autocompleter
     * @return array<string, string>
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getAutocompleteData(array $autocompleter): array
    {
        $data = [];

        foreach ($autocompleter as $name => $setting) {
            $columns = GeneralUtility::trimExplode(',', $setting);

            foreach ($columns as $column) {
                $columnData = GeneralUtility::trimExplode('.', $column);

                if (count($columnData) !== 2) {
                    continue;
                }

                $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($columnData[0]);

                $result = $qb->select($columnData[1])
                    ->from($columnData[0])
                    ->execute()
                    ->fetchAllAssociativeIndexed();
                $data[$name] = array_merge($data[$name] ?? [], array_filter(array_keys($result)));
            }
        }

        return $data;
    }
}
