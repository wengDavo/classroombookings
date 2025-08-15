<?php

namespace app\components\bookings\agent;

defined('BASEPATH') OR exit('No direct script access allowed');

use app\components\bookings\exceptions\AgentException;
use app\components\bookings\Slot;
use \Bookings_model;

/**
 * Agent handles the creation/editing/cancellation of bookings.
 */
class SingleAgent extends BaseAgent
{
    // Agent type
    const TYPE = 'single';

    // For single
    protected $period;
    protected $room;
    protected $department;
    protected $date_info;
    protected $datetime;
    protected $week;
    protected $recurring_dates;
    protected $course;
    protected $department_group;

    /**
     * Initialise the Agent with some values.
     *
     * Depending on the type of booking, these will be retrieved from different places.
     */
    public function load()
    {
        $this->view = 'bookings/create/single';

        $period_id = $this->CI->input->post_get('period_id');
        if (!empty($period_id)) $this->period = $this->CI->periods_model->get($period_id);
        if (!$this->period) throw AgentException::forNoPeriod();

        $room_id = $this->CI->input->post_get('room_id');
        if (!empty($room_id)) {
            $this->room = $this->CI->rooms_model->get_bookable_rooms([
                'user_id' => $this->user->user_id,
                'room_id' => $room_id,
            ]);
        }
        if (!$this->room) throw AgentException::forNoRoom();

        $date = $this->CI->input->post_get('date');
        $this->date_info = $this->CI->dates_model->get_by_date($date);
        if (!$this->date_info) throw AgentException::forInvalidDate();

        $this->week = $this->CI->weeks_model->get($this->date_info->week_id);
        if (!$this->week) throw AgentException::forNoWeek();

        $this->datetime = datetime_from_string($this->date_info->date);

        $department_id = $this->user->department_id;
        if ($this->is_admin && $this->CI->input->post('department_id')) {
            $department_id = $this->CI->input->post('department_id');
        }

        if (!empty($department_id)) {
            $this->department = $this->CI->departments_model->Get($department_id);
        }

        // Add courses
        $course_id = $this->CI->input->post_get('course_id');
        if (!empty($course_id)) {
            $this->course = $this->CI->courses_model->Get($course_id);
        }

        // Add department group
        $department_group_id = $this->CI->input->post_get('department_group_id');
        if (!empty($department_group_id)) {
            $this->department_group = $this->CI->department_groups_model->get($department_group_id);
        }

        // session
        $this->session = $this->CI->sessions_model->get_by_date($this->datetime);
        if (!$this->session) throw AgentException::forNoSession();

        // List of dates that a recurring booking can begin/end on.
        $this->recurring_dates = $this->CI->dates_model->get_recurring_dates($this->session->session_id, $this->date_info->week_id, $this->date_info->weekday);

        $schedule = $this->CI->schedules_model->get_applied_schedule($this->session->session_id, $this->room->room_group_id);

        // Load the list of available periods and rooms (for admins), now we have more required context.
        if ($this->is_admin && !empty($schedule)) {
            $this->all_periods = $this->CI->periods_model->filtered([
                'schedule_id' => $schedule->schedule_id,
                'bookable' => 1,
            ]);
        }

        if ($this->is_admin && !empty($this->room->room_group_id)) {
            $this->all_rooms = $this->CI->rooms_model->get_bookable_rooms([
                'user_id' => $this->user->user_id,
                'room_group_id' => $this->room->room_group_id,
            ]);
        }
    }

    /**
     * Main vars to ensure are in the view.
     */
    public function get_view_data()
    {
        $vars = [
            'recurring_dates' => $this->recurring_dates,
            'room' => $this->room,
            'period' => $this->period,
            'department' => $this->department,
            'date_info' => $this->date_info,
            'datetime' => $this->datetime,
            'week' => $this->week,
            'course' => $this->course,
            'department_group' => $this->department_group,
        ];

        return $vars;
    }

