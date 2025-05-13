<?php

namespace App\Infrastructure\Persistence\Category;

use App\Domain\Entity\Category;
use App\Domain\Interfaces\AccountRepository;
use App\Domain\Interfaces\CategoryReposiotry;
use App\Infrastructure\Persistence\PersistenceRepository;

class PdoCategoryRepository extends PersistenceRepository implements CategoryReposiotry
{

    public function createCategory(Category $category): Category
    {
        // TODO: Implement createCategory() method.
    }

    public function updateCategory(Category $category): Category
    {
        // TODO: Implement updateCategory() method.
    }

    public function deleteCategory(Category $category): void
    {
        // TODO: Implement deleteCategory() method.
    }

    public function findCategoryById(int $id): ?Category
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            return new Category(
                $result['id'],
                $result['name'],
            );
        }
        return null;
    }

    public function findAllCategories(): ?array
    {
        // TODO: Implement findAllCategories() method.
    }

    public function findCategoryByName(string $name): ?array
    {
        // TODO: Implement findCategoryByName() method.
    }
}