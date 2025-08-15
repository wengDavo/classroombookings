<?php

namespace app\components\bookings\agent;

defined('BASEPATH') OR exit('No direct script access allowed');


use app\components\bookings\exceptions\AgentException;
use app\components\bookings\Slot;
use \Bookings_model;


/**
 * Agent handles the creation/editing/cancellation of bookings.
 *
 */
class MultiAgent extends BaseAgent
{


	// Agent type
	const TYPE = 'multi';

	protected $department;

	private $selected_slots;
	private $multibooking;
	private $max_allowed_bookings = NULL;

	protected $department_group;
    protected $course;


	public function get_view_data()
	{
		return [
			'department' => $this->department,
		];
	}


	/**
	 * Initialise the Agent with some values.
	 *
	 * Depending on the type of booking, these will be retrieved from different places.
	 *
	 */
	public function load()
	{
		$department_id = $this->user->department_id;
		if ($this->is_admin && $this->CI->input->post('department_id')) {
			$department_id = $this->CI->input->post('department_id');
		}

		if (!empty($department_id)) {
			$this->department = $this->CI->departments_model->Get($department_id);
		}

		// Item 1: Set department_group with null default
        $department_group_id = $this->CI->input->post_get('department_group_id');
        if (!empty($department_group_id)) {
            $this->department_group = $this->CI->department_groups_model->get($department_group_id);
        } else {
            $this->department_group = null;
        }

        // Item 1: Set course with null default
        $course_id = $this->CI->input->post_get('course_id');
        if (!empty($course_id)) {
            $this->course = $this->CI->courses_model->Get($course_id);
        } else {
            $this->course = null;
        }

		// Check if the number of bookings selected is within the user's quota
		if ( ! $this->is_admin) {
			$max_active_bookings = (int) abs(setting('num_max_bookings'));
			if ($max_active_bookings > 0) {
				$user_active_booking_count = $this->CI->bookings_model->CountScheduledByUser($this->user->user_id);
				$this->max_allowed_bookings = ($max_active_bookings - $user_active_booking_count);
			}
		}

		$this->view = 'bookings/create/multi';
		$this->title = 'Create multiple bookings';

		$mb_id = (int) $this->CI->input->post_get('mb_id');
		$step = $this->CI->input->post_get('step');

		// Load the multibooking data from the DB if ID is provided.
		//
		if ($mb_id) {

			$this->view_data['mb_id'] = $mb_id;

			$multibooking = $this->CI->multi_booking_model->get($mb_id, $this->user->user_id);

			if ( ! $multibooking) {
				throw new AgentException('Could not load booking data.');
			}

			$this->multibooking = $multibooking;
			$this->session = $this->CI->sessions_model->get($multibooking->session_id);
			$this->view_data['multibooking'] = $multibooking;
		}

		// Determine the handler method based on the provided 'step' value.
		//

		switch ($step) {

			case 'selection':
				$this->handle_selection();
				break;

			case 'details':
				$this->handle_details();
				break;

			case 'recurring_customise':
				$this->handle_recurring_customise();
				break;

			case 'recurring_preview':
				$this->handle_recurring_preview();
				break;
		}
	}


	/**
	 * Handle the POST input from the bookings grid.
	 *
	 * No 'view' page for this one so immediately defer to processing the data.
	 *
	 */
	private function handle_selection()
	{
		if ($this->CI->input->post()) {
			$this->process_selection();
		}
	}


	/**
	 * First step of creating bookings.
	 *
	 * Show form to choose single/recurring.
	 *
	 *  For single bookings:
	 *  	- individual bookings can be (un-)selected and user can enter department/user/notes.
	 *
	 *  For recurring bookings:
	 *  	- Provide default user/department/notes.
	 *
	 */
	private function handle_details()
	{
		$this->view = 'bookings/create/multi/details';
		$this->title = 'Create multiple bookings';

		switch ($this->CI->input->post('type')) {

			case 'single':
				$this->process_create_single();
				break;

			case 'recurring':
				$this->process_recurring_defaults();
				break;
		}
	}


	private function handle_recurring_customise()
	{
		$this->view = 'bookings/create/multi/recurring_customise';
		$this->title = 'Create multiple recurring bookings';

		$session_key = sprintf('mb_%d', $this->view_data['mb_id']);
		$this->view_data['default_values'] = isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : [];

		if ($this->CI->input->post()) {
			$this->process_recurring_customise();
		}
	}


	private function handle_recurring_preview()
	{
		$this->view = 'bookings/create/multi/recurring_preview';
		$this->title = 'Create multiple recurring bookings';

		$session_key = sprintf('mb_%d_slots', $this->view_data['mb_id']);

		$slot_data = isset($_SESSION[$session_key]) ? $_SESSION[$session_key] : [];

		// die(print_r($slot_data));

		// Get existing bookings for conflicts

		// Loop through all slots in this multibooking
		foreach ($this->multibooking->slots as &$slot) {

			$mbs_id = $slot->mbs_id;

			$slot->conflict_count = 0;

			// Get booking data values from previous step
			$data = isset($slot_data[$mbs_id]) ? $slot_data[$mbs_id] : FALSE;
			if ( ! $data) continue;

			$recurring_start = datetime_from_string($data['recurring_start']);
			$recurring_end = datetime_from_string($data['recurring_end']);

			$dates = [];
			$instances = [];

			foreach ($slot->recurring_dates as $row) {
				if ($row->date < $recurring_start) continue;
				if ($row->date > $recurring_end) continue;
				$date_ymd = $row->date->format('Y-m-d');
				// Add date to list of dates for find_conflicts() check
				$dates[] = $date_ymd;
				// Generate key and add the date to the list of instances that will be created
				$key = Slot::generate_key($date_ymd, $slot->period_id, $slot->room_id);
				$instances[$key]['datetime'] = $row->date;
			}

			// Find conflicts for this booking
			$existing_bookings = $this->CI->bookings_model->find_conflicts($dates, $slot->period_id, $slot->room_id);

			// Update 'instances' data with the options for each one
			foreach ($instances as $key => $instance) {

				$actions = [];

				if (array_key_exists($key, $existing_bookings)) {
					$actions['do_not_book'] = 'Keep existing booking';
					$actions['replace'] = 'Replace existing booking';
					$instances[$key]['booking'] = $existing_bookings[$key];
					$slot->conflict_count++;
				} else {
					$actions['book'] = 'Book';
					$actions['do_not_book'] = 'Do not book';
				}

				$instances[$key]['actions'] = $actions;
			}

			$slot->existing_bookings = $existing_bookings;
			$slot->instances = $instances;
		}

		$this->view_data['slot_data'] = $slot_data;

		if ($this->CI->input->post()) {
			$this->process_create_recurring();
		}
	}




