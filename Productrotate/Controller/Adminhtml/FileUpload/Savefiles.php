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
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\ResourceConnection;
class Savefiles extends \Magento\Backend\App\Action
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
    protected $_resource;
    public $_storeManager;
   
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
		  FileUploadFactory $fileuploadFactory,
		  JsonFactory $resultJsonFactory,
		  StoreManagerInterface $storeManager,
		  ResourceConnection $resource,
        array $data = []
    )
    {
        
        $this->filesystem           =  $filesystem;
        $this->fileUploader         =  $fileUploader;
        $this->fileuploadFactory    =  $fileuploadFactory;
        $this->context              =  $context;
        
        $this->mediaDirectory       =  $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    	  $this->resultJsonFactory    =  $resultJsonFactory;
    	  $this->_storeManager        =  $storeManager;
    	  $this->_resource            =  $resource;
        parent::__construct($context,$data);
    }
 
    public function execute()
    {
      //establisshing db connection
		 
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('catalog_product_360'); //getting table name
        
        //getting post variables
        $_postData = $this->getRequest()->getPost();
        //getting input type=file details
        $file = $this->getRequest()->getFiles('attachment');
        
        $productId= $_postData['productId'];
         
        // this folder will be created inside "pub/media" folder
        $FolderName = '360images/'.$productId.'/';
 
        // "my_custom_file" is the HTML input file name
        $InputFileName = 'attachment';
        $datatxt='';
		  $message = "";
        $html='';
        $error = false;
        
        try
        {
            
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
					 	$error = true;
						
         			$message = "File can not be saved to the destination folder.";
            		
        			 }
				 
                if ($result['file']) {
							
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
									$_mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);//get media url
									$src=$_mediaUrl.$FolderName.$fname;
									$html .= '<div class="image item base-image" data-role="image" id="'. uniqid().'" >
                            <div class="product-image-wrapper"  id="'.$sortorder.'" accesskey="'.$productId.'">
                                <img class="product-image" data-role="image-element" src="'.$src.'" alt="">
                                <div class="actions">
                                    <button type="button" class="action-remove" data-role="delete-button" data-image="'.$fname.'" title="Delete image"><span>Delete image</span></button>
                                </div>
                                <div class="image-fade"><span>Hidden</span></div>
                            </div>
                            <div class="item-description">
                                <div class="item-title" data-role="img-title"></div>
                                <div class="item-size">
                                    <a href="'.$src.'" target="_blank"><span data-role="image-dimens">'.$fname.'</span></a>
                                </div>
                            </div>
                        	</div>';
    							}
								$zip->close();
								$error = false;
								
								
								$datatxt=$html;
         					$message = "File uploaded successfully";
         					 
								
							} else {
								$error = true;
            			   $message = 'Extraction error. '.$e->getMessage();
								
							}
                }
                
               return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                    'message' => $message,
                    'data' => $datatxt,
                    'error' => $error
        			]);
            }
        } 
        catch (\Exception $e) {
			$error = true;
         $message = "Something went wrong while saving the file(s). ".$e->getMessage();
			return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                    'message' => $message,
                    'data' => $datatxt,
                    'error' => $error
        			]);
        }
 			
       return false;
		 
      
    }
 
}