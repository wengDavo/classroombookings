<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courses extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crud_model');       
        $this->load->model('courses_model');
        $this->load->library('pagination');    
    }

    public function index($page = NULL)
    {
        $pagination_config = array(
            'base_url' => site_url('courses/index'),
            'total_rows' => $this->crud_model->Count('courses'),
            'per_page' => 25,
            'full_tag_open' => '<p class="pagination">',
            'full_tag_close' => '</p>',
        );

        $this->pagination->initialize($pagination_config);

        $this->data['pagelinks'] = $this->pagination->create_links();
        // Get list of courses from database
        $this->data['courses'] = $this->courses_model->Get(NULL, $pagination_config['per_page'], $page);

        $this->data['title'] = 'Courses';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('courses/courses_index', $this->data, TRUE);

        return $this->render();
    }

	function add()
	{
		// Load view
		$this->data['title'] = 'Add Course';
		$this->data['showtitle'] = $this->data['title'];
		$this->data['body'] = $this->load->view('courses/courses_add', NULL, TRUE);

		return $this->render();
	}

    public function edit($course_id)
    {
        $this->data['course'] = $this->courses_model->Get($course_id);
        if (empty($this->data['course'])) {
			show_404();
		}

        $this->data['title'] = 'Edit Course';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('courses/courses_add', $this->data, TRUE);
        return $this->render();
    }

    public function save()
    {
        $course_id = $this->input->post('course_id');
    
        $this->load->library('form_validation');
    
        $this->form_validation->set_rules('course_id', 'ID', 'integer');
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[1]|max_length[50]');
        $this->form_validation->set_rules('course_code', 'Course Code', 'required|min_length[1]|max_length[20]');
        $this->form_validation->set_rules('department_id', 'Department', 'required|integer');
        $this->form_validation->set_rules('credits', 'Credits', 'required|integer|greater_than[0]|less_than[11]');
        $this->form_validation->set_rules('level_id', 'Level', 'integer'); // Optional validation for level_id
    
        if ($this->form_validation->run() == FALSE) {
            return (empty($course_id) ? $this->add() : $this->edit($course_id));
        }
    
        $course_data = array(
            'name' => $this->input->post('name'),
            'course_code' => $this->input->post('course_code'),
            'department_id' => $this->input->post('department_id'),
            'credits' => $this->input->post('credits'),
            'level_id' => $this->input->post('level_id') ?: NULL // Include level_id, default to NULL if empty
        );
    
        if (empty($course_id)) {
            // Add new course
            $course_id = $this->courses_model->insert($course_data);
    
            if ($course_id) {
                $line = sprintf($this->lang->line('crbs_action_added'), $course_data['name']);
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'adding');
                $flashmsg = msgbox('error', $line);
            }
        } else {
            // Update existing course
            if ($this->courses_model->update($course_id, $course_data)) {
                $line = sprintf($this->lang->line('crbs_action_saved'), $course_data['name']);
                $flashmsg = msgbox('info', $line);
            } else {
                $line = sprintf($this->lang->line('crbs_action_dberror'), 'editing');
                $flashmsg = msgbox('error', $line);
            }
        }
    
        $this->session->set_flashdata('saved', $flashmsg);
        redirect('courses');
    }

    public function delete($id = NULL)
    {
        if ($this->input->post('id')) {
            $this->courses_model->delete($this->input->post('id'));
            $flashmsg = msgbox('info', $this->lang->line('crbs_action_deleted'));
            $this->session->set_flashdata('saved', $flashmsg);
            redirect('courses');
        }
    
        $row = $this->courses_model->Get($id);
        if (!$row) {
            $this->session->set_flashdata('saved', msgbox('error', 'Course not found.'));
            redirect('courses');
        }
    
        $this->data['action'] = 'courses/delete';
        $this->data['id'] = $id;
        $this->data['cancel'] = 'courses';
        $this->data['text'] = 'If you delete this course, any associated records (e.g., enrollments) may need to be updated or removed.';
    
        $this->data['title'] = 'Delete Course (' . $row->name . ')';
        $this->data['showtitle'] = $this->data['title'];
        $this->data['body'] = $this->load->view('partials/deleteconfirm', $this->data, TRUE);
    
        return $this->render();
    }
}