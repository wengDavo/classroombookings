<?php
// Display flashdata messages with consistent styling
$messages = $this->session->flashdata('saved');
echo !empty($messages) ? "<div class='messages' style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$messages}</div>" : '';

$attrs = [
    'class' => 'cssform',
    'id' => 'room_schedules_save',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
];
$form_open = form_open('room_schedules/save/' . $session->session_id, $attrs);
$form_close = form_close();
?>

<?= $form_open ?>

<fieldset style="border: none; padding: 0; margin-bottom: 20px;">
    <!-- <legend accesskey="S" tabindex="<?php echo tab_index() ?>" style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Schedules</legend> -->

    <div style="display: grid; gap: 20px;">
        <?php
        $schedule_options = ['' => ''];
        foreach ($schedules as $schedule) {
            $schedule_options[$schedule->schedule_id] = html_escape($schedule->name);
        }

        foreach ($room_groups as $group) {
            $field_name = sprintf('group_schedule[%d][room_group_id]', $group->room_group_id);
            $hidden = form_hidden($field_name, $group->room_group_id);

            $field_id = sprintf('group_%d_schedule', $group->room_group_id);
            $field_name = sprintf('group_schedule[%d][schedule_id]', $group->room_group_id);

            $data_key = sprintf('session_%d_group_%d', $session->session_id, $group->room_group_id);
            $data_val = isset($session_schedules[$data_key]) ? $session_schedules[$data_key] : '';
            $value = set_value($field_name, $data_val, FALSE);
            $input = form_dropdown([
                'name' => $field_name,
                'id' => $field_id,
                'options' => $schedule_options,
                'selected' => $value,
                'tabindex' => tab_index(),
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #ffffff;'
            ]);

            $label = form_label(html_escape($group->name), $field_id, ['style' => 'font-size: 12px; font-weight: 600; color: #444;']);

            echo "<div style='display: grid; gap: 8px;'>{$label}{$hidden}{$input}";
            echo form_error($field_name, '<div style="color: #cc0000; font-size: 12px;">', '</div>');
            echo "</div>";
        }
        ?>
    </div>
</fieldset>

<?php
$this->load->view('partials/submit', [
    'submit' => [
        'value' => 'Save',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;'
    ],
    // Uncomment if cancel is needed
    'cancel' => [
        'value' => 'Cancel',
        'tabindex' => tab_index(),
        'url' => 'rooms',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer;text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    ]
]);

echo $form_close;
?>