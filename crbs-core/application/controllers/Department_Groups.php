<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_groups extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');       
        $this->load->model('department_groups_model');
        $this->load->library('pagination');    
    }

    public function index($page = NULL)
    {
        $pagination_config = array(
            'base_url' => site_url('department_groups/index'),
            'total_rows' => $this->crud_model->Count('department_groups'),
            'per_page' => 25,
            'full_tag_open' => '<p class="pagination">',
            'full_tag_close' => '</p>',
        );

        $this->pagination->initialize($pagination_config);

        $this->data['pagelinks'] = $this->pagination->create_links();
        $this->data['department_groups'] = $this->department_groups_model->Get(NULL, $pagination_config['per_page'], $page);

        $this->data['title'] = 'Department Groups';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('department_groups/department_groups_index', $this->data, TRUE);

        return $this->render();
    }

    public function add()
    {
        $this->data['title'] = 'Add Department Group';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('department_groups/department_groups_add', NULL, TRUE);

        return $this->render();
    }

    public function edit($department_group_id)
    {
        $this->data['department_group'] = $this->department_groups_model->Get($department_group_id);
        if (empty($this->data['department_group'])) {
            show_404();
        }

        $this->data['title'] = 'Edit Department Group';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('department_groups/department_groups_add', $this->data, TRUE);
        return $this->render();
    }

    public function save()
    {
        $department_group_id = $this->input->post('department_group_id');

        $this->load->library('form_validation');

        $this->form_validation->set_rules('department_class_id', 'Department Class', 'required|integer');
        $this->form_validation->set_rules('size', 'Size', 'required|integer|greater_than[0]|less_than[1001]');
        $this->form_validation->set_rules('identifier', 'Identifier', 'required|exact_length[1]');

        if (!empty($department_group_id)) {
            $this->form_validation->set_rules('department_group_id', 'ID', 'integer');
        }

        if ($this->form_validation->run() == FALSE) {
            return (empty($department_group_id) ? $this->add() : $this->edit($department_group_id));
        }

        // Fetch level and department names for name generation
        $department_class_id = $this->input->post('department_class_id');
        $this->db->select('levels.name as level_name, departments.name as dept_name');
        $this->db->from('department_classes');
        $this->db->join('levels', 'department_classes.level_id = levels.level_id');
        $this->db->join('departments', 'department_classes.department_id = departments.department_id');
        $this->db->where('department_classes.department_class_id', $department_class_id);
        $class_info = $this->db->get()->row();

        $level_name = $class_info ? $class_info->level_name : 'Level Unknown';
        $dept_name = $class_info ? $class_info->dept_name : 'Dept Unknown';
        $identifier = $this->input->post('identifier');
        $generated_name = "$dept_name $level_name $identifier";

        $department_group_data = array(
            'department_class_id' => $department_class_id,
            'name' => $generated_name,
            'size' => $this->input->post('size'),
            'identifier' => $identifier
        );

        if (empty($department_group_id)) {
            // Add new department group
            $department_group_id = $this->department_groups_model->insert($department_group_data);

            if ($department_group_id) {
                $line = sprintf($this->lang->line('crbs_action_added'), $generated_name);
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'adding');
                $flashmsg = msgbox('error', $line);
            }
        } else {
            // Update existing department group
            if ($this->department_groups_model->update($department_group_id, $department_group_data)) {
                $line = sprintf($this->lang->line('crbs_action_saved'), $generated_name);
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'editing');
                $flashmsg = msgbox('error', $line);
            }
        }

        $this->session->set_flashdata('saved', $flashmsg);
        redirect('department_groups');
    }

    public function delete($id = NULL)
    {
        if ($this->input->post('id')) {
            $this->department_groups_model->delete($this->input->post('id'));
            $flashmsg = msgbox('info', $this->lang->line('crbs_action_deleted'));
            $this->session->set_flashdata('saved', $flashmsg);
            redirect('department_groups');
        }
    
        $row = $this->department_groups_model->Get($id);
        if (!$row) {
            $this->session->set_flashdata('saved', msgbox('error', 'Department group not found.'));
            redirect('department_groups');
        }
    
        $this->data['action'] = 'department_groups/delete';
        $this->data['id'] = $id;
        $this->data['cancel'] = 'department_groups';
        $this->data['text'] = 'If you delete this department group, any associated records (e.g., student enrollments) may need to be updated or removed.';
    
        $this->data['title'] = 'Delete Department Group (' . $row->name . ')';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('partials/deleteconfirm', $this->data, TRUE);
    
        return $this->render();
    }
}