<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxcompare\Block;

use Magento\Framework\View\Element\Template\Context;
use Tigren\Ajaxcompare\Helper\Data;

class Js extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'js/main.phtml';

    protected $_ajaxCompareHelper;

    public function __construct(
        Context $context,
        Data $ajaxCompareHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_ajaxCompareHelper = $ajaxCompareHelper;
    }

    public function getAjaxCompareInitOptions()
    {
        return $this->_ajaxCompareHelper->getAjaxCompareInitOptions();
    }
}