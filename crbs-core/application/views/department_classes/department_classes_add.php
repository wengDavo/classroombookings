<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set department_class_id for edit mode
$department_class_id = NULL;
if (isset($department_class) && is_object($department_class)) {
    $department_class_id = set_value('department_class_id', $department_class->department_class_id);
}

// Open the form with centered styling
echo form_open('department_classes/save', array(
    'id' => 'department_class_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
), array('department_class_id' => $department_class_id));
?>

<!-- Form Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Level (Dropdown) -->
    <div style="display: grid; gap: 8px;">
        <label for="level_id" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Level</label>
        <?php
        $levels = $this->crud_model->Get('levels', 'level_id', NULL, NULL, 'name asc');
        $options = ['' => 'Select a level'];
        if ($levels) {
            foreach ($levels as $level) {
                $options[$level->level_id] = $level->name;
            }
        }
        $field = 'level_id';
        $value = set_value($field, isset($department_class) ? $department_class->level_id : '');
        echo form_dropdown($field, $options, $value, array(
            'id' => $field,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Department (Dropdown) -->
    <div style="display: grid; gap: 8px;">
        <label for="department_id" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Department</label>
        <?php
        $departments = $this->crud_model->Get('departments', 'department_id', NULL, NULL, 'name asc');
        $options = ['' => 'Select a department'];
        if ($departments) {
            foreach ($departments as $dept) {
                $options[$dept->department_id] = $dept->name;
            }
        }
        $field = 'department_id';
        $value = set_value($field, isset($department_class) ? $department_class->department_id : '');
        echo form_dropdown($field, $options, $value, array(
            'id' => $field,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
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
        'url' => 'department_classes',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>