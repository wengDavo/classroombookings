<?php
// Open the form with centered styling
$attrs = [
    // 'class' => 'cssform-stacked',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
];
echo form_open('sessions/apply_week', $attrs, ['session_id' => $session->session_id]);
?>

<!-- Title -->
<div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Bulk Apply</h2>
</div>

<!-- Form content -->
<div style="display: grid; gap: 20px;">
    <!-- Description -->
    <div style="font-size: 14px; color: #666; padding: 12px 0;">
        Apply the selected Timetable Week to every week in this session.
    </div>

    <!-- Week Dropdown -->
    <div style="display: grid; gap: 8px;">
        <label for="week_id" style="font-size: 12px; font-weight: 600; color: #444;">Timetable Week</label>
        <?php
        $options = array('' => 'Select a week...');
        if (isset($weeks)) {
            foreach ($weeks as $week) {
                $options[$week->week_id] = html_escape($week->name);
            }
        }
        echo form_dropdown([
            'name' => 'week_id',
            'id' => 'week_id',
            'options' => $options,
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #fff;'
        ]);
        ?>
    </div>
</div>

<?php
// Submit button with consistent styling
$this->load->view('partials/submit', array(
    'submit' => array(
        'value' => 'Apply Week',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 20px; transition: background-color 0.3s;'
    )
));

echo form_close();
?>