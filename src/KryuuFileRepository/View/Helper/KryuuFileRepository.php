<?php

namespace KryuuFileRepository\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Http\Request;
use KryuuFileRepository\Entity\File;

class KryuuFileRepository extends AbstractHelper 
{
    /**
     * @var KryuuFileRepository Service
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
     * @return KryuuFileRepository\Entity\File
     */
    public function __invoke($id) {
        $file = $this->service->getFileById($id);
        $file = $this->generateDynamicParameters($file);
        return $file;
    }

    /**
     * Add dynamic data into the entity
     * 
     * @param KryuuFileRepository\Entity\File $file
     * @param Array $linkOptions
     * @return KryuuFileRepository\Entity\File
     */
    private function generateDynamicParameters(File $file) {
        $urlHelper = $this->getView()->plugin('url');

        $file->setUrl(
                $urlHelper('KryuuFileRepository') . '/' . $file->getId()
        );

        return $file;
    }

    /**
     * Get KryuuFileRepository service.
     *
     * @return $this->service
     */
    public function getService() {
        return $this->service;
    }

    /**
     * Set KryuuFileRepository service.
     *
     * @param $service
     */
    public function setService($service) {
        $this->service = $service;
        return $this;
    }

    /**
     * Get KryuuFileRepository params.
     *
     * @return $this->params
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Set KryuuFileRepository params.
     *
     * @param array $params
     */
    public function setParams(Array $params) {
        $this->params = $params;
        return $this;
    }
}