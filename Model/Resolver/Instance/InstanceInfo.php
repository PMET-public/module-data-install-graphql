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
use Magento\Framework\App\Config\ScopeConfigInterface;

class InstanceInfo implements ResolverInterface
{
    /** @var LogDataProvider */
    private $logDataProvider;

    /** @var Authentication */
    protected $authentication;

    /** @var ProductMetadataInterface */
    protected $productMetadata;

    /** @var ModuleManager */
    protected $moduleManager;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var string  */
    private const LIVESEARCH_MODULE = 'Magento_LiveSearch';

    /** @var string  */
    private const OPENSEARCH_MODULE = 'Magento_OpenSearch';

    /** @var string  */
    private const PRODUCT_RECS_MODULE = 'Magento_ProductRecommendationsAdmin';

    /** @var string  */
    private const SERVICES_ENVIRONMENT = 'services_connector/services_id/environment_id';

    /** @var string  */
    private const SERVICES_SANDBOX_PRIVATE_KEY =
    'services_connector/services_connector_integration/sandbox_private_key';

    /** @var string  */
    private const SERVICES_PROD_PRIVATE_KEY =
    'services_connector/services_connector_integration/production_private_key';

    /** @var string  */
    private const SERVICES_SANDBOX_PUBLIC_KEY = 'services_connector/services_connector_integration/sandbox_api_key';

    /** @var string  */
    private const SERVICES_PROD_PUBLIC_KEY = 'services_connector/services_connector_integration/production_api_key';

   /**
    *
    * @param LogDataProvider $logDataProvider
    * @param Authentication $authentication
    * @param ProductMetadataInterface $productMetadata
    * @param Manager $moduleManager
    * @param ScopeConfigInterface $scopeConfig
    * @return void
    */
    public function __construct(
        LogDataProvider $logDataProvider,
        Authentication $authentication,
        ProductMetadataInterface $productMetadata,
        ModuleManager $moduleManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logDataProvider = $logDataProvider;
        $this->authentication = $authentication;
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
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
             'product_recs' => $this->isProductRecsModuleEnabled() ? 'enabled' : 'disabled',
             'commerce_services_connector_configured' => $this->isCommerceConnectorConfigured(),
             'datapacks' => $logData,
        ];
    }

    /**
     * Check if LiveSearch module is enabled
     *
     * @return bool
     */

    private function isLiveSearchModuleEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::LIVESEARCH_MODULE);
    }

    private function isProductRecsModuleEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::PRODUCT_RECS_MODULE);
    }

    /**
     * Check if OpenSearch module is enabled
     *
     * @return bool
     */

    private function isOpenSearchModuleEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::OPENSEARCH_MODULE);
    }

    /**
     * Check if OpenSearch module is enabled
     *
     * @return bool
     */
    private function whichSearchEngine(): string
    {
        if ($this->isLiveSearchModuleEnabled() && $this->isOpenSearchModuleEnabled()) {
            return 'Both';
        } elseif ($this->isLiveSearchModuleEnabled()) {
            return 'Live Search';
        } elseif ($this->isOpenSearchModuleEnabled()) {
            return 'OpenSearch';
        } else {
            return 'None';
        }
    }

    /**
     * Check if Commerce Connector is configured
     *
     * @return bool
     */
    private function isCommerceConnectorConfigured()
    {
        $environment = $this->scopeConfig->getValue(self::SERVICES_ENVIRONMENT) ?? '';
        $sandboxPrivateKey = $this->scopeConfig->getValue(self::SERVICES_SANDBOX_PRIVATE_KEY) ?? '';
        $sandboxPublicKey = $this->scopeConfig->getValue(self::SERVICES_SANDBOX_PUBLIC_KEY) ?? '';
        $prodPrivateKey = $this->scopeConfig->getValue(self::SERVICES_PROD_PRIVATE_KEY) ?? '';
        $prodPublicKey = $this->scopeConfig->getValue(self::SERVICES_PROD_PUBLIC_KEY) ?? '';

        if (strlen($environment) > 0 && strlen($sandboxPrivateKey) > 0 && strlen($sandboxPublicKey) > 0
        && strlen($prodPrivateKey) > 0 && strlen($prodPublicKey) > 0) {
            return true;
        }
        return false;
    }
}
