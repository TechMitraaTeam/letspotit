<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

/**
 * Popup Statistics grid container
 *
 * @author      Tigren Team
 */
namespace Tigren\Popup\Block\Adminhtml;

class Statistics extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Tigren_Popup';
        $this->_controller = 'adminhtml_popup_statistics';
        $this->_headerText = __('Statistics');
        parent::_construct();
        $this->removeButton('add');
    }
}
