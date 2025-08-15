<?php
// Display flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set schedule_id for edit mode
$schedule_id = NULL;
if (isset($schedule) && is_object($schedule)) {
    $schedule_id = set_value('schedule_id', $schedule->schedule_id);
}

// Open the form with centered styling
echo form_open(current_url(), [
    'id' => 'schedule_edit',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
], ['schedule_id' => $schedule_id]);
?>

<!-- Schedule Details Title -->
<!-- <div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Schedule Details</h2>
</div> -->

<!-- Schedule Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name Field -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $field = 'name';
        $value = set_value($field, isset($schedule) ? $schedule->name : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '25',
            'maxlength' => '32',
            'tabindex' => tab_index(),
            'value' => $value,
            'autofocus' => true,
            'placeholder' => 'Enter schedule name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Description Field -->
    <div style="display: grid; gap: 8px;">
        <label for="description" style="font-size: 12px; font-weight: 600; color: #444;">Description</label>
        <?php
        $field = 'description';
        $value = set_value($field, isset($schedule) ? $schedule->description : '', FALSE);
        echo form_textarea(array(
            'name' => $field,
            'id' => $field,
            'rows' => '5',
            'cols' => '30',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter schedule description',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; resize: vertical;'
        ));
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
    'cancel' => array(
        'value' => 'Cancel',
        'tabindex' => tab_index(),
        'url' => 'schedules',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>

<?php if (isset($schedule) && $schedule->type == 'periods'): ?>
<!-- Periods Section -->
<div style="background-color: #ffffff; border-radius: 8px; margin-top: 50px;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 18px; font-weight: bold; color: #333; margin: 0;">Periods</h2>
    </div>
    <div
        id="period_list"
        hx-get="<?= site_url('periods/index/' . $schedule->schedule_id) ?>"
        hx-trigger="load"
        style="font-size: 14px; color: #888;"
    >
        <p>Loading...</p>
    </div>
</div>
<?php endif; ?>