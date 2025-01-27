<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Instance;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\DataInstallLog as LogDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class InstanceInfo implements ResolverInterface
{
    /**
     * @var LogDataProvider
     */
    private $logDataProvider;

    /** @var Authentication */
    protected $authentication;

    /** @var ProductMetadataInterface */
    protected $productMetadata;

    /** @var ModuleManager */
    protected $moduleManager;

    

    /** @var string  */
    private const LIVESEARCH_MODULE = 'Magento_LiveSearch';

    /** @var string  */
    private const OPENSEARCH_MODULE = 'Magento_OpenSearch';

   /**
    *
    * @param LogDataProvider $logDataProvider
    * @param Authentication $authentication
    * @param ProductMetadataInterface $productMetadata
    * @param Manager $moduleManager
    * @return void
    */
    public function __construct(
        LogDataProvider $logDataProvider,
        Authentication $authentication,
        ProductMetadataInterface $productMetadata,
        ModuleManager $moduleManager
    ) {
        $this->logDataProvider = $logDataProvider;
        $this->authentication = $authentication;
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();

        $logData = $this->logDataProvider->getInstalledDataPacks();
        
        return [
             'commerce_version' => $this->productMetadata->getVersion(),
             'search_engine' => $this->whichSearchEngine(),             
             'datapacks' => $logData,
        ];
    }

    /**
     * Check if LiveSearch module is enabled
     *
     * @return bool
     */

    private function isLiveSearchModuleInstalled(): bool
    {
        return $this->moduleManager->isEnabled(self::LIVESEARCH_MODULE);
    }

    /**
     * Check if OpenSearch module is enabled
     *
     * @return bool
     */

    private function isOpenSearchModuleInstalled(): bool
    {
        return $this->moduleManager->isEnabled(self::OPENSEARCH_MODULE);
    }

    private function whichSearchEngine(): string
    {
        if ($this->isLiveSearchModuleInstalled() && $this->isOpenSearchModuleInstalled()) {
            return 'Both';
        } elseif ($this->isLiveSearchModuleInstalled()) {
            return 'LiveSearch';
        } elseif ($this->isOpenSearchModuleInstalled()) {
            return 'OpenSearch';
        } else {
            return 'None';
        }
    }
}
