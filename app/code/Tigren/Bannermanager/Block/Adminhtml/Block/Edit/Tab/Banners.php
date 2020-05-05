<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Block\Adminhtml\Block\Edit\Tab;

class Banners extends \Magento\Backend\Block\Widget\Grid\Extended implements
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
        return __('Banners');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Banners');
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
            'bannersmanager/*/bannerGrid',
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
        $this->setId('bannermanager_banner_grid');
        $this->setDefaultSort('banner_id');
        $this->setUseAjax(true);
        if ($this->getBlock() && $this->getBlock()->getId()) {
            $this->setDefaultFilter(['in_banners' => 1]);
        }
    }

    /**
     * Retirve currently edited banner model
     *
     * @return \Tigren\Bannermanager\Model\Banner
     */
    public function getBlock()
    {
        return $this->_coreRegistry->registry('bannermanager_block');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in banner flag
        if ($column->getId() == 'in_banners') {
            $bannerIds = $this->_getSelectedBanners();
            if (empty($bannerIds)) {
                $bannerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('banner_id', ['in' => $bannerIds]);
            } else {
                if ($bannerIds) {
                    $this->getCollection()->addFieldToFilter('banner_id', ['nin' => $bannerIds]);
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
    protected function _getSelectedBanners()
    {
        $banners = array_keys($this->getSelectedBanners());
        return $banners;
    }

    /**
     * Retrieve banners
     *
     * @return array
     */
    public function getSelectedBanners()
    {
        $id = $this->getRequest()->getParam('block_id');
        if (!isset($id)) {
            $id = 0;
        }

        $block = $this->_blockFactory->create()->load($id);
        $banners = $block->getBanners();

        if (!$banners) {
            return [];
        }

        $bannerIds = [];

        foreach ($banners as $bannerId) {
            $bannerIds[$bannerId] = ['id' => $bannerId];
        }

        return $bannerIds;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_bannerFactory->create()->getCollection();
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
            'in_banners',
            [
                'type' => 'checkbox',
                'name' => 'banner',
                'values' => $this->_getSelectedBanners(),
                'align' => 'center',
                'index' => 'banner_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select'
            ]
        );

        $this->addColumn(
            'banner_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'banner_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'banner_title',
            [
                'header' => __('Title'),
                'index' => 'banner_title',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        $this->addColumn(
            'description',
            [
                'header' => __('Description'),
                'index' => 'description',
                'width' => '215px',
                'header_css_class' => 'col-description',
                'column_css_class' => 'col-description'
            ]
        );

        $this->addColumn(
            'banner_image',
            [
                'header' => __('Image'),
                'index' => 'banner_image',
                'header_css_class' => 'col-image',
                'column_css_class' => 'col-image',
                'renderer' => '\Tigren\Bannermanager\Block\Adminhtml\Banner\Widget\Renderer\Images',
            ]
        );

        $this->addColumn(
            'banner_url',
            [
                'header' => __('Banner Url'),
                'index' => 'banner_url',
                'header_css_class' => 'col-url',
                'column_css_class' => 'col-url'
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