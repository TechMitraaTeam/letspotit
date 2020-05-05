<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Block\Form;

class Login extends \Magento\Customer\Block\Form\Login
{

    protected $_ajaxloginHelper;

    public function __construct
    (
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Tigren\Ajaxlogin\Helper\Data $ajaxloginHelper,
        array $data
    )
    {
        parent::__construct($context, $customerSession, $customerUrl, $data);
        $this->_ajaxloginHelper = $ajaxloginHelper;
    }

    public function isEnableSocialLogin()
    {
        return $this->_ajaxloginHelper->getScopeConfig('ajaxlogin/social_login/enable');
    }

    public function isLoggedIn()
    {
        return $this->_ajaxloginHelper->isLoggedIn();
    }

}