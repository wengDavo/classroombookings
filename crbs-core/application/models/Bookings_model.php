<?php

use app\components\bookings\Context;
use app\components\bookings\Slot;
use app\components\bookings\exceptions\BookingValidationException;
use app\components\bookings\agent\UpdateAgent;


class Bookings_model extends CI_Model
{


	const STATUS_BOOKED = 10;
	const STATUS_CANCELLED = 15;

	protected $table = 'bookings';

	// Other objects to get/include with returned value
	private $include = [];

	// Error message
	private $error = FALSE;

	// private $all_periods;
	// private $periods_by_day_num;


	// Legacy:
	var $table_headings = '';
	var $table_rows = array();



	public function __construct()
	{
		$this->load->helper('result');
		$this->load->model('sessions_model');
	}


	public function get_error()
	{
		return $this->error;
	}


	public function include($objects)
	{
		if ( ! is_array($objects)) {
			$objects = [ $objects ];
		}

		$this->include = $objects;

		return $this;
	}


	public function get($booking_id)
	{
		$this->db->reset_query();

		$this->db->select([
			'b.*',
			'd.week_id AS week_id',
			'p.time_start',
			'p.time_end',
			'p.schedule_id',
		]);

		$this->db->from("{$this->table} b");
		$this->db->join('periods p', 'period_id', 'INNER');
		$this->db->join('dates d', 'date', 'LEFT');
		$this->db->join('weeks w', 'week_id', 'LEFT');

		$this->db->where('b.booking_id', $booking_id);
		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() === 1) {
			return $this->wake_value($query->row());
		}

