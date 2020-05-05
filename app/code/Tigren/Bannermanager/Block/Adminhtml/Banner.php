<?php
/**
 * @copyright Copyright (c) 2016 www.Tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml;

class Banner extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_banner';
        $this->_blockGroup = 'Tigren_Bannermanager';
        $this->_headerText = __('Manage Banners');

        parent::_construct();

        if ($this->_isAllowedAction('Tigren_Bannermanager::save')) {
            $this->buttonList->update('add', 'label', __('Add New Banner'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
