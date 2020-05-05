<?php

/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Controller\Login;

use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\StoreManagerInterface;
use Tigren\Ajaxlogin\Helper\Data as AjaxLoginHelper;

class SocialLogin extends Action
{
    protected $storeManager;
    protected $registration;
    protected $customerSession;
    protected $jsonHelper;
    protected $_ajaxLoginHelper;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registration $registration,
        Session $customerSession,
        JsonHelper $jsonHelper,
        AjaxLoginHelper $ajaxLoginHelper
    )
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->registration = $registration;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->_ajaxLoginHelper = $ajaxLoginHelper;
    }

    public function execute()
    {
        $result = array();
        if (!$this->registration->isAllowed()) {
            $result['error'] = __('Registration is not allow.');
        } else if ($this->customerSession->isLoggedIn()) {
            $result['error'] = __('You have already logged in.');
        } else {
            $this->customerSession->regenerateId();
            $params = $this->getRequest()->getPost();
            $socialType = $params['social_type'];
            if ($params) {
                $storeId = $this->storeManager->getStore()->getStoreId();
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $data = array('firstname' => $params['firstname'], 'lastname' => $params['lastname'], 'email' => $params['email'], 'password' => $params['password']);
                if ($data['email']) {
                    $customer = $this->_ajaxLoginHelper->getCustomerByEmail($data['email'], $websiteId);
                    if (!$customer || !$customer->getId()) {
                        $customer = $this->_ajaxLoginHelper->createCustomerMultiWebsite($data, $websiteId, $storeId);
                        if ($this->_ajaxLoginHelper->getScopeConfig('ajaxlogin/social_login/send_pass')) {
                            $customer->sendPasswordReminderEmail();
                        }
                    }
                    $this->customerSession->setCustomerAsLoggedIn($customer);
                } else {
                    $result['error'] = __('Something wrong with getting your email of your ') . $socialType;
                }
            } else {
                $result['error'] = __('Something wrong when processing Ajax.');
            }
        }

        if (!empty($result['error'])) {
            $htmlPopup = $this->_ajaxLoginHelper->getErrorMessageLoginPopupHtml();
            $result['html_popup'] = $htmlPopup;
        } else {
            $htmlPopup = $this->_ajaxLoginHelper->getSuccessMessageLoginPopupHtml();
            $result['html_popup'] = $htmlPopup;
        }
        return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}
    