	/**
	 * Initial selection step.
	 *
	 * Create new multibookingentry and go to next step.
	 *
	 */
	public function process_selection()
	{
		$slots = $this->CI->input->post('slots');

		if ( ! $slots || empty($slots)) {
			throw new AgentException("You did not select any free slots to book.");
		}


		if ( ! $this->is_admin && is_numeric($this->max_allowed_bookings)) {
			if (count($slots) > $this->max_allowed_bookings) {
				$msg = "You can only create a maximum of %d booking(s), please select fewer periods.";
				throw new AgentException(sprintf($msg, $this->max_allowed_bookings));
			}
		}

		// Rows of data for multibooking.
		$rows = [];

		// Validation rules
		$rules = [
			['field' => 'date', 'label' => 'Date', 'rules' => 'required|valid_date'],
			['field' => 'period_id', 'label' => 'Period', 'rules' => 'required|integer'],
			['field' => 'room_id', 'label' => 'Room', 'rules' => 'required|integer'],
		];

		$this->CI->load->library('form_validation');

		foreach ($slots as $json) {

			$data = json_decode($json, TRUE);

			$this->CI->form_validation->set_rules($rules);
			$this->CI->form_validation->set_data($data);

			if ($this->CI->form_validation->run() === FALSE) {
				throw new AgentException(validation_errors());
			}

			$rows[] = [
				'date' => $data['date'],
				'period_id' => $data['period_id'],
				'room_id' => $data['room_id'],
			];
		}

		// Get first date
		$date_ymd = $rows[0]['date'];
		// Get Date info
		$date_info = $this->CI->dates_model->get_by_date($date_ymd);

		// The scenarios below shouldn't really happen:

		if ( ! $date_info || ! $date_info->session_id) {
			throw new AgentException('Selected date does not belong to a session.');
		}

		if ( ! $date_info->week_id) {
			throw new AgentException('Selected date does not belong to a timetable week.');
		}

		// Got data - create multibooking entry.
		$mb_data = [
			'user_id' => $this->user->user_id,
			'session_id' => $date_info->session_id,
			'week_id' => $date_info->week_id,
			'slots' => $rows,
		];

		$mb_id = $this->CI->multi_booking_model->create($mb_data);

		if ( ! $mb_id) {
			throw new AgentException("Could not create multibooking entry.");
		}

		redirect(current_url() . '?' . http_build_query([
			'mb_id' => $mb_id,
			'step' => 'details',
		]));
	}


	/**
	 * Create multiple single bookings.
	 *
	 */
	// private function process_create_single()
	// {
	// 	$this->CI->load->library('form_validation');
	// 	$this->CI->load->model('Bookings_model');  // Load for conflict checking and creation
	
	// 	// Initial validation rules for form submission
	// 	$rules = [
	// 		['field' => 'mb_id', 'label' => 'ID', 'rules' => 'required|integer'],
	// 		['field' => 'type', 'label' => 'Type', 'rules' => 'required|in_list[single]'],
	// 		['field' => 'slot_single[]', 'label' => 'Slots', 'rules' => 'required'],
	// 	];
	
	// 	$this->CI->form_validation->set_rules($rules);
	
	// 	if ($this->CI->form_validation->run() === FALSE) {
	// 		$this->message = 'The form contained some invalid values. Please check and try again.';
	// 		return FALSE;
	// 	}
	
	// 	// Validation rules for individual booking data
	// 	$rules = [
	// 		['field' => 'date', 'label' => 'Date', 'rules' => 'required|valid_date'],
	// 		['field' => 'session_id', 'label' => 'Session', 'rules' => 'required|integer'],
	// 		['field' => 'period_id', 'label' => 'Period', 'rules' => 'required|integer'],
	// 		['field' => 'room_id', 'label' => 'Room', 'rules' => 'required|integer'],
	// 		['field' => 'department_id', 'label' => 'Department', 'rules' => 'integer'],
	// 		['field' => 'user_id', 'label' => 'User', 'rules' => 'integer'],
	// 		['field' => 'department_group_id', 'label' => 'Department Group', 'rules' => 'integer'],
	// 		['field' => 'course_id', 'label' => 'Course', 'rules' => 'integer'],
	// 		['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
	// 	];
	
	// 	$form_slots = $this->CI->input->post('slot_single');
	// 	$multibooking = $this->view_data['multibooking'];
	
	// 	$rows = [];          // Store validated booking data
	// 	$all_conflicts = []; // Store conflicts for each slot
	
	// 	foreach ($multibooking->slots as $slot_data) {
	// 		$mbs_id = $slot_data->mbs_id;
	
	// 		// Skip if slot is not in form or not selected for creation
	// 		if (!isset($form_slots[$mbs_id])) {
	// 			continue;
	// 		}
	// 		$form_slot = $form_slots[$mbs_id];
	// 		if ($form_slot['create'] == 0) {
	// 			continue;
	// 		}
	
