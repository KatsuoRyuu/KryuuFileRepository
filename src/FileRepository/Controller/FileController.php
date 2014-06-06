<?php

namespace FileRepository\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class FileController extends AbstractActionController 
{
    /**
     * Get the file from FileRepository and offer it for download 
     */
    public function downloadAction() 
    {
        $filelib = $this->getServiceLocator()->get('FileRepository');
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');

        $file = $filelib->getFileById($id);
        $filePath = $filelib->getRoot() . '/' .$file->getSavePath();

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file->getMimetype());
        header('Content-Disposition: attachment; filename=' . $file->getName());
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $file->getSize());
        ob_clean();
        flush();
        readfile($filePath);
        exit;
    }
}
