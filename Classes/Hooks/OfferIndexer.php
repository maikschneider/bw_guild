<?php

namespace Blueways\BwGuild\Hooks;

use Tpwd\KeSearch\Indexer\IndexerBase;
use Tpwd\KeSearch\Indexer\IndexerRunner;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OfferIndexer extends IndexerBase
{
    const KEY = 'tx_bwguild_domain_model_offer';

    protected array $hookObjectsArr = [];

    /**
     * @param $pObj
     */
    public function __construct($pObj)
    {
        parent::__construct($pObj);

        // prepare hooks
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bw_guild']['afterOfferIndex']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bw_guild']['afterOfferIndex'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['bw_guild']['afterOfferIndex'] as $classRef) {
                $this->hookObjectsArr[] = GeneralUtility::makeInstance($classRef);
            }
        }
    }

    /**
     * @return int
     */
    public function getStartMicrotime(): int
    {
        return $this->startMicrotime;
    }

    /**
     * @param $params
     * @param $pObj
     */
    public function registerIndexerConfiguration(&$params, $pObj)
    {
        // Set a name and an icon for your indexer.
        $customIndexer = [
            'Offer-Indexer (ext:bw_guild)',
            self::KEY,
            'EXT:bw_guild/ext_icon.svg',
        ];
        $params['items'][] = $customIndexer;
    }

    /**
     * @param array $indexerConfig
     * @param IndexerRunner $indexerObject
     * @return string
     */
    public function customIndexer(array &$indexerConfig, IndexerRunner &$indexerObject): string
    {
        if ($indexerConfig['type'] !== self::KEY) {
            return '';
        }

        $table = 'tx_bwguild_domain_model_offer';

        // Doctrine DBAL using Connection Pool.
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $queryBuilder = $connection->createQueryBuilder();

        // Handle restrictions.
        // Don't fetch hidden or deleted elements, but the elements
        // with frontend user group access restrictions or time (start / stop)
        // restrictions in order to copy those restrictions to the index.
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $folders = GeneralUtility::trimExplode(',', htmlentities($indexerConfig['storagepid']));
        $statement = $queryBuilder
            ->select('*')
            ->addSelectLiteral('GROUP_CONCAT(c.uid_local) as categories')
            ->from($table)
            ->leftJoin(
                $table,
                'sys_category_record_mm',
                'c',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('c.uid_foreign', $queryBuilder->quoteIdentifier($table . '.uid')),
                    $queryBuilder->expr()->eq(
                        'c.tablenames',
                        $queryBuilder->createNamedParameter($table, \PDO::PARAM_STR)
                    )
                )
            )
            ->where(
                $queryBuilder->expr()->in($table . '.pid', $folders)
            )
            ->groupBy($table . '.uid')
            ->execute();

        // Loop through the records and write them to the index.
        $counter = 0;

        while ($record = $statement->fetch()) {
            // Compile the information, which should go into the index.
            // The field names depend on the table you want to index!
            $title = strip_tags($record['title']);
            $description = strip_tags($record['description']);

            $fullContent = $title . "\n" . $description;

            // Link to detail view
            $params = '&tx_bwguild_offerlist[offer]=' . $record['uid']
                . '&tx_bwguild_offerlist[controller]=Offer&tx_bwguild_offerlist[action]=show';

            // Tags
            $tags = '';
            if ($record['categories']) {
                $tags = GeneralUtility::intExplode(',', $record['categories']);
                $tags = '#syscat' . implode('#syscat', $tags);
            }

            // Additional information
            $additionalFields = [
                'orig_uid' => $record['uid'],
                'orig_pid' => $record['pid'],
                'sortdate' => $record['tstamp'],
            ];

            // ... and store the information in the index
            $indexerObject->storeInIndex(
                $indexerConfig['storagepid'],   // storage PID
                $title,                         // record title
                self::KEY,            // content type
                $indexerConfig['targetpid'],    // target PID: where is the single view?
                $fullContent,                   // indexed content, includes the title (linebreak after title)
                $tags,                          // tags for faceted search
                $params,                        // typolink params for singleview
                $description,                   // abstract; shown in result list if not empty
                $record['sys_language_uid'],    // language uid
                $record['starttime'],           // starttime
                $record['endtime'],             // endtime
                $record['fe_group'],            // fe_group
                false,                          // debug only?
                $additionalFields               // additionalFields
            );

            // Call Hook
            foreach ($this->hookObjectsArr ?? [] as $hookObject) {
                $hookObject->updateIndexOfRecord($indexerObject, $record);
            }

            $counter++;
        }

        return $counter . ' Elements have been indexed.';
    }
}
