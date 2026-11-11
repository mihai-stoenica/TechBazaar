<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationControllerTest extends WebTestCase
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
    }

    public function testRegisterPageRendersSuccessfully(): void
    {
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Create your account');
    }

    public function testSuccessfulRegistration(): void
    {
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Register Account', [
            'registration_form[email]' => 'newuser@example.com',
            'registration_form[plainPassword]' => 'secretPassword123',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.bg-green-50', 'Registration successful! You can now log in.');

        // Verify entity in database
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'newuser@example.com']);
        self::assertNotNull($user);
        self::assertContains('ROLE_USER', $user->getRoles());
        self::assertNotEquals('secretPassword123', $user->getPassword());

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get('security.user_password_hasher');
        self::assertTrue($hasher->isPasswordValid($user, 'secretPassword123'));
    }

    public function testRegistrationFailsWithShortPassword(): void
    {
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Register Account', [
            'registration_form[email]' => 'shortpass@example.com',
            'registration_form[plainPassword]' => '123',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="registration_form"]', 'Your password should be at least 6 characters');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'shortpass@example.com']);
        self::assertNull($user);
    }

    public function testRegistrationFailsWithDuplicateEmail(): void
    {
        // Pre-create an existing user
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get('security.user_password_hasher');
        $existing = (new User())->setEmail('duplicate@example.com');
        $existing->setPassword($hasher->hashPassword($existing, 'pass123456'));
        $this->entityManager->persist($existing);
        $this->entityManager->flush();

        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Register Account', [
            'registration_form[email]' => 'duplicate@example.com',
            'registration_form[plainPassword]' => 'anotherPassword',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name="registration_form"]', 'There is already an account with this email address');
    }
}
