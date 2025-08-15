<?php
// Display notice with consistent styling
echo isset($notice) ? "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$notice}</div>" : '';

// Open the form with styled container
echo form_open_multipart(current_url(), [
    'id' => 'install_step2',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
]);
?>

<fieldset style="border: none; padding: 0; margin-bottom: 20px;">
    <!-- <legend accesskey="S" tabindex="<?php echo tab_index() ?>" style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Settings</legend> -->

    <div style="display: grid; gap: 20px;">
        <!-- School Name -->
        <div style="display: grid; gap: 8px;">
            <label for="name" class="required" style="font-size: 12px; font-weight: 600; color: #444;">School Name</label>
            <?php
            $field = 'name';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '40',
                'maxlength' => '255',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., Example High School',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>
    </div>
</fieldset>

<fieldset style="border: none; padding: 0; margin-bottom: 20px;">
    <legend accesskey="U" tabindex="<?php echo tab_index() ?>" style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Administrator User</legend>

    <div style="display: grid; gap: 20px;">
        <!-- Admin Username -->
        <div style="display: grid; gap: 8px;">
            <label for="admin_username" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Username</label>
            <?php
            $field = 'admin_username';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '25',
                'maxlength' => '255',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., admin',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Admin Password -->
        <div style="display: grid; gap: 8px;">
            <label for="admin_password" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password</label>
            <?php
            $field = 'admin_password';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'type' => 'password', // Changed to password type for security
                'size' => '25',
                'maxlength' => '255',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'Enter password (min 8 characters)',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <p style="font-size: 12px; color: #666; margin: 8px 0 0 0;">At least 8 characters.</p>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>
    </div>
</fieldset>

<?php
$this->load->view('partials/submit', [
    'submit' => [
        'value' => 'Next',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;'
    ],
    'cancel' => [
        'value' => 'Back',
        'tabindex' => tab_index(),
        'url' => 'install/config',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    ]
]);

echo form_close();
?>