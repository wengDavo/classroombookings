<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_rooms_add_capacity extends CI_Migration
{
    public function up()
    {
        // Add the 'capacity' column to the 'rooms' table
        $fields = [
            'capacity' => [
                'type' => 'INT',
                'constraint' => 11, // Standard integer length
                'unsigned' => TRUE, // Non-negative values only
                'default' => 0,     // Default to 0 if not specified
                'after' => 'bookable', // Place after 'bookable' column
            ],
        ];

        $this->dbforge->add_column('rooms', $fields);
    }

    public function down()
    {
        // Remove the 'capacity' column if rolling back
        $this->dbforge->drop_column('rooms', 'capacity');
    }
}