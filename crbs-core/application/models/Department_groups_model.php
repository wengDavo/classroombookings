<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_groups_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');
    }

    function Get($department_group_id = NULL, $pp = 10, $start = 0)
    {
        if ($department_group_id == NULL) {
            return $this->crud_model->Get('department_groups', NULL, NULL, NULL, 'department_class_id asc, identifier asc', $pp, $start);
        } else {
            return $this->crud_model->Get('department_groups', 'department_group_id', $department_group_id);
        }
    }

    public function insert($data = [])
    {
        $insert = $this->db->insert('department_groups', $data);
        return ($insert ? $this->db->insert_id() : FALSE);
    }

    public function update($department_group_id, $data = [])
    {
        $where = ['department_group_id' => $department_group_id];
        return $this->db->update('department_groups', $data, $where);
    }

    public function delete($department_group_id)
    {
        return $this->db->delete('department_groups', ['department_group_id' => $department_group_id]);
    }
}