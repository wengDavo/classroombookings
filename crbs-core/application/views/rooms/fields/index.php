<?php
// Display flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Iconbar with styled "Add Field" button
echo iconbar([
    ['rooms/add_field', 'Add Field', 'add.png'],
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');

// Sort columns array (for sorting functionality)
$sort_cols = ["Name", "Type", "Options", "None"];
?>

<table
    style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;"
    id="jsst-roomfields"
    up-data='<?= json_encode($sort_cols) ?>'
>
    <col style="width: 30%;" /><col style="width: 20%;" /><col style="width: 40%;" /><col style="width: 10%;" />
    <thead>
        <tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Name">Name</th>
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Type">Type</th>
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Options">Options</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="X"></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    if ($fields) {
        foreach ($fields as $field) { ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 14px; font-size: 14px; color: #444;"><?php echo html_escape($field->name) ?></td>
                <td style="padding: 14px; font-size: 14px; color: #444;"><?php echo $options_list[$field->type] ?></td>
                <td style="padding: 14px; font-size: 14px; color: #444;"><?php
                    if (isset($field->options) && is_array($field->options)) {
                        $values = array();
                        foreach ($field->options as $option) {
                            $label = trim($option->value);
                            if (empty($label)) continue;
                            $values[] = html_escape($label);
                        }
                        echo implode(", ", $values);
                    }
                ?></td>
                <td style="padding: 14px; text-align: center; font-size: 14px; color: #444;">
                    <?php
                    $actions['edit'] = 'rooms/edit_field/' . $field->field_id;
                    $actions['delete'] = 'rooms/delete_field/' . $field->field_id;
                    $this->load->view('partials/editdelete', $actions);
                    ?>
                </td>
            </tr>
        <?php $i++;
        }
    } else {
        echo '<tr><td colspan="4" style="padding: 20px 0; font-size: 14px; color: #888; text-align: center; border-bottom: 1px solid #eee;">No room fields exist!</td></tr>';
    }
    ?>
    </tbody>
</table>