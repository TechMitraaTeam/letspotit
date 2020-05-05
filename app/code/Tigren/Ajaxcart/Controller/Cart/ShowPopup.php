<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Ajaxcart\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Tigren\Ajaxcart\Helper\Data as AjaxcartData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShowPopup extends \Magento\Checkout\Controller\Cart
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Ajaxcart Data
     */
    protected $_ajaxcartData;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Registry $registry,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        AjaxcartData $ajaxcartData
    )
    {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->_ajaxcartData = $ajaxcartData;
        $this->_coreRegistry = $registry;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
//        if (!$this->_formKeyValidator->validate($this->getRequest())) {
//            return $this->resultRedirectFactory->create()->setPath('*/*/');
//        }

        $params = $this->getRequest()->getParams();

        try {
            $product = $this->_initProduct();
            if (!empty($params['ajaxcart_error'])) {
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxcartData->getErrorHtml($product);
                $result['error'] = true;
                $result['html_popup'] = $htmlPopup;

                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
                );

                return;
            }

            if (!empty($params['ajaxcart_success'])) {
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxcartData->getSuccessHtml($product);
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;

                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
                );

                return;
            }

            /* return options popup content when product type is grouped */
            if ($product->getHasOptions()
                || ($product->getTypeId() == 'grouped' && !isset($params['super_group']))
                || ($product->getTypeId() == 'configurable' && !isset($params['super_attribute']))
                || $product->getTypeId() == 'bundle'
            ) {
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxcartData->getOptionsPopupHtml($product);
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;

                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
                );
                return;
            } else {
                return $this->_forward(
                    'add',
                    'cart',
                    'checkout',
                    $params);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->goBack();
        }
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}