<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Controller\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ShowPopup extends \Magento\Framework\App\Action\Action
{
    protected $_ajaxWishlistHelper;
    protected $_ajaxSuiteHelper;
    protected $_productRepository;
    protected $_coreRegistry = null;

    /**
     * ShowPopup constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Tigren\Ajaxwishlist\Helper\Data $ajaxWishlistHelper
     * @param \Tigren\Ajaxsuite\Helper\Data $ajaxSuiteHelper
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Tigren\Ajaxwishlist\Helper\Data $ajaxWishlistHelper,
        \Tigren\Ajaxsuite\Helper\Data $ajaxSuiteHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context);
        $this->_ajaxWishlistHelper = $ajaxWishlistHelper;
        $this->_productRepository = $productRepository;
        $this->_coreRegistry = $registry;
        $this->_ajaxSuiteHelper = $ajaxSuiteHelper;
    }

    public function execute()
    {
        $result = [];
        $params = $this->_request->getParams();
        $isLoggedIn = $this->_ajaxSuiteHelper->getLoggedCustomer();

        if ($isLoggedIn == true) {
            try {
                $product = $this->_initProduct();
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxWishlistHelper->getOptionsPopupHtml($product);
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('You can\'t login right now.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $result['success'] = false;
            }
        } else {
            $product = $this->_initProduct();
            $this->_coreRegistry->register('product', $product);
            $this->_coreRegistry->register('current_product', $product);

            $htmlPopup = $this->_ajaxWishlistHelper->getErrorHtml($product);
            $result['success'] = false;
            $result['html_popup'] = $htmlPopup;
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }

    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                $product = $this->_productRepository->getById($productId, false, $storeId);
                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}