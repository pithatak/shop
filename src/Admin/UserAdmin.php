<?php

namespace App\Admin;

use App\Entity\Product;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType ;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

final class UserAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('phone')
            ->add('products', CollectionType::class, [
                'type_options' => [
                    // Prevents the "Delete" option from being displayed
                    'delete' => false,
                    'delete_options' => [
                        // You may otherwise choose to put the field but hide it
                        // In that case, you need to fill in the options as well
                        'type_options' => [
                            'mapped'   => false,
                            'required' => false,
                        ]
                    ]
                ]
            ], [
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position',
            ])
        ;

    }

//    protected function configureDatagridFilters(DatagridMapper $datagrid): void
//    {
//        $datagrid->add('name');
//    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('email')
             ->add('phone')
             ->add('products', EntityType::class, [
                 'class' => Product::class,
                 'choice_label' => 'name',
                 'associated_property' => 'name'
             ]);

    }

//    protected function configureShowFields(ShowMapper $show): void
//    {
//        $show->add('name');
//    }
}