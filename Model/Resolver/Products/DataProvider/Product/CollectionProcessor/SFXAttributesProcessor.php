<?php
declare(strict_types=1);

namespace StorefrontX\ProductAttributesGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use StorefrontX\ProductAttributesGraphQl\Model\IgnoredFields;

class SFXAttributesProcessor implements CollectionProcessorInterface
{
    private IgnoredFields $ignoredFields;
    private AttributeRepositoryInterface $attributeRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterBuilder $filterBuilder;

    public const SFX_ATTR_KEY = 'sfx_attributes';

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        IgnoredFields $ignoredFields
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->ignoredFields = $ignoredFields;
    }

    /**
     * Process collection to add additional joins, attributes, and clauses to a product collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {

        if (in_array(self::SFX_ATTR_KEY, $attributeNames)) {
            $attributes = $this->getAttributes();
            foreach ($attributes as $attribute) {
                if (!in_array($attribute->getAttributeCode(), $this->ignoredFields->getIgnoredFields())) {
                    $collection->addAttributeToSelect($attribute->getAttributeCode(), true);
                }
            }
        }

        return $collection;
    }

    /**
     * @return AttributeInterface[]
     */
    private function getAttributes(): array
    {
        $attributes = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->searchCriteriaBuilder
                ->addFilter('is_visible_on_front', 1)
                ->create()
        );
        return $attributes->getItems();
    }
}
