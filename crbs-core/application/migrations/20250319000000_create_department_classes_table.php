<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_department_classes_table extends CI_Migration
{
    public function up()
    {
        $fields = [
            'department_class_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => FALSE,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => FALSE,
            ],
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('department_class_id', TRUE);
        $this->dbforge->create_table('department_classes', TRUE, ['ENGINE' => 'InnoDB']);

        // Foreign key for level_id
        $sql_level = "ALTER TABLE `department_classes`
                      ADD CONSTRAINT `fk_department_classes_level`
                      FOREIGN KEY (`level_id`)
                      REFERENCES `levels` (`level_id`)
                      ON DELETE RESTRICT
                      ON UPDATE CASCADE";
        $this->db->query($sql_level);

        // Foreign key for department_id
        $sql_dept = "ALTER TABLE `department_classes`
                     ADD CONSTRAINT `fk_department_classes_department`
                     FOREIGN KEY (`department_id`)
                     REFERENCES `departments` (`department_id`)
                     ON DELETE RESTRICT
                     ON UPDATE CASCADE";
        $this->db->query($sql_dept);

        // Composite unique constraint on (level_id, department_id)
        $this->db->query("ALTER TABLE `department_classes`
                          ADD UNIQUE KEY `unique_department_class` (`level_id`, `department_id`)");
    }

    public function down()
    {
        $this->dbforge->drop_table('department_classes', TRUE);
    }
}