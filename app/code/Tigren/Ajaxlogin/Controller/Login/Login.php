<?php

/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Controller\Login;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends Action
{

    protected $helperData;
    protected $accountManagement;
    protected $customerSession;
    protected $jsonHelper;
    protected $_ajaxLoginHelper;
    private $cookieMetadataManager;
    private $cookieMetadataFactory;
    private $scopeConfig;

    public function __construct(
        Context $context,
        AccountManagementInterface $accountManagement,
        CustomerSession $customerSession,
        JsonHelper $jsonHelper,
        \Tigren\Ajaxlogin\Helper\Data $ajaxloginHelper
    )
    {
        $this->_ajaxLoginHelper = $ajaxloginHelper;
        parent::__construct($context);
        $this->accountManagement = $accountManagement;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
    }

    public function execute()
    {
        $result = array();
        if ($this->customerSession->isLoggedIn()) {
            $result['error'] = 'You have already logged in.';
        } else {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->accountManagement->authenticate($login['username'], $login['password']);
                    $this->customerSession->setCustomerDataAsLoggedIn($customer);
                    $this->customerSession->regenerateId();
                    $result['success'] = __('You have logged in successfully. Please wait ...');
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $result['error'] = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.', $value
                    );
                } catch (UserLockedException $e) {
                    $result['error'] = __(
                        'The account is locked. Please wait and try again or contact %1.', $this->getScopeConfig()->getValue('contact/email/recipient_email')
                    );
                } catch (AuthenticationException $e) {
                    $result['error'] = __('Invalid login or password.');
                } catch (LocalizedException $e) {
                    $result['error'] = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $result['error'] = $e->getMessage();
//                    $result['error'] = __('An unspecified error occurred. Please contact us for assistance.');
                }
            } else {
                $result['error'] = 'A login and a password are required.';
            }
        }

        if (!empty($result['error'])) {
            $htmlPopup = $this->_ajaxLoginHelper->getErrorMessageLoginPopupHtml();
            $result['html_popup'] = $htmlPopup;
        } else {
            $htmlPopup = $this->_ajaxLoginHelper->getSuccessMessageLoginPopupHtml();
            $result['html_popup'] = $htmlPopup;
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

}
