<?php
// Display notice with consistent styling
echo isset($notice) ? "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$notice}</div>" : '';

// Error message box
echo "<div class='req-error' style='background-color: #fbe6f2; color: #85144b; padding: 12px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; font-size: 14px; display: none;'>";
echo msgbox('exclamation', "Please address the errors below and refresh the page before continuing.");
echo "</div>";

// Open the form with styled container
echo form_open(current_url(), [
    // 'class' => 'cssform',
    'id' => 'install_step3',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
]);

echo form_hidden('install', '1');

$items = [
    'php_version' => 'PHP Version 7.2.0 or greater',
    'php_module_gd' => "PHP module 'GD' is available",
    'php_module_ldap' => "PHP module 'LDAP' is available",
    'database' => 'Database connection',
    'database_empty' => 'Database is empty',
    'folder_local' => "'local' directory exists and writable",
    'folder_uploads' => "'uploads' directory exists and writable",
];

$errors = 0;
?>

<table class="req-table" style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif; line-height: 1.5;">
    <thead>
        <tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">
            <th style="padding: 14px; border-bottom: 2px solid #ddd; text-align: left;">Requirement</th>
            <th style="padding: 14px; border-bottom: 2px solid #ddd; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($items as $name => $label) {
            $status = '-';
            $message = '';

            if (array_key_exists($name, $requirements) && is_array($requirements[$name])) {
                if ($requirements[$name]['status'] == 'ok') {
                    $status = "<span class='line-status status-ok' style='font-weight: 600; background-color: #3D9970; color: #ffffff; padding: 6px 10px; border-radius: 4px;'>OK</span>";
                } elseif ($requirements[$name]['status'] == 'warn') {
                    $status = "<span class='line-status status-warn' style='font-weight: 600; background-color: #DDA458; color: #ffffff; padding: 6px 10px; border-radius: 4px;'>Warning</span>";
                } elseif ($requirements[$name]['status'] == 'err') {
                    $errors++;
                    $status = "<span class='line-status status-err' style='font-weight: 600; background-color: #85144b; color: #ffffff; padding: 6px 10px; border-radius: 4px;'>Error</span>";
                }

                if (array_key_exists('message', $requirements[$name])) {
                    $message = $requirements[$name]['message'];
                }
            }

            echo "<tr style='border-bottom: 1px solid #eee;'>";
            echo "<td class='req-table-label' style='padding: 14px; font-size: 14px; color: #444; text-align: left;'>";
            echo "<div class='req-table-label-title' style='font-weight: 600; margin: 0 0 5px 0; font-size: 14px;'>{$label}</div>";
            echo "<div class='req-table-label-message' style='font-size: 12px; color: #666;'>{$message}</div>";
            echo "</td>";
            echo "<td class='req-table-status' style='padding: 14px; font-size: 14px; text-align: center;'>{$status}</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<?php
if ($errors === 0) {
    $this->load->view('partials/submit', [
        'submit' => [
            'value' => 'Install',
            'tabindex' => tab_index(),
            'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;'
        ],
        'cancel' => [
            'value' => 'Back',
            'tabindex' => tab_index(),
            'url' => 'install/info',
            'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
        ]
    ]);
    echo "<style>.req-error { display: none; }</style>";
} else {
    $this->load->view('partials/submit', [
        'cancel' => [
            'value' => 'Go back',
            'tabindex' => tab_index(),
            'url' => 'install/info',
            'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
        ]
    ]);
    echo "<style>.req-error { display: block; }</style>";
}

echo form_close();
?>