<?php

namespace App\EventSubscriber;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $flash;
    public function __construct(UrlGeneratorInterface $urlGenerator, FlashBagInterface $flash)
    {
        $this->urlGenerator = $urlGenerator;
        $this->flash = $flash;

    }

    public function onLogoutEvent(LogoutEvent $event)
    {
        $this->flash->add(
            'success', 
            $event->getToken()->getUser()->getFullName().' vous êtes déconnecté avec succès.');

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_home_page')));

    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
