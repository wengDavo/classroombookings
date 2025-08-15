<?php

echo $this->session->flashdata('saved');

echo form_open('profile/save', array(
	'id' => 'profile_edit',
	'style' => 'margin: auto; background-color: #ffffff; border-radius: 8px;'
	)
);

?>

<!-- Title -->
<div style="margin: 15px 0 30px 0;">
	<p style="font-size: 14px; color: #757575;">Edit 📝</p>
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">My Details</h2>
</div>

<!-- Form fields -->
<section style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
	<div style="display: grid; gap: 20px;">
		<div style="display: grid; gap: 8px;">
			<label for="email" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Email Address</label>
			<?php
				$email = set_value('email', $user->email, FALSE);
				echo form_input(array(
					'name' => 'email',
					'id' => 'email',
					'size' => '35',
					'maxlength' => '255',
					'tabindex' =>tab_index(),
					'value' => $email,
					'placeholder' => 'Enter your email address',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('email'); ?>
		</div>

		<div style="display: grid; gap: 8px;">
			<label for="password1" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password</label>
			<?php
				echo form_password(array(
					'name' => 'password1',
					'id' => 'password1',
					'size' => '20',
					'tabindex' => tab_index(),
					'value' => '',
					'placeholder' => 'Enter your password',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('password1'); ?>
		</div>

		<div style="display: grid; gap: 8px;">
			<label for="password2" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Password (again)</label>
			<?php
				echo form_password(array(
					'name' => 'password2',
					'id' => 'password2',
					'size' => '20',
					'tabindex' => tab_index(),
					'value' => '',
					'placeholder' => 'Enter your password again',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('password2'); ?>
		</div>

	</div>
	<div style="display: grid; gap: 20px;">
		<div style="display: grid; gap: 8px;">
			<label for="firstname" class="required" style="font-size: 12px; font-weight: 600; color: #444;">First Name</label>
			<?php
				$firstname = set_value('firstname', $user->firstname, FALSE);
				echo form_input(array(
					'name' => 'firstname',
					'id' => 'firstname',
					'size' => '20',
					'maxlength' => '100',
					'tabindex' =>tab_index(),
					'value' => $firstname,
					'placeholder' => 'Enter your first name',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('firstname'); ?>
		</div>

		<div style="display: grid; gap: 8px;">
			<label for="lastname" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Last Name</label>
			<?php
				$lastname = set_value('lastname', $user->lastname, FALSE);
				echo form_input(array(
					'name' => 'lastname',
					'id' => 'lastname',
					'size' => '20',
					'maxlength' => '100',
					'tabindex' =>tab_index(),
					'value' => $lastname,
					'placeholder' => 'Enter your last name',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('lastname'); ?>
		</div>

		<div style="display: grid; gap: 8px;">
			<label for="displayname" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Display Name</label>
			<?php
				$displayname = set_value('displayname', $user->displayname, FALSE);
				echo form_input(array(
					'name' => 'displayname',
					'id' => 'displayname',
					'size' => '20',
					'maxlength' => '100',
					'tabindex' =>tab_index(),
					'value' => $displayname,
					'placeholder' => 'Enter your display name',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('displayname'); ?>
		</div>

		<!-- <div style="display: grid; gap: 8px;">
			<label for="ext" class="required" style="font-size: 12px; font-weight: 600; color: #444;">Number</label>
			<?php
				$ext = set_value('ext', $user->ext, FALSE);
				echo form_input(array(
					'name' => 'ext',
					'id' => 'ext',
					'size' => '10',
					'maxlength' => '10',
					'tabindex' =>tab_index(),
					'value' => $ext,
					'placeholder' => 'Enter your number',
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('ext'); ?>
		</div> -->

	</div>
</section>

<?php
// $this->load->view('partials/submit', array(
// 	'submit' => array('Save', tab_index()),
// ));
$this->load->view('partials/submit', array(
    'submit' => array(
        'value' => 'Save',
        'tabindex' => tab_index(),
        'style' => 'width: 100%; padding: 12px; background-color: #1A3C5E; color: #ffffff !important; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 20px; transition: background-color 0.3s;'
    )
));
echo form_close();