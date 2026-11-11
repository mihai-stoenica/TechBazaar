<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

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

        // Customer user
        $customer = (new User())->setEmail('customer@example.com');
        $customer->setRoles(['ROLE_USER']);
        $customer->setPassword($passwordHasher->hashPassword($customer, 'password123'));
        $this->entityManager->persist($customer);

        // Admin user
        $admin = (new User())->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($passwordHasher->hashPassword($admin, 'admin123'));
        $this->entityManager->persist($admin);

        $this->entityManager->flush();
    }

    public function testLoginPageRendersSuccessfully(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Sign in to your account');
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'nonexistent@example.com',
            '_password' => 'wrongpass',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorExists('.alert-danger');
    }

    public function testCustomerCanLoginSuccessfully(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'customer@example.com',
            '_password' => 'password123',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'customer@example.com');
    }

    public function testAdminCanLoginSuccessfully(): void
    {
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'admin@example.com',
            '_password' => 'admin123',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'admin@example.com');
    }

    public function testLogout(): void
    {
        // Login first
        $this->client->request('GET', '/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'customer@example.com',
            '_password' => 'password123',
        ]);
        $this->client->followRedirect();

        // Perform logout
        $this->client->request('GET', '/logout');
        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        // Verify user is no longer logged in
        self::assertSelectorTextContains('nav', 'Log In');
    }
}
