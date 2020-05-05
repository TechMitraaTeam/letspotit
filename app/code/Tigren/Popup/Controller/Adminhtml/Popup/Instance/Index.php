<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Controller\Adminhtml\Popup\Instance;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * Widget Instances Grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Popups'));
        $this->_view->renderLayout();
    }

    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Tigren_Popup::popup'
        )->_addBreadcrumb(
            __('Popup'),
            __('Popup')
        )->_addBreadcrumb(
            __('Manage Popups'),
            __('Manage Popups')
        );
        return $this;
    }
}