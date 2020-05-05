<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Block\Messages\Forgot;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Success extends Template
{
    protected $_coreRegistry;

    public function __construct(Template\Context $context, Registry $registry, array $data)
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    public function getEmailFromLayout()
    {
        return $this->_coreRegistry->registry('email');
    }
}