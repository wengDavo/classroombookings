<?php
// rooms/index_groups/group partial
$group = $group ?? null;
$rooms = $rooms ?? [];

// Group title
echo "<div style='margin: 30px 0 15px;'>";
if ($group) {
    echo "<h3 style='font-size: 18px; font-weight: bold; color: #333; margin: 0;'>";
    echo html_escape($group->name) . " <span style='font-weight: normal; color: #666;'>(" . $group->room_count . ")</span>";
    echo "</h3>";
} else {
    echo "<h3 style='font-size: 18px; font-weight: bold; color: #333; margin: 0;'>Ungrouped Rooms</h3>";
}
echo "</div>";

// Rooms table
echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;'>";
echo "<thead>";
echo "<tr style='background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;'>";
echo "<th style='padding: 14px; text-align: left; border-bottom: 2px solid #ddd;'>Name</th>";
echo "<th style='padding: 14px; text-align: left; border-bottom: 2px solid #ddd;'>Location</th>";
echo "<th style='padding: 14px; text-align: left; border-bottom: 2px solid #ddd;'>Teacher</th>";
echo "<th style='padding: 14px; text-align: center; border-bottom: 2px solid #ddd;'>Photo</th>";
echo "<th style='padding: 14px; text-align: center; border-bottom: 2px solid #ddd;'></th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

if (empty($rooms)) {
    echo "<tr><td colspan='5' style='padding: 20px 0; font-size: 14px; color: #888; text-align: center; border-bottom: 1px solid #eee;'>No rooms in this group.</td></tr>";
} else {
    foreach ($rooms as $room) {
        echo "<tr style='border-bottom: 1px solid #eee;'>";
        echo "<td style='padding: 14px; font-size: 14px; color: #444;'>" . html_escape($room->name) . "</td>";
        echo "<td style='padding: 14px; font-size: 14px; color: #444;'>" . html_escape($room->location) . "</td>";
        echo "<td style='padding: 14px; font-size: 14px; color: #444;'>";
        $owner_html = '';
        if (!empty($room->user_id)) {
            $owner = empty($room->owner->displayname) ? $room->owner->username : $room->owner->displayname;
            $owner_html = html_escape($owner);
        }
        echo $owner_html;
        echo "</td>";
        echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>";
        if (!empty($room->photo) && $image_url = image_url($room->photo)) {
            $url = site_url("rooms/photo/{$room->room_id}");
            $icon_src = base_url('assets/images/ui/picture.png');
            $icon_el = "<img src='{$icon_src}' width='16' height='16' alt='View Photo' style='vertical-align: middle;'>";
            echo "<a href='{$url}' up-history='false' up-layer='new drawer' up-target='.room-photo' title='View Photo' style='color: #1A3C5E; text-decoration: none;'>{$icon_el}</a>";
        }
        echo "</td>";
        echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>";
        $actions['edit'] = 'rooms/edit/' . $room->room_id;
        $actions['delete'] = 'rooms/delete/' . $room->room_id;
        $this->load->view('partials/editdelete', $actions);
        echo "</td>";
        echo "</tr>";
    }
}
echo "</tbody>";
echo "</table>";
?>