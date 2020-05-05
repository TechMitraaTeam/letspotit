<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Helper;

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

    protected $_storeId;

    protected $_coreRegistry;

    protected $_storeManager;

    protected $customerFactory;

    protected $objectManager;

    protected $_customerSession;

    protected $_urlBuilder;

    protected $_jsonEncoder;

    protected $_jsonDecoder;

    protected $_layoutFactory;

    protected $_ajaxSuiteHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $coreRegistry,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        \Tigren\Ajaxsuite\Helper\Data $ajaxSuiteHelper
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_customerFactory = $customerFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_layoutFactory = $layoutFactory;
        $this->_ajaxSuiteHelper = $ajaxSuiteHelper;
        $this->setStoreId($this->getCurrentStoreId());
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

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    /**
     * Get Login Popup
     *
     * @return string
     */
    public function getLoginPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_login_popup');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getSuccessMessageLoginPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_login_success');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getSuccessMessageRegisterPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_register_success');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getSuccessMessageForgotPasswordPopupHtml($email)
    {
        $layout = $this->_layoutFactory->create();
        $this->_coreRegistry->register('email', $email);
        $layout->getUpdate()->load('ajaxlogin_forgotpassword_success');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getSuccessMessageLogoutPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_logout_success');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getErrorMessageLoginPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_login_error');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getErrorMessageRegisterPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_register_error');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getErrorMessageForgotPasswordPopupHtml($email)
    {
        $layout = $this->_layoutFactory->create();
        $this->_coreRegistry->register('email', $email);
        $layout->getUpdate()->load('ajaxlogin_forgotpassword_error');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    public function getErrorMessageLogoutPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_logout_error');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    /**
     * Get Register Popup
     *
     * @return string
     */
    public function getRegisterPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_register_popup');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    /**
     * Get Forgot Password Popup
     *
     * @return string
     */
    public function getForgotPasswordPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxlogin_forgotpassword_popup');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    /**
     * @return string
     */
    public function getAjaxLoginInitOptions()
    {
        $optionsAjaxsuite = $this->_jsonDecoder->decode($this->_ajaxSuiteHelper->getAjaxSuiteInitOptions());
        $options = [
            'ajaxLogin' => [
                'ajaxGetPopupUrl' => $this->_getUrl('ajaxsuite/login/showPopup'),
                'ajaxLoginUrl' => $this->_getUrl('ajaxlogin/login/login'),
                'ajaxSocialLoginUrl' => $this->_getUrl('ajaxlogin/login/socialLogin'),
                'ajaxRegisterUrl' => $this->_getUrl('ajaxlogin/login/create'),
                'ajaxTwitterUrl' => $this->_getUrl('ajaxlogin/login/twitter'),
                'ajaxForgotPasswordUrl' => $this->_getUrl('ajaxlogin/login/forgot'),
                'ajaxLogoutUrl' => $this->_getUrl('ajaxlogin/login/logout'),
                'enabled' => $this->getScopeConfig('ajaxlogin/general/enabled'),
                'urlRedirect' => $this->getScopeConfig('ajaxlogin/general/login_destination'),
                'slideAnimation' => $this->getScopeConfig('ajaxlogin/general/slide_animation'),
                'socialLoginEnable' => $this->getScopeConfig('ajaxlogin/social_login/enable'),
                'facebookAppId' => $this->getScopeConfig('ajaxlogin/social_login/facebook_appid'),
                'ggPlusClientId' => $this->getScopeConfig('ajaxlogin/social_login/googleplus_clientid'),
                'baseUrl' => $this->getBaseUrl()
            ]
        ];
        $options = array_merge($optionsAjaxsuite, $options);

        return $this->_jsonEncoder->encode($options);
    }

    public function getScopeConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getTwitterConsumerKey()
    {
        return $this->getScopeConfig('ajaxlogin/social_login/twitter_consumer_key');
    }

    public function getTwitterConsumerSecret()
    {
        return $this->getScopeConfig('ajaxlogin/social_login/twitter_consumer_secret');
    }

    public function getTwitterLoginUrl()
    {
        $baseUrl = $this->getBaseUrl();
        return $baseUrl . 'ajaxlogin/login/twitter.php';
    }

    public function getTwitterCallbackUrl()
    {
        $baseUrl = $this->getBaseUrl();
        return $baseUrl . 'ajaxlogin/login/callback';
    }

    public function getCustomerByEmail($email, $websiteId = null)
    {
        $customer = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        );
        if (!$websiteId) {
            $customer->setWebsiteId($this->_storeManager->getWebsite()->getId());
        } else {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);

        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    public function createCustomerMultiWebsite($data, $websiteId, $storeId)
    {
        $customer = $this->_customerFactory->create();
        $customer->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setWebsiteId($websiteId)
            ->setStoreId($storeId);
        try {
            $customer->save();
        } catch (\Exception $e) {
        }

        return $customer;
    }

}
