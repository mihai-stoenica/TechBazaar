<?php

namespace App\Tests;

use App\Entity\Address;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AddressTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $customer;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        $addressRepository = $this->entityManager->getRepository(Address::class);
        foreach ($addressRepository->findAll() as $address) {
            $this->entityManager->remove($address);
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        foreach ($userRepository->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $this->customer = (new User())->setEmail('customer@test.com');
        $this->customer->setPassword($passwordHasher->hashPassword($this->customer, 'password123'));
        $this->entityManager->persist($this->customer);
        $this->entityManager->flush();
    }

    public function testAnonymousCannotAccessAddresses(): void
    {
        $this->client->request('GET', '/account/addresses');
        self::assertResponseRedirects('/login');
    }

    public function testAuthenticatedCustomerCanViewAndAddAddress(): void
    {
        $this->client->loginUser($this->customer);

        $this->client->request('GET', '/account/addresses');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Saved Delivery Addresses');

        $this->client->submitForm('Save Address', [
            'address[street]' => 'Strada Calea Victoriei 100',
            'address[city]' => 'Bucuresti',
            'address[phone]' => '+40799112233',
        ]);

        self::assertResponseRedirects('/account/addresses');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.bg-green-50', 'Delivery address saved successfully!');
        self::assertSelectorTextContains('main', 'Strada Calea Victoriei 100');
        self::assertSelectorTextContains('main', 'Bucuresti');
        self::assertSelectorTextContains('main', '+40799112233');

        // Check DB
        $savedAddress = $this->entityManager->getRepository(Address::class)->findOneBy(['phone' => '+40799112233']);
        self::assertNotNull($savedAddress);
        self::assertEquals($this->customer->getId(), $savedAddress->getUser()->getId());
    }

    public function testAddressValidationFailsOnEmptyFields(): void
    {
        $this->client->loginUser($this->customer);

        $this->client->request('GET', '/account/addresses');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save Address', [
            'address[street]' => '',
            'address[city]' => '',
            'address[phone]' => '',
        ]);

        self::assertResponseStatusCodeSame(422);
    }
}
