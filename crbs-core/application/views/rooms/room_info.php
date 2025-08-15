<div class="room-info" style="min-width: 320px; max-width: 500px; margin: 0 auto; padding: 25px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">

    <h3 style="font-size: 20px; font-weight: bold; color: #333; margin: 0 0 20px;"><?= html_escape($room->name) ?></h3>

    <?php
    $photo_html = '';
    $fields_html = '';

    // Set table template with inline styling
    $this->table->set_template([
        'table_open' => '<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">',
        'row_start' => '<tr style="border-bottom: 1px solid #eee;">',
        'cell_start' => '<td style="padding: 12px; font-size: 14px; color: #444;">',
        'heading_cell_start' => '<th style="padding: 12px; font-size: 14px; font-weight: 600; color: #333; text-align: left; border-bottom: 2px solid #ddd;">',
    ]);

    foreach ($room_info as $row) {
        $this->table->add_row($row['label'], $row['value']);
    }

    $fields_html = $this->table->generate();

    if ($photo_url) {
        $img = img($photo_url, false, ['style' => 'max-width: 100%; height: auto; border-radius: 5px; margin-top: 20px;']);
        $photo_html = "<div class='room-photo' style='text-align: center;'>{$img}</div>";
    }

    if (!empty($room_info)) {
        echo $fields_html;
    } else {
        echo "<p style='font-size: 14px; color: #888; margin: 0;'><em>No details available.</em></p>";
    }

    echo $photo_html;
    ?>

</div>