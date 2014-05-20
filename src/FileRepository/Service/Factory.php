<?php

namespace FileRepository\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use FileRepository\Manager;

/**
 * FileRepository service manager factory
 */
class Factory implements FactoryInterface 
{
    /**
     * Factory method for FileRepository Manager service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \FileRepository\Manager
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('config');
        $params = $config['FileRepository']['params'];
        $em = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $manager = new Manager($params, $em);
        return $manager;
    }
}