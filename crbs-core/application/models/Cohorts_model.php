<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cohorts_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');
    }

    /**
     * Retrieve cohort(s) from the database.
     * 
     * @param int|null $cohort_id Specific cohort ID to fetch (optional)
     * @param int $pp Per page limit for pagination (default: 10)
     * @param int $start Offset for pagination (default: 0)
     * @return array|object|null Cohort data or null if not found
     */
    function Get($cohort_id = NULL, $pp = 10, $start = 0)
    {
        if ($cohort_id == NULL) {
            return $this->crud_model->Get('cohorts', NULL, NULL, NULL, 'name asc', $pp, $start);
        } else {
            return $this->crud_model->Get('cohorts', 'cohort_id', $cohort_id);
        }
    }

    /**
     * Insert a new cohort into the database.
     * 
     * @param array $data Cohort data to insert
     * @return int|bool Inserted cohort ID or FALSE on failure
     */
    public function insert($data = [])
    {
        $insert = $this->db->insert('cohorts', $data);
        return ($insert ? $this->db->insert_id() : FALSE);
    }

    /**
     * Update an existing cohort.
     * 
     * @param int $cohort_id Cohort ID to update
     * @param array $data Data to update
     * @return bool TRUE on success, FALSE on failure
     */
    public function update($cohort_id, $data = [])
    {
        $where = ['cohort_id' => $cohort_id];
        return $this->db->update('cohorts', $data, $where);
    }

    /**
     * Delete a cohort from the database.
     * 
     * @param int $cohort_id Cohort ID to delete
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete($cohort_id)
    {
        return $this->db->delete('cohorts', ['cohort_id' => $cohort_id]);
    }
}