<?php

echo form_open('login/submit', array(
    'id' => 'login',
    'style' => 'margin: auto; background-color: #ffffff; border-radius: 8px;'
), array('page' => $this->uri->uri_string()));
?>

<!-- Title -->
<div style="margin: 0px 0px 40px 0px;">
	<p style="font-size: 14px; color: #757575;">Welcome back! 👋</p>
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">Login to your account</h2>
</div>

<!-- Form fields -->
<div style="display: grid; gap: 20px;">
    <div style="display: grid; gap: 8px;">
        <label for="username" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Username</label>
        <?php
        $value = set_value('username', '', FALSE);
        echo form_input(array(
            'name' => 'username',
            'id' => 'username',
            'size' => '20',
            'maxlength' => '100',
            'tabindex' => tab_index(),
            'value' => $value,
            'placeholder' => 'Enter your username',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
        ));
        ?>
    </div>

    <div style="display: grid; gap: 8px;">
        <label for="password" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password</label>
        <?php
        echo form_password(array(
            'name' => 'password',
            'id' => 'password',
            'size' => '20',
            'tabindex' => tab_index(),
            'placeholder' => 'Enter your password',
            'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s; background-color: #D8D8D8;'
        ));
        ?>
    </div>
</div>

<?php
// Pass styles to the partial with explicit text color enforcement
$this->load->view('partials/submit', array(
    'submit' => array(
        'value' => 'Login',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: background-color 0.3s;'
    )
));

echo form_close();
?>