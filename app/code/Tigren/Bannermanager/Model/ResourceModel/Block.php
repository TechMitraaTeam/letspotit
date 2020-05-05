<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Bannermanager block mysql resource
 */
class Block extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Block store table
     *
     * @var string
     */
    protected $_blockStoreTable;

    /**
     * Block banner entity table
     *
     * @var string
     */
    protected $_blockBannerTable;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $resourcePrefix = null
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tigren_bannermanager_block', 'block_id');
        $this->_blockStoreTable = $this->getTable('tigren_bannermanager_block_store');
        $this->_blockBannerTable = $this->getTable('tigren_bannermanager_block_banner_entity');
    }

    /**
     * Actions after load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Tigren\Bannermanager\Model\Block $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        if (!$object->getId()) {
            return $this;
        }

        // load block available in stores
        $object->setStores($this->getStores((int)$object->getId()));

        $object->setCustomerGroupIds(explode(',', $object->getCustomerGroupIds()));

        $object->setBanners($this->getBanners((int)$object->getId()));

        return $this;
    }

    /**
     * Retrieve store IDs related to given rating
     *
     * @param  int $blockId
     * @return array
     */
    public function getStores($blockId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_blockStoreTable),
            'store_id'
        )->where(
            'block_id = ?',
            $blockId
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve banner IDs related
     *
     * @param  int $blockId
     * @return array
     */
    public function getBanners($blockId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_blockBannerTable),
            'banner_id'
        )->where(
            'block_id = ?',
            $blockId
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores([$object->getStores(), 0]);
        }

        if ($object->hasData('customer_group_ids') && is_array($object->getCustomerGroupIds())) {
            $object->setCustomerGroupIds(implode(',', $object->getCustomerGroupIds()));
        } elseif ($object->hasData('customer_group_ids')) {
            $object->setCustomerGroupIds($object->getCustomerGroupIds());
        }

        return $this;
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
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = ['block_id = ?' => $object->getId()];
            $connection->delete($this->_blockStoreTable, $condition);

            $insertedStoreIds = [];
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = ['store_id' => $storeId, 'block_id' => $object->getId()];
                $connection->insert($this->_blockStoreTable, $storeInsert);
            }
        }

        /**
         * save banners
         */
        $banners = $object->getBanners();
        if (!empty($banners)) {
            $condition = ['block_id = ?' => $object->getId()];
            $connection->delete($this->_blockBannerTable, $condition);

            $insertedBannerIds = [];
            foreach ($banners as $bannerId) {
                if (in_array($bannerId, $insertedBannerIds)) {
                    continue;
                }

                $insertedBannerIds[] = $bannerId;
                $bannerInsert = ['banner_id' => $bannerId, 'block_id' => $object->getId()];
                $connection->insert($this->_blockBannerTable, $bannerInsert);
            }
        }

        return $this;
    }
}
