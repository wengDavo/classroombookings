<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_department_groups_table extends CI_Migration
{
    public function up()
    {
        $fields = [
            'department_group_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'department_class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => FALSE,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => FALSE,
            ],
            'size' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => TRUE,
                'null'       => FALSE,
            ],
            'identifier' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => FALSE,
            ],
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('department_group_id', TRUE);
        $this->dbforge->create_table('department_groups', TRUE, ['ENGINE' => 'InnoDB']);

        // Foreign key for department_class_id
        $sql_fk = "ALTER TABLE `department_groups`
                   ADD CONSTRAINT `fk_department_groups_department_class`
                   FOREIGN KEY (`department_class_id`)
                   REFERENCES `department_classes` (`department_class_id`)
                   ON DELETE CASCADE
                   ON UPDATE CASCADE";
        $this->db->query($sql_fk);

        // Unique constraint on (department_class_id, identifier)
        $this->db->query("ALTER TABLE `department_groups`
                          ADD UNIQUE KEY `unique_department_group_identifier` (`department_class_id`, `identifier`)");
    }

    public function down()
    {
        $this->dbforge->drop_table('department_groups', TRUE);
    }
}