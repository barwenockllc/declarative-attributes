<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Model\Attributes;

class AttributesManagement
{
    /**
     * Product entity type code
     * @var string
     */
    protected const PRODUCT_ENTITY_TYPE_CODE = 'catalog_product';

    /**
     * @var \Barwenock\DeclarativeAttributes\Model\File\CsvHandler
     */
    protected $csvHandler;

    /**
     * @var \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesCreate
     */
    protected $attributesCreate;

    /**
     * @var \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesUpdate
     */
    protected $attributesUpdate;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param \Barwenock\DeclarativeAttributes\Model\File\CsvHandler $csvHandler
     * @param \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesCreate $attributesCreate
     * @param \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesUpdate $attributesUpdate
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        \Barwenock\DeclarativeAttributes\Model\File\CsvHandler $csvHandler,
        \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesCreate $attributesCreate,
        \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesUpdate $attributesUpdate,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
    ) {
        $this->csvHandler = $csvHandler;
        $this->attributesCreate = $attributesCreate;
        $this->attributesUpdate = $attributesUpdate;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->directoryList = $directoryList;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Attributes processing
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validator\ValidateException
     */
    public function attributesProcess(): void
    {
        $importFile = $this->directoryList->getPath('pub') . '/import/declarative_attributes.csv';
        $importAttributes = $this->csvHandler->readCsvFile($importFile);

        $existingAttributes = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('is_user_defined', ['eq' => 1])->getItems();

        if (count($existingAttributes) > 0) {
            foreach ($existingAttributes as $existingAttribute) {
                $csvAttribute = $this->findCsvAttributeByCode(
                    $importAttributes,
                    $existingAttribute->getAttributeCode()
                );

                if ($csvAttribute) {
                    $this->attributesUpdate->update($csvAttribute, $existingAttribute);
                } else {
                    $this->attributeRepository->deleteById($existingAttribute->getAttributeId());
                }
            }
        }

        foreach ($importAttributes as $importAttribute) {
            $existingAttribute = $this->checkIfAttributeExists($importAttribute);
            if (!$existingAttribute) {
                $this->attributesCreate->create($importAttribute);
            }
        }
    }

    /**
     * Validate if attribute exists
     *
     * @param array $importAttributes
     * @return false|\Magento\Eav\Api\Data\AttributeInterface
     */
    protected function checkIfAttributeExists($importAttributes)
    {
        try {
            return $this->attributeRepository->get(
                self::PRODUCT_ENTITY_TYPE_CODE,
                $importAttributes['attribute_code']
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException) {
            return false;
        }
    }

    /**
     * Find a CSV attribute by attribute code.
     *
     * @param array  $importAttributes
     * @param string $attributeCode
     * @return array|bool
     */
    protected function findCsvAttributeByCode(array $importAttributes, string $attributeCode)
    {
        foreach ($importAttributes as $importAttribute) {
            if ($importAttribute['attribute_code'] === $attributeCode) {
                return $importAttribute;
            }
        }

        return false;
    }
}
