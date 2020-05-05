<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block\Product;

class Image extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
    }

    public function getImageUrl()
    {
        $color = $this->_request->getParam('color');
        $configurablePrdModel = $this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $attributeOptions = [93 => $color];
        $prdId = $this->_coreRegistry->registry('current_product')->getId();
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($prdId);
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $assPro = $configurablePrdModel->getProductByAttributes($attributeOptions, $product);
            if (!empty($assPro)) {
                $imageUrl = $this->_objectManager->get('Tigren\Ajaxcart\Helper\Data')->getProductImageUrl($assPro, 'category');
            } else {
                $imageUrl = $this->_objectManager->get('Tigren\Ajaxcart\Helper\Data')->getProductImageUrl($product, 'category');
            }
        } else {
            $imageUrl = $this->_objectManager->get('Tigren\Ajaxcart\Helper\Data')->getProductImageUrl($product, 'category');
        }
        return $imageUrl;
    }
}