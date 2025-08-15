<?php
// Flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

$field_id = NULL;
if (isset($field) && is_object($field)) {
    $field_id = set_value('field_id', $field->field_id);
}

echo "<!-- $field_id -->";

// Warning message for edit mode (replacing msgbox)
if (!empty($field_id)) {
    echo "<div style='background-color: #fff3e0; color: #e65100; padding: 12px; border: 1px solid #ffcc80; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>";
    echo "<strong style='font-weight: 600;'>Note:</strong> You cannot change the type of a field. Instead, delete the field and create a new one.";
    echo "</div>";
}

// Open the form with centered styling
echo form_open('rooms/save_field', array(
    'class' => 'cssform',
    'id' => 'fields_add',
    'style' => 'max-width: 500px; margin: 50px auto; padding: 25px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'
), array('field_id' => $field_id));
?>

<!-- Field Details Title -->
<div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Field Details</h2>
</div>

<!-- Form Fields -->
<div style="display: grid; gap: 20px;">
    <!-- Name -->
    <div style="display: grid; gap: 8px;">
        <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Name</label>
        <?php
        $input_name = 'name';
        $value = set_value($input_name, isset($field) ? $field->name : '', FALSE);
        echo form_input(array(
            'name' => $input_name,
            'id' => $input_name,
            'size' => '30',
            'maxlength' => '64',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter field name',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($input_name, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Type -->
    <?php if (!isset($field)): ?>
    <div style="display: grid; gap: 8px;">
        <label for="type" style="font-size: 12px; font-weight: 600; color: #444;">Type</label>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php
            $input_name = 'type';
            $value = set_value($input_name, isset($field) ? $field->type : '', FALSE);
            foreach ($options_list as $k => $v) {
                $id = "{$input_name}_{$k}";
                $input = form_radio(array(
                    'name' => $input_name,
                    'id' => $id,
                    'value' => $k,
                    'checked' => ($value == $k),
                    'tabindex' => tab_index(),
                    'up-switch' => '.dropdown_options',
                    'style' => 'margin: 0;'
                ));
                echo "<label for='{$id}' style='display: flex; align-items: center; gap: 8px; font-size: 14px; color: #444; cursor: pointer;'>{$input}{$v}</label>";
            }
            ?>
        </div>
        <?php echo form_error($input_name, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>
    <?php else: ?>
    <?php
    $input_name = 'type';
    $value = set_value($input_name, isset($field) ? $field->type : '');
    echo form_input(array(
        'type' => 'hidden',
        'name' => $input_name,
        'id' => $input_name,
        'value' => $value,
    ));
    ?>
    <?php endif; ?>

    <!-- Options (Dropdown) -->
    <?php
    $options_attrs = '';
    if (!isset($field)) {
        $options_attrs .= ' up-show-for="SELECT" ';
    } elseif (isset($field) && $field->type != 'SELECT') {
        $options_attrs .= ' style="display:none;"';
    }
    ?>
    <div class="dropdown_options" <?= $options_attrs ?> style="display: grid; gap: 8px;">
        <label for="options" style="font-size: 12px; font-weight: 600; color: #444;">Items</label>
        <?php
        $input_name = 'options';
        $options_str = '';
        if (isset($field) && is_array($field->options)) {
            $option_values = array();
            foreach ($field->options as $option) {
                $option_values[] = html_escape($option->value);
            }
            $options_str = implode("\n", $option_values);
        }
        $value = set_value($input_name, $options_str, FALSE);
        echo form_textarea(array(
            'name' => $input_name,
            'id' => $input_name,
            'rows' => '10',
            'cols' => '40',
            'tabindex' => tab_index(),
            'value' => $options_str,
            'placeholder' => "Enter options, one per line",
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; resize: vertical;'
        ));
        ?>
        <span style="font-size: 12px; color: #666;">Enter the selectable options for the dropdown list here; one on each line.</span>
        <?php echo form_error($input_name, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>
</div>

<?php
// Submit and cancel buttons with consistent styling
$this->load->view('partials/submit', array(
    'submit' => array(
        'value' => 'Save',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 20px; transition: background-color 0.3s;'
    ),
    'cancel' => array(
        'value' => 'Cancel',
        'tabindex' => tab_index(),
        'url' => 'rooms/fields',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>