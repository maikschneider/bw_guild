<?php

namespace Blueways\BwGuild\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OfferExpireCommand extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CacheManager $cacheManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Deletes outdated offers');
        $this->addArgument('periodInSeconds', InputArgument::REQUIRED, 'Seconds until a offer becomes deleted');
        $this->addArgument(
            'dateColumn',
            InputArgument::OPTIONAL,
            'Database column which should be used for calculation',
            'tstamp'
        );
        $this->setHelp('Marks offers as deleted after specified time');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $minTimestamp = (new \DateTime())->getTimestamp() - ((int)$input->getArgument('periodInSeconds'));

        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_bwguild_domain_model_offer');
        $updated = $qb->update('tx_bwguild_domain_model_offer')
            ->set('deleted', 1)
            ->where(
                $qb->expr()->lte($input->getArgument('dateColumn'), $minTimestamp)
            )
            ->execute();

        $output->writeln('Deleted ' . $minTimestamp . ' records');
        $this->logger->info('Deleted ' . $updated . ' records');

        $this->cacheManager->flushCachesByTag('tx_bwguild_domain_model_offer');

        return Command::SUCCESS;
    }
}
