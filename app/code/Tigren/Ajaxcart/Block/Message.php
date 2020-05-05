<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block;

class Message extends \Magento\Framework\View\Element\Template
{
    protected $_ajaxcartHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxcart\Helper\Data $ajaxcartHelper,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->_ajaxcartHelper = $ajaxcartHelper;
    }

    public function getMessage()
    {
        $message = $this->_ajaxcartHelper->getScopeConfig('ajaxcart/general/message');
        if (!$message) {
            $message = 'You have recently added this product to your Cart';
        }
        return $message;
    }
}