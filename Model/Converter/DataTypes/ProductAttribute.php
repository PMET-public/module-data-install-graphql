<?php
/**
 * Copyright 2022 Adobe, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace MagentoEse\DataInstallGraphQl\Model\Converter\DataTypes;

use Magento\Eav\Api\AttributeOptionManagementInterface;

class ProductAttribute
{
    /** @var string */
    protected $tokenStart = '{{productattribute code="';
    
    /** @var string */
    protected $tokenEnd = '"}}';

    /** @var string */
    // phpcs:ignoreFile Generic.Files.LineLength.TooLong
    protected $regexToSearch = [
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"==","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"!=","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"!()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"{}","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\Attributes","attribute":"',
        'substringend'=>'","operator":"!{}","value":["',
        'delimiter'=>'","'],

        // //this may be redundant
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"==","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"!=","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"!()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"{}","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Product\\\\\\\\Attributes","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Product\\\\Attributes","attribute":"',
        'substringend'=>'","operator":"!{}","value":["',
        'delimiter'=>'","'],

        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"==","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"==","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"!=","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"!=","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"\!\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"!()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"\(\)","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"()","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"{}","value":["',
        'delimiter'=>'","'],
        ['regex'=> '/Condition\\\\Product","attribute":"([a-zA-Z0-9_]+)","operator":"!{}","value":\["([0-9,"]+)"\]/',
        'substringstart'=> 'Condition\\Product","attribute":"',
        'substringend'=>'","operator":"!{}","value":["',
        'delimiter'=>'","'],
        //Block
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`==`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`==`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`!=`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`!=`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:``,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:``,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`\!\(\)`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`!()`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`\(\)`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`()`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`{}`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`{}`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`!{}`,`value`:`([0-9,`]+)`/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`!{}`,`value`:`',
        'delimiter'=>'`,`'],
        ['regex'=> '/Condition\|\|Product`,`attribute`:`([a-zA-Z0-9_]+)`,`operator`:`\^\[\^\]`,`value`:\[([0-9,`]+)\]/',
        'substringstart'=> 'Condition||Product`,`attribute`:`',
        'substringend'=>'`,`operator`:`^[^]`,`value`:[',
        'delimiter'=>'`,`'],
    ];
    /** @var AttributeOptionManagementInterface */
    protected $attributeOptionManagement;

    /**
     * Constructor
     * 
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     */
    public function __construct(
        AttributeOptionManagementInterface $attributeOptionManagement
    ) {
        $this->attributeOptionManagement = $attributeOptionManagement;
    }

    /**
     * Replace product attribute ids with tokens
     * @param string $content
     * @return string
     */
    public function replaceAttributeOptionIds($content)
    {
        foreach ($this->regexToSearch as $search) {
            preg_match_all($search['regex'], $content, $matchesOptions, PREG_SET_ORDER);
            foreach ($matchesOptions as $match) {
                $attributeCode = $match[1];
                $idToReplace = $match[2];
                $attributeOptions = $this->attributeOptionManagement->getItems(4, $attributeCode);
                if ($idToReplace) {
                    //may be list of ids
                    $optionIds = explode(",", str_replace('"', '', str_replace('`', '', $idToReplace)));
                    $replacementArr = [];
                    foreach ($optionIds as $optionId) {
                        foreach ($attributeOptions as $attributeOption) {
                            if ($attributeOption->getvalue()==$optionId) {
                                $replacementArr[]= $this->tokenStart.$attributeCode.":".$attributeOption->getLabel().
                                $this->tokenEnd;
                                break;
                            }
                        }
                    }
                    $replacementString = implode($search['delimiter'], $replacementArr);
                    $content = $this->strLreplace($search['substringstart'].$attributeCode.$search['substringend'].
                        $idToReplace, $search['substringstart'].$attributeCode.$search['substringend'].
                        $replacementString, $content);
                }
            }
        }
        return $content;
    }
     /**
      * @param string $search
      * @param string $replace
      * @param string $subject
      * @return string
      */

    private function strLreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);
    
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
    
        return $subject;
    }
}
