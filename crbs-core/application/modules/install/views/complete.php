<?php
// Display notice with consistent styling
echo isset($notice) ? "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$notice}</div>" : '';
?>

<div style="max-width: 600px; margin: 20px auto; padding: 25px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    <p style="font-size: 16px; color: #444; margin: 0 0 20px 0;">Classroombookings has been installed!</p>

    <?php
    echo iconbar([
        ['login', 'Click here to log in', 'user_go.png'],
    ], 'style="display: inline-block; padding: 12px 24px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: 600; transition: background-color 0.3s;"');
    ?>
</div>