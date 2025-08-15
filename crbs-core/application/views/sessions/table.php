<table
    style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-radius: 8px; font-family: Arial, sans-serif;"
    up-data='<?= json_encode($sort_cols) ?>'
    id="<?= $id ?>"
>
    <col style="width: 20%;" /><col style="width: 10%;" /><col style="width: 10%;" /><col style="width: 25%;" /><col style="width: 25%;" /><col style="width: 10%;" />
    <thead>
        <tr style="background-color: #f5f5f5; color: #333; font-weight: 600; font-size: 14px; text-transform: uppercase;">
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Name">Name</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="Current?">Current?</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="Available?">Available?</th>
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="Start date">Start date</th>
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid #ddd;" title="End date">End date</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid #ddd;" title="Actions"></th>
        </tr>
    </thead>

    <?php if (empty($items)): ?>

    <tbody>
        <tr>
            <td colspan="6" style="padding: 20px 0; font-size: 14px; color: #888; text-align: center; border-bottom: 1px solid #eee;">No sessions.</td>
        </tr>
    </tbody>

    <?php else: ?>

    <tbody>
        <?php
        $dateFormat = setting('date_format_long', 'crbs');

        foreach ($items as $session) {
            echo "<tr style='border-bottom: 1px solid #eee;'>";

            $name = html_escape($session->name);
            $link = anchor("sessions/view/{$session->session_id}", $name, 'style="color: #1A3C5E; text-decoration: none; font-weight: 600;"');
            echo "<td style='padding: 14px; font-size: 14px; color: #444;'>{$link}</td>";

            // Current
            $img = '';
            if ($session->is_current == 1) {
                $img = img(['src' => 'assets/images/ui/enabled.png', 'width' => '16', 'height' => '16', 'alt' => 'Current session', 'style' => 'vertical-align: middle;']);
            }
            echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>{$img}</td>";

            // Selectable
            $img = '';
            if ($session->is_selectable == 1) {
                $img = img(['src' => 'assets/images/ui/enabled.png', 'width' => '16', 'height' => '16', 'alt' => 'Selectable', 'style' => 'vertical-align: middle;']);
            }
            echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>{$img}</td>";

            $start = $session->date_start ? $session->date_start->format($dateFormat) : '';
            echo "<td style='padding: 14px; font-size: 14px; color: #444;'>{$start}</td>";

            $end = $session->date_end ? $session->date_end->format($dateFormat) : '';
            echo "<td style='padding: 14px; font-size: 14px; color: #444;'>{$end}</td>";

            echo "<td style='padding: 14px; text-align: center; font-size: 14px; color: #444;'>";
            $actions['delete'] = 'sessions/delete/' . $session->session_id;
            $this->load->view('partials/editdelete', $actions);
            echo "</td>";

            echo "</tr>";
        }
        ?>
    </tbody>

    <?php endif; ?>
</table>