<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version129 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('orders_items', 'discount', 'float', '10', array(
             'default' => '0',
             ));
    }

    public function down()
    {
        $this->removeColumn('orders_items', 'discount');
    }
}