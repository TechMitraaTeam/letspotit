<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxsuite\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\LayoutFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const IS_ENABLED = 'ajaxsuite/general/enabled';
    const ISTTL = 'ajaxsuite/general/enabled_popupttl';
    const TTL = 'ajaxsuite/general/popupttl';
    const THEME = 'ajaxsuite/effects/theme';
    const THEME_CUSTOM_BACKGROUND = 'ajaxsuite/effects/color_background';
    const THEME_CUSTOM_TITLE = 'ajaxsuite/effects/color_title';
    const ANIMATION = 'ajaxsuite/effects/animation';
    protected $_storeId;
    protected $_customerSession;
    protected $_jsonEncoder;
    protected $_layoutFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        EncoderInterface $jsonEncoder
    )
    {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_layoutFactory = $layoutFactory;
    }

    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    public function getAjaxSuiteInitOptions()
    {
        $options = [
            'ajaxSuite' => [
                'enabled' => $this->isEnabledAjaxSuite(),
                'popupTTL' => $this->getTTLAjaxSuite(),
                'animation' => $this->getAnimationAjaxSuite(),
                'backgroundColor' => $this->getScopeConfig('ajaxsuite/effects/background_color'),
                'headerBackgroundColor' => $this->getScopeConfig('ajaxsuite/effects/header_background_color'),
                'headerTextColor' => $this->getScopeConfig('ajaxsuite/effects/header_text_color'),
                'buttonTextColor' => $this->getScopeConfig('ajaxsuite/effects/button_text_color'),
                'buttonBackgroundColor' => $this->getScopeConfig('ajaxsuite/effects/button_background_color'),
            ]
        ];

        return $this->_jsonEncoder->encode($options);
    }

    public function isEnabledAjaxSuite()
    {
        return (bool)$this->scopeConfig->getValue(
            self::IS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getTTLAjaxSuite()
    {
        if ($this->isEnabledTTLAjaxSuite()) {
            return (int)$this->scopeConfig->getValue(
                self::TTL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeId
            );
        } else {
            return null;
        }
    }

    public function isEnabledTTLAjaxSuite()
    {
        return (bool)$this->scopeConfig->getValue(
            self::ISTTL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getAnimationAjaxSuite()
    {
        return $this->scopeConfig->getValue(
            self::ANIMATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function getScopeConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
    }

    public function getLoggedCustomer()
    {
        return (bool)$this->_customerSession->isLoggedIn();
    }
}
