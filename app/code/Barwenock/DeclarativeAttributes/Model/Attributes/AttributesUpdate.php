<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Model\Attributes;

class AttributesUpdate
{
    /**
     * Attribute map for csv file
     * @var array
     */
    protected const ATTRIBUTE_MAPPING = [
        'attribute_code' => 'attribute_code',
        'label' => 'frontend_label',
        'backend' => 'backend_model',
        'frontend' => 'frontend_model',
        'source' => 'source_model',
        'visible' => 'is_visible',
        'user_defined' => 'is_user_defined',
        'searchable' => 'is_searchable',
        'filterable' => 'is_filterable',
        'visible_on_front' => 'is_visible_on_front',
        'used_in_product_listing' => 'used_in_product_listing',
        'type' => 'backend_type',
        'input' => 'frontend_input',
        'global' => 'is_global',
        'required' => 'is_required',
        'options' => 'options'
    ];

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionManagement
     */
    protected $optionManagment;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\OptionManagement $optionManagement
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\OptionManagement $optionManagement,
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        $this->optionManagment = $optionManagement;
        $this->eavSetup = $eavSetup;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Update attributes logic
     *
     * @param array $importAttribute
     * @param \Magento\Eav\Api\Data\AttributeInterface $existingAttribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update($importAttribute, $existingAttribute): void
    {
        $updateData = $this->getUpdateData($importAttribute, $existingAttribute);

        foreach ($updateData as $update) {
            if ($update['key'] === 'options') {
                $attributeId = $this->eavSetup->getAttributeId(
                    \Magento\Catalog\Model\Product::ENTITY,
                    trim($update['attribute_code'])
                );
                $this->eavSetup->addAttributeOption([
                    'attribute_id' => $attributeId,
                    'values' => $update['value']
                ]);
                return;
            }

            $this->eavSetup->updateAttribute(
                \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesCreate::PRODUCT_ENTITY_TYPE_CODE,
                trim($update['attribute_code']),
                $update['key'],
                $update['value']
            );
        }
    }

    /**
     * Get data for update
     *
     * @param array $importAttribute
     * @param \Magento\Eav\Api\Data\AttributeInterface $existingAttribute
     * @return array
     */
    protected function getUpdateData($importAttribute, $existingAttribute)
    {
        $importAttribute = $this->mapArrayKeys($importAttribute, self::ATTRIBUTE_MAPPING);

        $updateData = [];
        foreach ($importAttribute as $key => $value) {
            if (isset($existingAttribute[$key]) && $existingAttribute[$key] != $value) {
                $updateData[$key] = [
                    'key' => $key,
                    'value' => $value,
                    'attribute_code' => $importAttribute['attribute_code'],
                ];
            }
        }

        return $this->validateOptions($updateData, $importAttribute, $existingAttribute);
    }

    /**
     * Attributes mapping
     *
     * @param array $originalArray
     * @param array $mapping
     * @return array
     */
    protected function mapArrayKeys(array $originalArray, array $mapping)
    {
        $mapArray = [];

        foreach ($mapping as $originalKey => $newKey) {
            if (array_key_exists($originalKey, $originalArray)) {
                $mapArray[$newKey] = $originalArray[$originalKey];
            }
        }

        return $mapArray;
    }

    /**
     * Validate options
     *
     * @param array $updateData
     * @param array $importAttribute
     * @param \Magento\Eav\Api\Data\AttributeInterface $existingAttribute
     * @return array
     */
    protected function validateOptions(array $updateData, array $importAttribute, $existingAttribute)
    {
        if ($importAttribute['options']) {
            $existingOptions = $existingAttribute->getOptions();

            // Remove the first empty option
            array_shift($existingOptions);

            // Convert the object to an array
            $existingOptionsArray = [];
            foreach ($existingOptions as $existingOption) {
                $existingOptionsArray[] = $existingOption->getLabel();
            }

            $key = 'options';
            if ($existingOptionsArray != $importAttribute['options']) {
                $updateData[$key] = [
                    'key' => $key,
                    'value' => $importAttribute['options'],
                    'attribute_code' => $importAttribute['attribute_code'],
                ];
            }
        }

        return $updateData;
    }
}
