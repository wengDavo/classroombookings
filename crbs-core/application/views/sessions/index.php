<?php
// Display flashdata message with green success styling
$messages = $this->session->flashdata('saved');
if (!empty($messages)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$messages}</div>";
}

// Iconbar with styled "Add Session" button
echo iconbar([
    ['sessions/add', 'Add Session', 'add.png'],
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');

// Sort columns array (passed to partial, not styled directly)
$sort_cols = ["Name", "Start date", "End date", "Current?", "Selectable?"];

// Current and future sessions section
// echo "<h3 style='font-size: 20px; font-weight: bold; color: #333; margin: 30px 0 15px;'>Current and Future Sessions</h3>";
$this->load->view('sessions/table', ['items' => $active, 'id' => 'sessions_active', 'sort_cols' => $sort_cols]);

// Past sessions section (if applicable)
if (!empty($past)) {
    echo "<div style='margin: 40px 0 15px;'><h3 style='font-size: 20px; font-weight: bold; color: #333; margin: 0;'>Past Sessions</h3></div>";
    $this->load->view('sessions/table', ['items' => $past, 'id' => 'sessions_past', 'sort_cols' => $sort_cols]);
}
?>