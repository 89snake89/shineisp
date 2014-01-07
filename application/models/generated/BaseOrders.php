<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Orders', 'doctrine');

/**
 * BaseOrders
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $order_id
 * @property integer $customer_id
 * @property string $external_id
 * @property integer $isp_id
 * @property string $uuid
 * @property date $order_date
 * @property boolean $is_renewal
 * @property boolean $is_upgrade
 * @property integer $status_id
 * @property float $total
 * @property float $cost
 * @property float $vat
 * @property float $grandtotal
 * @property integer $invoice_id
 * @property date $expiring_date
 * @property string $note
 * @property string $order_number
 * @property Customers $Customers
 * @property Isp $Isp
 * @property Invoices $Invoices
 * @property Statuses $Statuses
 * @property Doctrine_Collection $Messages
 * @property Doctrine_Collection $OrdersItems
 * @property Doctrine_Collection $OrdersItemsDomains
 * @property Doctrine_Collection $OrdersItemsServers
 * @property Doctrine_Collection $Payments
 * @property Doctrine_Collection $StatusHistory
 * @property Doctrine_Collection $Tickets
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseOrders extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('orders');
        $this->hasColumn('order_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('customer_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('external_id', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));
        $this->hasColumn('isp_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'default' => 1,
             'length' => '4',
             ));
        $this->hasColumn('uuid', 'string', 50, array(
             'type' => 'string',
             'length' => '50',
             ));
        $this->hasColumn('order_date', 'date', 25, array(
             'type' => 'date',
             'notnull' => true,
             'length' => '25',
             ));
        $this->hasColumn('is_renewal', 'boolean', 25, array(
             'type' => 'boolean',
             'default' => 0,
             'length' => '25',
             ));
        $this->hasColumn('is_upgrade', 'boolean', 25, array(
             'type' => 'boolean',
             'default' => 0,
             'length' => '25',
             ));
        $this->hasColumn('status_id', 'integer', 4, array(
             'type' => 'integer',
             'default' => '1',
             'length' => '4',
             ));
        $this->hasColumn('total', 'float', 10, array(
             'type' => 'float',
             'default' => '0.00',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('cost', 'float', 10, array(
             'type' => 'float',
             'default' => '0.00',
             'length' => '10',
             ));
        $this->hasColumn('vat', 'float', 10, array(
             'type' => 'float',
             'default' => '0.00',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('grandtotal', 'float', 10, array(
             'type' => 'float',
             'default' => '0.00',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('invoice_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('expiring_date', 'date', 25, array(
             'type' => 'date',
             'length' => '25',
             ));
        $this->hasColumn('note', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('order_number', 'string', 50, array(
             'type' => 'string',
             'length' => '50',
             ));


        $this->index('uuid', array(
             'fields' => 
             array(
              0 => 'uuid',
             ),
             'type' => 'unique',
             ));
        $this->index('order_number', array(
             'fields' => 
             array(
              0 => 'order_number',
             ),
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Customers', array(
             'local' => 'customer_id',
             'foreign' => 'customer_id',
             'onDelete' => 'Cascade'));

        $this->hasOne('Isp', array(
             'local' => 'isp_id',
             'foreign' => 'isp_id'));

        $this->hasOne('Invoices', array(
             'local' => 'invoice_id',
             'foreign' => 'invoice_id',
             'onDelete' => 'Cascade'));

        $this->hasOne('Statuses', array(
             'local' => 'status_id',
             'foreign' => 'status_id'));

        $this->hasMany('Messages', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));

        $this->hasMany('OrdersItems', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));

        $this->hasMany('OrdersItemsDomains', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));

        $this->hasMany('OrdersItemsServers', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));

        $this->hasMany('Payments', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));

        $this->hasMany('StatusHistory', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));

        $this->hasMany('Tickets', array(
             'local' => 'order_id',
             'foreign' => 'order_id'));
    }
}