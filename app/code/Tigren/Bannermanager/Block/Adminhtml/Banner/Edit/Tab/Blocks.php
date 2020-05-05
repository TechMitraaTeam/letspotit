<?php
/**
 * @copyright Copyright (c) 2016 www.Tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml\Banner\Edit\Tab;

class Blocks extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Tigren\Bannermanager\Model\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * @var \Tigren\Bannermanager\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Tigren\Bannermanager\Model\BannerFactory $bannerFactory
     * @param \Tigren\Bannermanager\Model\BlockFactory $blockFactory
     * @param \Tigren\Bannermanager\Model\Banner\Attribute\Source\Status $status
     * @param \Tigren\Bannermanager\Model\Banner\Visibility $visibility
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Tigren\Bannermanager\Model\BannerFactory $bannerFactory,
        \Tigren\Bannermanager\Model\BlockFactory $blockFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        $this->_bannerFactory = $bannerFactory;
        $this->_blockFactory = $blockFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Blocks');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Blocks');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData(
            'grid_url'
        ) ? $this->_getData(
            'grid_url'
        ) : $this->getUrl(
            'bannersmanager/*/blockGrid',
            ['_current' => true]
        );
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bannermanager_block_grid');
        $this->setDefaultSort('block_id');
        $this->setUseAjax(true);
        if ($this->getBanner() && $this->getBanner()->getId()) {
            $this->setDefaultFilter(['in_blocks' => 1]);
        }
    }

    /**
     * Retirve currently edited banner model
     *
     * @return \Tigren\Bannermanager\Model\Banner
     */
    public function getBanner()
    {
        return $this->_coreRegistry->registry('bannermanager_banner');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in block flag
        if ($column->getId() == 'in_blocks') {
            $blockIds = $this->_getSelectedBlocks();
            if (empty($blockIds)) {
                $blockIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('block_id', ['in' => $blockIds]);
            } else {
                if ($blockIds) {
                    $this->getCollection()->addFieldToFilter('block_id', ['nin' => $blockIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Retrieve selected banners
     *
     * @return array
     */
    protected function _getSelectedBlocks()
    {
        $blocks = array_keys($this->getSelectedBlocks());
        return $blocks;
    }

    /**
     * Retrieve blocks
     *
     * @return array
     */
    public function getSelectedBlocks()
    {
        $id = $this->getRequest()->getParam('image_id');
        if (!isset($id)) {
            $id = 0;
        }

        $banner = $this->_bannerFactory->create()->load($id);
        $blocks = $banner->getBlocks();

        if (!$blocks) {
            return [];
        }

        $blockIds = [];

        foreach ($blocks as $blockId) {
            $blockIds[$blockId] = ['id' => $blockId];
        }

        return $blockIds;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_blockFactory->create()->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_blocks',
            [
                'type' => 'checkbox',
                'name' => 'banner',
                'values' => $this->_getSelectedBlocks(),
                'align' => 'center',
                'index' => 'block_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'block_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'block_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'block_title',
            [
                'header' => __('Title'),
                'index' => 'block_title',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        $this->addColumn(
            'block_position',
            [
                'header' => __('Block Position'),
                'index' => 'block_position',
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );

        $this->addColumn(
            'from_date',
            [
                'header' => __('From'),
                'type' => 'date',
                'index' => 'from_date',
                'header_css_class' => 'col-from-date',
                'column_css_class' => 'col-from-date'
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => __('To'),
                'type' => 'date',
                'index' => 'to_date',
                'header_css_class' => 'col-to-date',
                'column_css_class' => 'col-to-date'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => true,
                'edit_only' => true,
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );

        return parent::_prepareColumns();
    }
}