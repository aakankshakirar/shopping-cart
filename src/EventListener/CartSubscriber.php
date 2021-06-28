<?php

namespace App\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CartSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.

        return [
            RequestEvent::class => 'myCustomMethod'
        ];
    }

    public function myCustomMethod()
    {
        echo 'this is cart subscriber page';
    }

}