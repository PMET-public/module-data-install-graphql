<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Cms\Api\PageRepositoryInterface;

class PageId
{
    /** @var string */
    protected $tokenStart = '{{pageid key="';
    
    /** @var string */
    protected $tokenEnd = '"}}';
    
    /** @var array */
    protected $regexToSearch = [
        ['regex'=> "/page_id='([0-9]+)'/",
        'substring'=> "page_id='"]
    ];
    
    /** @var PageRepositoryInterface */
    protected $pageRepository;

    /**
     * Constructor
     *
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        PageRepositoryInterface $pageRepository
    ) {
        $this->pageRepository = $pageRepository;
    }

    /**
     * Replace page ids with tokens
     *
     * @param string $content
     * @return string
     */
    public function replacePageIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesPageId, PREG_SET_ORDER);
            foreach ($matchesPageId as $match) {
                $idToReplace = $match[1];
                if ($idToReplace) {
                    //ids may be a list
                    $pageIds = explode(",", $idToReplace);
                    $replacementString = '';
                    foreach ($pageIds as $pageId) {
                        $replacementString.= $this->getPageIdTag($pageId);
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
     * Get tag to replace page id
     *
     * @param int $pageId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPageIdTag($pageId)
    {
        $page = $this->pageRepository->getById($pageId);
        $identifier = $page->getIdentifier();
        return $this->tokenStart.$identifier.$this->tokenEnd;
    }
}
