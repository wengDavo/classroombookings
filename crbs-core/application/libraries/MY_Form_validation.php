<?php

use app\components\bookings\Slot;


class MY_Form_validation extends CI_Form_validation
{

	public $CI;

	public function __construct($rules = array())
	{
		parent::__construct($rules);

		$this->CI =& get_instance();
	}


	public function valid_date($value)
	{
		// Log the input for debugging
		log_message('debug', "Validating date: $value");
	
		$dt = datetime_from_string($value);
	
		if (!$dt || !($dt instanceof DateTime)) {
			$this->set_message('valid_date', '{field} must be a valid date.');
			log_message('error', "Invalid date format for: $value");
			return FALSE;
		}
	
		// Optional: Ensure it’s a valid Y-m-d string
		$formatted = $dt->format('Y-m-d');
		if ($formatted !== $value) {
			$this->set_message('valid_date', '{field} must be in YYYY-MM-DD format.');
			log_message('error', "Date $value reformatted to $formatted, expected exact match");
			return FALSE;
		}
	
		return TRUE;
	}


	public function valid_time($value)
	{
		$am = strtotime('00:00');
		$pm = strtotime('23:59');
		$ts = strtotime($value);

		$has_ts = !empty($ts);
		$is_after = $ts >= $am;
		$is_before = $ts <= $pm;

		if ( ! $has_ts) {
			$this->set_message('valid_time', 'Time must be provided.');
			return FALSE;
		}

		if ( ! $is_after) {
			$this->set_message('valid_time', 'Time must be after 00:00');
			return FALSE;
		}

		if ( ! $is_before) {
			$this->set_message('valid_time', 'Time must be before 23:59');
			return FALSE;
		}

		return TRUE;
	}


	public function time_is_after($value, $earlier_field)
	{
		$earlier_value = $this->_field_data[$earlier_field]['postdata'];
		if (empty($earlier_value)) return TRUE;

		$earlier_ts = strtotime($earlier_value);
		$ts = strtotime($value);

		if ($ts < $earlier_ts) {
			$this->set_message('time_is_after', 'Time must be after the earlier time.');
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * Should be applied to the 'date' field for a booking.
	 *
	 */
	public function no_conflict($value, $params)
	{
		// Parse parameters: booking_id and field name
		list($booking_id, $field) = explode(',', $params);
	
		// Load data from POST or fallback to current booking
		$date = $this->_field_data['booking_date']['postdata'] ?? null;
		if ($date) {
			// Assume POST date is in 'd/m/Y' format from the view; adjust if different
			$date = DateTime::createFromFormat('d/m/Y', $date); // Match your form input format
			$date_ymd = $date ? $date->format('Y-m-d') : null;
		} else {
			$date_ymd = null; // Will fallback below
		}
	
		$period_id = $this->_field_data['period_id']['postdata'] ?? null;
		$room_id = $this->_field_data['room_id']['postdata'] ?? null;
		$user_id = $this->_field_data['user_id']['postdata'] ?? null;
		$department_group_id = $this->_field_data['department_group_id']['postdata'] ?? null;
	
		$current_booking = $this->CI->Bookings_model->get($booking_id);
		// Ensure date is a string, handling both string and DateTime cases
		$date_ymd = $date_ymd ?? (is_object($current_booking->date) ? $current_booking->date->format('Y-m-d') : $current_booking->date);
		$period_id = $period_id ?? $current_booking->period_id;
		$room_id = $room_id ?? $current_booking->room_id;
		$user_id = $user_id ?? $current_booking->user_id;
		$department_group_id = $department_group_id ?? $current_booking->department_group_id;
	
		if (!$date_ymd) {
			$this->set_message('no_conflict', 'A valid date is required.');
			return FALSE;
		}
	
		$conflicts = $this->CI->Bookings_model->find_conflicts(
			[$date_ymd],
			$period_id,
			$room_id,
			$user_id,
			$department_group_id
		);
	
		switch ($field) {
			case 'booking_date':
			case 'period_id':
			case 'room_id':
				$conflict_array = $conflicts['room_conflicts'];
				$conflict_type = 'room';
				break;
			case 'user_id':
				$conflict_array = $conflicts['user_conflicts'];
				$conflict_type = 'user';
				break;
			case 'department_group_id':
				$conflict_array = $conflicts['department_group_conflicts'];
				$conflict_type = 'department group';
				break;
			default:
				return TRUE;
		}
	
		if (empty($conflict_array)) {
			return TRUE;
		}
	
		$conflict = current($conflict_array);
		if ($conflict->booking_id == $booking_id) {
			return TRUE;
		}
	
		$booking_card_uri = site_url('bookings/card/' . $conflict->booking_id);
		$msg = sprintf('Another booking already exists for this %s. <a href="%s" up-target=".bookings-card" up-layer="new popup" up-size="medium">View details</a>.',
			$conflict_type,
			$booking_card_uri
		);
	
		$this->set_message('no_conflict', $msg);
		return FALSE;
	}
}
