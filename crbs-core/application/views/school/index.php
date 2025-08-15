<?php
echo $this->session->flashdata('saved');
echo form_open_multipart(current_url(), array(
	'id' => 'schooldetails',
	'style' => 'margin: auto; background-color: #ffffff; border-radius: 8px;'
	)
);
?>

<!-- Title -->
<div style="margin: 15px 0 30px 0;">
	<p style="font-size: 14px; color: #757575;">View 📖</p>
    <h2 style="font-size: 22px; font-weight: bold; color: #333; margin: 0;">School Information</h2>
</div>

<div style="display: grid; gap: 20px;">
	<!-- <legend accesskey="I" tabindex="<?php echo tab_index();?>">School Information</legend>	 -->
	<div style="display: grid; gap: 8px;">
			<label for="schoolname" class="required" style="font-size: 12px; font-weight: 600; color: #444;">School name</label>
			<?php
				$value = set_value('schoolname', element('name', $settings), FALSE);
				echo form_input(array(
					'name' => 'schoolname',
					'id' => 'schoolname',
					'size' => '30',
					'maxlength' => '255',
					'tabindex' =>tab_index(),
					'value' => $value,
					'disabled' => true,
					'readonly'=> true,
					'style' => 'width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; outline: none; transition: border-color 0.3s;'
				));
				?>
			<?php echo form_error('schoolname'); ?>
	</div>
<!-- 
	<p>
		<label for="website">Website address</label>
		<?php
		$value = set_value('website', element('website', $settings), FALSE);
		echo form_input(array(
			'name' => 'website',
			'id' => 'website',
			'size' => '40',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => $value,
		));
		?>
	</p>
	<?php echo form_error('website'); ?> -->

</div>



<!-- <fieldset>

	<legend accesskey="L" tabindex="<?php echo tab_index() ?>">School Logo</legend>

	<div>Use this section to upload a school logo.</div>

	<p>
		<label>Current logo</label>
		<?php
		$logo = element('logo', $settings);
		$image_url = image_url($logo);
		if ( ! empty($image_url)) {
			echo img($image_url, FALSE, "style='padding:1px; border:1px solid #ccc; max-width: 300px; width: auto; height: auto'");
		} else {
			echo "<span><em>None found</em></span>";
		}
		?>
	</p>

	<p>
		<label for="userfile">File upload</label>
		<?php
		echo form_upload(array(
			'name' => 'userfile',
			'id' => 'userfile',
			'size' => '25',
			'maxlength' => '255',
			'tabindex' => tab_index(),
			'value' => '',
		));
		?>
		<p class="hint">Uploading a new logo will <span>overwrite</span> the current one.</p>
	</p>

	<?php
	if ($this->session->flashdata('image_error') != '') {
		echo "<p class='hint error'><span>" . $this->session->flashdata('image_error') . "</span></p>";
	}
	?>

	<p>
		<label for="logo_delete">Delete logo?</label>
		<?php
		echo form_checkbox(array(
			'name' => 'logo_delete',
			'id' => 'logo_delete',
			'value' => '1',
			'tabindex' => tab_index(),
			'checked' => FALSE,
		));
		?>
		<p class="hint">Tick this box to <span>delete the current logo</span>. If you are uploading a new logo this will be done automatically.</p>
	</p>

</fieldset> -->


<?php

// $this->load->view('partials/submit', array(
// 	'submit' => array('Save', tab_index()),
// 	'cancel' => array('Cancel', tab_index(), 'controlpanel'),
// ));

echo form_close();
