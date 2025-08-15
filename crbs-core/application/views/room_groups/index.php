<?php
// Display flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 30px; font-size: 14px;'>{$flashdata}</div>";
}

// Iconbar with styled "Add Group" button
echo iconbar([
    ['room_groups/add', 'Add Group', 'add.png'],
], 'style="display: inline-block; padding: 12px 24px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: 600; transition: background-color 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"');

// Set table template with increased row spacing
$this->table->set_template([
    'table_open' => '<table
        style="width: 100%; border-collapse: separate; border-spacing: 0; margin: 30px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif; line-height: 1.5;"
        data-sortable="group_sort_form"
    >',
    'heading_row_start' => '<tr style="background-color: #f8f9fa; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">',
    'heading_cell_start' => '<th style="padding: 20px; border-bottom: 2px solid #e0e0e0; text-align: left;">',
    'row_start' => '<tr style="border-bottom: 1px solid #eee;">',
    'row_alt_start' => '<tr style="border-bottom: 1px solid #eee; background-color: #fafafa;">', // Added for alternating rows
    'cell_start' => '<td style="padding: 25px; font-size: 14px; color: #444;">', // Increased from 20px to 25px
]);

// Adjusted widths to sum to 100%: 5% + 30% + 10% + 45% + 10% = 100%
$this->table->set_heading([
    ['data' => '', 'style' => 'width: 5%; text-align: center;'],
    ['data' => 'Name', 'style' => 'width: 30%; text-align: left;'],
    ['data' => 'Rooms', 'style' => 'width: 10%; text-align: left;'],
    ['data' => 'Description', 'style' => 'width: 45%; text-align: left;'],
    ['data' => 'Actions', 'style' => 'width: 10%; text-align: center;'],
]);

foreach ($groups as $idx => $group) {
    $sort_img = img('assets/images/ui/arrow_ns.png', FALSE, "alt='sort' style='vertical-align: middle;'");
    $sort_btn = "<div role='button' class='handle' style='cursor: grab; display: inline-block;'>{$sort_img}</div>";
    $sort_input = form_hidden('groups[]', $group->room_group_id);
    $sort_html = $sort_input . $sort_btn;

    $name = html_escape($group->name);
    $name_html = anchor('room_groups/edit/' . $group->room_group_id, $name, 'style="color: #1A3C5E; text-decoration: none; font-weight: 600; transition: color 0.3s;"');

    $room_count = sprintf('%d', $group->room_count);
    $rooms_html = anchor('rooms?group=' . $group->room_group_id, $room_count, 'style="color: #1A3C5E; text-decoration: none; font-weight: 600; transition: color 0.3s;"');

    $description_html = (empty($group->description))
        ? '<span style="color: #888;">No description</span>'
        : word_limiter(html_escape($group->description), 8);

    $actions = [
        'edit' => 'room_groups/edit/' . $group->room_group_id,
        'delete' => 'room_groups/delete/' . $group->room_group_id,
    ];
    $actions_html = $this->load->view('partials/editdelete', $actions, TRUE);

    // Explicitly set alignment for each cell
    $this->table->add_row([
        ['data' => $sort_html, 'style' => 'text-align: center;'],
        ['data' => $name_html, 'style' => 'text-align: left;'],
        ['data' => $rooms_html, 'style' => 'text-align: left;'],
        ['data' => $description_html, 'style' => 'text-align: left;'],
        ['data' => $actions_html, 'style' => 'text-align: center;'],
    ]);
}

if (empty($groups)) {
    // Styled empty state
    echo "<div style='padding: 30px 0; font-size: 15px; color: #888; text-align: center; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin: 30px 0;'>No room groups added yet.</div>";
} else {
    $form_attrs = [
        'id' => 'group_sort_form',
        'up-target' => '.content_area',
        'up-submit' => '',
        'up-navigate' => 'false',
        'style' => 'margin: 0;',
    ];
    echo form_open('room_groups/save_pos', $form_attrs);

    // Message and table wrapper
    echo "<div style='position: relative; margin: 30px 0;'>";
    $msg = $message ?? '<br>';
    if (!empty($msg) && $msg !== '<br>') {
        echo "<div style='position: absolute; top: -48px; right: 0; font-size: 14px; color: #666;'>{$msg}</div>";
    }
    echo $this->table->generate();
    echo "</div>";

    echo form_close();
}
?>