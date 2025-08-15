<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_bookings_add_cohorts_and_courses extends CI_Migration
{
    public function up()
    {
        // Define fields to add, matching cohorts and courses structure
        $fields = [
            'cohort_id' => [
                'type'       => 'INT',
                'constraint' => 11,  // Matches cohorts.cohort_id
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'after'      => 'department_id'
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,  // Matches courses.course_id
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'after'      => 'cohort_id'
            ]
        ];

        // Add the new columns to the bookings table
        $this->dbforge->add_column('bookings', $fields);

        // Add indexes for performance
        $this->db->query("ALTER TABLE `bookings` ADD INDEX `idx_bookings_cohort_id` (`cohort_id`)");
        $this->db->query("ALTER TABLE `bookings` ADD INDEX `idx_bookings_course_id` (`course_id`)");

        // Add foreign key constraints
        $this->db->query("ALTER TABLE `bookings` 
            ADD CONSTRAINT `fk_bookings_cohort_id` 
            FOREIGN KEY (`cohort_id`) 
            REFERENCES `cohorts` (`cohort_id`) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE");

        $this->db->query("ALTER TABLE `bookings` 
            ADD CONSTRAINT `fk_bookings_course_id` 
            FOREIGN KEY (`course_id`) 
            REFERENCES `courses` (`course_id`) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE");
    }

    public function down()
    {
        // Drop foreign key constraints first
        $this->db->query("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_cohort_id`");
        $this->db->query("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_course_id`");

        // Drop indexes
        $this->db->query("DROP INDEX `idx_bookings_cohort_id` ON `bookings`");
        $this->db->query("DROP INDEX `idx_bookings_course_id` ON `bookings`");

        // Drop the columns
        $this->dbforge->drop_column('bookings', 'cohort_id');
        $this->dbforge->drop_column('bookings', 'course_id');
    }
}