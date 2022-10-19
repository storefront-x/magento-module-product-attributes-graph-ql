<?php
declare(strict_types=1);

namespace StorefrontX\ProductAttributesGraphQl\Model;

class IgnoredFields
{
    private array $ignoredFields;

    public function __construct(
        array $ignoredFields = []
    ) {
        $this->ignoredFields = $ignoredFields;
    }

    public function getIgnoredFields(): array
    {
        return $this->ignoredFields;
    }
}
