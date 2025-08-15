<?php
$form_id = (isset($group))
    ? sprintf('room_sort_form_%d', $group->room_group_id)
    : 'room_sort_form_ungrouped';

$room_count = count($rooms);

$table_attrs = ($room_count > 1)
    ? "data-sortable='{$form_id}'"
    : '';

// Set table template with inline styling
$this->table->set_template([
    'table_open' => '<table
        style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif; line-height: 1.3;"
        ' . $table_attrs . '
    >',
    'heading_row_start' => '<tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">',
    'heading_cell_start' => '<th style="padding: 14px; border-bottom: 2px solid #ddd;">',
    'row_start' => '<tr style="border-bottom: 1px solid #eee;">',
    'cell_start' => '<td style="padding: 14px; font-size: 14px; color: #444;">',
]);

$this->table->set_heading([
    ['data' => '', 'style' => 'width: 5%; text-align: center;'],
    ['data' => 'Name', 'style' => 'width: 25%; text-align: left;'],
    ['data' => 'Location', 'style' => 'width: 25%; text-align: left;'],
    ['data' => 'Owner', 'style' => 'width: 25%; text-align: left;'],
    ['data' => 'Photo', 'style' => 'width: 10%; text-align: center;'],
    ['data' => 'Actions', 'style' => 'width: 10%; text-align: center;'],
]);

foreach ($rooms as $room) {
    $sort_img = img('assets/images/ui/arrow_ns.png', FALSE, "alt='sort' style='vertical-align: middle;'");
    $sort_btn = "<div role='button' class='handle' style='cursor: grab; display: inline-block;'>{$sort_img}</div>";
    $sort_input = form_hidden('rooms[]', $room->room_id);
    $sort_html = $sort_input . $sort_btn;

    $name = html_escape($room->name);
    $name_html = anchor('rooms/edit/' . $room->room_id, $name, 'style="color: #1A3C5E; text-decoration: none; font-weight: 600;"');
    $location_html = html_escape($room->location);

    $owner_html = '';
    if (!empty($room->user_id)) {
        $owner = empty($room->owner->displayname)
            ? $room->owner->username
            : $room->owner->displayname;
        $owner_html = html_escape($owner);
    }

    $photo_html = '';
    if (!empty($room->photo) && $image_url = image_url($room->photo)) {
        $url = site_url("rooms/photo/{$room->room_id}");
        $icon_src = base_url('assets/images/ui/picture.png');
        $icon_el = "<img src='{$icon_src}' width='16' height='16' alt='View Photo' style='vertical-align: middle;'>";
        $photo_html = "<a href='{$url}' up-history='false' up-layer='new drawer' up-target='.room-photo' title='View Photo' style='color: #1A3C5E; text-decoration: none;'>{$icon_el}</a>";
    }

    $actions = [
        'edit' => 'rooms/edit/' . $room->room_id,
        'delete' => 'rooms/delete/' . $room->room_id,
    ];
    $actions_html = $this->load->view('partials/editdelete', $actions, TRUE);

    $this->table->add_row([
        ($room_count > 1) ? ['data' => $sort_html, 'style' => 'text-align: center;'] : null,
        $name_html,
        $location_html,
        $owner_html,
        $photo_html,
        $actions_html,
    ]);
}

if (empty($rooms)) {
    // Styled empty state (replacing msgbox)
    echo "<div style='padding: 20px; font-size: 14px; color: #888; text-align: center; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin: 20px 0;'>No rooms in this group.</div>";
} else {
    $form_attrs = [
        'id' => $form_id,
        'up-target' => "#{$form_id}",
        'up-submit' => '',
        'up-navigate' => 'false',
        'style' => 'margin: 0;',
    ];
    echo form_open('rooms/save_pos', $form_attrs, [
        'group' => (isset($group)) ? $group->room_group_id : null,
    ]);

    echo $this->table->generate();

    echo form_close();
}
?>