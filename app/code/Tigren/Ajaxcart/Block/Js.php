<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block;


class Js extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'js/main.phtml';

    protected $_ajaxcartHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxcart\Helper\Data $ajaxcartHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_ajaxcartHelper = $ajaxcartHelper;
        $this->formKey = $formKey;
    }

    public function getAjaxCartInitOptions()
    {
        return $this->_ajaxcartHelper->getAjaxCartInitOptions();
    }

    public function getAjaxSidebarInitOptions()
    {
        $icon = $this->getViewFileUrl('images/loader-1.gif');
        return $this->_ajaxcartHelper->getAjaxSidebarInitOptions($icon);
    }

}