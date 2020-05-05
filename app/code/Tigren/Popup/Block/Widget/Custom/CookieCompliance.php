<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Widget\Custom;

class CookieCompliance extends \Tigren\Popup\Block\Widget\AbstractPopup
{
    protected $_cookieHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Popup\Helper\Data $popupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cookie\Helper\Cookie $cookieHelper,
        array $data = []
    )
    {
        parent::__construct($context, $popupHelper, $customerSession, $data);
        $this->_cookieHelper = $cookieHelper;
    }

    /**
     * Get Link to cookie restriction privacy policy page
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPrivacyPolicyLink()
    {
        return $this->_urlBuilder->getUrl('privacy-policy-cookie-restriction-mode');
    }

    protected function _toHtml()
    {
        if (!$this->_cookieHelper->isUserNotAllowSaveCookie()) {
            return parent::_toHtml();
        }

        return '';
    }
}