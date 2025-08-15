<?php
// Display flashdata message with green success styling
$flashdata = $this->session->flashdata('saved');
if (!empty($flashdata)) {
    echo "<div style='background-color: #e6ffe6; color: #006600; padding: 12px; border: 1px solid #99ff99; border-radius: 5px; margin-bottom: 20px; font-size: 14px;'>{$flashdata}</div>";
}

// Iconbar with styled "Add User" button
$iconbar = iconbar([
    ['users/add', 'Add User', 'add.png'],
    // ['users/import', 'Import Users', 'user_import.png'], // Uncomment if needed
], 'style="display: inline-block; padding: 10px 20px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: 600; transition: background-color 0.3s;"');


echo $iconbar;

// Render filter partial (assumed styled separately)
$this->load->view('users/filter');

// Sort columns array (for sorting functionality)
$sort_cols = ["Type", "Enabled", "Username", "Display Name", "Last Login", "Actions"];
?>

<div id="users_list" style="margin: 20px 0;">
    <table
        style="width: 100%; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;"
        up-data='<?= json_encode($sort_cols) ?>'
    >
        <col style="width: 7%;" /><col style="width: 8%;" /><col style="width: 25%;" /><col style="width: 25%;" /><col style="width: 25%;" /><col style="width: 10%;" />
        <thead>
            <tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">
                <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="Type">Type</th>
                <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="Enabled">Enabled</th>
                <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Username">Username</th>
                <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Name">Display Name</th>
                <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Lastlogin">Last Login</th>
                <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="X"></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 0;
        if ($users) {
            foreach ($users as $user) { ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <?php
                    $img_type = ($user->authlevel == ADMINISTRATOR ? 'user_administrator.png' : 'user_teacher.png');
                    $img_enabled = ($user->enabled == 1) ? 'enabled.png' : 'no.png';
                    ?>
                    <td style="padding: 14px; text-align: center; font-size: 14px; color: #444;">
                        <img src="<?= base_url("assets/images/ui/{$img_type}") ?>" width="16" height="16" alt="<?php echo $img_type ?>" style="vertical-align: middle;" />
                    </td>
                    <td style="padding: 14px; text-align: center; font-size: 14px; color: #444;">
                        <img src="<?= base_url("assets/images/ui/{$img_enabled}") ?>" width="16" height="16" alt="<?php echo $img_enabled ?>" style="vertical-align: middle;" />
                    </td>
                    <td style="padding: 14px; font-size: 14px; color: #444;"><?php echo html_escape($user->username) ?></td>
                    <td style="padding: 14px; font-size: 14px; color: #444;"><?php
                        if (empty($user->displayname)) { $user->displayname = $user->username; }
                        echo html_escape($user->displayname);
                    ?></td>
                    <td style="padding: 14px; font-size: 14px; color: #444;"><?php
                        if ($user->lastlogin == '0000-00-00 00:00:00' || empty($user->lastlogin)) {
                            $lastlogin = 'Never';
                        } else {
                            $lastlogin = date("d/m/Y, H:i", strtotime($user->lastlogin));
                        }
                        echo $lastlogin;
                    ?></td>
                    <td style="padding: 14px; text-align: center; font-size: 14px; color: #444;">
                        <?php
                        $actions['edit'] = 'users/edit/' . $user->user_id;
                        $actions['delete'] = 'users/delete/' . $user->user_id;
                        $this->load->view('partials/editdelete', $actions);
                        ?>
                    </td>
                </tr>
            <?php $i++;
            }
        } else {
            echo '<tr><td colspan="6" style="padding: 20px 0; font-size: 14px; color: #888; text-align: center; border-bottom: 1px solid #eee;">No users found!</td></tr>';
        }
        ?>
        </tbody>
    </table>

    <!-- Pagination links with consistent styling -->
    <div style="margin-top: 20px; text-align: center;">
        <?php
        echo str_replace(
            ['<a ', '<span'],
            ['<a style="padding: 8px 12px; margin: 0 4px; background-color: #1A3C5E; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 14px; transition: background-color 0.3s;" ', '<span style="padding: 8px 12px; margin: 0 4px; background-color: #e0e0e0; color: #666; border-radius: 5px; font-size: 14px;"'],
            $pagelinks
        );
        ?>
    </div>
</div>

<?php
// Render iconbar after table (per original placement)
echo $iconbar;
?>