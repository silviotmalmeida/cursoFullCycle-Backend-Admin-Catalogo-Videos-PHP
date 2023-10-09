<?php

namespace Core\UseCase\DTO\Category\InsertCategory;

class InsertCategoryInputDto
{
    public function __construct(
        public string $name,
        public string $description = '',
        public bool $isActive = true,
    ) {
    }
}
