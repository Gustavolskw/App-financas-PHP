<?php

namespace App\Domain\Interfaces;

use App\Domain\Entity\Category;

interface CategoryReposiotry
{

    public function createCategory(Category $category): Category;
    public function updateCategory(Category $category): Category;
    public function deleteCategory(Category $category): void;
    public function findCategoryById(int $id): ?Category;
    public function findAllCategories(): ?array;
    public function findCategoryByName(string $name): ?array;

}