		return FALSE;
	}


	/**
	 * Find all bookings in a repeating series.
	 *
	 */
	public function find_by_repeat($repeat_id)
	{
		$this->db->reset_query();

		$this->db->select([
			'b.*',
			'p.time_start',
			'p.time_end',
		]);

		$this->db->from("{$this->table} AS b");
		$this->db->join('periods p', 'period_id', 'INNER');
		$this->db->where('b.repeat_id', $repeat_id);

		$this->db->order_by('date', 'ASC');

		$query = $this->db->get();
		$result = $query->result();

		$out = [];

		foreach ($result as &$row) {
			$key = Slot::generate_key($row->date, $row->period_id, $row->room_id);
			$out[ $key ] = $this->wake_value($row);
		}

		return $out;
	}


	/**
	 * Given a list of dates, a Period ID and Room ID, get a list of active
	 * bookings that already exist for that criteria.
	 *
	 * @param $dates Array of dates in Y-m-d format to check for
	 *
	 */
	// 
	
	// public function find_conflicts(array $dates, $period_id, $room_id, $user_id = null, $department_group_id = null)
	// {
	// 	$date_strings = array_filter(array_map(function($date) {
	// 		return is_string($date) ? $date : (is_object($date) && method_exists($date, 'format') ? $date->format('Y-m-d') : NULL);
	// 	}, (array) $dates));
	
	// 	if (empty($date_strings)) {
	// 		return ['user_conflicts' => [], 'department_group_conflicts' => [], 'room_conflicts' => []];
	// 	}

	// 	if (empty($dates)) return false;

	// 	$this->db->reset_query();

	// 	$this->db->select([
	// 		'b.booking_id',
	// 		'b.repeat_id',
	// 		'b.period_id',
	// 		'b.room_id',
	// 		'b.user_id',
	// 		'b.date',
	// 		'b.status',
	// 		'b.notes',
	// 		'b.department_group_id', // Replaced cohort_id
	// 		'p.time_start',
	// 		'p.time_end',
	// 	]);

	// 	$this->db->select([
	// 		'u.user_id AS user__user_id',
	// 		'u.username AS user__username',
	// 		'u.displayname AS user__displayname',
	// 	], FALSE);

	// 	$this->db->select([
	// 		'r.room_id AS room__room_id',
	// 		'r.name AS room__name',
	// 		'r.capacity AS room__capacity',
	// 	], FALSE);

	// 	$this->db->select([
	// 		'dg.department_group_id AS department_group__department_group_id',
	// 		'dg.name AS department_group__name',
	// 		'dg.size AS department_group__size', // Size from department_groups
	// 	], FALSE);

	// 	$this->db->from("{$this->table} AS b");
	// 	$this->db->join('periods p', 'p.period_id = b.period_id', 'INNER');
	// 	$this->db->join('users u', 'u.user_id = b.user_id', 'LEFT');
	// 	$this->db->join('rooms r', 'r.room_id = b.room_id', 'LEFT');
	// 	$this->db->join('department_groups dg', 'dg.department_group_id = b.department_group_id', 'LEFT'); // Replaced cohorts with department_groups

	// 	$this->db->where('b.period_id', $period_id);
	// 	$this->db->where('b.status', self::STATUS_BOOKED);
	// 	$this->db->where_in('b.date', $dates);

	// 	$this->db->order_by('date', 'ASC');

	// 	$query = $this->db->get();
		
	// 	$result = $query->result();

	// 	$out = [
	// 		'room_conflicts' => [],
	// 		'user_conflicts' => [],
	// 		'department_group_conflicts' => [],
	// 		'capacity_info' => [],
	// 	];

	// 	foreach ($result as &$row) {
	// 		$key = Slot::generate_key($row->date, $row->period_id, $row->room_id);
	// 		$booking = $this->wake_value($row);

	// 		// Room-specific conflicts
	// 		if ($row->room_id == $room_id) {
	// 			$out['room_conflicts'][$key] = $booking;
	// 		}

	// 		// User conflicts (across all rooms)
	// 		if ($user_id !== null && $row->user_id == $user_id) {
	// 			$out['user_conflicts'][$key] = $booking;
	// 		}

	// 		// Department group conflicts (across all rooms and users)
	// 		if ($department_group_id !== null && $row->department_group_id == $department_group_id) {
	// 			$out['department_group_conflicts'][$key] = $booking;
	// 		}

	// 		// Capacity info for the specified room
	// 		if ($row->room_id == $room_id) {
	// 			$out['capacity_info'][$key] = [
	// 				'capacity' => $row->room__capacity,
	// 				'department_group_size' => $row->department_group__size ?? 1,
	// 			];
	// 		}
	// 	}

	// 	return $out;
	// }

	public function find_conflicts(array $dates, $period_id, $room_id, $course_id = null, $user_id = null, $department_group_id = null)
	{
		$date_strings = array_filter(array_map(function($date) {
			return is_string($date) ? $date : (is_object($date) && method_exists($date, 'format') ? $date->format('Y-m-d') : NULL);
		}, (array) $dates));
	
		if (empty($date_strings)) {
			return ['user_conflicts' => [], 'department_group_conflicts' => [], 'room_conflicts' => [], 'level_mismatch' => false];
		}
	
		if (empty($dates)) return false;
	
		$this->db->reset_query();
	
		$this->db->select([
			'b.booking_id',
			'b.repeat_id',
			'b.period_id',
			'b.room_id',
			'b.user_id',
			'b.date',
			'b.status',
			'b.notes',
			'b.department_group_id',
			'p.time_start',
			'p.time_end',
		]);
	
		$this->db->select([
			'u.user_id AS user__user_id',
			'u.username AS user__username',
			'u.displayname AS user__displayname',
		], FALSE);
	
		$this->db->select([
			'r.room_id AS room__room_id',
			'r.name AS room__name',
			'r.capacity AS room__capacity',
		], FALSE);
	
		$this->db->select([
			'dg.department_group_id AS department_group__department_group_id',
			'dg.name AS department_group__name',
			'dg.size AS department_group__size',
		], FALSE);
	
		$this->db->from("{$this->table} AS b");
		$this->db->join('periods p', 'p.period_id = b.period_id', 'INNER');
		$this->db->join('users u', 'u.user_id = b.user_id', 'LEFT');
		$this->db->join('rooms r', 'r.room_id = b.room_id', 'LEFT');
		$this->db->join('department_groups dg', 'dg.department_group_id = b.department_group_id', 'LEFT');
	
		$this->db->where('b.period_id', $period_id);
		$this->db->where('b.status', self::STATUS_BOOKED);
		$this->db->where_in('b.date', $dates);
	
		$this->db->order_by('date', 'ASC');
	
		$query = $this->db->get();
		
		$result = $query->result();
	
		$out = [
			'room_conflicts' => [],
			'user_conflicts' => [],
			'department_group_conflicts' => [],
			'capacity_info' => [],
			'level_mismatch' => false,
		];
	
		foreach ($result as &$row) {
			$key = Slot::generate_key($row->date, $row->period_id, $row->room_id);
			$booking = $this->wake_value($row);
	
			// Room-specific conflicts
			if ($row->room_id == $room_id) {
				$out['room_conflicts'][$key] = $booking;
			}
	
			// User conflicts (across all rooms)
			if ($user_id !== null && $row->user_id == $user_id) {
				$out['user_conflicts'][$key] = $booking;
			}
	
			// Department group conflicts (across all rooms and users)
			if ($department_group_id !== null && $row->department_group_id == $department_group_id) {
				$out['department_group_conflicts'][$key] = $booking;
			}
	
			// Capacity info for the specified room
			if ($row->room_id == $room_id) {
				$out['capacity_info'][$key] = [
					'capacity' => $row->room__capacity,
					'department_group_size' => $row->department_group__size ?? 1,
				];
			}
		}
	
		// Check for course level mismatch with department group’s department class
		if ($course_id && $department_group_id) {
			// Get the course’s level_id
			$course = $this->db->select('level_id')
				->from('courses')
				->where('course_id', $course_id)
				->get()
				->row();
	
			if ($course) {
				$course_level_id = $course->level_id;
	
				// Get the department group’s department class level_id
				$department_group = $this->db->select('dc.level_id')
					->from('department_groups dg')
					->join('department_classes dc', 'dg.department_class_id = dc.department_class_id')
					->where('dg.department_group_id', $department_group_id)
					->get()
					->row();
	
				if ($department_group && $department_group->level_id != $course_level_id) {
					$out['level_mismatch'] = true;
				}
			}
		}
	
		return $out;
	}

	/**
	 * Find all bookings relevant to the provided Context.
	 *
	 * Context will include things like room, dates, session, course and department
	 * this is the slot that loads on the calendar to be clicked on
	 *
	 * @param Context $context Populated Context instance.
	 * @return array Array of bookings
	 *
	 */
	// public function find_for_context(Context $context)
	// {
	// 	$this->db->reset_query();
	
	// 	$this->db->select([
	// 		'b.booking_id',
	// 		'b.repeat_id',
	// 		'b.period_id',
	// 		'b.room_id',
	// 		'b.user_id',
	// 		'b.department_group_id', // Added
	// 		'b.course_id',           // Added
	// 		'b.date',
	// 		'b.status',
	// 		'b.notes',
	// 		'p.time_start',
	// 		'p.time_end',
	// 	]);
	
	// 	$this->db->select([
	// 		'u.user_id AS user__user_id',
	// 		'u.username AS user__username',
	// 		'u.displayname AS user__displayname'
	// 	], FALSE);
	
	// 	$this->db->select([
	// 		'r.week_id AS repeat__week_id',
	// 		'r.weekday AS repeat__weekday',
	// 	], FALSE);
	
	// 	$this->db->select([
	// 		'w.week_id AS repeat_week__week_id',
	// 		'w.name AS repeat_week__name',
	// 		'w.fgcol AS repeat_week__fgcol',
	// 		'w.bgcol AS repeat_week__bgcol',
	// 	], FALSE);
	
	// 	// Add department group fields
	// 	$this->db->select([
	// 		'dg.department_group_id AS department_group__department_group_id',
	// 		'dg.name AS department_group__name',
	// 	], FALSE);
	
	// 	// Add course fields
	// 	$this->db->select([
	// 		'c.course_id AS course__course_id',
	// 		'c.name AS course__name',
	// 	], FALSE);
	
	// 	$this->db->from("{$this->table} AS b");
	// 	$this->db->join('periods p', 'period_id', 'INNER');
	// 	$this->db->join('users u', 'user_id', 'LEFT');
	// 	$this->db->join('bookings_repeat r', 'repeat_id', 'LEFT');
	// 	$this->db->join('weeks w', 'week_id', 'LEFT');
	// 	$this->db->join('department_groups dg', 'b.department_group_id = dg.department_group_id', 'LEFT'); // Join department_groups
	// 	$this->db->join('courses c', 'b.course_id = c.course_id', 'LEFT');                         // Join courses
	
	// 	$this->db->where('b.status', self::STATUS_BOOKED);
	// 	if ($context->session) {
	// 		$this->db->where('b.session_id', $context->session->session_id);
	// 	} else {
	// 		$this->db->where('b.session_id', -1);
	// 	}
	
	// 	switch ($context->display_type) {
	// 		case 'day':
	// 			$this->db->where('b.date', $context->datetime->format('Y-m-d'));
	// 			break;
	
	// 		case 'room':
	// 			$this->db->where([
	// 				'b.room_id' => ($context->room) ? $context->room->room_id : null,
	// 			]);
	// 			$this->db->where([
	// 				'b.date >=' => $context->week_start->format('Y-m-d'),
	// 				'b.date <=' => $context->week_end->format('Y-m-d'),
	// 			]);
	// 			break;
	// 	}
	
	// 	$query = $this->db->get();
	// 	$result = $query->result();
	
	// 	$out = [];
	
	// 	foreach ($result as &$row) {
	// 		$key = Slot::generate_key($row->date, $row->period_id, $row->room_id);
	// 		$out[$key] = $this->wake_value($row);
	// 	}
	
	// 	return $out;
	// }
	public function find_for_context(Context $context)
	{
		$this->db->reset_query();
	
		$this->db->select([
			'b.booking_id',
			'b.repeat_id',
			'b.period_id',
			'b.room_id',
			'b.user_id',
			'b.department_group_id',
			'b.course_id',
			'b.date',
			'b.status',
			'b.notes',
			'p.time_start',
			'p.time_end',
		]);
	
		$this->db->select([
			'u.user_id AS user__user_id',
			'u.username AS user__username',
			'u.displayname AS user__displayname'
		], FALSE);
	
		$this->db->select([
			'r.week_id AS repeat__week_id',
			'r.weekday AS repeat__weekday',
		], FALSE);
	
		$this->db->select([
			'w.week_id AS repeat_week__week_id',
			'w.name AS repeat_week__name',
			'w.fgcol AS repeat_week__fgcol',
			'w.bgcol AS repeat_week__bgcol',
		], FALSE);
	
		// Department group fields (unchanged)
		$this->db->select([
			'dg.department_group_id AS department_group__department_group_id',
			'dg.name AS department_group__name',
			'dg.department_class_id AS department_group__department_class_id',
			'dg.identifier AS department_group__identifier',
		], FALSE);
	
		// Course fields (unchanged)
		$this->db->select([
			'c.course_id AS course__course_id',
			'c.name AS course__name',
			'c.course_code AS course__course_code',
			'c.department_id AS course__department_id',
			'c.credits AS course__credits',
		], FALSE);
	
		// Add department fields
		$this->db->select([
			'd.department_id AS department__department_id',
			'd.name AS department__name',
			'd.description AS department__description',
		], FALSE);
	
		$this->db->from("{$this->table} AS b");
		$this->db->join('periods p', 'period_id', 'INNER');
		$this->db->join('users u', 'user_id', 'LEFT');
		$this->db->join('bookings_repeat r', 'repeat_id', 'LEFT');
		$this->db->join('weeks w', 'week_id', 'LEFT');
		$this->db->join('department_groups dg', 'b.department_group_id = dg.department_group_id', 'LEFT');
		$this->db->join('courses c', 'b.course_id = c.course_id', 'LEFT');
		$this->db->join('departments d', 'c.department_id = d.department_id', 'LEFT'); // Join departments on course.department_id
	
		$this->db->where('b.status', self::STATUS_BOOKED);
		if ($context->session) {
			$this->db->where('b.session_id', $context->session->session_id);
		} else {
			$this->db->where('b.session_id', -1);
		}
	
		switch ($context->display_type) {
			case 'day':
				$this->db->where('b.date', $context->datetime->format('Y-m-d'));
				break;
	
			case 'room':
				$this->db->where([
					'b.room_id' => ($context->room) ? $context->room->room_id : null,
				]);
				$this->db->where([
					'b.date >=' => $context->week_start->format('Y-m-d'),
					'b.date <=' => $context->week_end->format('Y-m-d'),
				]);
				break;
		}
	
		$query = $this->db->get();
		$result = $query->result();
	
		$out = [];
	
		foreach ($result as &$row) {
			$key = Slot::generate_key($row->date, $row->period_id, $row->room_id);
			$out[$key] = $this->wake_value($row);
		}
	
		return $out;
	}

	/**
	 * Check various parameters of a booking creation request to ensure it can
	 * be made, no conflicts will occur and all parameters are correct.
	 *
	 */
	public function validate_booking($data)
	{
		$sql = 'SELECT booking_id
				FROM bookings
				WHERE `date` = ?
				AND period_id = ?
				AND room_id = ?
				AND status = ?
				LIMIT 1';

		$query = $this->db->query($sql, [$data['date'], $data['period_id'], $data['room_id'], self::STATUS_BOOKED]);

		$row = $query->row();

		if ($query->num_rows() === 1 && $row->booking_id) {
			throw BookingValidationException::forExistingBooking();
		}

		return TRUE;
	}


	public function create($data)
	{
		try {
			$this->validate_booking($data);
		} catch (BookingValidationException $e) {
			$this->error = $e->getMessage();
			return FALSE;
		}

		$data = $this->sleep_values($data);

		$data['created_at'] = date('Y-m-d H:i:s');
		$data['created_by'] = $this->userauth->user->user_id;

		$ins = $this->db->insert($this->table, $data);

		return ($ins && $id = $this->db->insert_id())
			? $id
			: FALSE;
	}


	public function update($booking_id, $data, $edit_mode = UpdateAgent::EDIT_ONE)
{
    $data = $this->sleep_values($data);

    $data['updated_at'] = date('Y-m-d H:i:s');
    $data['updated_by'] = $this->userauth->user->user_id;

    switch ($edit_mode) {

        case UpdateAgent::EDIT_ONE:

            $where = [
                'booking_id' => $booking_id,
            ];

            return $this->db->update($this->table, $data, $where, 1);

            break;

        case UpdateAgent::EDIT_FUTURE:

            $booking = $this->get($booking_id);

            $where = [
                'repeat_id' => $booking->repeat_id,
                'session_id' => $booking->session_id,
                'date >=' => $booking->date->format('Y-m-d'),
            ];

            return $this->db->update($this->table, $data, $where);

            break;

        case UpdateAgent::EDIT_ALL:

            $booking = $this->get($booking_id);

            $where = [
                'repeat_id' => $booking->repeat_id,
                'session_id' => $booking->session_id,
            ];

            $update_bk = $this->db->update($this->table, $data, $where);

            // Update repeat table with data
            $repeat_keys = [
                'user_id',
                // 'department_id', // Commented out as redundant with department_groups
                'department_group_id', // Added for department_groups
                'notes',
                'updated_at',
                'updated_by',
            ];

            // Ensure we only send valid data to repeat table
            $repeat_data = [];
            foreach ($repeat_keys as $k) {
                $repeat_data[$k] = isset($data[$k]) ? $data[$k] : NULL;
            }

            $update_rep = $this->db->update('bookings_repeat', $repeat_data, $where);

            return ($update_bk && $update_rep);

            break;
    }

    return FALSE;
}


	/**
	 * Cancel a single instance of a booking.
	 *
	 */
	public function cancel_single($booking_id)
	{
		$data = [
			'status' => self::STATUS_CANCELLED,
			'cancelled_at' => date('Y-m-d H:i:s'),
			'cancelled_by' => $this->userauth->user->user_id,
		];

		return $this->db->update($this->table, $data, ['booking_id' => $booking_id], 1);
	}


	/**
	 * Cancel booking + future instances in series.
	 *
	 */
	public function cancel_future($booking_id)
	{
		$booking = $this->get($booking_id);

		if ( ! $booking->repeat_id) return FALSE;

		$data = [
			'status' => self::STATUS_CANCELLED,
			'cancelled_at' => date('Y-m-d H:i:s'),
			'cancelled_by' => $this->userauth->user->user_id,
		];

		$where = [
			'repeat_id' => $booking->repeat_id,
			'session_id' => $booking->session_id,
			'date >=' => $booking->date->format('Y-m-d'),
		];

		return $this->db->update($this->table, $data, $where);
	}


	public function cancel_all($booking_id)
	{
		$booking = $this->get($booking_id);

		if ( ! $booking->repeat_id) return FALSE;

		$data = [
			'status' => self::STATUS_CANCELLED,
			'cancelled_at' => date('Y-m-d H:i:s'),
			'cancelled_by' => $this->userauth->user->user_id,
		];

		$where = [
			'repeat_id' => $booking->repeat_id,
			'session_id' => $booking->session_id,
		];

		$update1 = $this->db->update($this->table, $data, $where);

		$update2 = $this->db->update('bookings_repeat', $data, $where, 1);

		return ($update1 && $update2);
	}


	public function wake_value($row)
{
    $row = nest_object_keys($row);

    if (isset($row->period) && is_object($row->period)) {
        $row->time_start = $row->period->time_start;
        $row->time_end = $row->period->time_end;
    }

    $datetime_value = (empty($row->time_start))
        ? $row->date
        : "{$row->date} {$row->time_start}";

    $row->date = datetime_from_string($datetime_value);

    if (is_object($row->date)) {
        $row->time_start = datetime_from_string(sprintf('%s %s', $row->date->format('Y-m-d'), $row->time_start));
        $row->time_end = datetime_from_string(sprintf('%s %s', $row->date->format('Y-m-d'), $row->time_end));
    }

    foreach ($this->include as $include) {
        switch ($include) {
            case 'user':
                $this->load->model('users_model');
                $this->load->model('departments_model');
                $user = $this->users_model->get_by_id($row->user_id);
                unset($user->password);
                $row->user = $user;
                if ($row->user) {
                    $row->user->department = ($user->department_id)
                        ? $this->departments_model->Get($user->department_id)
                        : false;
                }
                break;

            case 'department':
                $this->load->model('departments_model');
                $row->department = isset($row->department_id)
                    ? $this->departments_model->Get($row->department_id)
                    : false;
                break;

            case 'room':
                $this->load->model('rooms_model');
                $room = $this->rooms_model->get_by_id($row->room_id);
                $row->room = $room;
                $row->room->info = $this->rooms_model->room_info($room);
                $row->room->fields = $this->rooms_model->GetFields();
                $row->room->fieldvalues = $this->rooms_model->GetFieldValues($room->room_id);
                break;

            case 'week':
                $this->load->model('weeks_model');
                $row->week = isset($row->week_id)
                    ? $this->weeks_model->get($row->week_id)
                    : false;
                break;

            case 'period':
                $this->load->model('periods_model');
                $row->period = $this->periods_model->get($row->period_id);
                break;

            case 'session':
                $this->load->model('sessions_model');
                $row->session = $this->sessions_model->get($row->session_id);
                break;

            case 'repeat':
                $this->load->model('bookings_repeat_model');
                $row->repeat = $this->bookings_repeat_model->get($row->repeat_id);
                break;

            case 'department_group':
                $this->load->model('department_groups_model');
                $row->department_group = isset($row->department_group_id)
                    ? $this->department_groups_model->get($row->department_group_id)
                    : false;
                break;

            case 'course':
                $this->load->model('courses_model');
                $row->course = isset($row->course_id)
                    ? $this->courses_model->get($row->course_id)
                    : false;
                break;
        }
    }

    return $row;
}

	// public function sleep_values($data)
	// {
	// 	if (isset($data['user_id'])) {
	// 		$data['user_id'] = (!empty($data['user_id']))
	// 			? (int) $data['user_id']
	// 			: NULL;
	// 	}

	// 	if (isset($data['department_id'])) {
	// 		$data['department_id'] = (!empty($data['department_id']))
	// 			? (int) $data['department_id']
	// 			: NULL;
	// 	}

	// 	if (isset($data['date'])) {
	// 		$dt = datetime_from_string($data['date']);
	// 		$data['date'] = $dt ? $dt->format('Y-m-d') : NULL;
	// 	}

	// 	return $data;
	// }

	public function sleep_values($data)
{
    // Handle ID fields, converting to int or NULL
    $id_fields = [
        'user_id',
        'department_id',
        'department_group_id',
        'course_id',
        'session_id',
        'period_id',
        'room_id',
        'repeat_id'
    ];

    foreach ($id_fields as $field) {
        if (isset($data[$field])) {
            $data[$field] = (!empty($data[$field])) ? (int) $data[$field] : NULL;
        }
    }

    // Handle date
    if (isset($data['date'])) {
        $dt = datetime_from_string($data['date']);
        $data['date'] = $dt ? $dt->format('Y-m-d') : NULL;
    }

    return $data;
}

	/**
	 * Given a session ID, delete any existing bookings that fall outside of its date range.
	 *
	 */
	public function check_session_dates($session_id)
	{
		$session = $this->sessions_model->get($session_id);
		if ( ! $session) return FALSE;

		$sql = "DELETE FROM {$this->table}
				WHERE session_id = ?
				AND (`date` < ? OR `date` > ?)";

		$this->db->query($sql, [
			$session->session_id,
			$session->date_start->format('Y-m-d'),
			$session->date_end->format('Y-m-d'),
		]);

		return $this->db->affected_rows();
	}


	/**
	 * Delete entries for a given session.
	 *
	 */
	public function delete_by_session($session_id)
	{
		return $this->db->delete($this->table, ['session_id' => $session_id]);
		return $this->db->delete('bookings_repeat', ['session_id' => $session_id]);
	}


	function ByRoomOwner($user_id)
	{
		$date = new \DateTime();
		$start = $date->format('Y-m-d');
		$date->modify('+14 days');
		$end = $date->format('Y-m-d');

		$this->db->reset_query();

		$this->db->select([
			'b.date',
			'b.notes',
		]);

		$this->db->select([
			'p.name AS period__name',
			'p.time_start AS period__time_start',
			'p.time_end AS period__time_end',
		], FALSE);

		$this->db->select([
			'r.room_id AS room__room_id',
			'r.name AS room__name',
		], FALSE);

		$this->db->select([
			'u.user_id AS user__user_id',
			'u.username AS user__username',
			'u.displayname AS user__displayname'
		], FALSE);

		$this->db->from("{$this->table} AS b");
		$this->db->join('periods p', 'period_id', 'INNER');
		$this->db->join('rooms r', 'room_id', 'INNER');
		$this->db->join('users u', 'b.user_id = u.user_id', 'INNER');

		$this->db->where('r.user_id', $user_id);
		$this->db->where('b.user_id!=', $user_id);
		$this->db->where('b.repeat_id IS NULL');
		$this->db->where('b.status', self::STATUS_BOOKED);
		$this->db->where('b.date>=', $start);
		$this->db->where('b.date<=', $end);

		$this->db->order_by('b.date', 'ASC');
		$this->db->order_by('p.time_start', 'ASC');

		$query = $this->db->get();
		$result = $query->result();

		if ($query->num_rows() == 0) return FALSE;

		foreach ($result as &$row) {
			$row = $this->wake_value($row);
		}

		return $result;
	}




	function ByUser($user_id)
	{
		$date = new \DateTime();
		$start = $date->format('Y-m-d');
		$time = $date->format('H:i') . ':00';
		$date->modify('+14 days');
		$end = $date->format('Y-m-d');

		$this->db->reset_query();

		$this->db->select([
			'b.date',
			'b.notes',
		]);

		$this->db->select([
			'p.name AS period__name',
			'p.time_start AS period__time_start',
			'p.time_end AS period__time_end',
		], FALSE);

		$this->db->select([
			'r.room_id AS room__room_id',
			'r.name AS room__name',
		], FALSE);

		$this->db->from("{$this->table} AS b");
		$this->db->join('periods p', 'period_id', 'INNER');
		$this->db->join('rooms r', 'room_id', 'INNER');

		$this->db->where('b.user_id', $user_id);
		$this->db->where('b.repeat_id IS NULL');
		$this->db->where('b.status', self::STATUS_BOOKED);

		$this->db->where('b.date<=', $end);

		$start = $this->db->escape($start);
		$end = $this->db->escape($end);
		$time = $this->db->escape($time);
		$this->db->where("( (b.date > {$start}) OR (b.date = {$start} AND p.time_start > {$time}) )");

		$this->db->order_by('b.date', 'ASC');
		$this->db->order_by('p.time_start', 'ASC');

		$query = $this->db->get();
		$result = $query->result();

		if ($query->num_rows() == 0) return FALSE;

		foreach ($result as &$row) {
			$row = $this->wake_value($row);
		}

		return $result;
	}


	public function CountScheduledByUser($user_id)
	{
		$today = date("Y-m-d");
		$time = date('H:i') . ':00';

		$sql = 'SELECT COUNT(booking_id) AS total
				FROM bookings
				INNER JOIN periods USING (period_id)
				WHERE bookings.user_id = ?
				AND bookings.status = 10
				AND bookings.date IS NOT NULL
				AND bookings.repeat_id IS NULL
				AND (
					(bookings.date > ?)	/* after today */
					OR
					(bookings.date = ? AND periods.time_start > ?) /* today, but after cur time */
				)';

		$query = $this->db->query($sql, [
			$user_id,
			$today,
			$today,
			$time
		]);

		$row = $query->row_array();
		return (int) $row['total'];
	}


	function TotalNum($user_id = 0)
	{
		$total = [];

		// All bookings by user, EVER!
		$sql = "SELECT COUNT(booking_id) AS total
				FROM bookings
				WHERE user_id = ?";
		$query = $this->db->query($sql, [$user_id]);
		$row = $query->row_array();
		$total['all'] = (int) $row['total'];

		// All bookings by user, for the current session
		$sql = "SELECT COUNT(b.booking_id) AS total
				FROM bookings b
				JOIN sessions s USING (session_id)
				WHERE b.user_id = ?
				AND s.is_current = 1";
		$query = $this->db->query($sql, [$user_id]);
		$row = $query->row_array();
		$total['session'] = (int) $row['total'];

		// All bookings up to and including today
		// $sql = "SELECT COUNT(booking_id) AS total
		// 		FROM bookings
		// 		WHERE bookings.user_id = ?
		// 		AND bookings.date <= ?";
		// $query = $this->db->query($sql, [$user_id, $today]);
		// $row = $query->row_array();
		// $total['todate'] = $row['total'];

		$total['active'] = $this->CountScheduledByUser($user_id);

		return $total;
	}




}
