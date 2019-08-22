<?php
/**
* Sreyas IT Solutions
*
* This source file is subject to the Sreyas IT Solutions Software License, which is available at https://www.sreyas.com/license/.
* Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
* If you wish to customize this module for your needs.
* Please refer to http://www.magentocommerce.com for more information.
*
* @category  Sreyas
* @package   sreyas/module-360view
* @version   2.0.44
* @copyright Copyright (C) 2019 Sreyas IT Solutions Pvt Ltd. (https://www.sreyas.com/)
*/ 



namespace Sreyas\Productrotate\Controller\Adminhtml\FileUpload;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class DeleteFile extends \Magento\Backend\App\Action {
    
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    public $_storeManager;
    protected $_file;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonHelper $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        $this->_file = $file;
        $this->_resource   =  $resource;
    }

    
    public function execute(){
        
        $_postData = $this->getRequest()->getPost();
        
        $message = "";
        $newFileName = "";
        $success = false;
        
        $mediaRootDir = $this->_mediaDirectory->getAbsolutePath();
        $_fileName = $mediaRootDir .'360images/'.$_postData['productID'].'/'. $_postData['filename'];
        if ($this->_file->isExists($_fileName))  {
            try{
					 $connection = $this->_resource->getConnection();
        			 $tableName = $this->_resource->getTableName('catalog_product_360'); //getting table name  
        			 $sql = "DELETE FROM " . $tableName . " WHERE product_id = ". $_postData['productID']." and sort_order=".$_postData['sortOrder'];
					 $connection->query($sql);  
                $this->_file->deleteFile($_fileName);
                $message = "File removed successfully.";
                $success = true;
            } catch (Exception $ex) {
                $message = $e->getMessage();
                $success = false;
            }
        }else{
            $message = "File not found.".$_fileName;
            $success = false;
        }
        
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
                    'message' => $message,
                    'data' => '',
                    'success' => $success
        ]);         
    }
}