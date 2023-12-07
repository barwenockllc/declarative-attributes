<?php
/**
 * @author Barwenock
 * @copyright Copyright (c) Barwenock
 * @package Declarative Attributes Import for Magento 2
 */

declare(strict_types=1);

namespace Barwenock\DeclarativeAttributes\Test\Unit\Model\Attributes;

class AttributesManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement
     */
    private $attributesManagement;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $csvHandlerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attributesCreateMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attributesUpdateMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryListMock;

    protected function setUp(): void
    {
        $this->csvHandlerMock = $this
            ->createMock(\Barwenock\DeclarativeAttributes\Model\File\CsvHandler::class);
        $this->attributesCreateMock = $this
            ->createMock(\Barwenock\DeclarativeAttributes\Model\Attributes\AttributesCreate::class);
        $this->attributesUpdateMock = $this
            ->createMock(\Barwenock\DeclarativeAttributes\Model\Attributes\AttributesUpdate::class);
        $this->attributeRepositoryMock = $this
            ->createMock(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this
            ->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->directoryListMock = $this
            ->createMock(\Magento\Framework\Filesystem\DirectoryList::class);
        $this->attributeCollectionFactoryMock = $this
            ->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->attributesManagement = new \Barwenock\DeclarativeAttributes\Model\Attributes\AttributesManagement(
            $this->csvHandlerMock,
            $this->attributesCreateMock,
            $this->attributesUpdateMock,
            $this->attributeRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->directoryListMock,
            $this->attributeCollectionFactoryMock
        );
    }

    public function testAttributesProcess(): void
    {
        // Mock data
        $importAttributes = [['attribute_code' => 'brand', 'label' => 'Brand', 'backend' => '', 'frontend' => '',
            'source' => '', 'visible' => 1, 'user_defined' => 1, 'searchable' => 1, 'filterable' => 1,
            'visible_on_front' => 1, 'used_in_product_listing' => 1, 'type' => 'select',
            'options' => 'None|DK2|SnowBear|Currahee Trailers', 'input' => '', 'required' => 0, 'global' => 1,
            'group' => 'Main-Pre', 'attribute_set' => 'Default']];

        $importFile = 'pub/import/declarative_attributes.csv';

        // Mock expectations
        $this->directoryListMock->expects($this->once())->method('getPath')->willReturn('pub');
        $this->csvHandlerMock
            ->expects($this->once())
            ->method('readCsvFile')
            ->with($importFile)
            ->willReturn($importAttributes);

        $attributeCollectionMock = $this
            ->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFieldToFilter', 'getItems'])
            ->getMock();

        $attributeCollectionMock->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $attributeCollectionMock->method('getItems')->willReturn([]);

        $this->attributeCollectionFactoryMock->method('create')->willReturn($attributeCollectionMock);

        // Mock an existing attribute
        $existingAttribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $this->attributeRepositoryMock->expects($this->once())->method('get')->willReturn($existingAttribute);

        // Expectations for create method
        $this->attributesCreateMock->expects($this->never())->method('create');

        // Run the method to be tested
        $this->attributesManagement->attributesProcess();
    }
}
