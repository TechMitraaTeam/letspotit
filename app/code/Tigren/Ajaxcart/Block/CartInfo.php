<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcart\Block;

class CartInfo extends \Magento\Framework\View\Element\Template
{
    protected $_cart;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->_cart = $cart;
    }

    public function getItemsCount()
    {
        return $this->_cart->getItemsCount();
    }

    public function getItemsQty()
    {
        return $this->_cart->getItemsQty();
    }

    public function getSubTotal()
    {
        return $this->_cart->getQuote()->getSubtotal();
    }
}