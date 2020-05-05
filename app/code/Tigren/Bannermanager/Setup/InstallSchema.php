<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Bannermanager\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'tigren_bannermanager_block'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_bannermanager_block'))
            ->addColumn(
                'block_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Block Id'
            )
            ->addColumn(
                'block_position',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Block Position'
            )
            ->addColumn(
                'block_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Title'
            )
            ->addColumn(
                'display_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Display Type'
            )
            ->addColumn(
                'from_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'From Date'
            )
            ->addColumn(
                'to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'To Date'
            )
            ->addColumn(
                'customer_group_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Customer Group IDs'
            )
            ->addColumn(
                'category',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Category'
            )
            ->addColumn(
                'category_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Category Type'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Sort Order'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Is Active'
            )
            ->addIndex(
                $installer->getIdxName('tigren_bannermanager_block', ['block_position', 'display_type', 'category', 'category_type', 'is_active']),
                ['block_position', 'display_type', 'category', 'category_type', 'is_active']
            )
            ->addIndex(
                $setup->getIdxName(
                    $installer->getTable('tigren_bannermanager_block'),
                    ['block_title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['block_title'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )
            ->setComment('Blocks');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'tigren_bannermanager_block_store'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_bannermanager_block_store'))
            ->addColumn(
                'block_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Block Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store Id'
            )
            ->addIndex(
                $installer->getIdxName('tigren_bannermanager_block_store', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('tigren_bannermanager_block_store', 'block_id', 'tigren_bannermanager_block', 'block_id'),
                'block_id',
                $installer->getTable('tigren_bannermanager_block'),
                'block_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('tigren_bannermanager_block_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Blocks To Stores Relations');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'tigren_bannermanager_banner'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_bannermanager_banner'))
            ->addColumn(
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Banner Id'
            )
            ->addColumn(
                'banner_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Title'
            )
            ->addColumn(
                'banner_image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Banner Image'
            )
            ->addColumn(
                'banner_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Banner Url'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )
            ->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Created Date'
            )
            ->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Update Time'
            )
            ->addColumn(
                'start_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Start Time'
            )
            ->addColumn(
                'end_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'End Time'
            )
            ->addColumn(
                'target',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Target'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Sort Order'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Is Active'
            )
            ->addIndex(
                $installer->getIdxName('tigren_bannermanager_banner', ['is_active']),
                ['is_active']
            )
            ->addIndex(
                $setup->getIdxName(
                    $installer->getTable('tigren_bannermanager_banner'),
                    ['banner_title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['banner_title'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )
            ->setComment('Banners');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'tigren_bannermanager_block_banner_entity'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_bannermanager_block_banner_entity'))
            ->addColumn(
                'block_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Block Id'
            )
            ->addColumn(
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Banner Id'
            )
            ->addIndex(
                $installer->getIdxName('tigren_bannermanager_block_banner_entity', ['block_id', 'banner_id']),
                ['block_id', 'banner_id']
            )
            ->addForeignKey(
                $installer->getFkName('tigren_bannermanager_block_banner_entity', 'block_id', 'tigren_bannermanager_block', 'block_id'),
                'block_id',
                $installer->getTable('tigren_bannermanager_block'),
                'block_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('tigren_bannermanager_block_banner_entity', 'banner_id', 'tigren_bannermanager_banner', 'banner_id'),
                'banner_id',
                $installer->getTable('tigren_bannermanager_banner'),
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Block Banner Entity');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
