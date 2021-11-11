<?php

namespace App\Security\Voter;

use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GlobalVoter extends AbstractAppVoter
{
    protected string $module = 'global';

    protected function supports($attribute, $subject): bool
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && 'global' == $subject;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->isGranted($attribute);
    }
}
