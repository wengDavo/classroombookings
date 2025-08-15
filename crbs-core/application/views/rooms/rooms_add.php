<?php
// Flashdata message with green success styling (not in original, added for consistency)
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set room_id for edit mode
$room_id = NULL;
if (isset($room) && is_object($room)) {
    $room_id = set_value('room_id', $room->room_id);
}

// Open the form with centered styling
echo form_open_multipart('rooms/save', array(
    'id' => 'rooms_add',
    'style' => 'bacgkground-color: #ffffff; border-radius: 8px;' // Note: Typo 'bacgkground' should be 'background'
), array('room_id' => $room_id));
?>

<!-- Room Details Title -->
<!-- <div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Room Details</h2>
</div> -->

<!-- Room Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $field = 'name';
        $value = set_value($field, isset($room) ? $room->name : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '20',
            'maxlength' => '20',
            'tabindex' => tab_index(),
            'value' => $value,
            'autofocus' => true,
            'placeholder' => 'Enter room name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Group -->
    <div style="display: grid; gap: 8px;">
        <label for="room_group_id" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Group</label>
        <?php
        $group_options = ['' => ''];
        foreach ($groups as $group) {
            $group_options[$group->room_group_id] = html_escape($group->name);
        }
        $field = 'room_group_id';
        $value = set_value($field, isset($room) ? $room->room_group_id : $group_id, FALSE);
        echo form_dropdown([
            'name' => $field,
            'id' => $field,
            'options' => $group_options,
            'selected' => $value,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #fff;'
        ]);
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Location -->
    <div style="display: grid; gap: 8px;">
        <label for="location" style="font-size: 12px; font-weight: 600; color: #444;">Location</label>
        <?php
        $field = 'location';
        $value = set_value($field, isset($room) ? $room->location : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '30',
            'maxlength' => '40',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter room location',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Teacher -->
    <div style="display: grid; gap: 8px;">
        <label for="user_id" style="font-size: 12px; font-weight: 600; color: #444;">Teacher</label>
        <?php
        $userlist = array('' => '(None)');
        foreach ($users as $user) {
            $label = empty($user->displayname) ? $user->username : $user->displayname;
            $userlist[$user->user_id] = html_escape($label);
        }
        $field = 'user_id';
        $value = set_value($field, isset($room) ? $room->user_id : '', FALSE);
        echo form_dropdown($field, $userlist, $value, 'tabindex="' . tab_index() . '" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #fff;"');
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Notes -->
    <div style="display: grid; gap: 8px;">
        <label for="notes" style="font-size: 12px; font-weight: 600; color: #444;">Notes</label>
        <?php
        $field = 'notes';
        $value = set_value($field, isset($room) ? $room->notes : '', FALSE);
        echo form_textarea(array(
            'name' => $field,
            'id' => $field,
            'rows' => '5',
            'cols' => '30',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter notes about the room',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; resize: vertical;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Bookable -->
    <div style="display: grid; gap: 8px;">
        <label for="bookable" style="font-size: 12px; font-weight: 600; color: #444;">Can be booked</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <?php
            $field = 'bookable';
            $value = isset($room) ? $room->bookable : '1';
            $checked = set_checkbox($field, '1', $value == '1');
            echo form_hidden($field, '0');
            echo form_checkbox(array(
                'name' => $field,
                'id' => $field,
                'value' => '1',
                'tabindex' => tab_index(),
                'checked' => $checked,
                'style' => 'margin: 0;'
            ));
            ?>
            <span style="font-size: 14px; color: #666;">Tick this box to allow bookings to be made in this room</span>
        </div>
    </div>

    <!-- Capacity (New Field) -->
    <div style="display: grid; gap: 8px;">
        <label for="capacity" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Capacity</label>
        <?php
        $field = 'capacity';
        $value = set_value($field, isset($room) ? $room->capacity : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'type' => 'number',
            'min' => '0',
            'max' => '1000', // Adjust max as needed
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter room capacity (e.g., 30)',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>
</div>

<!-- Photo Section (Commented Out, Styled for Reference) -->
<?php /* ?>
<div style="margin: 30px 0 0;">
    <h2 style="font-size: 18px; font-weight: bold; color: #333; margin: 0 0 20px;">Photo</h2>
    <div style="font-size: 14px; color: #666; margin-bottom: 20px;">Add a photo of the room which users will be able to view.</div>

    <!-- Current Photo -->
    <div style="display: grid; gap: 8px; margin-bottom: 20px;">
        <label style="font-size: 12px; font-weight: 600; color: #444;">Current photo</label>
        <div style="font-size: 14px; color: #444;">
            <?php
            if (isset($room) && isset($room->photo) && !empty($room->photo)) {
                $image_url = image_url($room->photo);
                if ($image_url) {
                    $img = img($image_url, false, [
                        'width' => '200',
                        'style' => 'width: 200px; height: auto; max-width: 200px; padding: 1px; border: 1px solid #ccc; border-radius: 5px;'
                    ]);
                    echo anchor($image_url, $img, ['target' => '_blank', 'style' => 'text-decoration: none;']);
                } else {
                    echo '<em style="color: #888;">None</em>';
                }
            } else {
                echo '<em style="color: #888;">None</em>';
            }
            ?>
        </div>
    </div>

    <!-- File Upload -->
    <div style="display: grid; gap: 8px;">
        <label for="userfile" style="font-size: 12px; font-weight: 600; color: #444;">File upload</label>
        <?php
        echo form_upload(array(
            'name' => 'userfile',
            'id' => 'userfile',
            'size' => '30',
            'maxlength' => '255',
            'tabindex' => tab_index(),
            'value' => '',
            'style' => 'width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;'
        ));
        ?>
        <div style="font-size: 12px; color: #666; margin-top: 8px;">
            <p style="margin: 0;">Maximum filesize <span style="font-weight: 600;"><?php echo $max_size_human ?></span>.</p>
            <p style="margin: 0;">Uploading a new photo will <span style="font-weight: 600;">replace</span> the current one.</p>
        </div>
        <?php
        if ($this->session->flashdata('image_error') != '') {
            $err = $this->session->flashdata('image_error');
            echo "<p style='color: #cc0000; font-size: 12px; margin: 8px 0 0;'><span>{$err}</span></p>";
        }
        ?>
    </div>

    <?php if (isset($room) && !empty($room->photo)): ?>
    <!-- Delete Photo -->
    <div style="display: grid; gap: 8px; margin-top: 20px;">
        <label for="photo_delete" style="font-size: 12px; font-weight: 600; color: #444;">Delete photo?</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <?php
            $field = 'photo_delete';
            echo form_hidden($field, '0');
            echo form_checkbox(array(
                'name' => $field,
                'id' => $field,
                'value' => '1',
                'tabindex' => tab_index(),
                'checked' => FALSE,
                'style' => 'margin: 0;'
            ));
            ?>
            <span style="font-size: 14px; color: #666;">Tick this box to <span style="font-weight: 600;">remove the current photo</span> without adding a new one.</span>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php */ ?>

<!-- Fields Section -->
<?php if (isset($fields) && is_array($fields)): ?>
<div style="margin: 30px 0 0;">
    <h2 style="font-size: 18px; font-weight: bold; color: #333; margin: 0 0 20px;">Fields</h2>
    <div style="display: grid; gap: 20px;">
        <?php
        foreach ($fields as $field) {
            echo '<div style="display: grid; gap: 8px;">';
            echo '<label style="font-size: 12px; font-weight: 600; color: #444;">' . html_escape($field->name) . '</label>';

            switch ($field->type) {
                case Rooms_model::FIELD_TEXT:
                    $input = "f{$field->field_id}";
                    $value = set_value($input, element($field->field_id, $fieldvalues), FALSE);
                    echo form_input(array(
                        'name' => $input,
                        'id' => $input,
                        'size' => '30',
                        'maxlength' => '255',
                        'tabindex' => tab_index(),
                        'value' => $value,
                        'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
                    ));
                    break;

                case Rooms_model::FIELD_SELECT:
                    $input = "f{$field->field_id}";
                    $value = set_value($input, element($field->field_id, $fieldvalues), FALSE);
                    $options = $field->options;
                    $opts = array();
                    foreach ($options as $option) {
                        $opts[$option->option_id] = html_escape($option->value);
                    }
                    echo form_dropdown($input, $opts, $value, 'tabindex="' . tab_index() . '" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #fff;"');
                    break;

                case Rooms_model::FIELD_CHECKBOX:
                    $input = "f{$field->field_id}";
                    $checked = set_checkbox($input, '1', element($field->field_id, $fieldvalues) == '1');
                    echo form_hidden($input, '0');
                    echo form_checkbox(array(
                        'name' => $input,
                        'id' => $input,
                        'value' => '1',
                        'tabindex' => tab_index(),
                        'checked' => $checked,
                        'style' => 'margin: 0;'
                    ));
                    break;
            }
            echo '</div>';
        }
        ?>
    </div>
</div>
<?php endif; ?>

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
        'url' => 'rooms',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>