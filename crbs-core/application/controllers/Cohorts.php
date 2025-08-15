<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cohorts extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('cohorts_model');
        $this->load->library('pagination');
    }

    public function index($page = NULL)
    {
        $per_page = 25;
        $start = ($page > 0) ? ($page - 1) * $per_page : 0;

        $pagination_config = array(
            'base_url' => site_url('cohorts/index'),
            'total_rows' => $this->db->count_all('cohorts'),
            'per_page' => $per_page,
            'full_tag_open' => '<p class="pagination">',
            'full_tag_close' => '</p>',
        );

        $this->pagination->initialize($pagination_config);

        $this->data['pagelinks'] = $this->pagination->create_links();
        $this->data['cohorts'] = $this->cohorts_model->Get(NULL, $per_page, $start);

        $this->data['title'] = 'Cohorts';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('cohorts/cohorts_index', $this->data, TRUE);

        return $this->render();
    }

    function add()
	{
		// Load view
		$this->data['title'] = 'Add Cohort';
		$this->data['showtitle'] = $this->data['title'];
		$this->data['body'] = $this->load->view('cohorts/cohorts_add', NULL, TRUE);

		return $this->render();
	}

    public function edit($cohort_id)
    {
        $this->data['cohort'] = $this->cohorts_model->Get($cohort_id);
        if (empty($this->data['cohort'])) {
            show_404();
        }

        $this->data['title'] = 'Edit Cohort';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('cohorts/cohorts_add', $this->data, TRUE);
        return $this->render();
    }

    public function save()
    {
        $cohort_id = $this->input->post('cohort_id');

        $this->load->library('form_validation');

        $this->form_validation->set_rules('cohort_id', 'ID', 'integer');
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[1]|max_length[50]');
        $this->form_validation->set_rules('cohort_identifier', 'Cohort Identifier', 'required|min_length[1]|max_length[1]');
        $this->form_validation->set_rules('level_id', 'Level', 'required|integer');
        $this->form_validation->set_rules('department_id', 'Department', 'required|integer');
        $this->form_validation->set_rules('size', 'Size', 'required|integer|greater_than[0]');

        if ($this->form_validation->run() == FALSE) {
            return (empty($cohort_id) ? $this->add() : $this->edit($cohort_id));
        }

        $cohort_data = array(
            'name' => $this->input->post('name'),
            'cohort_identifier' => $this->input->post('cohort_identifier'),
            'level_id' => $this->input->post('level_id'),
            'department_id' => $this->input->post('department_id'),
            'size' => $this->input->post('size')
        );

        if (empty($cohort_id)) {
            // Add new cohort
            $cohort_id = $this->cohorts_model->insert($cohort_data);

            if ($cohort_id) {
                $line = sprintf($this->lang->line('crbs_action_added'), $cohort_data['name']);
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'adding');
                $flashmsg = msgbox('error', $line);
            }
        } else {
            // Update existing cohort
            if ($this->cohorts_model->update($cohort_id, $cohort_data)) {
                $line = sprintf($this->lang->line('crbs_action_saved'), $cohort_data['name']);
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'editing');
                $flashmsg = msgbox('error', $line);
            }
        }

        $this->session->set_flashdata('saved', $flashmsg);
        redirect('cohorts');
    }

    public function delete($id = NULL)
    {
        if ($this->input->post('id')) {
            $this->cohorts_model->delete($this->input->post('id'));
            $flashmsg = msgbox('info', $this->lang->line('crbs_action_deleted'));
            $this->session->set_flashdata('saved', $flashmsg);
            redirect('cohorts');
        }
    
        $row = $this->cohorts_model->Get($id);
        if (!$row) {
            $this->session->set_flashdata('saved', msgbox('error', 'Cohort not found.'));
            redirect('cohorts');
        }
    
        $this->data['action'] = 'cohorts/delete';
        $this->data['id'] = $id;
        $this->data['cancel'] = 'cohorts';
        $this->data['text'] = 'If you delete this cohort, any associated records (e.g., student assignments) may need to be updated or removed.';
    
        $this->data['title'] = 'Delete Cohort (' . $row->name . ')';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('partials/deleteconfirm', $this->data, TRUE);
    
        return $this->render();
    }
}