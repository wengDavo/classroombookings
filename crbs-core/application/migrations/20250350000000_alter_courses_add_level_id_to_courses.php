<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_courses_add_level_id_to_courses extends CI_Migration
{
    public function up()
    {
        // Add the level_id column to the existing courses table
        $fields = [
            'level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => TRUE, 
                'after'      => 'credits'
            ]
        ];

        $this->dbforge->add_column('courses', $fields);

            
        $sql = "ALTER TABLE `courses`
                ADD CONSTRAINT `fk_courses_level`
                FOREIGN KEY (`level_id`)
                REFERENCES `levels` (`level_id`)
                ON DELETE SET NULL
                ON UPDATE CASCADE";
        $this->db->query($sql);
    }

    public function down()
    {
        // Remove the foreign key constraint
        $this->db->query("ALTER TABLE `courses` DROP FOREIGN KEY `fk_courses_level`");

        // Drop the level_id column
        $this->dbforge->drop_column('courses', 'level_id');
    }
}