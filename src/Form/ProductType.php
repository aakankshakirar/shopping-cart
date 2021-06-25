<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('my_file', FileType::class, [
                'mapped' => false,
                'label' => 'Product Image',
                'required' => false,
                'attr' => [
                    'class' => 'form-control my-3'
                ]
            ])
            ->add('Title', TextType::class, [
                'label' => 'Title',
                'help' => 'Please use the significant product title',
                'required' => true,
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Ex. Philips Headphone'
                ]
            ])
            ->add('category', EntityType::class, [
                'multiple' => true,
                'class' => 'App\Entity\Category',
                'attr' => [
                    'class' => 'form-control my-3'
                ]
            ])
            ->add('price', TextType::class, [
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Ex. 500'
                ]
            ])
            ->add('stock', TextType::class, [
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Ex. 10'
                ]
            ])
            ->add('description', CKEditorType::class, [
                'attr' => [
                    'class' => 'form-control my-3',
                    'placeholder' => 'Write more details about product'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
