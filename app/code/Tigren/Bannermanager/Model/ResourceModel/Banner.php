<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Bannermanager banner mysql resource
 */
class Banner extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Block banner entity table
     *
     * @var string
     */
    protected $_blockBannerTable;

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
        $this->_init('tigren_bannermanager_banner', 'banner_id');
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

        $object->setBlocks($this->getBlocks((int)$object->getId()));

        return $this;
    }

    /**
     * Retrieve block IDs related
     *
     * @param  int $bannerId
     * @return array
     */
    public function getBlocks($bannerId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_blockBannerTable),
            'block_id'
        )->where(
            'banner_id = ?',
            $bannerId
        );
        return $this->getConnection()->fetchCol($select);
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
        $blocks = $object->getBlocks();
        if (!empty($blocks)) {
            $condition = ['banner_id = ?' => $object->getId()];
            $connection->delete($this->_blockBannerTable, $condition);

            $insertedBlockIds = [];
            foreach ($blocks as $blockId) {
                if (in_array($blockId, $insertedBlockIds)) {
                    continue;
                }

                $insertedBlockIds[] = $blockId;
                $blockInsert = ['block_id' => $blockId, 'banner_id' => $object->getId()];
                $connection->insert($this->_blockBannerTable, $blockInsert);
            }
        }

        return $this;
    }
}
