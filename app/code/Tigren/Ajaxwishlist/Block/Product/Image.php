<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block\Product;

class Image extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_objectManager;
    protected $prdImageHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->prdImageHelper = $imageHelper;
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
            if ($assPro->getId()) {
                $imageUrl = $this->getProductImageUrl($assPro, 'category');
            } else {
                $imageUrl = $this->getProductImageUrl($product, 'category');
            }
        } else {
            $imageUrl = $this->getProductImageUrl($product, 'category');
        }
        return $imageUrl;
    }

    public function getProductImageUrl($product, $size)
    {
        $imageSize = 'product_page_image_' . $size;
        if ($size == 'category') {
            $imageSize = 'category_page_list';
        }
        $imageUrl = $this->prdImageHelper->init($product, $imageSize)
            ->keepAspectRatio(TRUE)
            ->keepFrame(FALSE)
            ->getUrl();
        return $imageUrl;
    }
}