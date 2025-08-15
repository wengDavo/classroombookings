<?php
// Display flashdata message with green success styling
$messages = $this->session->flashdata('saved');
if (!empty($messages)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$messages}</div>";
}

// Inject calendar CSS (unchanged)
$css = $calendar->get_css();
echo "<style type='text/css'>{$css}</style>";

// Date display with consistent typography
$dateFormat = setting('date_format_long', 'crbs');
$start = $session->date_start ? $session->date_start->format($dateFormat) : '';
$end = $session->date_end ? $session->date_end->format($dateFormat) : '';
echo "<div style='margin: 20px 0; font-size: 16px; color: #444; background-color: #ffffff; padding: 20px; border-radius: 8px;>";
echo "<p style='margin: 0 0 8px;'><strong style='font-weight: 600; color: #333;'>Start date:</strong> {$start}</p>";
echo "<p style='margin: 0;'><strong style='font-weight: 600; color: #333;'>End date:</strong> {$end}</p>";
echo "</div>";

// Conditional bulk apply week partial
if (!empty($weeks)) {
    $this->load->view('sessions/view_apply_week', [
        'weeks' => $weeks,
        'session' => $session,
    ]);
}

// Instruction text
echo "<div style='margin: 20px 0; font-size: 14px; color: #666;'><p style='margin: 0;'>Click on the dates in each calendar to toggle the Timetable Week for that week.</p></div>";

// Form with calendar and submit
echo form_open(current_url(), [
    'style' => 'max-width: 800px; margin: 20px auto; padding: 25px; background-color: #ffffff; border-radius: 8px;'
], ['session_id' => $session->session_id]);

echo "<div style='margin-bottom: 20px;'>";
echo $calendar->generate_full_session(['column_class' => 'b-50']);
echo "</div>";

$this->load->view('partials/submit', [
    'submit' => [
        'value' => 'Save',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;'
    ]
]);

echo form_close();
?>