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

namespace Sreyas\Productrotate\Model;

class FileUpload extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'catalog_product_360';

	protected $_cacheTag = 'catalog_product_360';

	protected $_eventPrefix = 'catalog_product_360';

	protected function _construct()
	{
		$this->_init('Sreyas\Productrotate\Model\ResourceModel\FileUpload');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}