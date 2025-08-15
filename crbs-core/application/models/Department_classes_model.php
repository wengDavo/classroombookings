<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_classes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');
    }

    function Get($department_class_id = NULL, $pp = 10, $start = 0)
    {
        if ($department_class_id == NULL) {
            return $this->crud_model->Get('department_classes', NULL, NULL, NULL, 'level_id asc, department_id asc', $pp, $start);
        } else {
            return $this->crud_model->Get('department_classes', 'department_class_id', $department_class_id);
        }
    }

    public function insert($data = [])
    {
        $insert = $this->db->insert('department_classes', $data);
        return ($insert ? $this->db->insert_id() : FALSE);
    }

    public function update($department_class_id, $data = [])
    {
        $where = ['department_class_id' => $department_class_id];
        return $this->db->update('department_classes', $data, $where);
    }

    public function delete($department_class_id)
    {
        return $this->db->delete('department_classes', ['department_class_id' => $department_class_id]);
    }
}