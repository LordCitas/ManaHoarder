<?php

namespace App\Security;

use App\Entity\DeckList;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DeckListVoter extends Voter
{
    public const MANAGE = 'DECKLIST_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::MANAGE && $subject instanceof DeckList;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!is_object($user)) {
            return false;
        }

        /** @var DeckList $deckList */
        $deckList = $subject;

        return $deckList->getOwner()?->getId() === $user->getId();
    }
}
