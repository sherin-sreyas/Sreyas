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

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Filesystem;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use \Sreyas\Productrotate\Model\FileUploadFactory;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Message\ManagerInterface;
class Uploader extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;
 	 
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     */
    protected $fileUploader;
 	 protected $fileuploadFactory;
    protected $messageManager = null;
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
		  FileUploadFactory $fileuploadFactory,
		  ManagerInterface $messageManager,
        array $data = []
    )
    {
        
        $this->filesystem           = $filesystem;
        $this->fileUploader         = $fileUploader;
        $this->fileuploadFactory    = $fileuploadFactory;
        $this->context              = $context;
        
        $this->mediaDirectory       = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    	  $this->messageManager       = $messageManager;
        parent::__construct($context,$data);
    }
 
    public function execute()
    {
        // your code
 
      $uploadedFile = $this->uploadFile();
		return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($uploadedFile);
		
        // your code
    }
 
    public function uploadFile()
    {
		  // getting product id from block using session		  
		  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $blockInstance = $objectManager->get('Sreyas\Productrotate\Block\Adminhtml\Product\Edit\Tab\Demo');
        $productId= $blockInstance->getCatalogSession()->getProductId() ;  
        $blockInstance->assign('data', 'jghjghjhj');
         
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('catalog_product_360'); 
              
        // this folder will be created inside "pub/media" folder
        $FolderName = '360images/'.$productId.'/';
 
        // "my_custom_file" is the HTML input file name
        $InputFileName = 'product[icon]';
		
        try
        {
            $file = $this->getRequest()->getFiles('product')['icon'];
            $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;
				         
           
           
            if ($file && $fileName) 
            {
            	
					 
					 
                $target = $this->mediaDirectory->getAbsolutePath($FolderName);        
              
                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                $uploader = $this->fileUploader->create(['fileId' => $InputFileName]);
                
                // set allowed file extensions
                $uploader->setAllowedExtensions(['zip']);
                
                // allow folder creation
                $uploader->setAllowCreateFolders(true);
 
                // rename file name if already exists 
                $uploader->setAllowRenameFiles(true);     
 
                // upload file in the specified folder
                $result = $uploader->save($target);
					 if (!$result) {
					 	/*
            			throw new \Magento\Framework\Exception\LocalizedException(
               	 __('File can not be saved to the destination folder.')
            		);
            		*/
            		$this->messageManager->addError(__("File can not be saved to the destination folder."));
        			 }
				 
                if ($result['file']) {
							echo 'File has been successfully uploaded';
							chmod('pub/media/360images/'.$productId.'/', 0777);
							chmod($target.$uploader->getUploadedFileName(), 0777);
							// deleting existing files in the product folder            	 
            	 		$extfiles = glob('pub/media/360images/'.$productId.'/*'); // get all file names
					 		foreach($extfiles as $extfile){ // iterate files
  					 			if(is_file($extfile) && ($extfile!='pub/media/'.$FolderName. $uploader->getUploadedFileName()))
    							unlink($extfile); // delete file
					 		 }
					 
                    	$zip = new \ZipArchive();
                    	
							$res = $zip->open($target. $uploader->getUploadedFileName());
							if ($res === TRUE) {
								$zip->extractTo($target);
								$post = $this->fileuploadFactory->create();
								$sql = "DELETE FROM " . $tableName . " WHERE product_id = " . $productId;
						      $connection->query($sql);
							   
								for($i = 0; $i < $zip->numFiles; $i++) 
     							{   
									$fname= $zip->getNameIndex($i); 
									$fullpath=$target.$fname;
									$filepath='pub/media/'.$FolderName.$fname;          							
        							
        							$sortorder=$i;
        							chmod($fullpath, 0777);
        							$post = $this->fileuploadFactory->create();
									$post->setData('product_id', $productId);
									$post->setData('image', $filepath);
									$post->setData('sort_order', $sortorder);
									$post->save();
    							}
								$zip->close();
								echo 'extraction successful';
								$this->_view->getLayout()->createBlock('Sreyas\Productrotate\Block\Adminhtml\Product\Edit\Tab\Demo')->toHtml();
								
								$this->messageManager->addSuccess(__("File uploaded successfully"));
								
							} else {
								echo 'extraction error';
							}
                }
                
                return $target . $uploader->getUploadedFileName();
            }
        } 
        catch (\Exception $e) {
		
         $this->messageManager->addError(__("Something went wrong while saving the file(s)."));		
		//	throw new \Magento\Framework\Exception\LocalizedException( __($e->getMessage()) );
			echo 'failed';
        }
 
        return false;
    }
}