<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductId
{
    /** @var string */
    protected $tokenStart = '{{productid sku="';

    /** @var string */
    protected $tokenEnd = '"}}';

    /** @var string */
    protected $regexToSearch = [
        ['regex'=> "/id_path='product\/([0-9]+)'/",
        'substring'=> "id_path='product/"]
    ];
    
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Replace product ids with tokens
     *
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
                        $replacementString.= $this->getProductIdTag($productId);
                    }
                    $content = str_replace(
                        $search['substring'].$idToReplace,
                        $search['substring'].$replacementString,
                        $content
                    );
                }
            }
        }
        return $content;
    }

    /**
     * 
     * @param int $productId 
     * @return string 
     * @throws NoSuchEntityException 
     */
    public function getProductIdTag($productId){
        $product = $this->productRepository->getById($productId);
        $sku = $product->getSku();
        return $this->tokenStart.$sku.$this->tokenEnd;
    }
}
