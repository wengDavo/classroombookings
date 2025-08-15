<?php
// Flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set room_group_id for edit mode
$room_group_id = NULL;
if (isset($group) && is_object($group)) {
    $room_group_id = set_value('room_group_id', $group->room_group_id);
}

// Open the form with centered styling
echo form_open(current_url(), [
    'id' => 'room_group_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
], ['room_group_id' => $room_group_id]);
?>

<!-- Room Group Title -->
<!-- <div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Room Group</h2>
</div> -->

<!-- Room Group Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $field = 'name';
        $value = set_value($field, isset($group) ? $group->name : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '25',
            'maxlength' => '32',
            'tabindex' => tab_index(),
            'value' => $value,
            'autofocus' => true,
            'placeholder' => 'Enter group name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error('name', '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Description -->
    <div style="display: grid; gap: 8px;">
        <label for="description" style="font-size: 12px; font-weight: 600; color: #444;">Description</label>
        <?php
        $field = 'description';
        $value = set_value($field, isset($group) ? $group->description : '', FALSE);
        echo form_textarea(array(
            'name' => $field,
            'id' => $field,
            'rows' => '5',
            'cols' => '30',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter group description',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; resize: vertical;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>
</div>

<!-- Rooms Section -->
<div style="margin: 30px 0 0;">
    <h2 style="font-size: 18px; font-weight: bold; color: #333; margin: 0 0 20px;">Rooms</h2>
    <div style="font-size: 14px; color: #666; margin-bottom: 20px;">Choose which rooms belong in this group.</div>

    <?php
    $field = 'room_ids';
    ?>
    <div style="display: grid; gap: 20px;">
        <?php
        foreach ($rooms as $_group => $_rooms) {
            if (isset($groups[$_group])) {
                $heading = html_escape($groups[$_group]->name);
            } else {
                $heading = 'Ungrouped';
            }
            ?>
            <div style="display: grid; gap: 8px;">
                <label style="font-size: 12px; font-weight: 600; color: #444;"><?php echo $heading; ?></label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php
                    foreach ($_rooms as $_room) {
                        $id = "{$field}_{$_room->room_id}";
                        $title = html_escape($_room->name);
                        $value = 0;
                        if (!empty($room_group_id) && $room_group_id == $_room->room_group_id) {
                            $value = 1;
                        }
                        $value = set_value("{$field}[{$_room->room_id}]", $value, FALSE);
                        echo form_hidden("{$field}[{$_room->room_id}]", '0');
                        $input = form_checkbox(array(
                            'name' => "{$field}[{$_room->room_id}]",
                            'id' => $id,
                            'value' => '1',
                            'tabindex' => tab_index(),
                            'checked' => ($value == '1'),
                            'style' => 'margin: 0;'
                        ));
                        echo "<label for='{$id}' style='display: flex; align-items: center; gap: 8px; font-size: 14px; color: #444; cursor: pointer;'>{$input}{$title}</label>";
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php
// Submit and cancel buttons with consistent styling
$this->load->view('partials/submit', array(
    'submit' => array(
        'value' => 'Save',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: background-color 0.3s;'
    ),
    'cancel' => array(
        'value' => 'Cancel',
        'tabindex' => tab_index(),
        'url' => 'room_groups',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>