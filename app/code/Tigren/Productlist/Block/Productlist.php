<?php
/**
 * Copyright © 2017 Tigren, Inc. All rights reserved.
 */
namespace Tigren\Productlist\Block;

class Productlist extends \Magento\Framework\View\Element\Template
{
    protected $_categoryFactory;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    public function getScopeConfig($path)
    {
        $storeId = $this->getCurrentStoreId();
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCategoryData($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        return $category;
    }
}