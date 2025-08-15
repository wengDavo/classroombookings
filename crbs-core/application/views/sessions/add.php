<?php
// Display flashdata message with green success styling
$messages = $this->session->flashdata('saved');
if (!empty($messages)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$messages}</div>";
}

// Set session_id for edit mode
$session_id = NULL;
if (isset($session) && is_object($session)) {
    $session_id = set_value('session_id', $session->session_id);
}

// Open the form with centered styling
echo form_open(current_url(), [
    'id' => 'session_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
], ['session_id' => $session_id]);
?>

<!-- Session Title -->
<!-- <div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Session Details</h2>
</div> -->

<!-- Form fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name Field -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $field = 'name';
        $value = set_value($field, isset($session) ? $session->name : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '25',
            'maxlength' => '50',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter session name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Start Date Field -->
    <div style="display: grid; gap: 8px;">
        <label for="date_start" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Start date</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <?php
            $field = 'date_start';
            $value = set_value($field, isset($session) ? $session->date_start->format('d/m/Y') : '', FALSE);
            echo form_input(array(
                'name' => $field,
                'id' => $field,
                'size' => '10',
                'maxlength' => '10',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'dd/mm/yyyy',
                'style' => 'width: 120px; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <img style="cursor: pointer; vertical-align: middle;" src="<?= base_url('assets/images/ui/cal_day.png') ?>" width="16" height="16" title="Choose date" onclick="displayDatePicker('date_start', false);" />
        </div>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- End Date Field -->
    <div style="display: grid; gap: 8px;">
        <label for="date_end" class="required" style="font-size: 12px; font-weight: 600; color: #444;">End date</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <?php
            $field = 'date_end';
            $value = set_value($field, isset($session) ? $session->date_end->format('d/m/Y') : '', FALSE);
            echo form_input(array(
                'name' => $field,
                'id' => $field,
                'size' => '10',
                'maxlength' => '10',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'dd/mm/yyyy',
                'style' => 'width: 120px; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <img style="cursor: pointer; vertical-align: middle;" src="<?= base_url('assets/images/ui/cal_day.png') ?>" width="16" height="16" title="Choose date" onclick="displayDatePicker('date_end', false);" />
        </div>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Is Selectable Field -->
    <div style="display: grid; gap: 8px;">
        <label for="is_selectable" style="font-size: 12px; font-weight: 600; color: #444;">Available</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <?php
            $field = 'is_selectable';
            $value = isset($session) ? $session->is_selectable : '0';
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
            <span style="font-size: 14px; color: #666;">Allow users to view and make bookings in this session</span>
        </div>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Default Schedule Field -->
    <div style="display: grid; gap: 8px;">
        <label for="default_schedule_id" style="font-size: 12px; font-weight: 600; color: #444;">Default schedule</label>
        <?php
        $schedule_options = ['' => ''];
        foreach ($schedules as $schedule) {
            $schedule_options[$schedule->schedule_id] = html_escape($schedule->name);
        }
        $field = 'default_schedule_id';
        $value = set_value($field, isset($session) ? $session->default_schedule_id : '', FALSE);
        echo form_dropdown([
            'name' => $field,
            'id' => $field,
            'options' => $schedule_options,
            'selected' => $value,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #fff;'
        ]);
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
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
    'cancel' => isset($session) ? null : array(
        'value' => 'Cancel',
        'tabindex' => tab_index(),
        'url' => 'sessions',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>