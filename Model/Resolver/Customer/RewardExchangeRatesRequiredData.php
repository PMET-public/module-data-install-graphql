<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerSegment\Model\Segment;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerGroup as CustomerGroupProvider;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Converter\RequiredDataInterfaceFactory;
use Magento\Reward\Model\ResourceModel\Reward\Rate\CollectionFactory as RateCollection;
use Magento\Reward\Model\Reward\Rate;

class RewardExchangeRatesRequiredData implements ResolverInterface
{
    private const DEFAULT_GROUPS = ["NOT LOGGED IN","General","Wholesale","Retailer","Default (General)"];

    /** @var CustomerGroupProvider */
    protected $customerGroupProvider;

    /** @var Converter */
    protected $converter;

    /** @var Authentication */
    protected $authentication;

    /** @var RequiredDataInterfaceFactory */
    protected $requiredDataFactory;

    /** @var RateCollection */
    protected $rateCollection;

    /** @var Rate */
    protected $rate;
    
    /**
     * SegmentCollection
     * @param CustomerGroupProvider $customerGroupProvider
     * @param Converter $converter
     * @param Authentication $authentication
     * @param RequiredDataInterfaceFactory $requiredDataFactory
     * @param RateCollection $rateCollection
     * @param Rate $rate
     * @return void
     */
    public function __construct(
        CustomerGroupProvider $customerGroupProvider,
        Converter $converter,
        Authentication $authentication,
        RequiredDataInterfaceFactory $requiredDataFactory,
        RateCollection $rateCollection,
        Rate $rate
    ) {
        $this->customerGroupProvider = $customerGroupProvider;
        $this->converter = $converter;
        $this->authentication = $authentication;
        $this->requiredDataFactory = $requiredDataFactory;
        $this->rateCollection = $rateCollection;
        $this->rate = $rate;
    }
    
    /**
     * Returns other elements required by the block
     *
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authentication->authorize();
        $groupString = '"attribute":"group_id","operator":"!=","value":"';
        if (!empty($value['rate_id'])) {
            $requiredData = $this->requiredDataFactory->create();
            /** @var Rate $segment */
            $rateResults = $this->rateCollection->create()
            ->addFieldToFilter('rate_id', $value['rate_id'])->getItems();
            $rate = current($rateResults);
            $returnData = $requiredData->
            getRequiredData($groupString.$rate->getCustomerGroupId().'"');
            
            // Check if name is in DEFAULT_GROUPS
            if (isset($returnData[0]['name']) && in_array($returnData[0]['name'], self::DEFAULT_GROUPS)) {
                return [];
            }
            
            return $returnData;
        } else {
            return [];
        }
    }
}
