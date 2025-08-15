<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bookings_repeat_model extends CI_Model
{
    // Table for this model
    protected $table = 'bookings_repeat';

    // Other objects to get/include with returned value
    private $include = ['bookings']; // Default to include bookings for recurring

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Bookings_model'); // Capitalized to match CI convention
        $this->load->helper('array');
    }

    /**
     * Get Repeat by ID
     */
    public function get($repeat_id)
    {
        $where = ['repeat_id' => $repeat_id];
        $query = $this->db->get_where($this->table, $where, 1);

        if ($query->num_rows() === 1) {
            return $this->wake_value($query->row());
        }

        return FALSE;
    }

    /**
     * Create a repeating booking entry along with all its instances.
     * Compatible with SingleAgent and MultiAgent structure.
     */
    public function create($data)
    {
        // Extract dates and validate
        $dates = isset($data['dates']) ? $data['dates'] : [];
        if (empty($dates) || !is_array($dates)) {
            return FALSE;
        }

        // Remove dates from data to process separately
        unset($data['dates']);

        // Prepare repeat data
        $data = $this->sleep_values($data);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $this->userauth->user->user_id;

        // Insert into bookings_repeat
        $this->db->insert($this->table, $data);
        if ($this->db->affected_rows() !== 1) {
            return FALSE;
        }

        $repeat_id = $this->db->insert_id();

        // Base data for each booking instance, excluding department_id
        $booking_data = [
            'repeat_id' => $repeat_id,
            'session_id' => element('session_id', $data, NULL),
            'period_id' => element('period_id', $data, NULL),
            'room_id' => element('room_id', $data, NULL),
            'user_id' => element('user_id', $data, NULL),
            'department_group_id' => element('department_group_id', $data, NULL),
            'course_id' => element('course_id', $data, NULL),
            'status' => Bookings_model::STATUS_BOOKED,
            'notes' => element('notes', $data, NULL),
        ];

        $booking_ids = [];

        // Process each date
        foreach ($dates as $date => $info) {
            if (!is_array($info) || !isset($info['action'])) {
                continue; // Skip malformed entries
            }

            $action = $info['action'];

            // Skip if not booking
            if ($action === 'do_not_book') {
                continue;
            }

            // Handle replacement of existing booking
            if ($action === 'replace') {
                $replace_booking_id = isset($info['replace_booking_id']) ? $info['replace_booking_id'] : NULL;
                if ($replace_booking_id) {
                    $this->Bookings_model->cancel_single($replace_booking_id);
                }
                $action = 'book';
            }

            if ($action !== 'book') {
                continue;
            }

            // Create individual booking
            $insert_data = array_merge($booking_data, ['date' => $date]);
            $booking_id = $this->Bookings_model->create($insert_data);
            if ($booking_id) {
                $booking_ids[] = $booking_id;
            }
        }

        // Return repeat_id even if no bookings were created (consistent with intent)
        return $repeat_id;
    }

    /**
     * Hydrate repeat booking object with related data
     */
    public function wake_value($row)
    {
        foreach ($this->include as $include) {
            switch ($include) {
                case 'user':
                    $this->load->model('Users_model');
                    $user = $this->Users_model->get_by_id($row->user_id);
                    if ($user) {
                        unset($user->password);
                        $row->user = $user;
                    }
                    break;

                case 'department': // Kept for compatibility, but may not be needed
                    $this->load->model('Departments_model');
                    $row->department = NULL; // No department_id in bookings, so set to NULL
                    break;

                case 'room':
                    $this->load->model('Rooms_model');
                    $room = $this->Rooms_model->get_by_id($row->room_id);
                    if ($room) {
                        $row->room = $room;
                        $row->room->info = $this->Rooms_model->room_info($room->room_id);
                        $row->room->fields = $this->Rooms_model->GetFields();
                        $row->room->fieldvalues = $this->Rooms_model->GetFieldValues($room->room_id);
                    }
                    break;

                case 'week':
                    $this->load->model('Weeks_model');
                    $row->week = isset($row->week_id)
                        ? $this->Weeks_model->get($row->week_id)
                        : FALSE;
                    break;

                case 'period':
                    $this->load->model('Periods_model');
                    $row->period = $this->Periods_model->get($row->period_id);
                    break;

                case 'session':
                    $this->load->model('Sessions_model');
                    $row->session = $this->Sessions_model->get($row->session_id);
                    break;

                case 'bookings':
                    $row->bookings = $this->Bookings_model->find_by_repeat($row->repeat_id);
                    break;
            }
        }

        return $row;
    }

    /**
     * Prepare data for database insertion
     */
    public function sleep_values($data)
    {
        $id_fields = [
            'user_id',
            'department_id',
            'department_group_id',
            'course_id',
            'session_id',
            'period_id',
            'room_id',
            'week_id',
            'repeat_id'
        ];

        foreach ($id_fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (!empty($data[$field])) ? (int) $data[$field] : NULL;
            }
        }

        if (isset($data['weekday'])) {
            $data['weekday'] = (int) $data['weekday'];
        }

        return $data;
    }
}