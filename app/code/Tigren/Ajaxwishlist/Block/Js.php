<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block;

use Magento\Framework\View\Element\Template\Context;
use Tigren\Ajaxwishlist\Helper\Data;

class Js extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'js/main.phtml';

    protected $_ajaxwishlistHelper;

    public function __construct(
        Context $context,
        Data $ajaxwishlistHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_ajaxwishlistHelper = $ajaxwishlistHelper;
    }

    public function getAjaxWishlistInitOptions()
    {
        return $this->_ajaxwishlistHelper->getAjaxWishlistInitOptions();
    }
}