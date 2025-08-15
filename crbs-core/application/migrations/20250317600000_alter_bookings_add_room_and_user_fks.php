<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_bookings_add_room_and_user_fks extends CI_Migration
{
    public function up()
    {
        // Add FK for room_id (assuming rooms table exists)
        $this->db->query("ALTER TABLE `bookings`
            ADD CONSTRAINT `fk_bookings_room_id`
            FOREIGN KEY (`room_id`)
            REFERENCES `rooms` (`room_id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE");

        // Add FK for user_id (assuming users table exists)
        $this->db->query("ALTER TABLE `bookings`
            ADD CONSTRAINT `fk_bookings_user_id`
            FOREIGN KEY (`user_id`)
            REFERENCES `users` (`user_id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE");
    }

    public function down()
    {
        // Drop foreign key constraints
        $this->db->query("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_room_id`");
        $this->db->query("ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_user_id`");
    }
}