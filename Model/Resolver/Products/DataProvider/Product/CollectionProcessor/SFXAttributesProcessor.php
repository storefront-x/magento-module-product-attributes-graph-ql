<?php
declare(strict_types=1);

namespace StorefrontX\ProductAttributesGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use StorefrontX\ProductAttributesGraphQl\Model\IgnoredFields;

class SFXAttributesProcessor implements CollectionProcessorInterface
{
    private IgnoredFields $ignoredFields;

    public const SFX_ATTR_KEY = 'sfx_attributes';

    public function __construct(
        IgnoredFields $ignoredFields
    ) {
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
            $ignoredFields = $this->ignoredFields->getIgnoredFields();
            foreach ($ignoredFields as $field) {
                $collection->removeAttributeToSelect($field);
            }
        }

        return $collection;
    }
}