    /**
     * Process the input and state.
     *
     * This should return bool TRUE for success of whole process.
     * Returning FALSE keeps the process active.
     */
    public function process()
    {
        $action = $this->CI->input->post('action');
        if (!$action) return;

        switch ($action) {
            // Single booking: validate / create.
            case 'create':
                return $this->create_single_booking();
                break;

            case 'preview_recurring':
                return $this->preview_single_recurring();
                break;

            case 'create_recurring':
                return $this->create_single_recurring();
                break;
        }
    }

    /**
     * Create a single booking.
     */

    // private function create_single_booking()
    // {
    //     $rules = [
    //         ['field' => 'date', 'label' => 'Date', 'rules' => 'required|valid_date'],
    //         ['field' => 'period_id', 'label' => 'Period', 'rules' => 'required|integer'],
    //         ['field' => 'room_id', 'label' => 'Room', 'rules' => 'required|integer'],
    //         ['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
    //     ];

    //     $this->CI->load->library('form_validation');
    //     $this->CI->form_validation->set_rules($rules);

    //     if ($this->CI->form_validation->run() == FALSE) {
    //         $this->message = 'The form contained some invalid values. Please check and try again.';
    //         return FALSE;
    //     }

    //     $booking_data = [
    //         'date' => $this->datetime->format('Y-m-d'),
    //         'notes' => $this->CI->input->post('notes'),
    //         'period_id' => $this->period->period_id,
    //         'room_id' => $this->room->room_id,
    //         // 'department_id' => $this->department ? $this->department->department_id : null, // Commented out as redundant
    //         'session_id' => $this->session->session_id,
    //         'user_id' => $this->user->user_id,
    //         'course_id' => $this->CI->input->post('course_id') ?: ($this->course ? $this->course->course_id : null),
    //         'department_group_id' => $this->CI->input->post('department_group_id') ?: ($this->department_group ? $this->department_group->department_group_id : null), // Added
    //     ];

    //     if ($this->is_admin) {
    //         $post_user_id = $this->CI->input->post('user_id');
    //         $booking_data['user_id'] = !empty($post_user_id) ? $post_user_id : null;
    //     }
        
    //     $conflicts = $this->CI->bookings_model->find_conflicts(
    //         [$booking_data['date']],
    //         $booking_data['period_id'],
    //         $booking_data['room_id'],
    //         $booking_data['user_id'],
    //         $booking_data['department_group_id']
    //     );

    //     // log_message('debug', '=========find_conflicts department_group result count: ' . print_r($conflicts, true));

    //     // Strict user constraint
    //     if (!empty($conflicts['user_conflicts'])) {
    //         $conflict = reset($conflicts['user_conflicts']);
    //         // $user_name = $conflict->user__displayname ?? $conflict->user__username ?? 'Unknown User';
    //         // $room_name = $conflict->room__name ?? 'Unknown Room';
    //         // $conflict_date = $conflict->date instanceof DateTime ? $conflict->date->format('Y-m-d') : $conflict->date;
    //         // $time_start = substr($conflict->time_start, 0, 5); // e.g., '09:00' from '09:00:00'
    //         // $time_end = substr($conflict->time_end, 0, 5);     // e.g., '10:00' from '10:00:00'
    //         // $this->message = "User {$user_name} is already booked in room {$room_name} on {$conflict_date} at {$time_start}-{$time_end}.";
    //         $this->message = "User is already booked";
    //         return FALSE;
    //     }

    //     // Strict department group constraint
    //     if (!empty($conflicts['department_group_conflicts'])) {
    //         // $conflict = reset($conflicts['department_group_conflicts']);
    //         // $department_group_name = $conflict->department_group__name ?? 'Unknown Department Group';
    //         // $room_name = $conflict->room__name ?? 'Unknown Room';
    //         // $conflict_date = $conflict->date instanceof DateTime ? $conflict->date->format('Y-m-d') : $conflict->date;
    //         // $time_start = substr($conflict->time_start, 0, 5);
    //         // $time_end = substr($conflict->time_end, 0, 5);
    //         // $this->message = "Department Group {$department_group_name} is already booked in room {$room_name} on {$conflict_date} at {$time_start}-{$time_end}.";
    //         $this->message = "Department Group is already booked";
    //         return FALSE;
    //     }

