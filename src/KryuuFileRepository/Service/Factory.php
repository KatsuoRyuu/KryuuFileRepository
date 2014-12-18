<?php

namespace KryuuFileRepository\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use KryuuFileRepository\Manager;

/**
 * KryuuFileRepository service manager factory
 */
class Factory implements FactoryInterface 
{
    /**
     * Factory method for KryuuFileRepository Manager service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \KryuuFileRepository\Manager
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('config');
        $params = $config['KryuuFileRepository']['params'];
        $em = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $eventManager = $e->getApplication()->getServiceManager()->get('EventManager');
        $manager = new Manager($params, $em, $eventManager);
        return $manager;
    }
}