<?php
// Set week_id for edit mode
$week_id = NULL;
if (isset($week) && is_object($week)) {
    $week_id = set_value('week_id', $week->week_id);
}

// Flashdata message
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Open the form
echo form_open(current_url(), [
    'id' => 'week_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
], ['week_id' => $week_id]);
?>

<!-- Form fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name field -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $field = 'name';
        $value = set_value($field, isset($week) ? $week->name : '', FALSE);
        echo form_input([
            'name' => $field,
            'id' => $field,
            'size' => '20',
            'maxlength' => '20',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter week name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ]);
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Colour field (Updated) -->
    <div style="display: grid; gap: 8px;">
        <label for="bgcol" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Colour</label>
        <?php
        $field = 'bgcol';
        $value = set_value($field, isset($week) ? $week->bgcol : '', FALSE);
        echo form_input([
            'type' => 'color',
            'name' => $field,
            'id' => $field,
            'value' => $value,
            'tabindex' => tab_index(),
            'style' => 'width: 100px; height: 40px; border: 1px solid #ccc; border-radius: 

5px; cursor: pointer;'
        ]);
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>
</div>

<?php
// Submit and cancel buttons
$this->load->view('partials/submit', array(
    'submit' => array(
        'value' => 'Save',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: background-color 0.3s;'
    ),
    'cancel' => array(
        'value' => 'Cancel',
        'tabindex' => tab_index(),
        'url' => 'weeks',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>