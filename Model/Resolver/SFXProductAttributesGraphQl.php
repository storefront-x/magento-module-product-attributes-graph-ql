<?php
declare(strict_types=1);

namespace StorefrontX\ProductAttributesGraphQl\Model\Resolver;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;
use StorefrontX\ProductAttributesGraphQl\Model\IgnoredFields;

class SFXProductAttributesGraphQl implements ResolverInterface
{

    protected StoreManagerInterface $storeManager;
    protected LoggerInterface $logger;
    protected array $cachedAttributes = [];
    protected IgnoredFields $ignoredFields;

    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        IgnoredFields $ignoredFields
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->ignoredFields = $ignoredFields;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $attributesArray = [];
        /** @var Product $product */
        $product = $value['model'];

        $attributes = $this->getAttributes($product);
        foreach ($attributes as $key => $attribute) {
            $value = $product->getData($attribute->getAttributeCode());
            if ($attribute->getFrontendInput() === "multiselect") {
                if ($value) {
                    $valueForTransform = explode(",", $value);
                } else {
                    $valueForTransform = [];
                }
            } elseif ($attribute->getFrontendInput() === "select") {
                $valueForTransform = ($value !== null) ? [$value] : null;
            } else {
                $attributesArray[] = [
                    'attribute_code' => $attribute->getAttributeCode(),
                    'frontend_label' => $attribute->getStoreLabel(),
                    'value' => $value,
                    'attribute_options' => null,
                ];
                continue;
            }
            $attributeOptions = [];
            $source = $attribute->getSource();
            if ($valueForTransform) {
                foreach ($valueForTransform as $option) {
                    $attributeOptions[] = [
                        'options_id' => (int)$option,
                        'option_value' => $source->getOptionText($option)
                    ];
                }
            }
            $attributesArray[] = [
                'attribute_code' => $attribute->getAttributeCode(),
                'frontend_label' => $attribute->getStoreLabel(),
                'value' => $value,
                'attribute_options' => $attributeOptions,
            ];

        }
        return $attributesArray;
    }

    /**
     * @param Product $product
     * @return AbstractAttribute[]
     */
    private function getAttributes(
        Product $product
    ): array {
        if (!isset($this->cachedAttributes[$product->getAttributeSetId()])) {
            // TODO replace using deprecated getResource()
            $resource = $product->getResource();
            $attributeObj = $resource->loadAllAttributes($product); // @phpstan-ignore-line
            /** @var AbstractAttribute[] $attributes */
            $attributes = $attributeObj->getSortedAttributes($product->getAttributeSetId());
            foreach ($attributes as $key => $attribute) {
                if (in_array($attribute->getAttributeCode(), $this->ignoredFields->getIgnoredFields())) {
                    unset($attributes[$key]);
                    continue;
                }
                if (!$attribute->getIsVisibleOnFront()) {
                    unset($attributes[$key]);
                    continue;
                }
            }
            $this->cachedAttributes[$product->getAttributeSetId()] = $attributes;
        }
        return $this->cachedAttributes[$product->getAttributeSetId()];
    }
}
