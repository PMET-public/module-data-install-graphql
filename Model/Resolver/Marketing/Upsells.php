<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Marketing;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Upsell as UpsellDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * Customer Segment field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class Upsells implements ResolverInterface
{
    /**
     * @var UpsellDataProvider
     */
    private $upsellDataProvider;

    /**
     * @param UpsellDataProvider $upsellDataProvider
     */
    public function __construct(
        UpsellDataProvider $upsellDataProvider
    ) {
        $this->upsellDataProvider = $upsellDataProvider;
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
        $upsellRuleIdentifiers = $this->getUpsellIdentifiers($args);
        $upsellRuleData = $this->getUpsellsData($upsellRuleIdentifiers);

        return [
            'items' => $upsellRuleData,
        ];
    }

    /**
     * Get upsell rule identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getUpsellIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Related Product Rules should be specified'));
        }
        return $args['identifiers'];
    }

    /**
     * Get upsell rule data
     *
     * @param array $upsellRuleIdentifiers
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getUpsellsData(array $upsellRuleIdentifiers): array
    {
        $upsellRulesData = [];
        foreach ($upsellRuleIdentifiers as $upsellRuleIdentifier) {
            try {
                if (!is_numeric($upsellRuleIdentifier)) {
                    $upsellRulesData[$upsellRuleIdentifier] = $this->upsellDataProvider
                        ->getUpsellRuleDataByName($upsellRuleIdentifier);
                } else {
                    $upsellRulesData[$upsellRuleIdentifier] = $this->upsellDataProvider
                        ->getUpsellRuleDataById((int)$upsellRuleIdentifier);
                }
            } catch (NoSuchEntityException $e) {
                $upsellRulesData[$upsellRuleIdentifier] = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $upsellRulesData;
    }
}
