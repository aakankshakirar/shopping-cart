<?php

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\Testing;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TestingVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const DELETE = "delete";

    protected function supports(string $attribute, $subject): bool
    {
        if(!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]) && !$subject instanceof Category) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        if($subject->getId() == '3')
        {
            return false;
        }
        return true;
    }
}
