<?php
// Container for the entire view
echo "<div style='max-width: 600px; margin: 20px auto; padding: 25px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>";

// Display database message (assumed to be a success/info message)
echo isset($db) ? "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$db}</div>" : '';
?>

<p style="font-size: 16px; color: #444; margin: 0 0 20px 0;">You have successfully set up <?php echo stripslashes($school['name']); ?>!</p>

<p style="margin: 0;">
<?php
$icondata[0] = ['login', 'Click here to login', 'user_go.png'];
$this->load->view('partials/iconbar', [
    'items' => $icondata,
    'style' => 'display: inline-block; padding: 12px 24px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: 600; transition: background-color 0.3s;'
]);
?>
</p>

<?php
echo "</div>";
?>