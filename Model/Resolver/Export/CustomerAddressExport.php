<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Resolver\Export;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use MagentoEse\DataInstallGraphQl\Model\Authentication;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;
use Magento\ImportExport\Api\ExportManagementInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;

class CustomerAddressExport implements ResolverInterface
{
    /** @var ExportInfoFactory */
    private $exportInfoFactory;

    /** @var ExportManagementInterface */
    private $exportManager;

     /** @var LocaleResolver */
     private $localeResolver;

    /** @var Authentication */
    private $authentication;

   /**
    *
    * @param ExportInfoFactory $exportInfoFactory
    * @param ExportManagementInterface $exportManager
    * @param LocaleResolver $localeResolver
    * @param Authentication $authentication
    * @return void
    */
    public function __construct(
        ExportInfoFactory $exportInfoFactory,
        ExportManagementInterface $exportManager,
        LocaleResolver $localeResolver,
        Authentication $authentication
    ) {
        $this->exportInfoFactory = $exportInfoFactory;
        $this->exportManager = $exportManager;
        $this->localeResolver = $localeResolver;
        $this->authentication = $authentication;
    }

    /**
     * Get Customer Export Data
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

        $exportData = $this->singleExport('customer_address', []);

        if (count($exportData) < 2) {
            throw new GraphQlNoSuchEntityException(__('No Customer Addresses Found'));
        }

        $json = json_encode($exportData);
        return [
            'data' => $json,
        ];
    }
    /**
     * Convert cvs to array when there are line breaks in content
     *
     * @param string $csvFile
     * @param int $linelen
     * @return false|array
     */
    private function csvToArray($csvFile, $linelen)
    {
        if (($contents = $csvFile) === false) {
              return false;
        }
        $fi_co = 0;
        $result = [];
        $tarray = [];
        while ($contents) {
             $word = "";
             // phpcs:ignore Squiz.Operators.IncrementDecrementUsage.NotAllowed
             $delim = (++$fi_co % $linelen) ? ',' : "\n";
             $pos = -1;
            do {
                if (($pos = strpos($contents, $delim, ++$pos)) === false) {
                    $pos = strlen($contents);
                }
                $word = substr($contents, 0, $pos);
                $x = substr_count($word, '"') % 2;
                $pos;
            } while ($x);
            if (($fi_co % $linelen) == 1) {
                $tarray = [$word];
            } else {
                $tarray[] = $word;
            }
            if ($fi_co % $linelen == 0) {
                $result[] = $tarray;
            }
            $contents = substr($contents, $pos+1);
        }
        if ($fi_co % $linelen != 0) {
            $result[] = $tarray;
        }
        return $result;
    }

     /**
      * Export Data for a single filter or no filter
      *
      * @param string $exportType
      * @param string $filter
      * @return array
      */
    private function singleExport($exportType, $filter)
    {

        $exportInfo = $this->exportInfoFactory->create(
            'csv', //file format
            $exportType,
            $filter, //filter
            [], //skip attributes is done by attribute id, not by attribute code
            $this->localeResolver->getLocale()
        );

        $data = $this->exportManager->export($exportInfo);
        //get # of colums in header row
        $headerColumns = explode(",", explode("\n", $data)[0]);

        //fix data in the case that data elements include line feeds
        $csvCleanData = $this->csvToArray($data, count($headerColumns));
        $csvCleanData = $this->removeExtraQuotes($csvCleanData);
        
        return $csvCleanData;
    }

    /**
     * Remove extra quotes at the start and end of values
     *
     * @param array $data
     * @return array
     */
    private function removeExtraQuotes($data)
    {
        foreach ($data as $rowKey => $row) {
            foreach ($row as $elementKey => $element) {
                if (substr($element, 0, 1)=='"' && substr($element, strlen($element)-1, 1)=='"') {
                    $newValue = str_replace('"', '', $element);
                    $data[$rowKey][$elementKey] = $newValue;
                }
            }
        }
        return $data;
    }
}
