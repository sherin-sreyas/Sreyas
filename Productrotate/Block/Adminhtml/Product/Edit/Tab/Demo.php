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

namespace Sreyas\Productrotate\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use \Sreyas\Productrotate\Model\ResourceModel\FileUpload\CollectionFactory as FileCollectionFactory;

class Demo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'product/edit/demo.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;
    protected $_fileCollectionFactory = null;
    public $_catalogSession;

    public function __construct(
        Context $context,
        Registry $registry,
        FileCollectionFactory $fileCollectionFactory,
        \Magento\Catalog\Model\Session $catalogSession,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_fileCollectionFactory = $fileCollectionFactory;
        $this->_catalogSession = $catalogSession;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }
    public function getProductFiles()
    {
		  $product=$this->_coreRegistry->registry('current_product');   
		  $productId = $product->getId();     
        $fileCollection = $this->_fileCollectionFactory->create();
        $fileCollection->addFieldToSelect('*');
        $fileCollection->addFieldToFilter('product_id', $productId)->load();
        return $fileCollection->getItems();
    }
    public function getCatalogSession() 
    {
        return $this->_catalogSession;
    }

}