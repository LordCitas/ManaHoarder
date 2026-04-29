<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\DeckList;
use App\Entity\DeckListItem;
use App\Form\DeckListAddCardType;
use App\Form\DeckListType;
use App\Repository\DeckListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lists')]
class DeckListController extends AbstractController
{
    #[Route('', name: 'deck_list_index', methods: ['GET'])]
    public function index(DeckListRepository $deckListRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('deck_list/index.html.twig', [
            'lists' => $deckListRepository->findByOwner($user),
        ]);
    }

    #[Route('/new', name: 'deck_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $deckList = new DeckList();
        $deckList->setOwner($user);

        $form = $this->createForm(DeckListType::class, $deckList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($deckList);
            $em->flush();

            return $this->redirectToRoute('deck_list_show', ['id' => $deckList->getId()]);
        }

        return $this->render('deck_list/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'deck_list_show', methods: ['GET'])]
    public function show(DeckList $deckList, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->denyAccessUnlessGranted('DECKLIST_MANAGE', $deckList);

        $view = $request->query->get('view', 'grid');
        if (!in_array($view, ['grid', 'compact'], true)) {
            $view = 'grid';
        }

        $addForm = $this->createForm(DeckListAddCardType::class);

        return $this->render('deck_list/show.html.twig', [
            'deckList' => $deckList,
            'view' => $view,
            'addForm' => $addForm,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'deck_list_edit', methods: ['GET', 'POST'])]
    public function edit(DeckList $deckList, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->denyAccessUnlessGranted('DECKLIST_MANAGE', $deckList);

        $form = $this->createForm(DeckListType::class, $deckList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deckList->touch();
            $em->flush();

            return $this->redirectToRoute('deck_list_show', ['id' => $deckList->getId()]);
        }

        return $this->render('deck_list/edit.html.twig', [
            'deckList' => $deckList,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'deck_list_delete', methods: ['POST'])]
    public function delete(DeckList $deckList, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->denyAccessUnlessGranted('DECKLIST_MANAGE', $deckList);

        if ($this->isCsrfTokenValid('delete_deck_list_'.$deckList->getId(), (string) $request->request->get('_token'))) {
            $em->remove($deckList);
            $em->flush();
        }

        return $this->redirectToRoute('deck_list_index');
    }

    #[Route('/{id<\d+>}/items', name: 'deck_list_add_item', methods: ['POST'])]
    public function addItem(DeckList $deckList, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->denyAccessUnlessGranted('DECKLIST_MANAGE', $deckList);

        $form = $this->createForm(DeckListAddCardType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'No se pudo añadir la carta. Revisa el formulario.');
            return $this->redirectToRoute('deck_list_show', ['id' => $deckList->getId()]);
        }

        $data = $form->getData();
        $cardName = trim((string) ($data['cardName'] ?? ''));
        $quantity = (int) ($data['quantity'] ?? 1);

        // MVP: si no existe la carta, la creamos con placeholder (pensando en futura API)
        $card = $em->getRepository(Card::class)->findOneBy(['name' => $cardName]);
        if (!$card) {
            $card = (new Card())
                ->setName($cardName)
                ->setImageUrl(null)
                ->setExternalId(null);
            $em->persist($card);
        }

        // Si ya existe el item, sumamos
        $existingItem = $em->getRepository(DeckListItem::class)->findOneBy([
            'deckList' => $deckList,
            'card' => $card,
        ]);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $item = (new DeckListItem())
                ->setDeckList($deckList)
                ->setCard($card)
                ->setQuantity($quantity);
            $em->persist($item);
        }

        $deckList->touch();
        $em->flush();

        $this->addFlash('success', 'Carta añadida.');
        return $this->redirectToRoute('deck_list_show', ['id' => $deckList->getId()]);
    }

    #[Route('/{id<\d+>}/items/{itemId<\d+>}/delete', name: 'deck_list_delete_item', methods: ['POST'])]
    public function deleteItem(DeckList $deckList, int $itemId, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->denyAccessUnlessGranted('DECKLIST_MANAGE', $deckList);

        $item = $em->getRepository(DeckListItem::class)->find($itemId);
        if (!$item || $item->getDeckList()?->getId() !== $deckList->getId()) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete_deck_list_item_'.$item->getId(), (string) $request->request->get('_token'))) {
            $em->remove($item);
            $deckList->touch();
            $em->flush();
            $this->addFlash('success', 'Carta eliminada.');
        }

        return $this->redirectToRoute('deck_list_show', ['id' => $deckList->getId()]);
    }
}

