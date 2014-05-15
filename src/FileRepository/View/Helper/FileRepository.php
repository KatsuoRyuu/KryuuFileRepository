<?php

namespace FileRepository\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Http\Request;
use FileRepository\Entity\File;

class FileRepository extends AbstractHelper 
{
    /**
     * @var FileRepository Service
     */
    protected $service;

    /**
     * @var array $params
     */
    protected $params;

    /**
     * Called upon invoke
     * 
     * @param integer $id
     * @return FileRepository\Entity\File
     */
    public function __invoke($id) {
        $file = $this->service->getFileById($id);
        $file = $this->generateDynamicParameters($file);
        return $file;
    }

    /**
     * Add dynamic data into the entity
     * 
     * @param FileRepository\Entity\File $file
     * @param Array $linkOptions
     * @return FileRepository\Entity\File
     */
    private function generateDynamicParameters(File $file) {
        $urlHelper = $this->getView()->plugin('url');

        $file->setUrl(
                $urlHelper('FileRepository') . '/' . $file->getId()
        );

        return $file;
    }

    /**
     * Get FileRepository service.
     *
     * @return $this->service
     */
    public function getService() {
        return $this->service;
    }

    /**
     * Set FileRepository service.
     *
     * @param $service
     */
    public function setService($service) {
        $this->service = $service;
        return $this;
    }

    /**
     * Get FileRepository params.
     *
     * @return $this->params
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Set FileRepository params.
     *
     * @param array $params
     */
    public function setParams(Array $params) {
        $this->params = $params;
        return $this;
    }
}