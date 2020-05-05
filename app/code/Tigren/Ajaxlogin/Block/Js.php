<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Block;

/**
 * Ajaxsuite js block
 */
class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'js/main.phtml';

    /**
     * Ajaxsuite helper
     */
    protected $_ajaxLoginHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Tigren\Ajaxlogin\Helper\Data $ajaxLoginHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->_ajaxLoginHelper = $ajaxLoginHelper;
    }

    /**
     * @return string
     */
    public function getAjaxLoginInitOptions()
    {
        return $this->_ajaxLoginHelper->getAjaxLoginInitOptions();
    }
}