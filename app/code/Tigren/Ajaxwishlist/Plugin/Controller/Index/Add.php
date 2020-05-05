<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Plugin\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Tigren\Ajaxwishlist\Helper\Data as AjaxwishlistData;

class Add
{
    protected $_coreRegistry = null;
    protected $_storeManager;
    protected $productRepository;
    protected $_jsonEncode;
    protected $resultRedirectFactory;
    protected $_ajaxWishlistHelper;


    public function __construct
    (
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonEncode,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        AjaxwishlistData $ajaxWishlistHelper
    )
    {
        $this->resultRedirectFactory = $redirectFactory;
        $this->_jsonEncode = $jsonEncode;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->_ajaxWishlistHelper = $ajaxWishlistHelper;
        $this->_coreRegistry = $registry;
    }

    public function aroundExecute($subject, $proceed)
    {
        $result = [];
        $params = $subject->getRequest()->getParams();

        $product = $this->_initProduct($subject);

        if (!empty($params['isWishlistSubmit'])) {
            $proceed();
            $this->_coreRegistry->register('product', $product);
            $this->_coreRegistry->register('current_product', $product);

            $htmlPopup = $this->_ajaxWishlistHelper->getSuccessHtml();
            $result['success'] = true;
            $result['html_popup'] = $htmlPopup;

            $subject->getResponse()->representJson($this->_jsonEncode->jsonEncode($result));
        } else {
            $proceed();
            return $this->resultRedirectFactory->create()->setPath('*');
        }
    }

    protected function _initProduct($subject)
    {
        $productId = (int)$subject->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);

                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
