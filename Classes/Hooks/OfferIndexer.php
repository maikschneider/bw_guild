<?php

namespace Blueways\BwGuild\Hooks;

use Tpwd\KeSearch\Indexer\IndexerBase;
use Tpwd\KeSearch\Indexer\IndexerRunner;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class OfferIndexer extends IndexerBase
{

    const KEY = 'tx_bwguild_domain_model_offer';

    /**
     * @param $params
     * @param $pObj
     */
    public function registerIndexerConfiguration(&$params, $pObj)
    {
        // Set a name and an icon for your indexer.
        $customIndexer = array(
            'Offer-Indexer (ext:bw_guild)',
            self::KEY,
            'EXT:bw_guild/ext_icon.svg'
        );
        $params['items'][] = $customIndexer;
    }

    /**
     * @param array $indexerConfig
     * @param \Tpwd\KeSearch\Indexer\IndexerRunner $indexerObject
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
            ->from($table)
            ->where($queryBuilder->expr()->in('pid', $folders))
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
            // If you use Sphinx, use "_" instead of "#" (configurable in the extension manager).
            $tags = '';

            // Additional information
            $additionalFields = array(
                'orig_uid' => $record['uid'],
                'orig_pid' => $record['pid'],
                'sortdate' => $record['tstamp'],
            );

            // set custom sorting
            //$additionalFields['mysorting'] = $counter;

            // Add something to the title, just to identify the entries
            // in the frontend.
            $title = '' . $title;

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

            $counter++;
        }

        return $counter . ' Elements have been indexed.';
    }
}