	// 		// Determine department_id and user_id
	// 		$department_id = isset($form_slot['department_id']) ? $form_slot['department_id'] : NULL;
	// 		$user_id = isset($form_slot['user_id']) ? $form_slot['user_id'] : NULL;
	
	// 		// Force logged-in user details for non-admins
	// 		if (!$this->is_admin) {
	// 			$user_id = $this->user->user_id;
	// 			$department_id = $this->user->department_id;
	// 		}
	
	// 		if (empty($department_id)) {
	// 			$department_id = NULL;
	// 		}
	
	// 		// Add department_group_id and course_id from form or defaults
	// 		$department_group_id = isset($form_slot['department_group_id']) ? $form_slot['department_group_id'] : 
	// 			(isset($this->department_group) ? $this->department_group->department_group_id : NULL);
	// 		$course_id = isset($form_slot['course_id']) ? $form_slot['course_id'] : 
	// 			(isset($this->course) ? $this->course->course_id : NULL);
	
	// 		// Prepare booking data
	// 		$booking_data = [
	// 			'date' => $slot_data->date,
	// 			'session_id' => $multibooking->session_id,
	// 			'period_id' => $slot_data->period_id,
	// 			'room_id' => $slot_data->room_id,
	// 			'department_id' => $department_id,
	// 			'user_id' => !empty($user_id) ? $user_id : NULL,
	// 			'department_group_id' => $department_group_id,
	// 			'course_id' => $course_id,
	// 			'notes' => !empty($form_slot['notes']) ? $form_slot['notes'] : NULL,
	// 		];
	
	// 		// Validate booking data
	// 		$this->CI->form_validation->reset_validation();
	// 		$this->CI->form_validation->set_rules($rules);
	// 		$this->CI->form_validation->set_data($booking_data);
	
	// 		if ($this->CI->form_validation->run() === FALSE) {
	// 			$this->message = "One or more bookings (mbs_id: $mbs_id) contained invalid values. Please check and try again.";
	// 			return FALSE;
	// 		}
	
	// 		// Check for conflicts
	// 		$conflicts = $this->CI->Bookings_model->find_conflicts(
	// 			[$booking_data['date']], // Single date as an array
	// 			$booking_data['period_id'],
	// 			$booking_data['room_id'],
	// 			$booking_data['user_id'],
	// 			$booking_data['department_group_id']
	// 		);
	
	// 		// If conflicts exist, store them and skip this slot
	// 		if (!empty($conflicts['user_conflicts']) || 
	// 			!empty($conflicts['department_group_conflicts']) || 
	// 			!empty($conflicts['room_conflicts'])) {
	// 			$all_conflicts[$mbs_id] = $conflicts;
	// 			continue;
	// 		}
	
	// 		// If no conflicts, add to rows for creation
	// 		$rows[] = $booking_data;
	// 	}
	
	// 	// Handle conflicts
	// 	if (!empty($all_conflicts)) {
	// 		$this->message = 'Conflicts detected in one or more selected slots.';
	// 		$this->data = ['conflicts' => $all_conflicts];
	// 		return FALSE;
	// 	}
	
	// 	// If no bookings to create, fail early
	// 	if (empty($rows)) {
	// 		$this->message = 'No bookings were selected for creation.';
	// 		return FALSE;
	// 	}
	
	// 	// Create bookings within a transaction
	// 	$booking_ids = [];
	// 	$this->CI->db->trans_start();
	
	// 	foreach ($rows as $row) {
	// 		$booking_id = $this->CI->Bookings_model->create($row);
	// 		if ($booking_id) {
	// 			$booking_ids[] = $booking_id;
	// 		} else {
	// 			$this->CI->db->trans_rollback();
	// 			$err = $this->CI->Bookings_model->get_error();
	// 			$this->message = $err ?: 'Could not create one or more bookings.';
	// 			return FALSE;
	// 		}
	// 	}
	
	// 	$this->CI->db->trans_complete();
	
	// 	if ($this->CI->db->trans_status() === FALSE || count($booking_ids) !== count($rows)) {
	// 		$this->message = 'Could not create all bookings due to a database error.';
	// 		return FALSE;
	// 	}
	
	// 	// Clear multibooking entry
	// 	$this->CI->multi_booking_model->delete($multibooking->mb_id);
	
