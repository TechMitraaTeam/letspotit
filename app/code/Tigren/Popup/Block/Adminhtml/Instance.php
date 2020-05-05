<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

/**
 * Popup Instance grid container
 *
 * @author      Tigren Team
 */
namespace Tigren\Popup\Block\Adminhtml;

class Instance extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Tigren_Popup';
        $this->_controller = 'adminhtml_popup_instance';
        $this->_headerText = __('Manage Popups');
        parent::_construct();
        $this->buttonList->update('add', 'label', __('Add New Popup'));
    }
}
