<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courses_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');
    }

    function Get($course_id = NULL, $pp = 10, $start = 0)
    {
        if ($course_id == NULL) {
            return $this->crud_model->Get('courses', NULL, NULL, NULL, 'name asc', $pp, $start);
        } else {
            return $this->crud_model->Get('courses', 'course_id', $course_id);
        }
    }

    public function insert($data = [])
    {
        $insert = $this->db->insert('courses', $data);
        return ($insert ? $this->db->insert_id() : FALSE);
    }

    public function update($course_id, $data = [])
    {
        $where = ['course_id' => $course_id];
        return $this->db->update('courses', $data, $where);
    }

    public function delete($course_id)
    {
        return $this->db->delete('courses', ['course_id' => $course_id]);
    }
}