	// 	// Success
	// 	$this->success = TRUE;
	// 	$this->message = sprintf('%d bookings have been created.', count($booking_ids));
	// 	$this->data = ['booking_ids' => $booking_ids];
	// 	return TRUE;
	// }
	private function process_create_single()
	{
		// Load required CodeIgniter libraries and models
		$this->CI->load->library('form_validation');
		$this->CI->load->model('Bookings_model');  // For conflict checking and booking creation
		$this->CI->load->model('Periods_model');   // For fetching period times
	
		// Initial form validation rules
		$rules = [
			['field' => 'mb_id', 'label' => 'ID', 'rules' => 'required|integer'],
			['field' => 'type', 'label' => 'Type', 'rules' => 'required|in_list[single]'],
			['field' => 'slot_single[]', 'label' => 'Slots', 'rules' => 'required'],
		];
	
		$this->CI->form_validation->set_rules($rules);
	
		// Check if initial form validation passes
		if ($this->CI->form_validation->run() === FALSE) {
			$this->message = 'The form contained some invalid values. Please check and try again.';
			return FALSE;
		}
	
		// Validation rules for individual booking data
		$rules = [
			['field' => 'date', 'label' => 'Date', 'rules' => 'required|valid_date'],
			['field' => 'session_id', 'label' => 'Session', 'rules' => 'required|integer'],
			['field' => 'period_id', 'label' => 'Period', 'rules' => 'required|integer'],
			['field' => 'room_id', 'label' => 'Room', 'rules' => 'required|integer'],
			['field' => 'department_id', 'label' => 'Department', 'rules' => 'integer'],
			['field' => 'user_id', 'label' => 'User', 'rules' => 'integer'],
			['field' => 'department_group_id', 'label' => 'Department Group', 'rules' => 'integer'],
			['field' => 'course_id', 'label' => 'Course', 'rules' => 'integer'],
			['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
		];
	
		// Retrieve form data and multibooking details
		$form_slots = $this->CI->input->post('slot_single');
		$multibooking = $this->view_data['multibooking'];
	
		// Fetch period times for all slots
		$period_ids = array_unique(array_column($multibooking->slots, 'period_id'));
		$periods = $this->CI->db->where_in('period_id', $period_ids)->get('periods')->result();

		$period_times = [];
		foreach ($periods as $period) {
			$period_times[$period->period_id] = substr($period->time_start, 0, 5) . '-' . substr($period->time_end, 0, 5);
		}
	
		// Initialize arrays for processing
		$rows = [];          // Validated booking data for creation
		$all_conflicts = []; // Conflicts detected per slot
		$conflict_messages = []; // Detailed conflict messages
	
		// Process each slot in the multibooking
		foreach ($multibooking->slots as $slot_data) {
			$mbs_id = $slot_data->mbs_id;
	
			// Skip if slot is not in form or not selected for creation
			if (!isset($form_slots[$mbs_id])) {
				continue;
			}
			$form_slot = $form_slots[$mbs_id];
			if ($form_slot['create'] == 0) {
				continue;
			}
	
			// Determine department_id and user_id
			$department_id = isset($form_slot['department_id']) ? $form_slot['department_id'] : NULL;
			$user_id = isset($form_slot['user_id']) ? $form_slot['user_id'] : NULL;
	
			// Override with logged-in user details for non-admins
			if (!$this->is_admin) {
				$user_id = $this->user->user_id;
				$department_id = $this->user->department_id;
			}
	
			if (empty($department_id)) {
				$department_id = NULL;
			}
	
			// Set department_group_id and course_id from form or defaults
			$department_group_id = isset($form_slot['department_group_id']) ? $form_slot['department_group_id'] : 
				(isset($this->department_group) ? $this->department_group->department_group_id : NULL);
			$course_id = isset($form_slot['course_id']) ? $form_slot['course_id'] : 
				(isset($this->course) ? $this->course->course_id : NULL);
	
			// Prepare booking data
			$booking_data = [
				'date' => $slot_data->date,
				'session_id' => $multibooking->session_id,
				'period_id' => $slot_data->period_id,
				'room_id' => $slot_data->room_id,
				'department_id' => $department_id,
				'user_id' => !empty($user_id) ? $user_id : NULL,
				'department_group_id' => $department_group_id,
				'course_id' => $course_id,
				'notes' => !empty($form_slot['notes']) ? $form_slot['notes'] : NULL,
			];
	
			// Validate individual booking data
			$this->CI->form_validation->reset_validation();
			$this->CI->form_validation->set_rules($rules);
			$this->CI->form_validation->set_data($booking_data);
	
			if ($this->CI->form_validation->run() === FALSE) {
				$this->message = "One or more bookings (mbs_id: $mbs_id) contained invalid values. Please check and try again.";
				return FALSE;
			}
	
			// Check for conflicts, including level mismatch
			$conflicts = $this->CI->Bookings_model->find_conflicts(
				[$booking_data['date']], // Single date as an array
				$booking_data['period_id'],
				$booking_data['room_id'],
				$booking_data['course_id'], // For level mismatch check
				$booking_data['user_id'],
				$booking_data['department_group_id']
			);
	
			// Handle conflicts if any exist
			if (!empty($conflicts['user_conflicts']) || 
				!empty($conflicts['department_group_conflicts']) || 
				!empty($conflicts['room_conflicts']) || 
				$conflicts['level_mismatch']) {
				
				$all_conflicts[$mbs_id] = $conflicts;
	
				// Build detailed conflict message
				$slot_date = $slot_data->date; // Assuming 'Y-m-d' format
				$period_time = $period_times[$slot_data->period_id] ?? 'Period ' . $slot_data->period_id;
				$slot_info = "On $slot_date from $period_time";
	
				$conflict_types = [];
				if (!empty($conflicts['user_conflicts'])) {
					$conflict_types[] = "User is already booked";
				}
				if (!empty($conflicts['department_group_conflicts'])) {
					$conflict_types[] = "Department Group is already booked";
				}
				if (!empty($conflicts['room_conflicts'])) {
					$conflict_types[] = "Room is already booked";
				}
				if ($conflicts['level_mismatch']) {
					$conflict_types[] = "Course level does not match the department group's level";
				}
	
				$message = "$slot_info: " . implode(', ', $conflict_types);
				$conflict_messages[] = $message;
				continue;
			}
	
			// No conflicts, add to rows for creation
			$rows[] = $booking_data;
		}
	
		// Return detailed conflict messages if any
		if (!empty($conflict_messages)) {
			$message = "Conflicts detected in the following slots:";
			foreach ($conflict_messages as $msg) {
				$message .= "$msg";
			}
			$this->message = $message;
			$this->data = ['conflicts' => $all_conflicts];
			return FALSE;
		}
	
		// Fail if no bookings are selected for creation
		if (empty($rows)) {
			$this->message = 'No bookings were selected for creation.';
			return FALSE;
		}
	
		// Create bookings within a database transaction
		$booking_ids = [];
		$this->CI->db->trans_start();
	
		foreach ($rows as $row) {
			$booking_id = $this->CI->Bookings_model->create($row);
			if ($booking_id) {
				$booking_ids[] = $booking_id;
			} else {
				$this->CI->db->trans_rollback();
				$err = $this->CI->Bookings_model->get_error();
				$this->message = $err ?: 'Could not create one or more bookings.';
				return FALSE;
			}
		}
	
		$this->CI->db->trans_complete();
	
		// Check transaction status
		if ($this->CI->db->trans_status() === FALSE || count($booking_ids) !== count($rows)) {
			$this->message = 'Could not create all bookings due to a database error.';
			return FALSE;
		}
	
		// Clear the multibooking entry
		$this->CI->multi_booking_model->delete($multibooking->mb_id);
	
		// Success case
		$this->success = TRUE;
		$this->message = sprintf('%d bookings have been created.', count($booking_ids));
		$this->data = ['booking_ids' => $booking_ids];
		return TRUE;
	}

	private function process_recurring_defaults()
	{
		// Validation rules
		$rules = [
			['field' => 'mb_id', 'label' => 'Multibooking ID', 'rules' => 'required|integer'],
			['field' => 'step', 'label' => 'Step', 'rules' => 'required'],
			// ['field' => 'department_id', 'label' => 'Department', 'rules' => 'integer'],
			['field' => 'user_id', 'label' => 'User', 'rules' => 'integer'],
			['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
			['field' => 'recurring_start', 'label' => 'Recurring start', 'rules' => 'required'],
			['field' => 'recurring_end', 'label' => 'Recurring end', 'rules' => 'required'],
			['field' => 'course_id', 'label' => 'Course', 'rules' => 'integer'],
			['field' => 'department_group_id', 'label' => 'Department Group', 'rules' => 'integer'],
		];

		$this->CI->load->library('form_validation');
		$this->CI->form_validation->set_rules($rules);

		if ($this->CI->form_validation->run() === FALSE) {
			$this->message = 'One or more of the bookings contained some invalid values. Please check and try again.';
			return FALSE;
		}

		$session_key = sprintf('mb_%d', $this->CI->input->post('mb_id'));

		$_SESSION[$session_key] = [
			// 'department_id' => $this->CI->input->post('department_id'),
			'department_group_id' => $this->CI->input->post('department_group_id'),
	 		'course_id' => $this->CI->input->post('course_id'),
			'user_id' => $this->CI->input->post('user_id'),
			'notes' => $this->CI->input->post('notes'),
			'recurring_start' => $this->CI->input->post('recurring_start'),
			'recurring_end' => $this->CI->input->post('recurring_end'),
		];

		
		redirect(current_url() . '?' . http_build_query([
			'mb_id' => $this->CI->input->post('mb_id'),
			'step' => 'recurring_customise',
		]));
	}

	// private function process_recurring_defaults()
	// {
	// 	// Validation rules
	// 	$rules = [
	// 		['field' => 'mb_id', 'label' => 'Multibooking ID', 'rules' => 'required|integer'],
	// 		['field' => 'step', 'label' => 'Step', 'rules' => 'required'],
	// 		['field' => 'user_id', 'label' => 'User', 'rules' => 'integer'],
	// 		['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
	// 		['field' => 'recurring_start', 'label' => 'Recurring start', 'rules' => 'required'],
	// 		['field' => 'recurring_end', 'label' => 'Recurring end', 'rules' => 'required'],
	// 		['field' => 'course_id', 'label' => 'Course', 'rules' => 'integer'],
	// 		['field' => 'department_group_id', 'label' => 'Department Group', 'rules' => 'integer'],
	// 	];
	
	// 	$this->CI->load->library('form_validation');
	// 	$this->CI->form_validation->set_rules($rules);
	
	// 	if ($this->CI->form_validation->run() === FALSE) {
	// 		$this->message = 'One or more of the bookings contained some invalid values. Please check and try again.';
	// 		return FALSE;
	// 	}
	
	// 	$mb_id = $this->CI->input->post('mb_id');
	// 	$session_key = sprintf('mb_%d_slots', $mb_id);
	
	// 	// Use existing multibooking object instead of calling get()
	// 	$multibooking = $this->multibooking;
	// 	if (!$multibooking || $multibooking->mb_id != $mb_id) {
	// 		$this->message = 'Invalid multibooking ID.';
	// 		return FALSE;
	// 	}
	
	// 	// Default values from Form 2
	// 	$default_values = [
	// 		'department_group_id' => $this->CI->input->post('department_group_id'),
	// 		'course_id' => $this->CI->input->post('course_id'),
	// 		'user_id' => $this->CI->input->post('user_id'),
	// 		'notes' => $this->CI->input->post('notes'),
	// 		'recurring_start' => $this->CI->input->post('recurring_start'),
	// 		'recurring_end' => $this->CI->input->post('recurring_end'),
	// 	];
	
	// 	// Structure session data per slot
	// 	$session_slots = [];
	// 	foreach ($multibooking->slots as $slot) {
	// 		$session_slots[$slot->mbs_id] = $default_values;
	// 	}
	
	// 	$this->CI->session->set_userdata($session_key, $session_slots);
	
	// 	// Debug log
	// 	log_message('debug', 'Session after process_recurring_defaults: ' . print_r($session_slots, true));
	
	// 	redirect(current_url() . '?' . http_build_query([
	// 		'mb_id' => $mb_id,
	// 		'step' => 'recurring_customise',
	// 	]));
	// }


	/**
	 * Get all the details for all the slots.
	 *
	 */

	private function process_recurring_customise()
	{
		$this->CI->load->library('form_validation');
	
		$rules = [
			['field' => 'mb_id', 'label' => 'ID', 'rules' => 'required|integer'],
			['field' => 'step', 'label' => 'Step', 'rules' => 'required|in_list[recurring_customise]'],
			['field' => 'slots[]', 'label' => 'Slot', 'rules' => 'required'],
		];
	
		$this->CI->form_validation->set_rules($rules);
	
		if ($this->CI->form_validation->run() === FALSE) {
			$this->message = 'The form contained some invalid values. Please check and try again.';
			return FALSE;
		}
		
		// Rules for each slot
		$rules = [
			['field' => 'session_id', 'label' => 'Session', 'rules' => 'required|integer'],
			['field' => 'period_id', 'label' => 'Period', 'rules' => 'required|integer'],
			['field' => 'room_id', 'label' => 'Room', 'rules' => 'required|integer'],
			// ['field' => 'department_id', 'label' => 'Department', 'rules' => 'integer'],
			['field' => 'user_id', 'label' => 'User', 'rules' => 'integer'],
			['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
			['field' => 'recurring_start', 'label' => 'Recurring start', 'rules' => 'required|valid_date'],
			['field' => 'recurring_end', 'label' => 'Recurring end', 'rules' => 'required|valid_date'],
			['field' => 'course_id', 'label' => 'Course', 'rules' => 'integer'],
			['field' => 'department_group_id', 'label' => 'Department Group', 'rules' => 'integer'],
		];
	
		$form_slots = $this->CI->input->post('slots');
		$multibooking = $this->view_data['multibooking'];
		$slots = [];
	
		foreach ($multibooking->slots as $slot) {
			$mbs_id = $slot->mbs_id;
			if (!isset($form_slots[$mbs_id])) continue;
			$form_slot = $form_slots[$mbs_id];
	
			$recurring_start = $form_slot['recurring_start'];
			$recurring_end = $form_slot['recurring_end'];
	
		
			if ($recurring_start == 'session') {
				foreach ($slot->recurring_dates as $row) {
					$dt = datetime_from_string($row->date);
					if ($dt >= $this->session->date_start) {
						$recurring_start = $dt->format('Y-m-d');
						break;
					}
				}
			}
	
			if ($recurring_end == 'session') {
				foreach (array_reverse($slot->recurring_dates) as $row) {
					$dt = datetime_from_string($row->date);
					if ($dt <= $this->session->date_end) {
						$recurring_end = $dt->format('Y-m-d');
						break;
					}
				}
			}
	
			$booking_data = [
				'session_id' => $multibooking->session_id,
				'period_id' => $slot->period_id,
				'room_id' => $slot->room_id,
				'course_id' => !empty($form_slot['course_id']) ? $form_slot['course_id'] : NULL,
				'department_group_id' => !empty($form_slot['department_group_id']) ? $form_slot['department_group_id'] : NULL,
				'user_id' => !empty($form_slot['user_id']) ? $form_slot['user_id'] : NULL,
				'notes' => !empty($form_slot['notes']) ? $form_slot['notes'] : NULL,
				'recurring_start' => $recurring_start,
				'recurring_end' => $recurring_end,
			];
			
			$this->CI->form_validation->reset_validation();
			$this->CI->form_validation->set_rules($rules);
			$this->CI->form_validation->set_data($booking_data);
	
			if ($this->CI->form_validation->run() === FALSE) {
				$this->message = 'One or more of the bookings contained some invalid values. Please check and try again.';
				
				return FALSE;
			}
	
			$slots[$mbs_id] = $booking_data;
		}

		$session_key = sprintf('mb_%d_slots', $this->CI->input->post('mb_id'));
		$_SESSION[$session_key] = $slots;
	
		redirect(current_url() . '?' . http_build_query([
			'mb_id' => $this->CI->input->post('mb_id'),
			'step' => 'recurring_preview',
		]));
	}

	/**
	 * Create all recurring bookings.
	 *
	 */
	
	//  private function process_create_recurring()
	//  {
	// 	 $this->CI->load->library('form_validation');
	// 	 $this->CI->load->model('Bookings_model');
	// 	 $this->CI->load->model('Bookings_repeat_model');
	 
	// 	 // Base validation rules
	// 	 $rules = [
	// 		 ['field' => 'mb_id', 'label' => 'ID', 'rules' => 'required|integer'],
	// 		 ['field' => 'step', 'label' => 'Step', 'rules' => 'required|in_list[recurring_preview]'],
	// 	 ];
	 
	// 	 // Get session slots
	// 	 $session_key = sprintf('mb_%d_slots', $this->multibooking->mb_id);
	// 	 $session_slots = $this->CI->session->userdata($session_key);
	// 	//  log_message('debug', '============================Session Slots: ' . print_r($session_slots, true));
	// 	 if (empty($session_slots)) {
	// 		 $this->message = 'Session data missing. Please restart the booking process.';
	// 		 return FALSE;
	// 	 }
	 
	// 	 // Dynamic rules for dates
	// 	 foreach ($session_slots as $mbs_id => $slot_data) {
	// 		 $rules[] = [
	// 			 'field' => "dates[$mbs_id]",
	// 			 'label' => "Dates for slot $mbs_id",
	// 			 'rules' => 'required',
	// 		 ];
	// 	 }
	 
	// 	 $this->CI->form_validation->set_rules($rules);
	 
	// 	 if ($this->CI->form_validation->run() === FALSE) {
	// 		 $this->message = 'The form contained some invalid values. Please check and try again.';
	// 		 return FALSE;
	// 	 }
	 
	// 	 // Get form data
	// 	 $dates = $this->CI->input->post('dates');
	// 	 log_message('debug', 'POST Dates: ' . print_r($dates, true));
	 
	// 	 if (!is_array($dates)) {
	// 		 $this->message = 'Invalid dates format.';
	// 		 return FALSE;
	// 	 }
	 
	// 	 $multibooking = $this->view_data['multibooking'];
	// 	 $all_conflicts = [];
	// 	 $repeat_ids = [];
	 
	// 	 $this->CI->db->trans_begin();
	 
	// 	 foreach ($multibooking->slots as $slot) {
	// 		 $mbs_id = $slot->mbs_id;
	 
	// 		 if (!isset($session_slots[$mbs_id]) || !isset($dates[$mbs_id]) || empty($dates[$mbs_id])) {
	// 			 log_message('debug', "Skipping Slot $mbs_id - Missing data");
	// 			 continue;
	// 		 }
	 
	// 		 $slot_data = $session_slots[$mbs_id];
	// 		 $slot_dates = $dates[$mbs_id];
	 
	// 		 // Extract dates to book
	// 		 $date_strings = [];
	// 		 foreach ($slot_dates as $date => $info) {
	// 			 if (isset($info['action']) && $info['action'] === 'book') {
	// 				 $date_strings[] = $date;
	// 			 }
	// 		 }
	 
	// 		 if (empty($date_strings)) {
	// 			 log_message('debug', "Skipping Slot $mbs_id - No dates to book");
	// 			 continue;
	// 		 }
	 
	// 		 // Use session data for key fields
	// 		 $user_id = !empty($slot_data['user_id']) ? $slot_data['user_id'] : (isset($this->user) ? $this->user->user_id : NULL);
	// 		 $department_group_id = !empty($slot_data['department_group_id']) ? $slot_data['department_group_id'] : NULL;
	// 		 $course_id = !empty($slot_data['course_id']) ? $slot_data['course_id'] : NULL;
	 
	// 		 log_message('debug', "=========================Slot $mbs_id - User ID: $user_id, Dept Group ID: $department_group_id, Course ID: $course_id");
	 
	// 		 $conflicts = $this->CI->Bookings_model->find_conflicts(
	// 			 $date_strings,
	// 			 $slot->period_id,
	// 			 $slot->room_id,
	// 			 $user_id,
	// 			 $department_group_id
	// 		 );
	 
	// 		 if (!empty($conflicts['user_conflicts']) || !empty($conflicts['department_group_conflicts']) || !empty($conflicts['room_conflicts'])) {
	// 			 $all_conflicts[$mbs_id] = $conflicts;
	// 			 continue;
	// 		 }
	 
	// 		 $repeat_data = [
	// 			 'session_id' => $multibooking->session_id,
	// 			 'period_id' => $slot->period_id,
	// 			 'room_id' => $slot->room_id,
	// 			 'user_id' => $user_id,
	// 			 'department_id' => !empty($slot_data['department_id']) ? $slot_data['department_id'] : NULL,
	// 			 'department_group_id' => $department_group_id,
	// 			 'course_id' => $course_id,
	// 			 'week_id' => $multibooking->week_id,
	// 			 'weekday' => $slot->weekday,
	// 			 'status' => Bookings_model::STATUS_BOOKED,
	// 			 'notes' => !empty($slot_data['notes']) ? $slot_data['notes'] : NULL,
	// 			 'dates' => $slot_dates,
	// 		 ];
	 
	// 		 $repeat_id = $this->CI->Bookings_repeat_model->create($repeat_data);
	 
	// 		 if (!$repeat_id) {
	// 			 $this->CI->db->trans_rollback();
	// 			 $this->message = "Could not create recurring booking for slot $mbs_id.";
	// 			 return FALSE;
	// 		 }
	 
	// 		 $repeat_ids[$mbs_id] = $repeat_id;
	// 	 }
	 
	// 	 if (!empty($all_conflicts)) {
	// 		 $this->CI->db->trans_rollback();
	// 		 $this->message = 'Conflicts detected in the selected slots.';
	// 		 $this->data = ['conflicts' => $all_conflicts];
	// 		 return FALSE;
	// 	 }
	 
	// 	 if (empty($repeat_ids)) {
	// 		 $this->CI->db->trans_rollback();
	// 		 $this->message = 'No bookings were created.';
	// 		 return FALSE;
	// 	 }
	 
	// 	 if ($this->CI->db->trans_status() === FALSE) {
	// 		 $this->CI->db->trans_rollback();
	// 		 $this->message = 'Could not create recurring bookings due to a database error.';
	// 		 return FALSE;
	// 	 }
	 
	// 	 $this->CI->db->trans_commit();
	// 	 $this->CI->session->unset_userdata($session_key); // Clean up
	// 	 $this->success = TRUE;
	// 	 $this->message = sprintf('%d recurring bookings created successfully.', count($repeat_ids));
	// 	 $this->data = ['booking_ids' => $repeat_ids];
	// 	 return TRUE;
	//  }
	private function process_create_recurring()
	{
		// Load required CodeIgniter libraries and models
		$this->CI->load->library('form_validation');
		$this->CI->load->model('Bookings_model');
		$this->CI->load->model('Bookings_repeat_model');
		$this->CI->load->model('Periods_model'); // For fetching period times
	
		// Base validation rules
		$rules = [
			['field' => 'mb_id', 'label' => 'ID', 'rules' => 'required|integer'],
			['field' => 'step', 'label' => 'Step', 'rules' => 'required|in_list[recurring_preview]'],
		];
	
		// Get session slots
		$session_key = sprintf('mb_%d_slots', $this->multibooking->mb_id);
		$session_slots = $this->CI->session->userdata($session_key);
		if (empty($session_slots)) {
			$this->message = 'Session data missing. Please restart the booking process.';
			return FALSE;
		}
	
		// Dynamic rules for dates
		foreach ($session_slots as $mbs_id => $slot_data) {
			$rules[] = [
				'field' => "dates[$mbs_id]",
				'label' => "Dates for slot $mbs_id",
				'rules' => 'required',
			];
		}
	
		$this->CI->form_validation->set_rules($rules);
	
		// Check if form validation passes
		if ($this->CI->form_validation->run() === FALSE) {
			$this->message = 'The form contained some invalid values. Please check and try again.';
			return FALSE;
		}
	
		// Get form data
		$dates = $this->CI->input->post('dates');
		log_message('debug', 'POST Dates: ' . print_r($dates, true));
	
		if (!is_array($dates)) {
			$this->message = 'Invalid dates format.';
			return FALSE;
		}
	
		$multibooking = $this->view_data['multibooking'];
		$all_conflicts = [];
		$repeat_ids = [];
		$conflict_messages = [];
	
		// Fetch period times for all slots
		$period_ids = array_unique(array_column($multibooking->slots, 'period_id'));
		$periods = $this->CI->db->where_in('period_id', $period_ids)->get('periods')->result();
		$period_times = [];
		foreach ($periods as $period) {
			$period_times[$period->period_id] = substr($period->time_start, 0, 5) . '-' . substr($period->time_end, 0, 5);
		}
	
		$this->CI->db->trans_begin();
	
		foreach ($multibooking->slots as $slot) {
			$mbs_id = $slot->mbs_id;
	
			if (!isset($session_slots[$mbs_id]) || !isset($dates[$mbs_id]) || empty($dates[$mbs_id])) {
				log_message('debug', "Skipping Slot $mbs_id - Missing data");
				continue;
			}
	
			$slot_data = $session_slots[$mbs_id];
			$slot_dates = $dates[$mbs_id];
	
			// Extract dates to book
			$date_strings = [];
			foreach ($slot_dates as $date => $info) {
				if (isset($info['action']) && $info['action'] === 'book') {
					$date_strings[] = $date;
				}
			}
	
			if (empty($date_strings)) {
				log_message('debug', "Skipping Slot $mbs_id - No dates to book");
				continue;
			}
	
			// Use session data for key fields
			$user_id = !empty($slot_data['user_id']) ? $slot_data['user_id'] : (isset($this->user) ? $this->user->user_id : NULL);
			$department_group_id = !empty($slot_data['department_group_id']) ? $slot_data['department_group_id'] : NULL;
			$course_id = !empty($slot_data['course_id']) ? $slot_data['course_id'] : NULL;
	
			log_message('debug', "=========================Slot $mbs_id - User ID: $user_id, Dept Group ID: $department_group_id, Course ID: $course_id");
	
			// Check for conflicts, including level mismatch
			$conflicts = $this->CI->Bookings_model->find_conflicts(
				$date_strings,
				$slot->period_id,
				$slot->room_id,
				$course_id, // Added for level mismatch check
				$user_id,
				$department_group_id
			);
	
			// Check all conflict types, including level mismatch
			if (!empty($conflicts['user_conflicts']) || 
				!empty($conflicts['department_group_conflicts']) || 
				!empty($conflicts['room_conflicts']) || 
				$conflicts['level_mismatch']) {
				$all_conflicts[$mbs_id] = $conflicts;
	
				// Build conflict message
				$slot_date = $slot->date; // Assuming 'Y-m-d' format
				$period_time = $period_times[$slot->period_id] ?? 'Period ' . $slot->period_id;
				$slot_info = "On $slot_date from $period_time";
	
				$conflict_types = [];
				if (!empty($conflicts['user_conflicts'])) {
					$conflict_types[] = "User is already booked";
				}
				if (!empty($conflicts['department_group_conflicts'])) {
					$conflict_types[] = "Department Group is already booked";
				}
				if (!empty($conflicts['room_conflicts'])) {
					$conflict_types[] = "Room is already booked";
				}
				if ($conflicts['level_mismatch']) {
					$conflict_types[] = "Course level does not match the department group's level";
				}
	
				$message = "$slot_info: " . implode(', ', $conflict_types);
				$conflict_messages[] = $message;
				continue;
			}
	
			// Proceed with booking creation if no conflicts
			$repeat_data = [
				'session_id' => $multibooking->session_id,
				'period_id' => $slot->period_id,
				'room_id' => $slot->room_id,
				'user_id' => $user_id,
				'department_id' => !empty($slot_data['department_id']) ? $slot_data['department_id'] : NULL,
				'department_group_id' => $department_group_id,
				'course_id' => $course_id,
				'week_id' => $multibooking->week_id,
				'weekday' => $slot->weekday,
				'status' => Bookings_model::STATUS_BOOKED,
				'notes' => !empty($slot_data['notes']) ? $slot_data['notes'] : NULL,
				'dates' => $slot_dates,
			];
	
			$repeat_id = $this->CI->Bookings_repeat_model->create($repeat_data);
	
			if (!$repeat_id) {
				$this->CI->db->trans_rollback();
				$this->message = "Could not create recurring booking for slot $mbs_id.";
				return FALSE;
			}
	
			$repeat_ids[$mbs_id] = $repeat_id;
		}
	
		// Handle conflicts if any
		if (!empty($conflict_messages)) {
			$message = "Conflicts detected in the following slots:";
			foreach ($conflict_messages as $msg) {
				$message .= "$msg";
			}
			$this->message = $message;
			$this->data = ['conflicts' => $all_conflicts];
			$this->CI->db->trans_rollback();
			return FALSE;
		}
	
		if (empty($repeat_ids)) {
			$this->CI->db->trans_rollback();
			$this->message = 'No bookings were created.';
			return FALSE;
		}
	
		if ($this->CI->db->trans_status() === FALSE) {
			$this->CI->db->trans_rollback();
			$this->message = 'Could not create recurring bookings due to a database error.';
			return FALSE;
		}
	
		$this->CI->db->trans_commit();
		$this->CI->session->unset_userdata($session_key);
		$this->success = TRUE;
		$this->message = sprintf('%d recurring bookings created successfully.', count($repeat_ids));
		$this->data = ['booking_ids' => $repeat_ids];
		return TRUE;
	}
}
