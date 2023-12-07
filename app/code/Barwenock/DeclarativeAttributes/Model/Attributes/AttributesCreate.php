<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Model\Attributes;

class AttributesCreate
{
    /**
     * Product entity type code
     * @var string
     */
    public const PRODUCT_ENTITY_TYPE_CODE = 'catalog_product';

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Eav\Model\AttributeSetManagement
     */
    protected $attributeSetManagement;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSetInterfaceFactory
     */
    protected $attributeSetInterfaceFactory;

    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    protected $attributeManagement;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Eav\Model\AttributeSetManagement $attributeSetManagement
     * @param \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetInterfaceFactory
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Model\AttributeSetManagement $attributeSetManagement,
        \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetInterfaceFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavSetup = $eavSetup;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeSetInterfaceFactory = $attributeSetInterfaceFactory;
        $this->attributeManagement = $attributeManagement;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Create attributes logic
     *
     * @param array $attribute
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validator\ValidateException|\Exception
     */
    public function create($attribute)
    {
        try {
            $this->ensureAttributeWithGroupSet($attribute['attribute_set'], $attribute['group']);

            if (!empty($attribute['options'])) {
                $attribute['option']['values'] = $attribute['options'];
            } else {
                unset($attribute['options']);
            }

            $this->eavSetup->addAttribute(
                self::PRODUCT_ENTITY_TYPE_CODE,
                trim($attribute['attribute_code']),
                $attribute
            );

            $this->eavConfig->clear();

            $this->assignAttributeToSet(
                $attribute['attribute_set'],
                $attribute['group'],
                trim($attribute['attribute_code']),
                $attribute['sort_order'] ?? 0
            );
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __($exception->getMessage()),
                $exception,
                $exception->getCode()
            );
        }
    }

    /**
     * Ensures that the specified attribute set and group exist for the given entity type
     *
     * @param string $attributeSetName
     * @param string $attributeGroup
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function ensureAttributeWithGroupSet($attributeSetName, $attributeGroup)
    {
        $entityTypeId = $this->eavSetup->getEntityTypeId(self::PRODUCT_ENTITY_TYPE_CODE);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_type_id', $entityTypeId)
            ->addFilter('attribute_set_name', $attributeSetName)
            ->create();

        $attributeSetRepository = $this->attributeSetRepository->getList($searchCriteria);

        if ($attributeSetRepository->getTotalCount() === 0) {
            $this->attributeSetManagement->create(
                self::PRODUCT_ENTITY_TYPE_CODE,
                $this->attributeSetInterfaceFactory->create()->setAttributeSetName($attributeSetName),
                $this->eavSetup->getDefaultAttributeSetId($entityTypeId)
            );
        }

        $this->eavSetup->addAttributeGroup(
            $entityTypeId,
            $this->eavSetup->getAttributeSetId($entityTypeId, $attributeSetName),
            $attributeGroup
        );
    }

    /**
     * Assigns the specified attribute to the given attribute set and group within the specified entity type
     *
     * @param string $attributeSetName
     * @param string $attributeGroup
     * @param string $attributeCode
     * @param string|int $attributeSortOrder
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function assignAttributeToSet($attributeSetName, $attributeGroup, $attributeCode, $attributeSortOrder)
    {
        $this->attributeManagement->assign(
            self::PRODUCT_ENTITY_TYPE_CODE,
            $this->eavSetup->getAttributeSetId(
                self::PRODUCT_ENTITY_TYPE_CODE,
                $attributeSetName
            ),
            $this->eavSetup->getAttributeGroupId(
                self::PRODUCT_ENTITY_TYPE_CODE,
                $attributeSetName,
                $attributeGroup
            ),
            trim($attributeCode),
            $attributeSortOrder
        );
    }
}
