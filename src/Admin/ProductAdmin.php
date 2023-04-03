<?php

namespace App\Admin;

use App\Entity\Tag;
use App\Entity\Category;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class ProductAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class)
             ->add('description')
            ->add('price')
            ->add('discount')
             ->add('category', EntityType::class, [
                 'class' => Category::class,
                 'choice_label' => 'name',
             ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('name')
        ->add('description')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id')
             ->add('name')
             ->add('description')
            ->add('category.name');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('name') ;
    }
}