    //     // Room conflict (strict)
    //     if (!empty($conflicts['room_conflicts'])) {
    //         // $conflict = reset($conflicts['room_conflicts']);
    //         // $room_name = $conflict->room__name ?? 'Unknown Room';
    //         // $conflict_date = $conflict->date instanceof DateTime ? $conflict->date->format('Y-m-d') : $conflict->date;
    //         // $time_start = substr($conflict->time_start, 0, 5);
    //         // $time_end = substr($conflict->time_end, 0, 5);
    //         // $this->message = "Room {$room_name} is already booked on {$conflict_date} at {$time_start}-{$time_end}.";
    //         $this->message = "Room is already booked";
    //         return FALSE;
    //     }

    //     // Partial capacity constraint (warning, not blocking)
    //     $department_group_size = $this->department_group ? $this->department_group->size : 1; // Replaced cohort
    //     $room_capacity = $this->room->capacity;
    //     $warning = '';
    //     if ($department_group_size > $room_capacity) {
    //         $warning = " Warning: Department group size ($department_group_size) exceeds room capacity ($room_capacity). Proceeding anyway.";
    //     }

    //     $booking_id = $this->CI->bookings_model->create($booking_data);

    //     if ($booking_id) {
    //         $this->success = TRUE;
    //         $this->message = 'The booking has been created successfully.' . $warning;
    //         return TRUE;
    //     }

    //     $err = $this->CI->bookings_model->get_error();
    //     $this->message = ($err) ? $err : 'Could not create booking.';
    //     return FALSE;
    // }
    private function create_single_booking()
    {
        $rules = [
            ['field' => 'date', 'label' => 'Date', 'rules' => 'required|valid_date'],
            ['field' => 'period_id', 'label' => 'Period', 'rules' => 'required|integer'],
            ['field' => 'room_id', 'label' => 'Room', 'rules' => 'required|integer'],
            ['field' => 'notes', 'label' => 'Notes', 'rules' => 'max_length[255]'],
        ];
    
        $this->CI->load->library('form_validation');
        $this->CI->form_validation->set_rules($rules);
    
        if ($this->CI->form_validation->run() == FALSE) {
            $this->message = 'The form contained some invalid values. Please check and try again.';
            return FALSE;
        }
    
        $booking_data = [
            'date' => $this->datetime->format('Y-m-d'),
            'notes' => $this->CI->input->post('notes'),
            'period_id' => $this->period->period_id,
            'room_id' => $this->room->room_id,
            'session_id' => $this->session->session_id,
            'user_id' => $this->user->user_id,
            'course_id' => $this->CI->input->post('course_id') ?: ($this->course ? $this->course->course_id : null),
            'department_group_id' => $this->CI->input->post('department_group_id') ?: ($this->department_group ? $this->department_group->department_group_id : null),
        ];
    
        if ($this->is_admin) {
            $post_user_id = $this->CI->input->post('user_id');
            $booking_data['user_id'] = !empty($post_user_id) ? $post_user_id : null;
        }
        
        // Updated call to find_conflicts with course_id
        $conflicts = $this->CI->bookings_model->find_conflicts(
            [$booking_data['date']],
            $booking_data['period_id'],
            $booking_data['room_id'],
            $booking_data['course_id'], // Added course_id parameter
            $booking_data['user_id'],
            $booking_data['department_group_id']
        );
    
        // Strict user constraint
        if (!empty($conflicts['user_conflicts'])) {
            $this->message = "User is already booked";
            return FALSE;
        }
    
        // Strict department group constraint
        if (!empty($conflicts['department_group_conflicts'])) {
            $this->message = "Department Group is already booked";
            return FALSE;
        }
    
        // Room conflict (strict)
        if (!empty($conflicts['room_conflicts'])) {
            $this->message = "Room is already booked";
            return FALSE;
        }
    
        // Level mismatch conflict (strict)
        if ($conflicts['level_mismatch']) {
            $this->message = "The course level does not match Department group's level.";
            return FALSE;
        }
    
        // Partial capacity constraint (warning, not blocking)
        $department_group_size = $this->department_group ? $this->department_group->size : 1;
        $room_capacity = $this->room->capacity;
        $warning = '';
        if ($department_group_size > $room_capacity) {
            $warning = " Warning: Department group size ($department_group_size) exceeds room capacity ($room_capacity). Proceeding anyway.";
        }
    
        $booking_id = $this->CI->bookings_model->create($booking_data);
    
        if ($booking_id) {
            $this->success = TRUE;
            $this->message = 'The booking has been created successfully.' . $warning;
            return TRUE;
        }
    
        $err = $this->CI->bookings_model->get_error();
        $this->message = ($err) ? $err : 'Could not create booking.';
        return FALSE;
    }

