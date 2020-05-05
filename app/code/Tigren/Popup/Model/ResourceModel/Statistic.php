<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Popup statistic mysql resource
 */
class Statistic extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Block statistic entity table
     *
     * @var string
     */
    protected $_popupInstance;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mb_popup_statistic', 'statistic_id');
        $this->_popupInstance = $this->getTable('mb_popup_widget_instance');
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();

        /**
         * save blocks
         */
        $instanceId = $object->getInstanceId();
        if (!empty($instanceId)) {
            $currentCount = $connection->fetchOne(
                'SELECT COUNT(*) FROM ' . $this->getTable('mb_popup_statistic') . ' WHERE instance_id = ?',
                [$object->getInstanceId()]
            );

            $connection->update(
                $this->_popupInstance,
                ['impression_count' => $currentCount],
                ['instance_id' => $object->getInstanceId()]
            );
        }

        return $this;
    }
}
