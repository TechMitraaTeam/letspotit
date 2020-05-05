<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Chooser for "Product Link" Cms Widget Plugin
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Tigren\Bannermanager\Block\Adminhtml\Banner\Widget;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Chooser extends Extended
{
    /**
     * @var array
     */
    protected $_selectedBanners = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_resourceBanner;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * stdlib timezone.
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
        \Magento\Backend\Helper\Data $backendHelper,
        \Tigren\Bannermanager\Model\ResourceModel\Banner\CollectionFactory $collectionFactory,
        \Tigren\Bannermanager\Model\ResourceModel\Banner $resourceBanner,
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_stdTimezone = $_stdTimezone;
        $this->_resourceBanner = $resourceBanner;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $element->setData('after_element_html', $this->_getAfterElementHtml($element));
        return $element;
    }

    public function _getAfterElementHtml($element)
    {
        $html = <<<HTML
    <style>
         .control .control-value {
            display: none !important;
        }
    </style>
HTML;

        $chooserHtml = $this->getLayout()
            ->createBlock('Tigren\Bannermanager\Block\Adminhtml\Banner\Widget\ChooserJs')
            ->setElement($element);

        $html .= $chooserHtml->toHtml();

        return $html;
    }


    public function getCheckboxCheckCallback()
    {
        return "function (grid, element) {
                $(grid.containerId).fire('banners:changed', {element: element});
            }";
    }


    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_banners') {
            $selected = $this->getSelectedBanners();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('banner_id', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('banner_id', ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare products collection, defined collection filters (category, product type)
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $dateTimeNow = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('start_time', [['to' => $dateTimeNow], ['start_time', 'null' => '']])
            ->addFieldToFilter('end_time', [['gteq' => $dateTimeNow], ['end_time', 'null' => '']])
            ->setOrder('sort_order', 'ASC');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for products grid
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_banners',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_banners',
                'inline_css' => 'checkbox entities',
                'field_name' => 'in_banners',
                'values' => $this->getSelectedBanners(),
                'align' => 'center',
                'index' => 'banner_id',
                'use_index' => true
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
                'column_css_class' => 'col-title',

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


        return parent::_prepareColumns();
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'bannermanager/banner_widget/chooser',
            [
                'banners_grid' => true,
                '_current' => true,
                'uniq_id' => $this->getId(),

            ]
        );
    }

    /**
     * Setter
     *
     * @param array $selectedProducts
     * @return $this
     */
    public function setSelectedBanners($selectedBanners)
    {
        $this->_selectedBanners = $selectedBanners;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedBanners()
    {
        if ($selectedBanners = $this->getRequest()->getParam('selected_banners', null)) {
            $this->setSelectedBanners($selectedBanners);
        }
        return $this->_selectedBanners;
    }
}
