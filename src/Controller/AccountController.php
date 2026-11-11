<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account', name: 'app_account_')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/addresses', name: 'addresses', methods: ['GET', 'POST'])]
    public function addresses(
        Request $request,
        EntityManagerInterface $entityManager,
        AddressRepository $addressRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $address = new Address();
        $address->setUser($user);
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Delivery address saved successfully!');

            return $this->redirectToRoute('app_account_addresses');
        }

        $addresses = $addressRepository->findByUser($user);

        return $this->render('account/addresses.html.twig', [
            'addresses' => $addresses,
            'addressForm' => $form,
        ]);
    }

    #[Route('/addresses/{id}/delete', name: 'address_delete', methods: ['POST'])]
    public function deleteAddress(
        Address $address,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($address->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have permission to delete this address.');
        }

        if ($this->isCsrfTokenValid('delete-address-' . $address->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();
            $this->addFlash('success', 'Delivery address removed.');
        }

        return $this->redirectToRoute('app_account_addresses');
    }
}
