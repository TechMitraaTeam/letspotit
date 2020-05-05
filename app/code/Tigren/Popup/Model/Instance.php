<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Model;

/**
 * @author      Tigren Team
 */
class Instance extends \Magento\Widget\Model\Widget\Instance
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tigren\Popup\Model\ResourceModel\Instance');
    }
}
