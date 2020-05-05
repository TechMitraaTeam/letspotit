<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Block\Messages\Logout;

use Magento\Framework\View\Element\Template;

class Success extends Template
{
    public function __construct(Template\Context $context, array $data)
    {
        parent::__construct($context, $data);
    }
}