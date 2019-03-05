<?php

namespace App\Event;

use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLocaleSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
                [
                    'onInteractiveLogin',
                    15
                ]
            ]
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUSer();

        $this->session->set('_locale', $user->getPreferences()->getLocale());
    }
}