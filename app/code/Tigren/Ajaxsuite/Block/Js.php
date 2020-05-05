<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxsuite\Block;

class Js extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'js/main.phtml';
    protected $_ajaxsuiteHelper;
    protected $formKey;

    /**
     * Js constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->_ajaxsuiteHelper = $ajaxsuiteHelper;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getAjaxLoginUrl()
    {
        return $this->getUrl('ajaxsuite/login');
    }

    public function getAjaxWishlistUrl()
    {
        return $this->getUrl('ajaxsuite/wishlist');
    }

    public function getAjaxCompareUrl()
    {
        return $this->getUrl('ajaxsuite/compare');
    }

    public function getAjaxSuiteInitOptions()
    {
        return $this->_ajaxsuiteHelper->getAjaxSuiteInitOptions();
    }
}