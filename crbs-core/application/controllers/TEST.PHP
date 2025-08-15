<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');
        $this->load->model('department_groups_model');
    }

    public function test_get_all()
    {
        $department_groups = $this->department_groups_model->Get(NULL, 10, 0);
        echo '<pre>';
        if ($department_groups) {
            print_r($department_groups);
        } else {
            echo "No department groups found or error.";
        }
        echo '</pre>';
    }

    public function test_get_one()
    {
        $department_group = $this->department_groups_model->Get(1); // Assuming department_group_id 1 exists
        echo '<pre>';
        if ($department_group) {
            print_r($department_group);
        } else {
            echo "Department group not found or error.";
        }
        echo '</pre>';
    }

    public function test_insert()
    {
        $data = [
            'department_class_id' => 3,  // Example FK to department_classes (adjust as needed)
            'name' => 'SE Level 4 Group A', // Descriptive name
            'size' => 30,                // Group size
            'identifier' => 'A'          // Identifier (unique per department_class_id)
        ];
        $result = $this->department_groups_model->insert($data);
        echo $result ? "Inserted department group with ID: $result" : "Insert failed.";
    }

    public function test_update()
    {
        $data = ['size' => 35]; // Example update (e.g., increase size)
        $result = $this->department_groups_model->update(1, $data); // Update department_group_id 1
        echo $result ? "Update successful" : "Update failed.";
    }

    public function test_delete()
    {
        $result = $this->department_groups_model->delete(1); // Delete department_group_id 1
        echo $result ? "Delete successful" : "Delete failed.";
    }
}