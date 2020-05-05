<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Block\Popup;

class Forgot extends \Magento\Framework\View\Element\Template
{
    protected $_ajaxLoginHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxlogin\Helper\Data $ajaxLoginHelper,
        array $data = []
    )
    {
        $this->_ajaxLoginHelper = $ajaxLoginHelper;
        parent::__construct($context, $data);
    }

    public function getHtml()
    {
        return $this->_ajaxLoginHelper->getForgotPasswordPopupHtml();
    }

}