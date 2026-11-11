<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Category;
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
        // 1. Seed Categories (for Catalog A1 compatibility)
        $categoriesData = [
            ['name' => 'Smartphones', 'slug' => 'smartphones'],
            ['name' => 'Laptops & Computers', 'slug' => 'laptops-computers'],
            ['name' => 'Audio & Headphones', 'slug' => 'audio-headphones'],
            ['name' => 'Wearables & Smartwatches', 'slug' => 'wearables-smartwatches'],
        ];

        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $manager->persist($category);
        }

        // 2. Demo Customer User (ROLE_USER)
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

        // 3. Demo Admin User (ROLE_ADMIN)
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
