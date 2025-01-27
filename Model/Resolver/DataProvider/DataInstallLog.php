<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use MagentoEse\DataInstall\Api\LoggerRepositoryInterface;
use MagentoEse\DataInstall\Api\Data\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager as ModuleManager;

class DataInstallLog
{
    /**
     * @var LoggerRepositoryInterface
     */
    private $loggerRepository;

    /**
     * @var ModuleManager
     */ 
    private $moduleManager;

    /** 
     * @var string  
     */
    private const LUMA_DATA_MODULE = 'Magento_CatalogSampleData';

    /** 
     * @var array  
     */
    private const LUMA_NATIVE_DATA = ["id" => 1, "datapack" => "Magento_CatalogSampleData",
    "message" => "Luma Data Pack Installed", "level" => "warning", "add_date" => "", "datapack_name" => "Luma",
    "metadata" => "","job_id" => ""];

    /**
     * @param LoggerRepositoryInterface $loggerRepository
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        LoggerRepositoryInterface $loggerRepository,
        ModuleManager $moduleManager
    ) {
        $this->loggerRepository = $loggerRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get log data by job Id
     *
     * @param string $jobId
     * @return array
     */
    public function getLogByJobId(string $jobId): array
    {
        $logData = $this->loggerRepository->getByJobId($jobId);
        return $this->formatLogData($logData, $jobId, LoggerInterface::JOBID);
    }

    /**
     * Get log data by datapack path
     *
     * @param int $datapack
     * @return array
     */
    public function getLogByDatpack(string $datapack): array
    {
        $logData = $this->loggerRepository->getByDatapack($datapack);
        return $this->formatLogData($logData, $datapack, LoggerInterface::DATAPACK);
    }

    public function getInstalledDataPacks(): array
    {
        $logData = $this->loggerRepository->getInstalledDataPacks();
        // Add Luma data pack to the list of if it was installed via sample data
        if ($this->isLumaDataPackInstalled()) {
            $logData[] = self::LUMA_NATIVE_DATA;
        }
        return $this->formatInstalledDataPackData($logData);
    }

    /**
     * Formats log data for return
     *
     * @param mixed $logResults
     * @param string $identifier
     * @param string $type
     * @return array
     * @throws NoSuchEntityException
     */
    private function formatLogData($logResults, $identifier, $type): array
    {
        if (empty($logResults)) {
            throw new NoSuchEntityException(
                __('The log information with %2 "%1" doesn\'t exist.', $identifier, $type)
            );
        }

        $results = [];
        foreach ($logResults as $log) {
            $results[]=[
                LoggerInterface::JOBID => $log[LoggerInterface::JOBID],
                LoggerInterface::DATAPACK => $log[LoggerInterface::DATAPACK],
                LoggerInterface::MESSAGE => $log[LoggerInterface::MESSAGE],
                LoggerInterface::LEVEL => $log[LoggerInterface::LEVEL],
                LoggerInterface::ADDDATE => $log[LoggerInterface::ADDDATE]
            ];
        }
        return $results;
    }

     /**
      * Formats log data for return
      *
      * @param mixed $logResults
      * @param string $identifier
      * @param string $type
      * @return array
      * @throws NoSuchEntityException
      */
    private function formatInstalledDataPackData($logResults): array
    {
        if (empty($logResults)) {
            return [];
        }

        $results = [];
        foreach ($logResults as $log) {
            $results[]=[
                LoggerInterface::JOBID => $log[LoggerInterface::JOBID],
                LoggerInterface::DATAPACK => $log[LoggerInterface::DATAPACK],
                LoggerInterface::MESSAGE => $log[LoggerInterface::MESSAGE],
                LoggerInterface::LEVEL => $log[LoggerInterface::LEVEL],
                LoggerInterface::ADDDATE => $log[LoggerInterface::ADDDATE],
                LoggerInterface::DATAPACKNAME => $log[LoggerInterface::DATAPACKNAME],
                LoggerInterface::METADATA => $log[LoggerInterface::METADATA],

            ];
        }
        return $results;
    }

     /**
     * Check if Luma data pack is installed
     *
     * @return bool
     */
    private function isLumaDataPackInstalled(): bool
    {
        return $this->moduleManager->isEnabled(self::LUMA_DATA_MODULE);
    }
}
