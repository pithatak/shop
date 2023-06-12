<?php

namespace App\Admin;

use App\Entity\Product;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

final class ReviewAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('user', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'username',
        ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
            ])
            ->add('counter', ChoiceType::class, [
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'attr' => ['class' => 'input-select'],
                'label' => 'Chose you`re rating',
            ])
            ->add('comment')
            ->add('date', DateTimeType::class, [
                'data' => new \DateTime(), // Установка сегодняшней даты
            ]);
        ;

    }

//    protected function configureDatagridFilters(DatagridMapper $datagrid): void
//    {
//        $datagrid->add('name');
//    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('user.username')
            ->add('product.name')
            ->add('counter')
            ->add('comment');
           
    }

//    protected function configureShowFields(ShowMapper $show): void
//    {
//        $show->add('name');
//    }
}