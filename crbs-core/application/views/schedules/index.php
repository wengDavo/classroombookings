<?php
// Display flashdata message with styled box (consistent with previous forms)
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>" . $flashdata . "</div>";
}

// Iconbar with styled button
echo iconbar([
    ['schedules/add', 'Add Schedule', 'add.png'],
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');

// Custom table template with modern styling
$this->table->set_template([
    'table_open' => '<table 
        style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;"
    >',
    'heading_row_start' => '<tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">',
    'heading_cell_start' => '<th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;">',
    'row_start' => '<tr style="border-bottom: 1px solid #eee;">',
    'cell_start' => '<td style="padding: 14px; font-size: 14px; color: #444;">',
]);

// Table headings with adjusted widths
$this->table->set_heading([
    ['data' => 'Name', 'width' => '25%'],
    ['data' => 'Description', 'width' => '55%'],
    ['data' => 'Actions', 'width' => '20%'],
]);

// Populate table rows
foreach ($schedules as $idx => $schedule) {
    $name = html_escape($schedule->name);
    $name_html = anchor('schedules/edit/' . $schedule->schedule_id, $name, 'style="color: #1A3C5E; text-decoration: none; font-weight: 600;"');

    $description_html = (empty($schedule->description))
        ? '<span style="color: #888; font-style: italic;">No description</span>'
        : word_limiter(html_escape($schedule->description), 8);

    $actions = [
        'edit' => 'schedules/edit/' . $schedule->schedule_id,
        'delete' => 'schedules/delete/' . $schedule->schedule_id,
    ];
    $actions_html = $this->load->view('partials/editdelete', $actions, TRUE);

    $this->table->add_row([
        $name_html,
        $description_html,
        $actions_html,
    ]);
}

// Handle empty schedules case
if (empty($schedules)) {
    echo "<div style='background-color: #fff3e6; color: #e68a00; padding: 12px; border: 1px solid #ffcc80; border-radius: 5px; font-size: 14px; text-align: center; margin: 20px 0;'>No schedules created yet.</div>";
} else {
    echo $this->table->generate();
}
?>