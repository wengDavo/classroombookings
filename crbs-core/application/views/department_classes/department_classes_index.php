<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Display flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Iconbar with styled "Add Department Class" button
$iconbar = iconbar([
    ['department_classes/add', 'Add Department Class', 'add.png'],
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');
echo $iconbar;

// Sort columns array (for sorting functionality)
$sort_cols = ["Level", "Department", "None"];
?>

<table
    style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;"
    id="jsst-department-classes"
    up-data='<?= json_encode($sort_cols) ?>'
>
    <col style="width: 30%;" /><col style="width: 50%;" /><col style="width: 20%;" />
    <thead>
        <tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Level">Level</th>
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Department">Department</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="X"></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    if ($department_classes) {
        foreach ($department_classes as $dc) { ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 14px; font-size: 14px; color: #444;"><?php echo html_escape($dc->level_id) ?></td>
                <td style="padding: 14px; font-size: 14px; color: #444;">
                    <?php 
                    // Fetch department name via department_id
                    $department_name = $this->db->get_where('departments', ['department_id' => $dc->department_id])->row('name');
                    echo html_escape($department_name ?: 'N/A');
                    ?>
                </td>
                <td style="padding: 14px; text-align: center; font-size: 14px; color: #444;">
                    <?php
                    $actions['edit'] = 'department_classes/edit/' . $dc->department_class_id;
                    $actions['delete'] = 'department_classes/delete/' . $dc->department_class_id;
                    $this->load->view('partials/editdelete', $actions);
                    ?>
                </td>
            </tr>
        <?php $i++;
        }
    } else {
        echo '<tr><td colspan="3" style="padding: 20px 0; font-size: 14px; color: #888; text-align: center; border-bottom: 1px solid #eee;">No department classes exist!</td></tr>';
    }
    ?>
    </tbody>
</table>

<?php
// Pagination links with consistent styling
echo "<div style='margin-top: 20px; text-align: center;'>";
echo str_replace(
    ['<a ', '<span'],
    ['<a style="padding: 8px 12px; margin: 0 4px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; transition: background-color 0.3s;" ', '<span style="padding: 8px 12px; margin: 0 4px; background-color: #e0e0e0; color: #666; border-radius: 5px; font-size: 14px;"'],
    $pagelinks
);
echo "</div>";
?>