<?php

namespace App\Fixtures;

use App\Entity\Address;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

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
            $product->setSpecs(['Display' => '6.1 in', 'RAM' => '8 GB', 'Storage' => '128 GB']);

            $manager->persist($product);
        }

        $customer = new User();
        $customer->setEmail('customer@techbazaar.com');
        $customer->setRoles(['ROLE_USER']);
        $customer->setPassword(
            $this->passwordHasher->hashPassword($customer, 'customer123')
        );

        $customerAddress = new Address();
        $customerAddress->setStreet('Strada Mihai Eminescu nr. 42');
        $customerAddress->setCity('Bucuresti');
        $customerAddress->setPhone('+40721000111');
        $customer->addAddress($customerAddress);

        $manager->persist($customer);

        $admin = new User();
        $admin->setEmail('admin@techbazaar.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );

        $adminAddress = new Address();
        $adminAddress->setStreet('Bulevardul Unirii nr. 1');
        $adminAddress->setCity('Cluj-Napoca');
        $adminAddress->setPhone('+40722000222');
        $admin->addAddress($adminAddress);

        $manager->persist($admin);

        $manager->flush();
    }
}
