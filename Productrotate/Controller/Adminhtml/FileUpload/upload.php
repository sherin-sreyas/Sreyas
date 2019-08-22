<?php

namespace Sreyas\Productrotate\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload extends Action
{
/**
 * Image uploader
 *
 * @var \Magento\Catalog\Model\ImageUploader
 */
protected $imageUploader;

/**
 * Upload constructor.
 *
 * @param \Magento\Backend\App\Action\Context $context
 * @param \Magento\Catalog\Model\ImageUploader $imageUploader
 */
public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Catalog\Model\ImageUploader $imageUploader
) {
    parent::__construct($context);
    $this->imageUploader = $imageUploader;
}



/**
 * Upload file controller action
 *
 * @return \Magento\Framework\Controller\ResultInterface
 */
public function execute()
{
    try {
        $result = $this->imageUploader->saveFileToTmpDir('price_matrix');

        $result['cookie'] = [
            'name' => $this->_getSession()->getName(),
            'value' => $this->_getSession()->getSessionId(),
            'lifetime' => $this->_getSession()->getCookieLifetime(),
            'path' => $this->_getSession()->getCookiePath(),
            'domain' => $this->_getSession()->getCookieDomain(),
        ];
    } catch (\Exception $e) {
        $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
    }
    return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
}
}