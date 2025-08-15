<?php
// Display flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Iconbar with styled "Add Room" button
echo iconbar([
    ['rooms/add', 'Add Room', 'add.png'],
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');

// Sort message with updated styling
echo "<div style='position: relative; margin: 20px 0;' id='sort_msg' up-hungry>";
$msg = $message ?? '<br>';
if (!empty($msg) && $msg !== '<br>') {
    echo "<div style='position: absolute; top: -48px; right: 0; font-size: 14px; color: #666;'>{$msg}</div>";
}
echo "</div>";

// Group and room rendering
$open_group_id = $open_group_id ?? null;
$items = []; // Not used in rendering here, but kept for your logic
foreach ($groups as $group) {
    $items[] = [
        'url' => site_url('room_groups/edit/' . $group->room_group_id),
        'title' => sprintf('%s <span>(%d)</span>', $group->name, $group->room_count),
        'active' => $group->room_group_id == $open_group_id,
    ];
}

foreach ($groups as $group) {
    $this->load->view('rooms/index_groups/group', [
        'group' => $group,
        'rooms' => $rooms[$group->room_group_id] ?? [],
    ]);
}

// Uncomment and style if needed
// $this->load->view('rooms/index_groups/group', [
//     'group' => null,
//     'rooms' => $rooms['ungrouped'] ?? [],
// ]);
?>