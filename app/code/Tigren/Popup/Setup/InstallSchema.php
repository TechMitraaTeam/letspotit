<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Popup\Setup;

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
         * Create table 'mb_popup_widget_instance'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mb_popup_widget_instance'))
            ->addColumn(
                'instance_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Instance Id'
            )
            ->addColumn(
                'unique_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'primary' => true],
                'Unique Id'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Sort Order'
            )
            ->addColumn(
                'impression_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Impression Count'
            )
            ->addIndex(
                $installer->getIdxName('mb_popup_widget_instance', ['instance_id', 'unique_id']),
                ['instance_id', 'unique_id']
            )
            ->addForeignKey(
                $installer->getFkName('mb_popup_widget_instance', 'instance_id', 'widget_instance', 'instance_id'),
                'instance_id',
                $installer->getTable('widget_instance'),
                'instance_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Widget Instance');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mb_popup_statistic'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('mb_popup_statistic'))
            ->addColumn(
                'statistic_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Statistic Id'
            )
            ->addColumn(
                'instance_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Instance Id'
            )
            ->addColumn(
                'impression_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'close_without_interaction'],
                'Impression Type'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Customer Id'
            )
            ->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Created Time'
            )
            ->addIndex(
                $installer->getIdxName('mb_popup_statistic', ['instance_id', 'impression_type', 'customer_id']),
                ['instance_id', 'impression_type', 'customer_id']
            )
            ->addForeignKey(
                $installer->getFkName('mb_popup_statistic', 'instance_id', 'mb_popup_widget_instance', 'instance_id'),
                'instance_id',
                $installer->getTable('mb_popup_widget_instance'),
                'instance_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Popup Statistics');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
