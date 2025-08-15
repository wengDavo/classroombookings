<?php

$display_user_setting = ($booking->repeat_id)
    ? setting('bookings_show_user_recurring')
    : setting('bookings_show_user_single');

// Template with department group and course code
$template = "
    {course_code}
    {department_name}
    {department_group_identifier}
    {course_name}
    {user}
    {actions}
";

$vars = [
    '{user}' => '',
    '{department_name}' => '',
    '{department_group_identifier}' => '',
    '{course_name}' => '',
    '{course_code}' => '', // Corrected from '{course}' to match template
    '{notes}' => '',       // Kept for consistency, though not in template
    '{actions}' => '',
];



$actions = [];

// User info
$user_is_admin = $this->userauth->is_level(ADMINISTRATOR);
$user_is_booking_owner = ($booking->user_id && $booking->user_id == $context->user->user_id);
$show_user = ($user_is_admin || $user_is_booking_owner || $display_user_setting);

// log_message('debug', '============================: ' . print_r($booking, true));

if ($show_user && !empty($booking->user)) {
    $user_label = !empty($booking->user->displayname)
        ? $booking->user->displayname
        : $booking->user->username;
    if (!empty($user_label)) {
        $vars['{user}'] = '<div class="booking-cell-user">' . html_escape($user_label) . '</div>';
    }
}

// Department Group
// if (!empty($booking->department_group->name)) {
//     $vars['{department_group}'] = '<div class="booking-cell-department-group">' . html_escape($booking->department_group->name) . '</div>';
// }

// Department Group Identifier
if (!empty($booking->department_group->identifier)) {
    $vars['{department_group_identifier}'] = '<div class="booking-cell-department-group"> Grp ' . html_escape($booking->department_group->identifier) . '</div>';
}

// Department
if (!empty($booking->department->name)) {
    $vars['{department_name}'] = '<div class="booking-cell-department-group">' . html_escape($booking->department->name) . '</div>';
}

// Course Code
if (!empty($booking->course->course_code)) {
    $vars['{course_code}'] = '<div class="booking-cell-notes">' . html_escape($booking->course->course_code) . '</div>';
}

// Course Name
if (!empty($booking->course->name)) {
    $vars['{course_name}'] = '<div class="booking-cell-notes">' . html_escape($booking->course->name) . '</div>';
}

// Notes 
if (!empty($booking->notes)) {
    $notes = html_escape($booking->notes);
    $tooltip = (strlen($notes) > 15) ? 'up-tooltip="' . $notes . '"' : '';
    $vars['{notes}'] = '<div class="booking-cell-notes" ' . $tooltip . '>' . character_limiter($notes, 15) . '</div>';
}

// Actions
if (!empty($actions)) {
    $vars['{actions}'] = ''; // Placeholder; update if actions are added later
}

// Process template
$body = strtr($template, $vars);
// Remove unused tags (not necessary here since all are defined in $template)
$body = str_replace(array_keys($vars), '', $body);

// URL params
$params = ['params' => http_build_query($context->get_query_params())];
$uri = sprintf('bookings/view/%d?%s', $booking->booking_id, http_build_query($params));
$url = site_url($uri);

?>



<td class='<?= $class ?>'>
    <a
        class="bookings-grid-button"
        href="<?= $url ?>"
        up-position="right"
        up-target=".bookings-view"
        up-layer="new drawer"
        up-history="false"
        up-preload
    >
        <?= $body ?>
    </a>
</td>