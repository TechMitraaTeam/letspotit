<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml\Block;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve text for header element depending on loaded block
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('bannermanager_block')->getId()) {
            return __("Edit Block '%1'", $this->escapeHtml($this->_coreRegistry->registry('bannermanager_block')->getBlockTitle()));
        } else {
            return __('New Block');
        }
    }

    /**
     * Initialize banner manager block edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'block_id';
        $this->_blockGroup = 'Tigren_Bannermanager';
        $this->_controller = 'adminhtml_block';

        parent::_construct();

        if ($this->_isAllowedAction('Tigren_Bannermanager::save')) {
            $this->buttonList->update('save', 'label', __('Save Block'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Tigren_Bannermanager::block_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Block'));
        } else {
            $this->buttonList->remove('delete');
        }

        if ($this->_coreRegistry->registry('bannermanager_block')->getId()) {
            $this->buttonList->remove('reset');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('bannersmanager/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
