<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div style="background-color: #ffffff; border-radius: 8px;  margin: 20px 0">
    <?php echo form_open($action, '', array('id' => $id)); ?>
    
    <p class="msgbox question" style="font-size: 16px; color: #444; margin: 0 0 15px 0; font-weight: 600;">Are you sure you want to delete this item?</p>
    <?php if (isset($text)) { ?>
        <p class="msgbox exclamation" style="font-size: 14px; color: #cc0000; margin: 0 0 20px 0; padding: 10px; background-color: #fff5f5; border: 1px solid #ffcccc; border-radius: 5px;"><?php echo $text; ?></p>
    <?php } ?>

    <div style="display: flex; gap: 10px; justify-content: flex-start;">
        <?php echo form_submit(array(
            'value' => 'Yes',
            'style' => 'padding: 6px 20px; background-color: #cc0000; color: #ffffff; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.3s;',
            'onmouseover' => "this.style.backgroundColor='#b30000';",
            'onmouseout' => "this.style.backgroundColor='#cc0000';"
        )); ?>
        <?php echo anchor($cancel, 'No', array(
            'style' => 'padding: 6px 20px; background-color: #ffffff; color: #1A3C5E; border: 1px solid #1A3C5E; border-radius: 5px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block; transition: background-color 0.3s;',
            'onmouseover' => "this.style.backgroundColor='#f0f5f9';",
            'onmouseout' => "this.style.backgroundColor='#ffffff';"
        )); ?>
    </div>

    <?php echo form_close(); ?>
</div>