<?php
// Flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Set user_id for edit mode
$user_id = NULL;
if (isset($user) && is_object($user)) {
    $user_id = set_value('user_id', $user->user_id);
}

// Open the form with centered styling and multipart support
echo form_open_multipart('users/save', array(
    'id' => 'users_add',
    'style' => 'background-color: #ffffff; border-radius: 8px;'
), array('user_id' => $user_id));
?>

<!-- User Details Section -->
<!-- <div style="margin: 20px 0;">
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">User Details</h2>
</div> -->

<div style="display: grid; gap: 20px;">
    <!-- Username -->
    <div style="display: grid; gap: 8px;">
        <label for="username" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Username</label>
        <?php
        $field = 'username';
        $value = set_value($field, isset($user) ? $user->username : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '20',
            'maxlength' => '100',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter username',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Authlevel (Type) -->
    <div style="display: grid; gap: 8px;">
        <label for="authlevel" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Type</label>
        <?php
        $field = 'authlevel';
        $value = set_value($field, isset($user) ? $user->authlevel : '2', FALSE);
        $options = array('1' => 'Administrator', '2' => 'Teacher');
        echo form_dropdown(
            $field,
            $options,
            $value,
            'id="authlevel" tabindex="' . tab_index() . '" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;"'
        );
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>

    <!-- Enabled -->
    <div style="display: flex; gap: 8px;">
        <label for="enabled" style="font-size: 12px; font-weight: 600; color: #444;">Enabled</label>
        <?php
        $field = 'enabled';
        $value = isset($user) ? $user->enabled : '1';
        $checked = set_checkbox($field, '1', $value == '1');
        echo form_hidden($field, '0');
        echo form_checkbox(array(
            'name' => $field,
            'id' => $field,
            'value' => '1',
            'tabindex' => tab_index(),
            'checked' => $checked,
            'style' => 'margin: 0;'
        ));
        ?>
        <span style="font-size: 12px; color: #666;">Check to enable the user account</span>
    </div>

    <!-- Email -->
    <div style="display: grid; gap: 8px;">
        <label for="email" style="font-size: 12px; font-weight: 600; color: #444;">Email Address</label>
        <?php
        $field = 'email';
        $value = set_value($field, isset($user) ? $user->email : '', FALSE);
        echo form_input(array(
            'name' => $field,
            'id' => $field,
            'size' => '35',
            'maxlength' => '255',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter email address',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
        <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
    </div>
</div>

<!-- Password Section -->
<div style="margin: 30px 0 0;">
    <h2 style="font-size: 20px; font-weight: bold; color: #333; margin: 0 0 20px;">Password</h2>
    <?php if (isset($user)): ?>
        <div style="font-size: 14px; color: #666; margin-bottom: 20px;">Change the user's password by entering it twice in these boxes.</div>
    <?php endif; ?>
    <div style="display: grid; gap: 20px;">
        <!-- Password -->
        <div style="display: grid; gap: 8px;">
            <label for="password1" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password</label>
            <?php
            $field = 'password1';
            echo form_password(array(
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'tabindex' => tab_index(),
                'value' => '',
                'placeholder' => 'Enter password',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Password (again) -->
        <div style="display: grid; gap: 8px;">
            <label for="password2" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password (again)</label>
            <?php
            $field = 'password2';
            echo form_password(array(
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'tabindex' => tab_index(),
                'value' => '',
                'placeholder' => 'Confirm password',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>
    </div>
</div>

<!-- Personal Details Section -->
<div style="margin: 30px 0 0;">
    <h2 style="font-size: 20px; font-weight: bold; color: #333; margin: 0 0 20px;">Personal Details</h2>
    <div style="display: grid; gap: 20px;">
        <!-- First Name -->
        <div style="display: grid; gap: 8px;">
            <label for="firstname" style="font-size: 12px; font-weight: 600; color: #444;">First Name</label>
            <?php
            $field = 'firstname';
            $value = set_value($field, isset($user) ? $user->firstname : '', FALSE);
            echo form_input(array(
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '20',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'Enter first name',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Last Name -->
        <div style="display: grid; gap: 8px;">
            <label for="lastname" style="font-size: 12px; font-weight: 600; color: #444;">Last Name</label>
            <?php
            $field = 'lastname';
            $value = set_value($field, isset($user) ? $user->lastname : '', FALSE);
            echo form_input(array(
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '20',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'Enter last name',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Display Name -->
        <div style="display: grid; gap: 8px;">
            <label for="displayname" style="font-size: 12px; font-weight: 600; color: #444;">Display Name</label>
            <?php
            $field = 'displayname';
            $value = set_value($field, isset($user) ? $user->displayname : '', FALSE);
            echo form_input(array(
                'name' => $field,
                'id' => $field,
                'size' => '20',
                'maxlength' => '20',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'Enter display name',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Department -->
        <div style="display: grid; gap: 8px;">
            <label for="department" style="font-size: 12px; font-weight: 600; color: #444;">Department</label>
            <?php
            $options = array('' => '(None)');
            if ($departments) {
                foreach ($departments as $department) {
                    $options[$department->department_id] = html_escape($department->name);
                }
            }
            $value = set_value('department_id', isset($user) ? $user->department_id : '', FALSE);
            echo form_dropdown(
                'department_id',
                $options,
                $value,
                'tabindex="' . tab_index() . '" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;"'
            );
            ?>
            <?php echo form_error('department_id', '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>

        <!-- Phone Number -->
        <div style="display: grid; gap: 8px;">
            <label for="ext" style="font-size: 12px; font-weight: 600; color: #444;">Phone Number</label>
            <?php
            $field = 'ext';
            $value = set_value($field, isset($user) ? $user->ext : '', FALSE);
            echo form_input(array(
                'name' => $field,
                'id' => $field,
                'size' => '10',
                'maxlength' => '10',
                'tabindex' => tab_index(),
                'value' => $value,
                'placeholder' => 'Enter phone number',
                'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
            ));
            ?>
            <?php echo form_error($field, '<div style="color: #cc0000; font-size: 12px;">', '</div>'); ?>
        </div>
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
        'url' => 'users',
        'style' => 'width: 100%; padding: 12px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; transition: background-color 0.3s;'
    )
));

echo form_close();
?>