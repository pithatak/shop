<?php

namespace App\Admin;

use App\Entity\Category;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ProductAdmin extends AbstractAdmin
{

    private $container;

    public function __construct($code, $class, $baseControllerName, ContainerInterface $container)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->container = $container;
    }

    protected function configureDashboardActions(array $actions): array
    {
        $actions['scrape_dashumiaocoin'] = [
            "label" => "Получить дані з сайта dashumiaocoin",
            "translation_domain" => "SonataAdminBundle",
            "url" => "/scrape/Dashumiaocoin",
            "icon" => "fas fa-list"
        ];
        $actions['scrape_amazon'] = [
            "label" => "Получить дані з сайта Amazon",
            "translation_domain" => "SonataAdminBundle",
            "url" => "/scrape/Amazon",
            "icon" => "fas fa-list"
        ];
        $actions['scrape_prom'] = [
            "label" => "Получить дані з сайта Prom",
            "translation_domain" => "SonataAdminBundle",
            "url" => "/scrape/Prom",
            "icon" => "fas fa-list"
        ];
         return parent::configureDashboardActions($actions); // TODO: Change the autogenerated stub
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name', TextType::class)
             ->add('description')
             ->add('price')
             ->add('discount')
             ->add('category', EntityType::class, [
                 'class' => Category::class,
                 'choice_label' => 'name',
             ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('brochure', FileType::class, [
                'label' => 'Brochure (PDF file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
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
             ->add('category.name')
             ->add('user.email');
    }

    public function postPersist(object $object): void
    {
        $brochureFile = $this->getRequest()->files->all();

        foreach ($brochureFile as $key => $bF ){
            foreach ($bF as $b ){
                if($b){

                    $originalFilename = pathinfo($b->getClientOriginalName(), PATHINFO_FILENAME);

                    $slug = strtolower($originalFilename);
                    $slug = preg_replace('/[\s_]+/', '-', $slug);
                    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                    $safeFilename = trim($slug, '-');

                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $b->guessExtension();

                    try {
                        $b->move(
                            './uploads/brochures/',
                            $newFilename
                        );
                    } catch (FileException $e) {

                    }

                    $object->setBrochureFilename($newFilename);
                    $em = $this->container->get('doctrine')->getManager();

                    $em->persist($object);
                    $em->flush();
                }
            }
        }

        parent::postPersist($object); // TODO: Change the autogenerated stub
    }

    public function preUpdate(object $object): void
    {
        $brochureFile = $this->getRequest()->files->all();

        foreach ($brochureFile as $key => $bF ){
            foreach ($bF as $b ){
                if($b){
                    $originalFilename = pathinfo($b->getClientOriginalName(), PATHINFO_FILENAME);

                    $slug = strtolower($originalFilename);

                    $slug = preg_replace('/[\s_]+/', '-', $slug);

                    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

                    $safeFilename = trim($slug, '-');

                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $b->guessExtension();

                    try {
                        $b->move('./uploads/brochures/', $newFilename);
                    } catch (FileException $e) {

                    }

                    $object->setBrochureFilename($newFilename);
                    $em = $this->container->get('doctrine')->getManager();

                    $em->persist($object);
                    $em->flush();

                }
            }
        }

        parent::preUpdate($object); // TODO: Change the autogenerated stub
    }
//    protected function configureShowFields(ShowMapper $show): void
//    {
//        $show->add('name') ;
//    }

    public function preValidate( object $object): void
    {
        parent::preValidate($object); // TODO: Change the autogenerated stub
    }
}