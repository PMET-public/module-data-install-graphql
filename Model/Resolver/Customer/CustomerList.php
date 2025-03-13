<?php
/**
 * Copyright 2023 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\CustomerGroup;
use Magento\Company\Api\CompanyRepositoryInterface as Company;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

class CustomerList implements ResolverInterface
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteria;

    /** @var Authentication */
    private $authentication;

    /** @var CustomerGroup */
    private $customerGroup;

    /** @var Company */
    private $company;

    /** @var StoreRepositoryInterface */
    private $storeRepository;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /**
     * Constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param Authentication $authentication
     * @param CustomerGroup $customerGroup
     * @param Company $company
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     *
     * @return void
     */

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteria,
        Authentication $authentication,
        CustomerGroup $customerGroup,
        Company $company,
        StoreRepositoryInterface $storeRepository,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->authentication = $authentication;
        $this->customerGroup = $customerGroup;
        $this->company = $company;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Get List of customers
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->authentication->authorize();
        $search = $this->searchCriteria->create();
        $customerList = $this->customerRepository->getList($search)->getItems();
        $customerData = [];
        foreach ($customerList as $customer) {
            $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
            $customerData[]=[
                'email' => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'customer_id' => $customer->getId(),
                'account_created_in_group_id' => $customer->getGroupId(),
                'account_created_in_group_name' => $this->customerGroup->getGroupDataById($customer->getGroupId())['name'],
                'website_id' => $customer->getWebsiteId(),
                'website_name' => $this->websiteRepository->getById($customer->getWebsiteId())->getName(),
                'company_id' => $companyId,
                'company_name' => ($companyId == 0) ? "" : $this->company->get($companyId)->getCompanyName()

            ];
        }

        return [
            'items' => $customerData,
        ];
    }
}