    /**
     * Single recurring booking marked as recurring: show preview of generated bookings.
     */
    private function preview_single_recurring()
    {
        $rules = [];

        if ($this->CI->input->post('recurring_start') == 'session') {
            $rules[] = ['field' => 'recurring_start', 'label' => 'Start date', 'rules' => 'required'];
            $recurring_start = false;

            foreach ($this->recurring_dates as $row) {
                $dt = datetime_from_string($row->date);
                if ($dt >= $this->session->date_start) {
                    $recurring_start = clone $dt;
                    break;
                }
            }
        } else {
            $rules[] = ['field' => 'recurring_start', 'label' => 'Start date', 'rules' => 'required|valid_date'];
            $recurring_start = $this->CI->input->post('recurring_start');
        }

        if ($this->CI->input->post('recurring_end') == 'session') {
            $rules[] = ['field' => 'recurring_end', 'label' => 'End date', 'rules' => 'required'];
            $recurring_end = FALSE;

            foreach (array_reverse($this->recurring_dates) as $row) {
                $dt = datetime_from_string($row->date);
                if ($dt <= $this->session->date_end) {
                    $recurring_end = clone $dt;
                    break;
                }
            }
        } else {
            $rules[] = ['field' => 'recurring_end', 'label' => 'End date', 'rules' => 'required|valid_date'];
            $recurring_end = $this->CI->input->post('recurring_end');
        }

        $this->CI->load->library('form_validation');
        $this->CI->form_validation->set_rules($rules);

        if ($this->CI->form_validation->run() == FALSE) {
            $this->message = 'The form contained some invalid values. Please check and try again.';
            return FALSE;
        }

        $recurring_start = datetime_from_string($recurring_start);
        $recurring_end = datetime_from_string($recurring_end);

        if ($recurring_end < $recurring_start) {
            $this->message = sprintf('The recurring End Date (%s) must be after the Starting From date of %s.',
                $recurring_end->format(setting('date_format_long')),
                $recurring_start->format(setting('date_format_long'))
            );
            return FALSE;
        }

        $dates = [];
        $slots = [];

        // Generate a list of all recurring dates that the user
        // has requested to be filled, starting with the list of *all* possible
        // recurring dates.
        foreach ($this->recurring_dates as $row) {
            if ($row->date < $recurring_start) continue;
            if ($row->date > $recurring_end) continue;
            $date_ymd = $row->date->format('Y-m-d');
            // List of dates for booking checking
            $dates[] = $date_ymd;
            // Slot for references
            $key = Slot::generate_key($date_ymd, $this->period->period_id, $this->room->room_id);
            $slots[$key]['datetime'] = $row->date;
        }

        // Get list of recurring bookings for these dates
        $existing_bookings = $this->CI->bookings_model->find_conflicts($dates, $this->period->period_id, $this->room->room_id);

        foreach ($slots as $key => $slot) {
            // List of possible actions for each slot
            $actions = [];

            if (array_key_exists($key, $existing_bookings)) {
                $actions['do_not_book'] = 'Keep existing booking';
                $actions['replace'] = 'Replace existing booking';
                $slots[$key]['booking'] = $existing_bookings[$key];
            } else {
                $actions['book'] = 'Book';
                $actions['do_not_book'] = 'Do not book';
            }

            $slots[$key]['actions'] = $actions;
        }

        $this->view_data['slots'] = $slots;

        $this->title = 'Preview recurring bookings';

        $this->view = 'bookings/create/single_recurring_preview';
    }

