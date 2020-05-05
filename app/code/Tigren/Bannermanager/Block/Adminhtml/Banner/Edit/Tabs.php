<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml\Banner\Edit;

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
        $this->setId('bannermanager_banner_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Banner'));
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'bannermanager_banner_edit_tab_main',
            [
                'label' => __('Banner Information'),
                'content' => $this->getLayout()->createBlock(
                    'Tigren\Bannermanager\Block\Adminhtml\Banner\Edit\Tab\Main'
                )->toHtml()
            ]
        );

        $this->addTab(
            'blockgrid',
            [
                'label' => __('Select Block'),
                'url' => $this->getUrl('bannersmanager/*/blockgrid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_prepareLayout();
    }

    public function getBanner()
    {
        if (!$this->getData('bannermanager_banner') instanceof \Tigren\Bannermanager\Model\Banner) {
            $this->setData('bannermanager_banner', $this->_coreRegistry->registry('bannermanager_banner'));
        }
        return $this->getData('bannermanager_banner');
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