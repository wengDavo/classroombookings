<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set department_group_id for edit mode
$department_group_id = NULL;
if (isset($department_group) && is_object($department_group)) {
    $department_group_id = set_value('department_group_id', $department_group->department_group_id);
}

// Open the form with centered styling
echo form_open('department_groups/save', array(
    'id' => 'department_group_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
), array('department_group_id' => $department_group_id));
?>

<!-- Form Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Department Class (Dropdown) -->
    <div style="display: grid; gap: 8px;">
        <label for="department_class_id" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Department Class</label>
        <?php
        $department_classes = $this->crud_model->Get('department_classes', 'department_class_id', NULL, NULL, 'department_class_id asc');
        $options = ['' => 'Select a department class'];
        if ($department_classes) {
            foreach ($department_classes as $dc) {
                $level_name = $this->db->get_where('levels', ['level_id' => $dc->level_id])->row('name') ?: $dc->level_id;
                $dept_name = $this->db->get_where('departments', ['department_id' => $dc->department_id])->row('name') ?: $dc->department_id;
                $options[$dc->department_class_id] = "$level_name - $dept_name";
            }
        }
        $field = 'department_class_id';
        $value = set_value($field, isset($department_group) ? $department_group->department_class_id : '');
        echo form_dropdown($field, $options, $value, array(
            'id' => $field,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Identifier -->
    <div style="display: grid; gap: 8px;">
        <label for="identifier" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Identifier</label>
        <?php
        $field = 'identifier';
        $value = set_value($field, isset($department_group) ? $department_group->identifier : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '5',
            'maxlength' => '1',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'e.g., A, B, C',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Size -->
    <div style="display: grid; gap: 8px;">
        <label for="size" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Size</label>
        <?php
        $field = 'size';
        $value = set_value($field, isset($department_group) ? $department_group->size : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'type' => 'number',
            'min' => '1',
            'max' => '1000',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter group size (e.g., 30)',
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
        'url' => 'department_groups',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>