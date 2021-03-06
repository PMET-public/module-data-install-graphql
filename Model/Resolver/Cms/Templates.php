<?php
namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Cms;

use MagentoEse\DataInstallGraphQl\Model\Resolver\DataProvider\Template as TemplateDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use function is_numeric;

/**
 * CMS templates field resolver, used for GraphQL request processing
 * copied from Magento\CmsGraphQl\Model\Resolver\Blocks
 */
class Templates implements ResolverInterface
{
    /**
     * @var TemplateDataProvider
     */
    private $templateDataProvider;

    /**
     * @param TemplateDataProvider $templateDataProvider
     */
    public function __construct(
        TemplateDataProvider $templateDataProvider
    ) {
        $this->templateDataProvider = $templateDataProvider;
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
        $templateIdentifiers = $this->getTemplateIdentifiers($args);
        $templatesData = $this->getTemplatesData($templateIdentifiers, 0);

        return [
            'items' => $templatesData,
        ];
    }

    /**
     * Get template identifiers
     *
     * @param array $args
     * @return string[]
     * @throws GraphQlInputException
     */
    private function getTemplateIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of Page Builder templates should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get templates data
     *
     * @param array $templateIdentifiers
     * @param int $storeId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getTemplatesData(array $templateIdentifiers, int $storeId): array
    {
        $templatesData = [];
        foreach ($templateIdentifiers as $templateIdentifier) {
            try {
                if (!is_numeric($templateIdentifier)) {
                    $templatesData[$templateIdentifier] = $this->templateDataProvider
                        ->getDataByTemplateName($templateIdentifier, $storeId);
                } else {
                    $templatesData[$templateIdentifier] = $this->templateDataProvider
                        ->getDataByTemplateId((int)$templateIdentifier, $storeId);
                }
            } catch (NoSuchEntityException $e) {
                $templatesData[$templateIdentifier] =
                new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
            }
        }
        return $templatesData;
    }
}
