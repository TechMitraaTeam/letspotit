<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Ajaxcart\Controller\Wishlist;

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShowPopup extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $helper;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var AjaxcartData
     */
    protected $_ajaxcartData;
    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory
     */
    private $optionFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor
     * @param \Magento\Wishlist\Model\ItemFactory $itemFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Wishlist\Model\Item\OptionFactory $
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Wishlist\Helper\Data $helper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Wishlist\Model\Item\OptionFactory $optionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Wishlist\Helper\Data $helper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Registry $registry,
        \Tigren\Ajaxcart\Helper\Data $ajaxcartData
    )
    {
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->itemFactory = $itemFactory;
        $this->cart = $cart;
        $this->optionFactory = $optionFactory;
        $this->productHelper = $productHelper;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
        $this->_ajaxcartData = $ajaxcartData;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Add product to shopping cart from wishlist action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();



        try {
            $itemId = (int)$params['item'];

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            /* @var $item \Magento\Wishlist\Model\Item */
            $item = $this->itemFactory->create()->load($itemId);
            $product = $item->getProduct();

            if (!empty($params['ajaxcart_error'])) {
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxcartData->getErrorHtml($product);
                $result['error'] = true;
                $result['html_popup'] = $htmlPopup;
                $result['item'] = $itemId;

                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
                );

                return;
            }

            if (!$item->getId()) {
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }



            if (!$product) {
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }

            if (!empty($params['ajaxcart_success'])) {
                $item->delete();
                $wishlist->save();

                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);
                $htmlPopup = $this->_ajaxcartData->getSuccessHtml($product);
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;
                $result['item'] = $itemId;

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
                $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
                $item->setOptions($options->getOptionsByItem($itemId));

                $buyRequest = $this->productHelper->addParamsToBuyRequest(
                    $this->getRequest()->getParams(),
                    ['current_config' => $item->getBuyRequest()]
                );
                $supperAttribute = $item->getBuyRequest()->getData('super_attribute');
                if (!empty($supperAttribute)) {
                    $item->mergeBuyRequest($buyRequest);
                    $item->addToCart($this->cart, true);
                    $this->cart->save()->getQuote()->collectTotals();
                    $item->delete();
                    $wishlist->save();

                    $this->_coreRegistry->register('product', $product);
                    $this->_coreRegistry->register('current_product', $product);
                    $htmlPopup = $this->_ajaxcartData->getSuccessHtml($product);
                    $result['success'] = true;
                    $result['html_popup'] = $htmlPopup;
                    $result['item'] = $itemId;
                    $result['addto'] = true;

                    $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
                    );
                    return;
                } else {
                    $this->_coreRegistry->register('product', $product);
                    $this->_coreRegistry->register('current_product', $product);

                    $htmlPopup = $this->_ajaxcartData->getOptionsPopupHtml($product);
                    $result['success'] = true;
                    $result['html_popup'] = $htmlPopup;
                    $result['item'] = $itemId;

                    $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
                    );

                    return;
                }

            } else {
                $params['product'] = $product->getId();

                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($params)
                );

                $this->_forward(
                    'add',
                    'cart',
                    'checkout',
                    $params);

                return;
            }
        } catch (ProductException $e) {
            $this->messageManager->addError(__('This product(s) is out of stock.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addNotice($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add the item to the cart right now.'));
        }

        $this->helper->calculate();
    }
}