<?php

namespace app\components\bookings\agent;

defined('BASEPATH') OR exit('No direct script access allowed');

use app\components\bookings\exceptions\AgentException;
use app\components\bookings\Slot;
use \Bookings_model;

/**
 * Agent handles the editing of a booking.
 */
class UpdateAgent extends BaseAgent
{
    // Agent type
    const TYPE = 'update';

    // Features that can be changed
    const FEATURE_DATE = 'date';
    const FEATURE_PERIOD = 'period';
    const FEATURE_ROOM = 'room';
    // const FEATURE_DEPARTMENT = 'department'; // Commented out as redundant with department_groups
    const FEATURE_USER = 'user';
    const FEATURE_NOTES = 'notes';
    const FEATURE_DEPARTMENT_GROUP = 'department_group'; // Added for department_groups
    const FEATURE_COURSE = 'course'; // Added for course

    // Edit modes
    const EDIT_ONE = '1';
    const EDIT_FUTURE = 'future';
    const EDIT_ALL = 'all';

    // Booking being edited
    private $booking;

    // Edit mode
    private $edit_mode;

    // Features
    private $features = [];

    /**
     * Initialise the Agent with some values.
     */
    public function load()
    {
        // Load booking that is being edited
        $booking_id = $this->CI->input->post_get('booking_id');
        $includes = [
            'week',
            'period',
            'room',
            'user',
            'department_group', // Already updated from 'department'
            'course',           // Added to load course data
        ];
        if (!empty($booking_id)) {
            $this->booking = $this->CI->bookings_model->include($includes)->get($booking_id);
        }
        if (!$this->booking) {
            throw AgentException::forNoBooking();
        }

        // Get session of booking
        $this->session = $this->CI->sessions_model->get($this->booking->session_id);
        if (!$this->session) {
            throw AgentException::forNoSession();
        }

        // Load rooms & periods lists
        $schedule = $this->CI->schedules_model->get_applied_schedule($this->session->session_id, $this->booking->room->room_group_id);

        // Load the list of available periods and rooms (for admins), now we have more required context
        if ($this->is_admin && !empty($schedule)) {
            $this->all_periods = $this->CI->periods_model->filtered([
                'schedule_id' => $schedule->schedule_id,
                'bookable' => 1,
            ]);
        }

        if ($this->is_admin && !empty($this->booking->room->room_group_id)) {
            $this->all_rooms = $this->CI->rooms_model->get_bookable_rooms([
                'user_id' => $this->user->user_id,
                'room_group_id' => $this->booking->room->room_group_id,
            ]);
        }

        // Get edit mode
        $this->edit_mode = $this->CI->input->post_get('edit') ?: self::EDIT_ONE;

        // Determine what aspects can be changed
        $default_feature = $this->is_admin ? TRUE : FALSE;

        $this->features = [
            self::FEATURE_DATE => $default_feature,
            self::FEATURE_PERIOD => $default_feature,
            self::FEATURE_ROOM => $default_feature,
            self::FEATURE_USER => $default_feature,
            self::FEATURE_NOTES => $default_feature,
            self::FEATURE_DEPARTMENT_GROUP => $default_feature, // Already updated from FEATURE_DEPARTMENT
            self::FEATURE_COURSE => $default_feature,           // Added for course editing
        ];

        // Booking owners can change the notes
        if ($this->booking->user_id == $this->user->user_id) {
            $this->features[self::FEATURE_NOTES] = TRUE;
        }

        // If a recurring booking future or all is being edited, then it can't be moved
        if ($this->booking->repeat_id) {
            if (in_array($this->edit_mode, [self::EDIT_FUTURE, self::EDIT_ALL])) {
                $this->features[self::FEATURE_DATE] = FALSE;
                $this->features[self::FEATURE_PERIOD] = FALSE;
                $this->features[self::FEATURE_ROOM] = FALSE;
            }
        }

        $this->handle_edit();
    }

    private function handle_edit()
    {
        $this->view = 'bookings/edit/form';
        $this->title = 'Edit booking';

        if ($this->CI->input->post()) {
            $this->process_edit_booking();
        }
    }

