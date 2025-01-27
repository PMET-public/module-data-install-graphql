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

   /**
    *
    * @param LogDataProvider $logDataProvider
    * @param Authentication $authentication
    * @param ProductMetadataInterface $productMetadata
    * @return void
    */
    public function __construct(
        LogDataProvider $logDataProvider,
        Authentication $authentication,
        ProductMetadataInterface $productMetadata
    ) {
        $this->logDataProvider = $logDataProvider;
        $this->authentication = $authentication;
        $this->productMetadata = $productMetadata;
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
             'datapacks' => $logData,
        ];
    }
}
