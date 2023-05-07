<?php

namespace App\Admin;

use phpDocumentor\Reflection\Types\Collection;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Product;
final class CategoryAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid->add('id')
                 ->add('name');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id')
             ->addIdentifier('name')
             ->add('products', Collection::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'associated_property' => 'name',
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id')
             ->add('name');
    }
}