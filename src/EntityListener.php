<?php

namespace Mapado\DoctrineBlenderBundle;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Mapado\DoctrineBlender\ObjectBlender;

class EntityListener
{
    /**
     * container
     *
     * @var ContainerInterface
     * @access private
     */
    private $container;

    /**
     * configuration
     *
     * @var array
     * @access private
     */
    private $configuration;

    /**
     * blender
     *
     * @var ObjectBlender
     * @access private
     */
    private $blender;

    /**
     * registeredManagers
     *
     * @var array
     * @access private
     */
    private $registeredManagers;

    /**
     * __construct
     *
     * @param ContainerInterface $container
     * @param array $configuration
     * @access public
     */
    public function __construct(ContainerInterface $container, array $configuration)
    {
        $this->container = $container;
        $this->configuration = $configuration;

        $this->blender = new ObjectBlender;
        $this->registeredManagers = [];
    }

    /**
     * Registered the postLoad calls for wanted classes
     *
     * @param LoadClassMetadataEventArgs $event
     * @access public
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $className = $event->getClassMetadata()->getName();

        if (!isset($this->registeredManagers[$className])) {
            foreach ($this->configuration as $config) {
                if ($className == $config['classname']) {
                    $sourceObjectManager = $this->container->get($config['source_object_manager']);

                    foreach ($config['references'] as $propertyName => $reference) {
                        $refIdGetter = !empty($reference['reference_id_getter']) ? $reference['reference_id_getter'] : null;
                        $refSetter = !empty($reference['reference_setter']) ? $reference['reference_setter'] : null;
                        $externalAssociation = new \Mapado\DoctrineBlender\ExternalAssociation(
                            $sourceObjectManager,
                            $className,
                            $propertyName,
                            $this->container->get($reference['reference_object_manager']),
                            $reference['reference_class'],
                            $refIdGetter,
                            $refSetter
                        );


                        $this->blender->mapExternalAssociation($externalAssociation);
                    }
                }
            }

            $this->registeredManagers[$className] = true;
        }
    }
}
