<?php

namespace App\Form;

use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\UserMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'First Name'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Last Name'
                ]
            ])
            ->add('contact', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Contact No'
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Your complete address'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'City'
                ]
            ])
            ->add('pincode', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Pin Code'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserMeta::class,
        ]);
    }
}
