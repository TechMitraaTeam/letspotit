<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcompare\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Tigren\Ajaxsuite\Helper\Data as AjaxsuiteHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeId;

    protected $_coreRegistry;

    protected $_storeManager;

    protected $_customerSession;

    protected $_layoutFactory;

    protected $_jsonEncoder;

    protected $_jsonDecoder;

    protected $_ajaxSuite;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        AjaxsuiteHelper $ajaxSuite
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_layoutFactory = $layoutFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_ajaxSuite = $ajaxSuite;

    }

    public function getAjaxCompareInitOptions()
    {
        $optionsAjaxsuite = $this->_jsonDecoder->decode($this->_ajaxSuite->getAjaxSuiteInitOptions());
        $options = [
            'ajaxCompare' => [
                'enabled' => $this->isEnabledAjaxCompare(),
                'ajaxCompareUrl' => $this->_getUrl('ajaxcompare/compare/addCompare')
            ],
        ];

        return $this->_jsonEncoder->encode(array_merge($optionsAjaxsuite, $options));
    }

    public function isEnabledAjaxCompare()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxcompare/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getSuccessHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxcompare_success_message');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getErrorHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxcompare_error_message');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }
}
