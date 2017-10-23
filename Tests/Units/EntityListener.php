<?php

namespace Mapado\DoctrineBlenderBundle\Tests\Units;

use atoum;

/**
 * Class EntityListener
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class EntityListener extends atoum
{
    private $blender;

    private $container;

    private $defaultConfiguration;

    public function beforeTestMethod($method)
    {
        $this->blender = new \mock\Mapado\DoctrineBlender\ObjectBlender;
        $this->container = new \mock\Symfony\Component\DependencyInjection\ContainerInterface;
        $this->container->getMockController()->get = function ($ref) {
            switch ($ref) {
                case 'doctrine.orm.order_entity_manager':
                    $entityManager = new \mock\Doctrine\ORM\EntityManagerInterface;
                    $entityManager->getMockController()->getReference = 'foo';
                    $entityManager->getMockController()->getEventManager = new \mock\Doctrine\Common\EventManager;

                    return $entityManager;
                    break;

                case 'doctrine.orm.customer_entity_manager':
                    return new \mock\Doctrine\ORM\EntityManagerInterface;
                    break;

                // case 'doctrine_mongodb.odm.product_document_manager':
                //     return ;
                //     break;

                // case 'doctrine_mongodb.odm.tag_document_manager':
                //     return ;
                //     break;
                default:
                    throw new \InvalidArgumentException(sprintf('reference "%s" not found', $ref));
                    break;
            }
        };

        $this->defaultConfiguration = [
            'order' => [
                'source_object_manager' => 'doctrine.orm.order_entity_manager',
                'classname' => 'Acme\DemoBundle\Entity\Order',
                'references' => [
                    'customer' => [
                        'reference_id_getter' => 'getCustomerId',
                        'reference_setter' => 'setCustomer',
                        'reference_object_manager' => 'doctrine.orm.customer_entity_manager',
                        'reference_class' => 'Acme\DemoBundle\Entity\Customer',
                    ],

                    // 'product' => [
                    //     'reference_id_getter' => 'getProductId',
                    //     'reference_setter' => 'setProduct',
                    //     'reference_object_manager' => 'doctrine_mongodb.odm.product_document_manager',
                    //     'reference_class' => 'Acme\DemoBundle\Document\Product',
                    // ],

                    // 'tags' => [
                    //     'reference_id_getter' => 'getTagIds',
                    //     'reference_setter' => 'setTags',
                    //     'reference_object_manager' => 'doctrine_mongodb.odm.tag_document_manager',
                    //     'reference_class' => 'Acme\DemoBundle\Document\Tag',
                    // ],
                ],
            ],
        ];
    }

    public function testLoadClassMetadata()
    {
        $this->mockGenerator->orphanize('__construct');
        $metadata = new \mock\Doctrine\Common\Persistence\Mapping\ClassMetadata();
        $this->mockGenerator->orphanize('__construct');
        $event = new \mock\Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs();
        $event->getMockController()->getClassMetadata = $metadata;

        $this
            ->given($this->newTestedInstance($this->container, $this->defaultConfiguration, $this->blender))
                ->and($metadata->getMockController()->getName = 'order')
            ->then
                ->if($this->testedInstance->loadClassMetadata($event))
            // ->then
            //     ->mock($this->blender)
            //         ->call('mapExternalAssociation')
            //             ->never()

            ->given($metadata->getMockController()->getName = 'Acme\DemoBundle\Entity\Order')
            ->if($this->testedInstance->loadClassMetadata($event))
            ->then
                ->mock($this->blender)
                    ->call('mapExternalAssociation')
                        ->once()
        ;
    }
}
