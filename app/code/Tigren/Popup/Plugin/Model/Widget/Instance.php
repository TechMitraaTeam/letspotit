<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Plugin\Model\Widget;

class Instance
{
    protected $_resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_resourceConnection = $resourceConnection;
    }

    /**
     * Perform actions after object save
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAfterSave(
        \Magento\Widget\Model\Widget\Instance $subject
    )
    {
        $widgetInstanceData = $subject->getData();
        $widgetParametters = unserialize($widgetInstanceData['widget_parameters']);
        $popupInstanceTypes = \Tigren\Popup\Helper\Data::INSTANCE_TYPES;

        $connection = $this->_resourceConnection->getConnection();

        $popupWidgetInstanceTable = $connection->getTableName('mb_popup_widget_instance');

        if (in_array($widgetInstanceData['instance_type'], $popupInstanceTypes)
            && !empty($widgetInstanceData['instance_id'])
            && !empty($widgetParametters['unique_id'])
        ) {
            $uniqueInsert = [
                'unique_id' => $widgetParametters['unique_id'],
                'instance_id' => $subject->getId(),
                'sort_order' => $subject->getSortOrder()
            ];
            $connection->insertOnDuplicate($popupWidgetInstanceTable, $uniqueInsert, ['sort_order']);
        }
    }
}