    // private function create_single_recurring()
    // {
    //     $dates = $this->CI->input->post('dates');

    //     if (empty($dates)) {
    //         $this->message = 'No dates selected.';
    //         return FALSE;
    //     }

    //     $repeat_data = [
    //         'session_id' => $this->session->session_id,
    //         'period_id' => $this->period->period_id,
    //         'room_id' => $this->room->room_id,
    //         'user_id' => $this->user->user_id,
    //         'department_id' => $this->department ? $this->department->department_id : NULL,
    //         'week_id' => $this->date_info->week_id,
    //         'weekday' => $this->date_info->weekday,
    //         'status' => Bookings_model::STATUS_BOOKED,
    //         'notes' => $this->CI->input->post('notes'),
    //         'dates' => $dates,
    //         'course_id' => $this->CI->input->post('course_id') ?: ($this->course ? $this->course->course_id : NULL),
    //         'department_group_id' => $this->CI->input->post('department_group_id') ?: ($this->department_group ? $this->department_group->department_group_id : NULL),
    //     ];

    //     if ($this->is_admin) {
    //         $post_user_id = $this->CI->input->post('user_id');
    //         $repeat_data['user_id'] = !empty($post_user_id) ? $post_user_id : NULL;
    //     }

    //     // Check conflicts
    //     $conflicts = $this->CI->bookings_model->find_conflicts(
    //         array_keys($dates), // Extract date strings
    //         $repeat_data['period_id'],
    //         $repeat_data['room_id'],
    //         $repeat_data['user_id'],
    //         $repeat_data['department_group_id']
    //     );

    //     // Strict user constraint
    //     if (!empty($conflicts['user_conflicts'])) {
    //         $conflict = reset($conflicts['user_conflicts']);
    //         $this->message = "User {$conflict->user__displayname} has a conflict";
    //         echo $this->message; // Temporary debug
    //         log_message('debug', $this->message); // Log to system/logs
    //         return FALSE;
    //     }

    //     // Strict department group constraint
    //     if (!empty($conflicts['department_group_conflicts'])) {
    //         $conflict = reset($conflicts['department_group_conflicts']);
    //         $this->message = "Department Group {$conflict->department_group__name} has a conflict on {$conflict->date} at {$conflict->time_start}-{$conflict->time_end}.";
    //         return FALSE;
    //     }

    //     // Room conflicts (strict)
    //     if (!empty($conflicts['room_conflicts'])) {
    //         $conflict = reset($conflicts['room_conflicts']);
    //         $this->message = "Room {$conflict->room__name} is already booked on {$conflict->date} at {$conflict->time_start}-{$conflict->time_end}.";
    //         return FALSE;
    //     }

    //     // Partial capacity constraint (warning, not blocking)
    //     $department_group_size = $this->department_group ? $this->department_group->size : 1;
    //     $room_capacity = $this->room->capacity;
    //     if ($department_group_size > $room_capacity) {
    //         $this->message .= " Warning: Department group size ($department_group_size) exceeds room capacity ($room_capacity) for some bookings. Proceeding anyway.";
    //     }

