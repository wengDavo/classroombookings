<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_levels_table extends CI_Migration
{
    public function up()
    {
        $fields = [
            'level_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'level_code' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => FALSE,
                'unique'     => TRUE,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => FALSE,
            ],
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('level_id', TRUE);

        $this->dbforge->create_table('levels', TRUE, array('ENGINE' => 'InnoDB'));  // Changed to plural 'levels'
    }

    public function down()
    {
        $this->dbforge->drop_table('levels', TRUE);  // Changed to plural 'levels'
    }
}