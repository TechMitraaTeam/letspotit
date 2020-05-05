<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Ajaxcart\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;


    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_layoutFactory;

    protected $_jsonEncoder;

    protected $_jsonDecoder;

    protected $_objectManager;

    protected $prdImageHelper;

    protected $_ajaxsuiteHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper
    )
    {
        $this->_customerSession = $customerSession;
        $this->_layoutFactory = $layoutFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_objectManager = $objectManager;
        $this->prdImageHelper = $imageHelper;
        $this->_ajaxsuiteHelper = $ajaxsuiteHelper;
        parent::__construct($context);
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    public function getOptionsPopupHtml($product)
    {
        $layout = $this->_layoutFactory->create();

        $update = $layout->getUpdate();
        $update->load('ajaxcart_options_popup');

        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function getSuccessHtml($product)
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxcart_success_message');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function getErrorHtml($product)
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxcart_error_message');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function isEnabledAjaxcart()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxcart/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getPopupTTL()
    {
        if ($this->isEnabledPopupTTL()) {
            return (int)$this->scopeConfig->getValue(
                'ajaxcart/general/popupttl',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeId
            );
        }
        return 0;
    }

    public function isEnabledPopupTTL()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxcart/general/enabled_popupttl',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getAjaxCartInitOptions()
    {
        $optionsAjaxsuite = $this->_jsonDecoder->decode($this->_ajaxsuiteHelper->getAjaxSuiteInitOptions());
        $options = [
            'ajaxCart' => [
                'addToCartUrl' => $this->_getUrl('ajaxcart/cart/showPopup'),
                'addToCartInWishlistUrl' => $this->_getUrl('ajaxcart/wishlist/showPopup'),
                'checkoutCartUrl' => $this->_getUrl('checkout/cart/add'),
                'wishlistAddToCartUrl' => $this->_getUrl('wishlist/index/cart'),
                'addToCartButtonSelector' => $this->getAddToCartButtonSelector()
            ]
        ];

        return $this->_jsonEncoder->encode(array_merge($optionsAjaxsuite, $options));
    }

    public function getAjaxSidebarInitOptions($icon)
    {
        $options = [
            'icon' => $icon,
            'texts' => [
                'loaderText' => __('Loading...'),
                'imgAlt' => __('Loading...')
            ]
        ];

        return $this->_jsonEncoder->encode($options);
    }

    public function getAddToCartButtonSelector()
    {
        $class = $this->getScopeConfig('ajaxcart/general/addtocart_btn_class');
        if (empty($class)) {
            $class = 'tocart';
        }
        return 'button.' . $class;
    }

    public function getScopeConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
    }

    public function getPriceWithCurrency($price)
    {
        if ($price) {
            return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency(number_format($price, 2, '.', ''), true, false);
        }
        return 0;
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
