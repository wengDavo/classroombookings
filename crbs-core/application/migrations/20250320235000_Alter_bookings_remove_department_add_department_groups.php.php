<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_bookings_remove_department_add_department_groups extends CI_Migration
{
    public function up()
    {
        // Drop foreign key constraint for department_id (if it exists)
        $this->db->query("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_department_id`");

        // Drop index for department_id (if it exists)
        $this->db->query("DROP INDEX `idx_bookings_department_id` ON `bookings`");

        // Drop the department_id column
        $this->dbforge->drop_column('bookings', 'department_id');

        // Define new field for department_group_id
        $fields = [
            'department_group_id' => [
                'type'       => 'INT',
                'constraint' => 6,  // Matches original booking table constraints
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'after'      => 'user_id' // Position after user_id, where department_id originally was
            ]
        ];

        // Add the new column to the bookings table
        $this->dbforge->add_column('bookings', $fields);

        // Add index for performance
        $this->db->query("ALTER TABLE `bookings` ADD INDEX `idx_bookings_department_group_id` (`department_group_id`)");

        // Add foreign key constraint
        $this->db->query("ALTER TABLE `bookings` 
            ADD CONSTRAINT `fk_bookings_department_group_id` 
            FOREIGN KEY (`department_group_id`) 
            REFERENCES `department_groups` (`department_group_id`) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE");
    }

    public function down()
    {
        // Reverse the changes: Drop department_group_id and re-add department_id

        // Drop foreign key constraint for department_group_id
        $this->db->query("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_department_group_id`");

        // Drop index for department_group_id
        $this->db->query("DROP INDEX `idx_bookings_department_group_id` ON `bookings`");

        // Drop the department_group_id column
        $this->dbforge->drop_column('bookings', 'department_group_id');

        // Re-add department_id field (from original creation)
        $fields = [
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 6,
                'unsigned'   => TRUE,
                'null'       => TRUE,
                'after'      => 'user_id'
            ]
        ];

        // Add the column back
        $this->dbforge->add_column('bookings', $fields);

        // Re-add index
        $this->db->query("ALTER TABLE `bookings` ADD INDEX `idx_bookings_department_id` (`department_id`)");

        // Re-add foreign key constraint
        $this->db->query("ALTER TABLE `bookings` 
            ADD CONSTRAINT `fk_bookings_department_id` 
            FOREIGN KEY (`department_id`) 
            REFERENCES `departments` (`department_id`) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE");
    }
}