    //     $repeat_id = $this->CI->bookings_repeat_model->create($repeat_data);

    //     if (!$repeat_id) {
    //         $this->message = 'Could not create recurring booking.';
    //         return FALSE;
    //     }

    //     $this->success = TRUE;
    //     $this->message = 'The bookings have been created successfully.' . $this->message;
    //     return TRUE;
    // }
    private function create_single_recurring()
    {
        $dates = $this->CI->input->post('dates');
    
        if (empty($dates)) {
            $this->message = 'No dates selected.';
            return FALSE;
        }
    
        $repeat_data = [
            'session_id' => $this->session->session_id,
            'period_id' => $this->period->period_id,
            'room_id' => $this->room->room_id,
            'user_id' => $this->user->user_id,
            'department_id' => $this->department ? $this->department->department_id : NULL,
            'week_id' => $this->date_info->week_id,
            'weekday' => $this->date_info->weekday,
            'status' => Bookings_model::STATUS_BOOKED,
            'notes' => $this->CI->input->post('notes'),
            'dates' => $dates,
            'course_id' => $this->CI->input->post('course_id') ?: ($this->course ? $this->course->course_id : NULL),
            'department_group_id' => $this->CI->input->post('department_group_id') ?: ($this->department_group ? $this->department_group->department_group_id : NULL),
        ];
    
        if ($this->is_admin) {
            $post_user_id = $this->CI->input->post('user_id');
            $repeat_data['user_id'] = !empty($post_user_id) ? $post_user_id : NULL;
        }
    
        // Check conflicts
        $conflicts = $this->CI->bookings_model->find_conflicts(
            array_keys($dates),           // Extract date strings
            $repeat_data['period_id'],    // Period ID
            $repeat_data['room_id'],      // Room ID
            $repeat_data['course_id'],    // Course ID (added)
            $repeat_data['user_id'],      // User ID
            $repeat_data['department_group_id'] // Department Group ID
        );
    
        // Strict user constraint
        if (!empty($conflicts['user_conflicts'])) {
            $conflict = reset($conflicts['user_conflicts']);
            $this->message = "User {$conflict->user__displayname} has a conflict";
            echo $this->message; // Temporary debug
            log_message('debug', $this->message); // Log to system/logs
            return FALSE;
        }
    
        // Strict department group constraint
        if (!empty($conflicts['department_group_conflicts'])) {
            $conflict = reset($conflicts['department_group_conflicts']);
            $this->message = "Department Group {$conflict->department_group__name} has a conflict on {$conflict->date} at {$conflict->time_start}-{$conflict->time_end}.";
            return FALSE;
        }
    
        // Room conflicts (strict)
        if (!empty($conflicts['room_conflicts'])) {
            $conflict = reset($conflicts['room_conflicts']);
            $this->message = "Room {$conflict->room__name} is already booked on {$conflict->date} at {$conflict->time_start}-{$conflict->time_end}.";
            return FALSE;
        }
    
        // Strict level mismatch constraint (added)
        if ($conflicts['level_mismatch']) {
            $this->message = "The course level does not match the department group's department class level.";
            return FALSE;
        }
    
        // Partial capacity constraint (warning, not blocking)
        $department_group_size = $this->department_group ? $this->department_group->size : 1;
        $room_capacity = $this->room->capacity;
        if ($department_group_size > $room_capacity) {
            $this->message .= " Warning: Department group size ($department_group_size) exceeds room capacity ($room_capacity) for some bookings. Proceeding anyway.";
        }
    
        $repeat_id = $this->CI->bookings_repeat_model->create($repeat_data);
    
        if (!$repeat_id) {
            $this->message = 'Could not create recurring booking.';
            return FALSE;
        }
    
        $this->success = TRUE;
        $this->message = 'The bookings have been created successfully.' . $this->message;
        return TRUE;
    }
}