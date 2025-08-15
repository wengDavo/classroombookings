<?php
// Display notice with consistent styling
echo isset($notice) ? "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$notice}</div>" : '';

// Open the form with styled container
echo form_open_multipart(current_url(), [
    // 'class' => 'cssform',
    'id' => 'install_step_config',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
]);
?>

<fieldset style="border: none; padding: 0; margin-bottom: 20px;">
    <!-- <legend accesskey="D" tabindex="<?php echo tab_index() ?>" style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Database Connection Details</legend> -->

    <div style="display: grid; gap: 20px;">
        <!-- Hostname -->
        <div style="display: grid; gap: 8px;">
            <label for="hostname" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Hostname</label>
            <?php
            $field = 'hostname';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '50',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., localhost',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Port -->
        <div style="display: grid; gap: 8px;">
            <label for="port" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Port</label>
            <?php
            $field = 'port';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '3306', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '10',
                'maxlength' => '5',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., 3306',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Database Name -->
        <div style="display: grid; gap: 8px;">
            <label for="database" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Database Name</label>
            <?php
            $field = 'database';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '50',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., classroombookings',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Username -->
        <div style="display: grid; gap: 8px;">
            <label for="username" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Username</label>
            <?php
            $field = 'username';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '100',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., root',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Password -->
        <div style="display: grid; gap: 8px;">
            <label for="password" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password</label>
            <?php
            $field = 'password';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : '', FALSE);
            echo form_input([
                'type' => 'password',
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '100',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'Enter password',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>
    </div>
</fieldset>

<fieldset style="border: none; padding: 0; margin-bottom: 20px;">
    <legend accesskey="C" tabindex="<?php echo tab_index() ?>" style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 15px;">Configuration</legend>

    <div style="display: grid; gap: 20px;">
        <!-- URL -->
        <div style="display: grid; gap: 8px;">
            <label for="url" class="required" style="font-size: 12px; font-weight: 600; color: #444;">URL</label>
            <?php
            $default = config_item('base_url');
            $field = 'url';
            $value = set_value($field, isset($_SESSION['data'][$field]) ? $_SESSION['data'][$field] : $default, FALSE);
            echo form_input([
                'name' => $field,
                'id' => $field,
                'size' => '40',
                'maxlength' => '255',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'e.g., http://localhost/catss/',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ]);
            ?>
            <p style="font-size: 12px; color: #666; margin: 8px 0 0 0;">This is the web address that classroombookings will be accessed at. It must end with a forward slash /.</p>
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
    // Uncomment if cancel is needed
    // 'cancel' => [
    //     'value' => 'Cancel',
    //     'tabindex' => tab_index(),
    //     'url' => 'users',
    //     'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    // ]
]);

echo form_close();
?>