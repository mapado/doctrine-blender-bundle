<?php

namespace Mapado\DoctrineBlenderBundle;

use Doctrine\Common\EventArgs;
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
     * registeredClasses
     *
     * @var array
     * @access private
     */
    private $registeredClasses;

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
        $this->registeredClasses = [];
        $this->registeredManagers = [];
    }

    /**
     * Registered the postLoad calls for wanted classes
     *
     * @param EventArgs $event
     * @access public
     * @return void
     */
    public function loadClassMetadata(EventArgs $event)
    {
        if ($event instanceof LoadClassMetadataEventArgs) {
            $className = $event->getClassMetadata()->getName();
            $this->loadExternalAssociationByClassName($className);
        } elseif (method_exists($event, 'getObjectManager')) {
            $this->loadExternalAssociationByManager($event->getObjectManager());
        } else {
            $msg = '$event must implements `Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs` ' .
                ' or have a `getObjectManager` method.';
            throw new \InvalidArgumentException($msg);
        }
    }

    private function loadExternalAssociationByManager($objectManager)
    {
        if (in_array($objectManager, $this->registeredManagers)) {
            return;
        }

        foreach ($this->configuration as $config) {
            $sourceObjectManager = $this->container->get($config['source_object_manager']);
            if ($sourceObjectManager === $objectManager) {
                $this->loadExternalAssociation($config, $sourceObjectManager);
            }
        }
        $this->registeredManagers[] = $sourceObjectManager;
    }

    /**
     * loadExternalAssociationByClassName
     *
     * @param string $className
     * @access private
     * @return void
     */
    private function loadExternalAssociationByClassName($className)
    {
        if (isset($this->registeredClasses[$className])) {
            return;
        }

        foreach ($this->configuration as $config) {
            if ($className == $config['classname']) {
                $sourceObjectManager = $this->container->get($config['source_object_manager']);
                $this->loadExternalAssociation($config, $sourceObjectManager);
            }
        }

        $this->registeredClasses[$className] = true;
    }

    /**
     * loadExternalAssociation
     *
     * @param array $config
     * @param object $sourceObjectManager
     * @access private
     * @return void
     */
    private function loadExternalAssociation(array $config, $sourceObjectManager)
    {
        foreach ($config['references'] as $propertyName => $reference) {
            $refIdGetter = !empty($reference['reference_id_getter']) ? $reference['reference_id_getter'] : null;
            $refSetter = !empty($reference['reference_setter']) ? $reference['reference_setter'] : null;
            $externalAssociation = new \Mapado\DoctrineBlender\ExternalAssociation(
                $sourceObjectManager,
                $config['classname'],
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
