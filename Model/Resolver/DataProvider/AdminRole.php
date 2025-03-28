<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory as RuleCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Authorization\Model\Role;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class AdminRole
{
    private const DEFAULT_ROLES = ["Administrators","admin"];

    /**
     * @var RoleCollection
     */
    private $roleCollection;

    /**
     * @var RuleCollection
     */
    private $ruleCollection;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $storeGroupRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param RoleCollection $roleCollection
     * @param RuleCollection $ruleCollection
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $storeGroupRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        RoleCollection $roleCollection,
        RuleCollection $ruleCollection,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $storeGroupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->roleCollection = $roleCollection;
        $this->ruleCollection = $ruleCollection;
        $this->websiteRepository = $websiteRepository;
        $this->storeGroupRepository = $storeGroupRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get customer role by name
     *
     * @param string $roleName
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRoleDataByName(string $roleName): array
    {
        $roleData = $this->fetchRoleData($roleName, 'role_name');

        return $roleData;
    }

    /**
     * Get customer role by id
     *
     * @param string $roleId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRoleDataById(int $roleId): array
    {
        $roleData = $this->fetchRoleData($roleId, 'role_id');

        return $roleData;
    }

     /**
      * Get all role ids
      *
      * @param int $storeViewId Store View ID
      * @return array
      */
    public function getAllAdminRolesList($storeViewId): array
    {
        try {
            // Convert store view ID to store group ID
            $storeView = $this->storeRepository->getById($storeViewId);
            $storeGroupId = $storeView->getStoreGroupId();
            
            $roleQuery = $this->roleCollection->create()->addFieldToFilter('role_type', 'G');
            
            // Apply OR condition: either global access (gws_is_all=1) OR specific store group access
            $roleQuery->getSelect()->where(
                'gws_is_all = 1 OR FIND_IN_SET(?, gws_store_groups)',
                $storeGroupId
            );
            
            $roleResults = $roleQuery->getItems();
            $roles = [];
            /** @var Role $role */
            foreach ($roleResults as $role) {
                if (!in_array($role->getRoleName(), self::DEFAULT_ROLES)) {
                    $websiteIds = $role->getGwsWebsites();
                    $storeGroupIds = $role->getGwsStoreGroups();
                    
                    // Get flattened website and store information
                    $websiteInfo = $this->getWebsitesInfo($websiteIds);
                    $storeGroupInfo = $this->getStoreGroupsInfo($storeGroupIds);
                    
                    $roles[] = [
                        'role' => $role->getRoleName(),
                        'role_id' => $role->getRoleId(),
                        'gws_is_all' => $role->getGwsIsAll(),
                        'gws_website_ids' => $websiteIds,
                        'gws_website_codes' => $websiteInfo['codes'],
                        'gws_website_names' => $websiteInfo['names'],
                        'gws_store_ids' => $storeGroupIds,
                        'gws_store_codes' => $storeGroupInfo['codes'],
                        'gws_store_names' => $storeGroupInfo['names'],
                    ];
                }
            }
            return $roles;
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * Fetch role data by field
     *
     * @param mixed $identifier
     * @param string $field
     * @return array
     * @throws NoSuchEntityException
     */
    private function fetchRoleData($identifier, string $field): array
    {
        $roleResults = $this->roleCollection->create()->addFieldToFilter($field, [$identifier])->getItems();
        $role = current($roleResults);
        $roleId = $role->getRoleId();
        $ruleResults = $this->ruleCollection->create()->getByRoles($roleId);
        
        if (empty($role)) {
            throw new NoSuchEntityException(
                __('The role with "%2" "%1" doesn\'t exist.', $identifier, $field)
            );
        }
        $roles = [];
        $roleName = $role->getRoleName();
        foreach ($ruleResults as $rule) {
            if ($rule->getPermission()=='allow') {
                $roles[] = [
                'role' => $roleName,
                'resource_id' => $rule->getResourceId(),
                'role_id' => $roleId,
                ];
            }
        }

        return $roles;
    }

    /**
     * Get website information by IDs
     *
     * @param string|null $websiteIds Comma separated website IDs
     * @return array
     */
    private function getWebsitesInfo($websiteIds): array
    {
        $result = [
            'codes' => '',
            'names' => ''
        ];
        
        if (empty($websiteIds)) {
            return $result;
        }

        $websiteIdsArray = explode(',', $websiteIds);
        $codes = [];
        $names = [];

        foreach ($websiteIdsArray as $websiteId) {
            try {
                $website = $this->websiteRepository->getById((int)$websiteId);
                $codes[] = $website->getCode();
                $names[] = $website->getName();
            } catch (NoSuchEntityException $e) {
                // Skip unavailable website
                continue;
            }
        }

        $result['codes'] = implode(',', $codes);
        $result['names'] = implode(',', $names);
        
        return $result;
    }

    /**
     * Get store group information by IDs
     *
     * @param string|null $storeGroupIds Comma separated store group IDs
     * @return array
     */
    private function getStoreGroupsInfo($storeGroupIds): array
    {
        $result = [
            'codes' => '',
            'names' => '',
            'store_codes' => '',
            'store_names' => ''
        ];
        
        if (empty($storeGroupIds)) {
            return $result;
        }

        $storeGroupIdsArray = explode(',', $storeGroupIds);
        $codes = [];
        $names = [];

        foreach ($storeGroupIdsArray as $storeGroupId) {
            try {
                $storeGroup = $this->storeGroupRepository->get((int)$storeGroupId);
                $codes[] = $storeGroup->getCode();
                $names[] = $storeGroup->getName();
            } catch (NoSuchEntityException $e) {
                // Skip unavailable store group
                continue;
            }
        }

        $result['codes'] = implode(',', $codes);
        $result['names'] = implode(',', $names);
        
        return $result;
    }
}