    /**
     * Main vars to ensure are in the view.
     */
    public function get_view_data()
    {
        $vars = [
            'booking' => $this->booking,
            'features' => $this->features,
            'edit_mode' => $this->edit_mode,
            // 'all_periods' => $this->all_periods,
            // 'all_rooms' => $this->all_rooms,
            // 'all_users' => $this->CI->users_model->get_all(),
            // 'all_department_groups' => $this->CI->department_groups_model->get_all(),
            // 'all_courses' => $this->CI->courses_model->get_all(),
            // 'message' => $this->message,
            // 'return_uri' => $this->CI->input->post_get('return_uri') ?: 'bookings', // Default fallback
        ];
    
        return $vars;
    }

    /**
     * Edit a booking
     */
    private function process_edit_booking()
    {
        $rules = $this->get_validation_rules($this->booking->booking_id);
        $this->CI->load->library('form_validation');
        $this->CI->form_validation->set_rules($rules);

        if ($this->CI->form_validation->run() == FALSE) {
            $this->message = 'The form contained some invalid values. Please check and try again.';
            return FALSE;
        }

        // Build data array with values that can be updated
        $booking_data = [];

        if ($this->features[self::FEATURE_DATE]) {
            $booking_data['date'] = $this->CI->input->post('booking_date');
        }

        if ($this->features[self::FEATURE_PERIOD]) {
            $booking_data['period_id'] = $this->CI->input->post('period_id');
        }

        if ($this->features[self::FEATURE_ROOM]) {
            $booking_data['room_id'] = $this->CI->input->post('room_id');
        }

        if ($this->features[self::FEATURE_USER]) {
            $booking_data['user_id'] = $this->CI->input->post('user_id');
        }

        if ($this->features[self::FEATURE_NOTES]) {
            $booking_data['notes'] = $this->CI->input->post('notes');
        }

        if ($this->features[self::FEATURE_DEPARTMENT_GROUP]) {
            $booking_data['department_group_id'] = $this->CI->input->post('department_group_id');
        }

        if ($this->features[self::FEATURE_COURSE]) {
            $booking_data['course_id'] = $this->CI->input->post('course_id');
        }

        $update = $this->CI->bookings_model->update($this->booking->booking_id, $booking_data, $this->edit_mode);

        if ($update) {
            $msgs = [
                self::EDIT_ONE => 'The booking has been updated successfully.',
                self::EDIT_FUTURE => 'The booking and all future bookings in the series have been updated.',
                self::EDIT_ALL => 'All bookings in the series have been updated successfully.',
            ];

            $this->message = $msgs[$this->edit_mode];
            $this->success = TRUE;

            return TRUE;
        }

        $err = $this->CI->bookings_model->get_error();
        $this->message = ($err) ? $err : 'Could not update booking.';

        return FALSE;
    }

    private function get_validation_rules($booking_id)
    {
        $rules = [];

        if ($this->features[self::FEATURE_DATE]) {
            $rules[] = [
                'field' => 'booking_date',
                'label' => 'Date',
                'rules' => "required|valid_date|no_conflict[$booking_id,booking_date]"
            ];
        }

        if ($this->features[self::FEATURE_PERIOD]) {
            $rules[] = [
                'field' => 'period_id',
                'label' => 'Period',
                'rules' => "required|integer|no_conflict[$booking_id,period_id]"
            ];
        }

        if ($this->features[self::FEATURE_ROOM]) {
            $rules[] = [
                'field' => 'room_id',
                'label' => 'Room',
                'rules' => "required|integer|no_conflict[$booking_id,room_id]"
            ];
        }
    
        if ($this->features[self::FEATURE_USER]) {
            $rules[] = [
                'field' => 'user_id',
                'label' => 'User',
                'rules' => "required|integer|no_conflict[$booking_id,user_id]"
            ];
        }

        if ($this->features[self::FEATURE_NOTES]) {
            $rules[] = [
                'field' => 'notes',
                'label' => 'Notes',
                'rules' => 'max_length[255]'
            ];
        }

        if ($this->features[self::FEATURE_DEPARTMENT_GROUP]) {
            $rules[] = [
                'field' => 'department_group_id',
                'label' => 'Department Group',
                'rules' => "integer|no_conflict[$booking_id,department_group_id]"
            ];
        }
    
        if ($this->features[self::FEATURE_COURSE]) {
            $rules[] = [
                'field' => 'course_id',
                'label' => 'Course',
                'rules' => 'integer'
            ];
        }

        return $rules;
    }

}