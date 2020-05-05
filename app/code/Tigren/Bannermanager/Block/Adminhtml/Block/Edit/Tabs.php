<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml\Block\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param InlineInterface $translateInline
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        InlineInterface $translateInline,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bannermanager_block_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Block'));
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'bannermanager_block_edit_tab_main',
            [
                'label' => __('Block Information'),
                'content' => $this->getLayout()->createBlock(
                    'Tigren\Bannermanager\Block\Adminhtml\Block\Edit\Tab\Main'
                )->toHtml()
            ]
        );

        $this->addTab(
            'bannergrid',
            [
                'label' => __('Select Banner'),
                'url' => $this->getUrl('bannersmanager/*/bannergrid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_prepareLayout();
    }

    public function getBlock()
    {
        if (!$this->getData('bannermanager_block') instanceof \Tigren\Bannermanager\Model\Block) {
            $this->setData('bannermanager_block', $this->_coreRegistry->registry('bannermanager_block'));
        }
        return $this->getData('bannermanager_block');
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }
}