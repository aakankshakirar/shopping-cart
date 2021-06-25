<?php

namespace App\Form;

use App\Entity\OrderItem;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('usermeta', UserMetaType::class)
            ->add('order', OrderType::class)
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'onPreSetData']
            )
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                [$this, 'onPostSetData']
    );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function onPostSetData(FormEvent $event): void
    {


    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $cart = $data['cart'];
        $order = $data['order'];
        $em = $data['em'];

        $total_amount = 0;
        foreach ($cart as $key => $item) {
            $total_amount += $item['subtotal'];

            // For adding order items
            $orderItem = new OrderItem();
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setAmount($item['subtotal']);
            $orderItem->setParentOrder($order);

            $product = $em->getRepository(Product::class)->find($item['id']);
            $orderItem->setProduct($product);

            $order->addOrderItem($orderItem);
        }
        $data['order']->setTotalAmount($total_amount);
        $data['order']->setUser($data['usermeta']->getUser());
    }
}
