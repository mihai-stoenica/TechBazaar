<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminSecurityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $customer;
    private User $admin;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        $userRepository = $this->entityManager->getRepository(User::class);
        foreach ($userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $this->customer = (new User())->setEmail('customer@test.com');
        $this->customer->setRoles(['ROLE_USER']);
        $this->customer->setPassword($passwordHasher->hashPassword($this->customer, 'customer123'));
        $this->entityManager->persist($this->customer);

        $this->admin = (new User())->setEmail('admin@test.com');
        $this->admin->setRoles(['ROLE_ADMIN']);
        $this->admin->setPassword($passwordHasher->hashPassword($this->admin, 'admin123'));
        $this->entityManager->persist($this->admin);

        $this->entityManager->flush();
    }

    public function testAnonymousUserRedirectedFromAdmin(): void
    {
        $this->client->request('GET', '/admin');
        self::assertResponseRedirects('/login');
    }

    public function testCustomerGetsAccessDeniedOnAdmin(): void
    {
        $this->client->loginUser($this->customer);

        $this->client->request('GET', '/admin');
        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminGetsAccessGrantedOnAdmin(): void
    {
        $this->client->loginUser($this->admin);

        $this->client->request('GET', '/admin');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Admin Dashboard');
        self::assertSelectorTextContains('p', 'admin@test.com');
    }
}
