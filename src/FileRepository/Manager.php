<?php

namespace FileRepository;

use FileRepository\Entity\File;
use FileRepository\Entity\Keyword;
use FileRepository\Exception\RuntimeException;

use Doctrine\ORM\Tools\SchemaValidator;

class Manager
{
    /**
     * @var Array 
     */
    protected $params;

    /**            
     * @var Doctrine\ORM\EntityManager
     */                
    protected $em;
    
    /**
     * @var array
     */
    protected $cache;
    
    /**
     * @var FileRepository\Entity\File
     */
    protected $file;
    
    /**
     * @var array
     */
    protected $mime_types = array(

        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',


        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );
    
    /**
     * Set the Module specific configuration parameters
     * 
     * @param Array $params
     * @param Doctrine\ORM\EntityManager $em 
     */
    public function __construct($params, $em) {
        $this->params = $params;
        $this->em = $em;
        $this->cache = array();
    }

    /**
     * Detetcs the mimetype of a file
     * 
     * @param null $sourceFilePath
     * @param int $mode mode 0 = full check, mode 1 = extension check only
     * @return string
     */
    private function getMimeType($sourceFilePath, $mode = 0)
    {
        $fileExtension = explode('.',$sourceFilePath);
        $ext = strtolower(array_pop($fileExtension));
        if (function_exists('mime_content_type') && $mode == 0) {
            $mimetype = mime_content_type($sourceFilePath);
            return $mimetype;
        } elseif (function_exists('finfo_open') && $mode == 0) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $sourceFilePath);
            finfo_close($finfo);
            return $mimetype;
        } elseif (array_key_exists($ext, $this->mime_types)) {
            return $this->mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Get the FileRepository's root folder
     * 
     * @return string 
     */
    public function getRoot() 
    {
        return realpath($this->params['filerepository_folder']);
    }
    
    /**
     * Get the file entity based on ID
     * 
     * @param integer $fileId
     * @return FileRepository\Entity\File 
     * @throws \Exception 
     */
    
    public function getFileById($fileId)
    {
        // Get the entity from cache if available
        if (isset($this->cache[$fileId])) {
            $entity = $this->cache[$fileId];
        } else {
            $entity = $this->em->find('FileRepository\Entity\File', $fileId);
        }
        
        if (!$entity) {
            throw new \Exception('File does not exist.', 404);
        }
        
        // Cache the file entity so we don't have to access db on each call
        // Enables to get multiple entity's properties at different times
        $this->cache[$fileId] = $entity;
        return $entity;
    }
    
    /**
     * Get array of file entities based on given keyword
     * 
     * @param Array $keywords
     * @return Array
     * @throws \Exception 
     */
    public function getFilesByKeywords($keywords)
    {
        // Create unique ID of the array for cache
        $id = md5(serialize($keywords));
        
        // Change all given keywords to lowercase
        $keywords = array_map('strtolower', $keywords );
        
        // Get the entity from cache if available
        if (isset($this->cache[$id])) {
            $entities = $this->cache[$id];
        } else {
            $list = "'" . implode("','", $keywords) . "'";
            
            $q = $this->em->createQuery(
                    "select f from FileRepository\Entity\File f, FileRepository\Entity\Keyword k
                     where k.file = f
                     and k.value in (" . $list . ")"
                    );
            
            $entities = $q->getResult();
            
            return $entities;
        }
        
        // Cache the file entity so we don't have to access db on each call
        // Enables to get multiple entity's properties at different times
        $this->cache[$id] = $entities;
        return $entities;
    }
    
    /**
     * Save file to FileRepository database
     * 
     * @param string $sourceFilePath
     * @param array $keywords
     * @return FileRepository\Entity\File
     * @throws \Exception 
     */
    public function save($sourceFilePath, $fileName = null, array $keywords = array())
    {
        if ($fileName == null) {
            $fileName = basename($sourceFilePath);
        }
        //$mimetype = mime_content_type($sourceFilePath);
        $mimetype = $this->getMimeType($sourceFilePath);
        $hash     = md5(microtime(true) . $fileName);
        $savePath = substr($hash,0,1).'/'.substr($hash,1,1).'/';

        $this->file = new File();
        $this->file->setName($fileName);
        $this->file->setMimetype($mimetype);
        $this->file->setSize(filesize($sourceFilePath));
        $this->file->setIsActive($this->params['default_is_active']);
        $this->file->setSavepath($savePath . $hash);
        $this->addKeywordsToFile($keywords);
        
        $this->em->persist($this->file);
        $this->em->flush();
        
        $absolutePath = $this->params['filerepository_folder'] . $savePath . $hash;
        

        $this->createPath($absolutePath, $this->params['chmod'], true);

        try {
            copy($sourceFilePath, $absolutePath);
        } catch (\Exception $e) {
            throw new \Exception('File cannot be saved.');
        }

        return $this->file;
    }
    
    /**
     * Get the file entity based on ID
     * 
     * @param integer $fileId
     * @return FileRepository\Entity\File 
     * @throws \Exception 
     */
    
    public function remove($fileId)
    {
        $entity = $this->em->find('FileRepository\Entity\File', $fileId);
        
        if (!$entity) {
            throw new \Exception('File does not exist.', 404);
        }
        
        
        $file = $this->params['filerepository_folder'] . $entity->getSavePath();
        unlink($file);
        
        $this->em->remove($entity);
        $this->em->flush();
    }
    
    /**
     * Attach keywords to file entity
     * 
     * @param array $keywords
     * @return FileRepository\Entity\File 
     */
    protected function addKeywordsToFile(array $keywords) 
    {
        if (!empty($keywords)) {
            $keywordEntities = array();

            foreach ($keywords as $word) {
                $keyword = new Keyword();
                $keyword->setValue(strtolower($word));
                $keyword->setFile($this->file);
                $this->em->persist($keyword);

                $keywordEntities[] = $keyword;
            }

            $this->file->setKeywords($keywordEntities);
        }
        
        return $this->file;
    }
    
    /**
     * Create path recursively
     * 
     * @param string $path
     * @param string $mode
     * @param boolean $isFileIncluded
     * 
     * @throws FileRepository\Exception\RuntimeException
     */
    protected function createPath($path, $mode, $isFileIncluded)
    {
        $success = true;
        
        if (!is_dir(dirname($path))) {
            if ($isFileIncluded) {
                $success = mkdir(dirname($path), $mode, true);
            } else {
                $success = mkdir($path, $mode, true);
            }
        }
        
        if (!$success) {
            throw new RuntimeException('Can\'t create filerepository storage folders: '.$path);
        }
    }
}
