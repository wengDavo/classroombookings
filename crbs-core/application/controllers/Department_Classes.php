<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_classes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');       
        $this->load->model('department_classes_model');
        $this->load->library('pagination');    
    }

    public function index($page = NULL)
    {
        $pagination_config = array(
            'base_url' => site_url('department_classes/index'),
            'total_rows' => $this->crud_model->Count('department_classes'),
            'per_page' => 25,
            'full_tag_open' => '<p class="pagination">',
            'full_tag_close' => '</p>',
        );

        $this->pagination->initialize($pagination_config);

        $this->data['pagelinks'] = $this->pagination->create_links();
        // Get list of department classes from database
        $this->data['department_classes'] = $this->department_classes_model->Get(NULL, $pagination_config['per_page'], $page);

        $this->data['title'] = 'Department Classes';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('department_classes/department_classes_index', $this->data, TRUE);

        return $this->render();
    }

    function add()
    {
        // Load view
        $this->data['title'] = 'Add Department Class';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('department_classes/department_classes_add', NULL, TRUE);

        return $this->render();
    }

    public function edit($department_class_id)
    {
        $this->data['department_class'] = $this->department_classes_model->Get($department_class_id);
        if (empty($this->data['department_class'])) {
            show_404();
        }

        $this->data['title'] = 'Edit Department Class';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('department_classes/department_classes_add', $this->data, TRUE);
        return $this->render();
    }

    public function save()
    {
        $department_class_id = $this->input->post('department_class_id');

        $this->load->library('form_validation');

        $this->form_validation->set_rules('department_class_id', 'ID', 'integer');
        $this->form_validation->set_rules('level_id', 'Level', 'required|integer');
        $this->form_validation->set_rules('department_id', 'Department', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            return (empty($department_class_id) ? $this->add() : $this->edit($department_class_id));
        }

        $department_class_data = array(
            'level_id' => $this->input->post('level_id'),
            'department_id' => $this->input->post('department_id')
        );

        if (empty($department_class_id)) {
            // Add new department class
            $department_class_id = $this->department_classes_model->insert($department_class_data);

            if ($department_class_id) {
                $line = sprintf($this->lang->line('crbs_action_added'), "Level {$department_class_data['level_id']} in Department {$department_class_data['department_id']}");
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'adding');
                $flashmsg = msgbox('error', $line);
            }
        } else {
            // Update existing department class
            if ($this->department_classes_model->update($department_class_id, $department_class_data)) {
                $line = sprintf($this->lang->line('crbs_action_saved'), "Level {$department_class_data['level_id']} in Department {$department_class_data['department_id']}");
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'editing');
                $flashmsg = msgbox('error', $line);
            }
        }

        $this->session->set_flashdata('saved', $flashmsg);
        redirect('department_classes');
    }

    public function delete($id = NULL)
    {
        if ($this->input->post('id')) {
            $this->department_classes_model->delete($this->input->post('id'));
            $flashmsg = msgbox('info', $this->lang->line('crbs_action_deleted'));
            $this->session->set_flashdata('saved', $flashmsg);
            redirect('department_classes');
        }
    
        $row = $this->department_classes_model->Get($id);
        if (!$row) {
            $this->session->set_flashdata('saved', msgbox('error', 'Department class not found.'));
            redirect('department_classes');
        }
    
        $this->data['action'] = 'department_classes/delete';
        $this->data['id'] = $id;
        $this->data['cancel'] = 'department_classes';
        $this->data['text'] = 'If you delete this department class, any associated records (e.g., department groups) may need to be updated or removed.';
    
        $this->data['title'] = 'Delete Department Class (Level ' . $row->level_id . ', Department ' . $row->department_id . ')';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('partials/deleteconfirm', $this->data, TRUE);
    
        return $this->render();
    }
}