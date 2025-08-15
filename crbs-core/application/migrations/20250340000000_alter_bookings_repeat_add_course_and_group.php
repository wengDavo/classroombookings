<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_bookings_repeat_add_course_and_group extends CI_Migration
{
    public function up()
    {
        // Add course_id
        $this->dbforge->add_column('bookings_repeat', [
            'course_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL,
                'after' => 'notes'
            ]
        ]);

        // Add department_group_id
        $this->dbforge->add_column('bookings_repeat', [
            'department_group_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL,
                'after' => 'course_id'
            ]
        ]);

        // Add foreign key constraints
        $this->db->query("ALTER TABLE `bookings_repeat`
            ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD FOREIGN KEY (`department_group_id`) REFERENCES `department_groups` (`department_group_id`) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        // Remove foreign keys first
        $this->db->query("ALTER TABLE `bookings_repeat` DROP FOREIGN KEY `bookings_repeat_ibfk_1`");
        $this->db->query("ALTER TABLE `bookings_repeat` DROP FOREIGN KEY `bookings_repeat_ibfk_2`");

        // Remove columns
        $this->dbforge->drop_column('bookings_repeat', 'course_id');
        $this->dbforge->drop_column('bookings_repeat', 'department_group_id');
    }
}