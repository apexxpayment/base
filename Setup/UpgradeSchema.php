<?php
/**
 * Custom payment method in Magento 2
 *
 * @category Base
 * @package  Apexx_Base
 */
namespace Apexx\Base\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;


/**
 * Class InstallData
 * @package Apexx\Base\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {   
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_payment'),
                'cvv_response',
                [
                    'type' => 'text',
                    'nullable' => true,
                    'comment' =>'cvvResponse'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_payment'),
                'avs_response',
                [
                    'type' => 'text',
                    'nullable' => true,
                    'comment' =>'avsResponse'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_payment'),
                'bin',
                [
                    'type' => 'text',
                    'nullable' => true,
                    'comment' =>'bin'
                ]
            );
        }
        $setup->endSetup();
  }
}
