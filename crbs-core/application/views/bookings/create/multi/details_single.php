	<?php
	// multiple selection -> single
	defined('BASEPATH') OR exit('No direct script access allowed');

	// Date format of bookings
	$date_format = setting('date_format_long', 'crbs');

	// For period display
	$time_fmt = setting('time_format_period');

	// Generate table of bookings
	$this->table->set_template([
		'table_open' => '<table class="zebra-table form-table multibooking-table" style="line-height:1.3;margin-bottom:16px" width="100%" cellpadding="8" cellspacing="0" border="0">',
	]);

	$is_first = TRUE;

	// Get columns for table
	$dates = [];
	$rooms = [];
	foreach ($multibooking->slots as $key => $slot) {
		$dates[] = $slot->date;
		$rooms[] = $slot->room_id;
	}

	$show_date_col = (count(array_unique($dates)) == 1) ? FALSE : TRUE;
	$show_room_col = (count(array_unique($rooms)) == 1) ? FALSE : TRUE;

	$cols = [];
	$cols[] = ['data' => '', 'width' => 10];

	if ($show_date_col) {
		$cols[] = ['data' => 'Date'];
	} else {
		$cols[] = ['data' => 'Period'];
	}

	if ($show_room_col) {
		$cols[] = ['data' => 'Room'];
	}

	if ($is_admin) {
		$cols[] = ['data' => 'Department Group'];
		$cols[] = ['data' => 'Course'];
		$cols[] = ['data' => 'User'];
	}

	$cols[] = ['data' => 'Notes'];

	$this->table->set_heading($cols);

	// Generate rows
	foreach ($multibooking->slots as $key => $slot) {
		// 'Create' checkbox col
		$create_field = sprintf('slot_single[%d][create]', $slot->mbs_id);
		$create_hidden = form_hidden($create_field, 0);
		$create_check = form_checkbox([
			'id' => $create_field,
			'name' => $create_field,
			'value' => 1,
			'checked' => (set_value($create_field, 1) == 1),
		]);
		$check_col = $create_hidden . $create_check;

		// Date column
		if ($show_date_col) {
			$date = "<div>" . $slot->datetime->format($date_format) . "</div>";
			$period = '<small class="hint">' . $slot->period->name . '</small>';
			$date_col = form_label($date . $period, $create_field, ['class' => 'ni']);
		} else {
			$date_col = form_label($slot->period->name, $create_field, ['class' => 'ni']);
		}

		// Department Group column
		$dept_group_field = sprintf('slot_single[%d][department_group_id]', $slot->mbs_id);
		$options = results_to_assoc($all_department_groups, 'department_group_id', 'name', '(None)');
		$value = set_value($dept_group_field, $department_group ? $department_group->department_group_id : '', FALSE);
		$input = form_dropdown([
			'name' => $dept_group_field,
			'id' => $dept_group_field,
			'options' => $options,
			'selected' => $value,
			'up-copy-group' => 'department_group_id',
			'style' => 'width: 90%; vertical-align: middle;',
		]);
		$input_block = "<div class='block b-90' style='display: inline-block;'>{$input}</div>";
		$append_block = $is_first ? "<div class='block b-10' style='display: inline-block;'><button type='button' class='btn-block' up-copy-to='department_group_id' style='width: 24px; height: 24px; padding: 0; font-size: 14px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; line-height: 24px; text-align: center;' title='Copy to all below'>↓</button></div>" : '';
		$dept_group_col = "<div class='block-group'>{$input_block}{$append_block}</div>";

		// Course column
		$course_field = sprintf('slot_single[%d][course_id]', $slot->mbs_id);
		$options = results_to_assoc($all_courses, 'course_id', 'name', '(None)');
		$value = set_value($course_field, $course ? $course->course_id : '', FALSE);
		$input = form_dropdown([
			'name' => $course_field,
			'id' => $course_field,
			'options' => $options,
			'selected' => $value,
			'up-copy-group' => 'course_id',
			'style' => 'width: 90%; vertical-align: middle;',
		]);
		$input_block = "<div class='block b-90' style='display: inline-block;'>{$input}</div>";
		$append_block = $is_first ? "<div class='block b-10' style='display: inline-block;'><button type='button' class='btn-block' up-copy-to='course_id' style='width: 24px; height: 24px; padding: 0; font-size: 14px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; line-height: 24px; text-align: center;' title='Copy to all below'>↓</button></div>" : '';
		$course_col = "<div class='block-group'>{$input_block}{$append_block}</div>";

		// User column
		$user_field = sprintf('slot_single[%d][user_id]', $slot->mbs_id);
		$options = results_to_assoc($all_users, 'user_id', function($user) {
			return !empty($user->displayname) ? $user->displayname : $user->username;
		}, '(None)');
		$value = set_value($user_field, $user->user_id, FALSE);
		$input = form_dropdown([
			'name' => $user_field,
			'id' => $user_field,
			'options' => $options,
			'selected' => $value,
			'up-copy-group' => 'user_id',
			'style' => 'width: 90%; vertical-align: middle;',
		]);
		$input_block = "<div class='block b-90' style='display: inline-block;'>{$input}</div>";
		$append_block = $is_first ? "<div class='block b-10' style='display: inline-block;'><button type='button' class='btn-block' up-copy-to='user_id' style='width: 24px; height: 24px; padding: 0; font-size: 14px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; line-height: 24px; text-align: center;' title='Copy to all below'>↓</button></div>" : '';
		$user_col = "<div class='block-group'>{$input_block}{$append_block}</div>";

		// Notes column
		$notes_field = sprintf('slot_single[%d][notes]', $slot->mbs_id);
		$value = set_value($notes_field, '', FALSE);
		$input = form_input([
			'name' => $notes_field,
			'id' => $notes_field,
			'size' => 30,
			'value' => $value,
			'up-copy-group' => 'notes',
			'style' => 'width: 90%; vertical-align: middle;',
		]);
		$input_block = "<div class='block b-90' style='display: inline-block;'>{$input}</div>";
		$append_block = $is_first ? "<div class='block b-10' style='display: inline-block;'><button type='button' class='btn-block' up-copy-to='notes' style='width: 24px; height: 24px; padding: 0; font-size: 14px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; line-height: 24px; text-align: center;' title='Copy to all below'>↓</button></div>" : '';
		$notes_col = "<div class='block-group'>{$input_block}{$append_block}</div>";

		// Add row
		$row = [];
		$row[] = $check_col;
		$row[] = $date_col;
		if ($show_room_col) {
			$row[] = $slot->room->name;
		}
		if ($is_admin) {
			$row[] = $dept_group_col;
			$row[] = $course_col;
			$row[] = $user_col;
		}
		$row[] = $notes_col;

		$this->table->add_row($row);

		if ($is_first) {
			$is_first = FALSE;
		}
	}

	// Display fixed date/room info if applicable
	if (!$show_room_col || !$show_date_col) {
		echo "<fieldset style='padding-top:0'>";
		if (!$show_date_col) {
			$date_str = $slot->datetime->format($date_format);
			echo "<p><label>Date</label>{$date_str}</p>";
		}
		if (!$show_room_col) {
			$room_str = html_escape($slot->room->name);
			echo "<p><label>Room</label>{$room_str}</p>";
		}
		echo "</fieldset>";
	}

	echo "<fieldset style='border:0; padding:0;'>";
	echo $this->table->generate();
	echo "</fieldset>";