<?php
// Display flashdata message with green success styling
$messages = $this->session->flashdata('saved');
if (!empty($messages)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$messages}</div>";
}

// Iconbar with styled "Add Week" button
echo iconbar([
    array('weeks/add', 'Add Week', 'add.png'),
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');

// Sort columns array (unused in styling but kept for functionality)
$sort_cols = ["Name", "Colour", "None"];
?>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;">
    <col style="width: 5%;" /><col style="width: 85%;" /><col style="width: 10%;" />

    <thead>
        <tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="Colour"></th>
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Name">Name</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="X"> </th>
        </tr>
    </thead>

    <?php if (empty($weeks)): ?>

    <tbody>
        <tr>
            <td colspan="3" style="padding: 20px 0; font-size: 14px; color: #888; text-align: center; border-bottom: 1px solid #eee;">No weeks.</td>
        </tr>
    </tbody>

    <?php else: ?>

    <tbody>
        <?php
        foreach ($weeks as $week) {
            echo "<tr style='border-bottom: 1px solid #eee;'>";

            $dot = week_dot($week);
            echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>{$dot}</td>";

            $name = html_escape($week->name);
            echo "<td style='padding: 14px; font-size: 14px; color: #1A3C5E; font-weight: 600;'>{$name}</td>";

            echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>";
            $actions['edit'] = 'weeks/edit/' . $week->week_id;
            $actions['delete'] = 'weeks/delete/' . $week->week_id;
            $this->load->view('partials/editdelete', $actions);
            echo "</td>";

            echo "</tr>";
        }
        ?>
    </tbody>

    <?php endif; ?>
</table>