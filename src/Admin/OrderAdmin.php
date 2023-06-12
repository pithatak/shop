<?php

namespace App\Admin;


use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class OrderAdmin extends AbstractAdmin

{
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('firstName')
            ->add('paidFor');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id')
            ->addIdentifier('FirstName')
            ->add('paidFor')
            ->add('date')
            ->add('totalPrice')
            ->add('user.username');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id')
            ->add('name');
    }
}