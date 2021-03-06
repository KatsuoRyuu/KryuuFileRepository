<?php

namespace FileRepository;

use FileRepository\View\Helper\FileRepository;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'FileRepository' => 'FileRepository\Service\Factory',
            )
        ); 
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'getFileById' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $config = $locator->get('Configuration');
                    $params = $config['FileRepository']['params'];
                    
                    $viewHelper = new View\Helper\FileRepository();
                    $viewHelper->setService($locator->get('FileRepository'));
                    $viewHelper->setParams($params);
                    
                    return $viewHelper;
                },
            ),
        );

    }
}