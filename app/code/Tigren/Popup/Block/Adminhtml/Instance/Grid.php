<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Block\Adminhtml\Instance;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        $collection = $this->getData('dataSource');

        $popupInstanceTypes = \Tigren\Popup\Helper\Data::INSTANCE_TYPES;
        $collection->addFieldToFilter('instance_type', ['in' => $popupInstanceTypes]);

        return $collection;
    }
}