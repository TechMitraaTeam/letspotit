<?php
/**
 * Copyright © 2017 Tigren, Inc. All rights reserved.
 */
namespace Tigren\Productlist\Block\Item;

use Magento\Customer\Model\Context as CustomerContext;

class NewProduct extends \Magento\Catalog\Block\Product\NewProduct
{
    protected $_productCollection;

    public function getCacheKeyInfo()
    {
        return [
            'CATALOG_PRODUCT_NEW',
            $this->getCategory()->getId(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            $this->getProductsCount()
        ];
    }

    public function getNewProductsCollection()
    {
        $collection = parent::_getProductCollection();
        if ($this->getCategory()) {
            $collection->addCategoryFilter($this->getCategory());
        }
        $collection->setPageSize(3);
        return $collection;
    }
}