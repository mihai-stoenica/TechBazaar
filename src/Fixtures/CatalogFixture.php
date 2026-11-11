<?php

namespace App\Fixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CatalogFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categoriesData = ['Phones', 'Laptops', 'Accessories', 'Smart home', 'Audio'];
        $categories = [];

        foreach ($categoriesData as $catName) {
            $category = new Category();
            $category->setName($catName);
            $category->setSlug(strtolower(str_replace(' ', '-', $catName)));
            $manager->persist($category);
            $categories[$catName] = $category;
        }

        $manager->flush();

        $productsData = [
            ['name' => 'Phone model A', 'price' => 1299.00, 'stock' => 10, 'category' => 'Phones'],
            ['name' => 'Phone model B', 'price' => 1449.00, 'stock' => 5, 'category' => 'Phones'],
            ['name' => 'Phone model C', 'price' => 1599.00, 'stock' => 2, 'category' => 'Phones'],
            ['name' => 'Phone model D', 'price' => 1749.00, 'stock' => 8, 'category' => 'Phones'],
            ['name' => 'Phone model E', 'price' => 1699.00, 'stock' => 15, 'category' => 'Phones'],
            ['name' => 'Phone model F', 'price' => 1849.00, 'stock' => 3, 'category' => 'Phones'],
            ['name' => 'Phone model G', 'price' => 1999.00, 'stock' => 7, 'category' => 'Phones'],
            ['name' => 'Phone model H', 'price' => 2149.00, 'stock' => 0, 'category' => 'Phones'],
            ['name' => 'Ultrabook Pro 14', 'price' => 4499.00, 'stock' => 4, 'category' => 'Laptops'],
            ['name' => 'Gaming Laptop X', 'price' => 6299.00, 'stock' => 2, 'category' => 'Laptops'],
            ['name' => 'Wireless ANC Earbuds', 'price' => 499.00, 'stock' => 25, 'category' => 'Audio'],
            ['name' => 'Smart Hub Speaker', 'price' => 349.00, 'stock' => 12, 'category' => 'Smart home'],
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);
            $product->setCategory($categories[$data['category']]);
            $product->setSpecs(['Brand' => 'TechBazaar Spec', 'Warranty' => '24 months']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
