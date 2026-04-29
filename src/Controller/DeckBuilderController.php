<?php

namespace App\Controller;

use App\Repository\DeckListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeckBuilderController extends AbstractController
{
    #[Route('/builder', name: 'deck_builder', methods: ['GET'])]
    public function index(DeckListRepository $deckListRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('deck_builder/index.html.twig', [
            'deckLists' => $deckListRepository->findByOwner($user),
        ]);
    }
}

