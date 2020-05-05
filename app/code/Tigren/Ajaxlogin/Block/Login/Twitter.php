<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Block\Login;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Twitter extends Template
{
    protected $_coreRegistry;

    public function __construct(Template\Context $context, Registry $registry, array $data)
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getUrlLgoin()
    {
        return $this->_coreRegistry->registry('url');
    }

    public function createWindown()
    {

    }
}