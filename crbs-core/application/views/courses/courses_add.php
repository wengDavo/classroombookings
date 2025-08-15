<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set course_id for edit mode
$course_id = NULL;
if (isset($course) && is_object($course)) {
    $course_id = set_value('course_id', $course->course_id);
}

// Open the form with centered styling
echo form_open('courses/save', array(
    'id' => 'course_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
), array('course_id' => $course_id));
?>

<!-- Form Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $field = 'name';
        $value = set_value($field, isset($course) ? $course->name : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '20',
            'maxlength' => '50',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter course name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Course Code -->
    <div style="display: grid; gap: 8px;">
        <label for="course_code" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Course Code</label>
        <?php
        $field = 'course_code';
        $value = set_value($field, isset($course) ? $course->course_code : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '20',
            'maxlength' => '20',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter course code (e.g., CS101)',
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
        $value = set_value($field, isset($course) ? $course->department_id : '');
        echo form_dropdown($field, $options, $value, array(
            'id' => $field,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Level (Dropdown) -->
    <div style="display: grid; gap: 8px;">
        <label for="level_id" style="font-size: 12px; font-weight: 600; color: #444;">Level</label>
        <?php
        $levels = $this->crud_model->Get('levels', 'level_id', NULL, NULL, 'name asc');
        $options = ['' => 'Select a level'];
        if ($levels) {
            foreach ($levels as $level) {
                $options[$level->level_id] = $level->name;
            }
        }
        $field = 'level_id';
        $value = set_value($field, isset($course) ? $course->level_id : '');
        echo form_dropdown($field, $options, $value, array(
            'id' => $field,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Credits -->
    <div style="display: grid; gap: 8px;">
        <label for="credits" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Credits</label>
        <?php
        $field = 'credits';
        $value = set_value($field, isset($course) ? $course->credits : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'type' => 'number',
            'min' => '1',
            'max' => '10',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter credits (1-10)',
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
        'url' => 'courses',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>