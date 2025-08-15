<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_courses_table extends CI_Migration
{
    public function up()
    {
        $fields = [
            'course_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => FALSE,
            ],
            'course_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => FALSE,
                'unique'     => TRUE,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => TRUE,
            ],
            'credits' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => FALSE,
            ],
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('course_id', TRUE);

        $this->dbforge->create_table('courses', TRUE, array('ENGINE' => 'InnoDB'));  // Changed back to plural 'courses'

        // Add foreign key with named constraint for department
        $sql = "ALTER TABLE `courses`
                ADD CONSTRAINT `fk_courses_department`
                FOREIGN KEY (`department_id`)
                REFERENCES `departments` (`department_id`)
                ON DELETE SET NULL
                ON UPDATE CASCADE";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->dbforge->drop_table('courses', TRUE);  // Changed back to plural 'courses'
    }
}