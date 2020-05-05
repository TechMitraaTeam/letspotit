<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block;

class Message extends \Magento\Framework\View\Element\Template
{
    protected $_ajaxsuiteHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->_ajaxsuiteHelper = $ajaxsuiteHelper;
    }

    public function getMessage()
    {
        $message = $this->_ajaxsuiteHelper->getScopeConfig('ajaxwishlist/general/message');
        if (!$message) {
            $message = 'You have added this product to your wishlist';
        }
        return $message;
    }
}