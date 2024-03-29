<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Banner\Model\ResourceModel\Banner as BannerResource;
use Magento\Banner\Model\ResourceModel\Banner\CollectionFactory as BannerCollection;
use MagentoEse\DataInstallGraphQl\Model\Converter\Converter;

class CatalogRule
{
     /**
      * @var RuleCollection
      */
    private $ruleCollection;

     /**
      * @var CouponRepository
      */
    private $couponRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

     /**
      * @var GroupRepositoryInterface
      */
    private $groupRepositoryInterface;

    /**
     * @var BannerResource
     */
    private $bannerResource;

    /**
     * @var BannerCollection
     */
    private $bannerCollection;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @param RuleCollection $ruleCollection
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepositoryInterface
     * @param BannerResource $bannerResource
     * @param BannerCollection $bannerCollection
     * @param Converter $converter
     */
    public function __construct(
        RuleCollection $ruleCollection,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepositoryInterface,
        BannerResource $bannerResource,
        BannerCollection $bannerCollection,
        Converter $converter
    ) {
        $this->ruleCollection = $ruleCollection;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepositoryInterface = $groupRepositoryInterface;
        $this->bannerResource = $bannerResource;
        $this->bannerCollection = $bannerCollection;
        $this->converter = $converter;
    }

    /**
     * Get rule data by name
     *
     * @param string $ruleName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCatalogRuleDataByName(string $ruleName): array
    {
        $ruleData = $this->fetchRuleData($ruleName, RuleInterface::NAME);

        return $ruleData;
    }

    /**
     * Get block data by block_id
     *
     * @param int $ruleId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCatalogRuleDataById(int $ruleId): array
    {
        $ruleData = $this->fetchRuleData($ruleId, RuleInterface::RULE_ID);

        return $ruleData;
    }

    /**
     * Fetch rule data by either id or name field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchRuleData($identifier, string $field): array
    {
        $ruleResults = $this->ruleCollection->create()->addFieldToFilter($field, [$identifier])->getItems();
        
        if (empty($ruleResults)) {
            throw new NoSuchEntityException(
                __('The rule with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        /** @var RuleInterface $rule */
        $rule = current($ruleResults);

        return [
            'name' => $rule->getName(),
            'site_code' => $this->getWebsiteCodes($rule->getWebsiteIds()),
            'description' => $rule->getDescription(),
            'actions_serialized' => $this->converter->convertContent($rule->getActionsSerialized()),
            'conditions_serialized' => $this->converter->convertContent($rule->getConditionsSerialized()),
            'customer_groups' => $this->getCustomerGroupNames($rule->getCustomerGroupIds()),
            'stop_rules_processing' => $rule->getStopRulesProcessing(),
            'sort_order' => $rule->getSortOrder(),
            'simple_action' => $rule->getSimpleAction(),
            'discount_amount' => $rule->getDiscountAmount(),
            'dynamic_blocks' => ($this->getBannerNames($rule->getRuleID()) !== "") ?
            $this->getBannerNames($rule->getRuleID()) : null,
            'is_active' => $rule->getIsActive(),
            'rule_id' => $rule->getRuleId()
           ];
    }

    /**
     * Get all rule ids
     *
     * @return array
     */
    public function getAllRuleIds(): array
    {
        $ruleQuery = $this->ruleCollection->create();
        $ruleResults = $ruleQuery->getItems();
        $ruleIds = [];
        foreach ($ruleResults as $rule) {
             $ruleIds[] = $rule->getRuleId();
        }
        return $ruleIds;
    }

    /**
     * Get website codes by ids
     *
     * @param array $siteIds
     * @return string
     */
    private function getWebsiteCodes($siteIds)
    {
        $siteCodes = [];
        foreach ($siteIds as $siteId) {
            $site = $this->websiteRepository->getById($siteId);
            $siteCodes[] = $site->getCode();
        }
        return implode(",", $siteCodes);
    }

    /**
     * Get customer group names by ids
     *
     * @param array $groupIds
     * @return string
     */
    private function getCustomerGroupNames($groupIds)
    {
        $groupNames = [];
        foreach ($groupIds as $groupId) {
            $group = $this->groupRepositoryInterface->getById($groupId);
            $groupNames[] = $group->getCode();
        }
        return implode(",", $groupNames);
    }

    /**
     * Get banner names in rule
     *
     * @param int $ruleId
     * @return string
     */
    private function getBannerNames($ruleId)
    {
        $bannerIds = $this->getCatalogRuleRelatedBannerIds($ruleId);
        $bannerNames = [];
        foreach ($bannerIds as $bannerId) {
            $bannerCollection = $this->bannerCollection->create();
            $bannerResults = $bannerCollection->addFilter('banner_id', $bannerId, 'eq')->setPageSize(1)->setCurPage(1);
            $banner = $bannerResults->getFirstItem();
            $bannerNames[]=$banner->getName();
        }
        return implode(",", $bannerNames);
    }

    /**
     * Get banners that associated to catalog rules
     *
     * @param int $ruleId
     * @return array
     */
    private function getCatalogRuleRelatedBannerIds($ruleId): array
    {
        $connection = $this->bannerResource->getConnection();
        $select = $connection->select()->from(
            $this->bannerResource->getTable('magento_banner_catalogrule'),
            ['banner_id']
        )->where(
            'rule_id = ?',
            $ruleId
        );
        return $connection->fetchCol($select);
    }
}
