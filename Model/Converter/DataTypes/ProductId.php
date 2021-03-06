<?php
namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductId
{
    protected $tokenStart = '{{productid sku="';
    protected $tokenEnd = '"}}';
    protected $regexToSearch = [
        ['regex'=> "/id_path='product\/([0-9]+)'/",
        'substring'=> "id_path='product/"]
    ];
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $content
     * @return string
     */
    public function replaceProductIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesProductId, PREG_SET_ORDER);
            foreach ($matchesProductId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //productids may be a list
                    $productIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($productIds as $productId) {
                        $product = $this->productRepository->getById($productId);
                        $sku = $product->getSku();
                        $replacementString.= $this->tokenStart.$sku.$this->tokenEnd;
                    }
                    $content = str_replace($search['substring'].$idToReplace, $search['substring'].$replacementString, $content);
                }
            }
        }
        return $content;
    }
}
