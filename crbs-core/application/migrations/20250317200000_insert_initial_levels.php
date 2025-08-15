<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Insert_initial_levels extends CI_Migration
{
    public function up()
    {
        // Array of levels to insert (100 to 600 with increments of 100)
        $levels = [
            ['level_code' => 100, 'name' => 'Level 100'],
            ['level_code' => 200, 'name' => 'Level 200'],
            ['level_code' => 300, 'name' => 'Level 300'],
            ['level_code' => 400, 'name' => 'Level 400'],
            ['level_code' => 500, 'name' => 'Level 500'],
            ['level_code' => 600, 'name' => 'Level 600'],
        ];

        // Insert each level into the levels table
        foreach ($levels as $level) {
            $sql = "INSERT INTO levels (
                        `level_code`,
                        `name`
                    ) VALUES (
                        " . $this->db->escape($level['level_code']) . ",
                        " . $this->db->escape($level['name']) . "
                    )";
            $this->db->query($sql);
        }
    }

    public function down()
    {
        // Delete all levels from 100 to 600
        $sql = "DELETE FROM levels WHERE level_code IN (100, 200, 300, 400, 500, 600)";
        $this->db->query($sql);
    }
}