<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version113 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('tickets_notes', 'parent_id', 'int', '4', array(
             'default' => '0',
             ));
    }

    public function down()
    {
        $this->removeColumn('tickets_notes', 'parent_id');
    }
}