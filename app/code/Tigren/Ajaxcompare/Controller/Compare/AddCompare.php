<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcompare\Controller\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Tigren\Ajaxcompare\Helper\Data as AjaxcompareData;

class AddCompare extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * @var AjaxcompareData Data
     */
    protected $_ajaxCompareHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonEncode;

    /**
     * @var null
     */
    protected $_coreRegistry = null;

    public function __construct
    (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession, \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        Data $jsonEncode,
        AjaxcompareData $ajaxCompareHelper,
        Registry $registry
    )
    {
        parent::__construct($context, $compareItemFactory, $itemCollectionFactory, $customerSession, $customerVisitor, $catalogProductCompareList, $catalogSession, $storeManager, $formKeyValidator, $resultPageFactory, $productRepository);
        $this->_ajaxCompareHelper = $ajaxCompareHelper;
        $this->_jsonEncode = $jsonEncode;
        $this->_coreRegistry = $registry;
    }

    /**
     * Add item to compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];
        $params = $this->_request->getParams();

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product) {
                $this->_catalogProductCompareList->addProduct($product);
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);

                if (!empty($params['isCompare'])) {
                    $this->_coreRegistry->register('product', $product);
                    $this->_coreRegistry->register('current_product', $product);

                    $htmlPopup = $this->_ajaxCompareHelper->getSuccessHtml();
                    $result['success'] = true;
                    $result['html_popup'] = $htmlPopup;

                } else {
                    $htmlPopup = $this->_ajaxCompareHelper->getErrorHtml();
                    $result['success'] = false;
                    $result['html_popup'] = $htmlPopup;
                }

            }
            $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();

        }
        return $this->getResponse()->representJson($this->_jsonEncode->jsonEncode($result));
    }
}