<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Company;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\PurchaseOrderRule\Api\RuleRepositoryInterface;
use Magento\PurchaseOrderRule\Api\Data\RuleInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Company\Api\RoleRepositoryInterface;

/**
 * Approval rule field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class ApprovalRules implements ResolverInterface
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     * @param RoleRepositoryInterface $roleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        RoleRepositoryInterface $roleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->roleRepository = $roleRepository;
        $this->searchCriteria = $searchCriteriaBuilder;
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $rulesData = $this->getRulesData($value['model']->getId());

        return [
            'items' => $rulesData,
        ];
    }

     /**
      * Fetch rule data by field
      *
      * @param int $companyId
      * @return array
      * @throws NoSuchEntityException
      */
    private function getRulesData($companyid): array
    {
        $rulesData = [];
        $search = $this->searchCriteria
            ->addFilter(RuleInterface::KEY_COMPANY_ID, $companyid, 'eq')->create();
            $ruleList = $this->ruleRepository->getList($search)->getItems();
          
        foreach ($ruleList as $rule) {
            $rulesData[]= [
                        "name" => $rule->getName(),
                        "description" => $rule->getDescription(),
                        "is_active" => $rule->isActive(),
                        "apply_to_roles" => $this->getRoleIds($rule->getAppliesToRoleIds()),
                        "conditions_serialized" => $rule->getConditionsSerialized(),
                        "approval_from" => $this->getRoleIds($rule->getApproverRoleIds()),
                        "requires_manager_approval" => $rule->isManagerApprovalRequired(),
                        "requires_admin_approval" => $rule->isAdminApprovalRequired()
                    ];
        }
        return $rulesData;
    }
    /**
     * Get names of roles by id, return comma delimited list
     *
     * @param array $roleIds
     * @return string
     * @throws NoSuchEntityException
     */
    private function getRoleIds(array $roleIds): string
    {
        $roleNames = [];
        foreach ($roleIds as $roleId) {
            $roleNames[] = $this->roleRepository->get($roleId)->getRoleName();
        }
        return implode(',', $roleNames);
    }
}