<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use app\components\bookings\agent\UpdateAgent;

// Form attributes
$attrs = [
    'id' => 'bookings_edit',
    'class' => 'cssform',
    'up-layer' => 'current root',
    'up-target' => '.bookings-edit',
];

$hidden = [
    'booking_id' => $booking->booking_id,
    'edit' => $edit_mode,
];

// Display messages and validation errors
if ($message) {
    echo msgbox('error', $message);
}

echo validation_errors();

echo form_open(current_url(), $attrs, $hidden);

// Recurring booking message
if ($booking->repeat_id) {
    $msg = '';
    switch ($edit_mode) {
        case UpdateAgent::EDIT_ONE:
            $msg = 'The changes you make below will apply to the selected booking only.';
            break;
        case UpdateAgent::EDIT_FUTURE:
            $msg = 'The changes you make below will apply to the selected booking and all future entries in the series.';
            break;
        case UpdateAgent::EDIT_ALL:
            $msg = 'The changes you make below will apply to all bookings in the series.';
            break;
    }
    echo "<div style='margin-bottom:16px'>{$msg}</div>";
}

// Booking fields
echo "<fieldset style='border:0'>";

// Date
$field = 'booking_date';
$label = form_label('Date', $field);
$datetime = datetime_from_string($booking->date);
if ($features[UpdateAgent::FEATURE_DATE]) {
    $input = form_input([
        'class' => 'up-datepicker-input',
        'name' => $field,
        'id' => $field,
        'size' => '10',
        'maxlength' => '10',
        'tabindex' => tab_index(),
        'value' => set_value($field, $datetime ? $datetime->format('d/m/Y') : '', FALSE),
    ]);
    $input .= img([
        'style' => 'cursor:pointer',
        'align' => 'top',
        'src' => base_url('assets/images/ui/cal_day.png'),
        'width' => 16,
        'height' => 16,
        'title' => 'Choose date',
        'class' => 'up-datepicker',
        'up-data' => html_escape(json_encode(['input' => $field])),
    ]);
} else {
    $input = sprintf('%s (%s)', $datetime->format(setting('date_format_long')), html_escape($booking->week->name));
    if ($edit_mode != UpdateAgent::EDIT_ONE) {
        $input .= ' (+ others)';
    }
}
echo "<p>{$label}{$input}</p>";

// Period
$field = 'period_id';
$label = form_label('Period', $field);
$time_fmt = setting('time_format_period');
if ($features[UpdateAgent::FEATURE_PERIOD]) {
    $options = results_to_assoc($all_periods, 'period_id', function($period) use ($time_fmt) {
        $start = date($time_fmt, strtotime($period->time_start));
        $end = date($time_fmt, strtotime($period->time_end));
        return sprintf('%s (%s - %s)', $period->name, $start, $end);
    });
    $value = set_value($field, $booking->period_id, FALSE);
    $input = form_dropdown([
        'name' => $field,
        'id' => $field,
        'options' => $options,
        'selected' => $value,
    ]);
} else {
    $input = html_escape($booking->period->name);
    if (!empty($time_fmt)) {
        $start = date($time_fmt, strtotime($booking->period->time_start));
        $end = date($time_fmt, strtotime($booking->period->time_end));
        $input .= sprintf(' <span style="font-size:90%%;color:#aaa;background:transparent">(%s - %s)</span>', $start, $end);
    }
}
echo "<p>{$label}{$input}</p>";

// Room
$field = 'room_id';
$label = form_label('Room', $field);
if ($features[UpdateAgent::FEATURE_ROOM]) {
    $options = results_to_assoc($all_rooms, 'room_id', 'name');
    $value = set_value($field, $booking->room_id, FALSE);
    $input = form_dropdown([
        'name' => $field,
        'id' => $field,
        'options' => $options,
        'selected' => $value,
    ]);
} else {
    $input = html_escape($booking->room->name);
}
echo "<p>{$label}{$input}</p>";

// Who (User)
$field = 'user_id';
$label = form_label('Who', $field);
if ($features[UpdateAgent::FEATURE_USER]) { // Changed to use FEATURE_USER for consistency
    $options = results_to_assoc($all_users, 'user_id', function($user) {
        return !empty($user->displayname) ? $user->displayname : $user->username;
    }, '(None)');
    $value = set_value($field, $booking->user_id, FALSE);
    $input = form_dropdown([
        'name' => $field,
        'id' => $field,
        'options' => $options,
        'selected' => $value,
    ]);
} else {
    $input = !empty($booking->user->displayname) ? html_escape($booking->user->displayname) : html_escape($booking->user->username);
}
echo "<p>{$label}{$input}</p>";

// Department Group
$field = 'department_group_id';
$label = form_label('Department Group', $field);
$show_department_group = FALSE;
if ($features[UpdateAgent::FEATURE_DEPARTMENT_GROUP] ?? FALSE) {
    $show_department_group = TRUE;
    $options = results_to_assoc($all_department_groups, 'department_group_id', 'name', '(None)');
    $value = set_value($field, $booking->department_group_id, FALSE);
    $input = form_dropdown([
        'name' => $field,
        'id' => $field,
        'options' => $options,
        'selected' => $value,
    ]);
    if ($booking->department_group_id && $booking->room && $booking->department_group->size > $booking->room->capacity) {
        echo "<p style='color: #ff9900; font-size: 12px;'>Warning: Department group size ({$booking->department_group->size}) exceeds room capacity ({$booking->room->capacity}).</p>";
    }
} else {
    if ($booking->department_group_id) {
        $show_department_group = TRUE;
        $input = html_escape($booking->department_group->name);
    }
}
echo $show_department_group ? "<p>{$label}{$input}</p>" : '';

// Course (new field)
$field = 'course_id';
$label = form_label('Course', $field);
$show_course = FALSE;
if ($features[UpdateAgent::FEATURE_COURSE] ?? FALSE) {
    $show_course = TRUE;
    $options = results_to_assoc($all_courses, 'course_id', 'name', '(None)');
    $value = set_value($field, $booking->course_id, FALSE);
    $input = form_dropdown([
        'name' => $field,
        'id' => $field,
        'options' => $options,
        'selected' => $value,
    ]);
} else {
    if ($booking->course_id) {
        $show_course = TRUE;
        $input = html_escape($booking->course->name);
    }
}
echo $show_course ? "<p>{$label}{$input}</p>" : '';

// Notes
$field = 'notes';
$value = set_value($field, $booking->notes, FALSE);
$label = form_label('Notes', $field);
if ($features[UpdateAgent::FEATURE_NOTES]) {
    $input = form_textarea([
        'name' => $field,
        'id' => $field,
        'rows' => '3',
        'cols' => '50',
        'tabindex' => tab_index(),
        'value' => $value,
    ]);
} else {
    $input = '<span>' . html_escape($booking->notes) . '</span>';
}
echo sprintf("<p>%s%s</p>%s", $label, $input, form_error($field));

echo "</fieldset>";

// Form actions
$submit = form_button([
    'type' => 'submit',
    'name' => 'action',
    'value' => 'update',
    'content' => 'Update booking',
]);
$cancel = anchor($return_uri, 'Cancel', ['up-dismiss' => '']);
echo "<div class='submit' style='border-top:0px;'>{$submit} &nbsp; {$cancel}</div>";

echo form_close();