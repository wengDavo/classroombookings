<div class="" style="margin: 0 auto 20px;">
<!-- <div class="filter-form add-bottom" style="margin: 0 auto 20px;"> -->

    <?php
    $attrs = [
        // 'class' => 'cssform-stacked',
        'id' => 'users_filter',
        'method' => 'GET',
        'up-autosubmit' => '',
        'up-target' => '#users_list',
    ];

    echo form_open('users', $attrs);
    ?>

    <div style="display: flex; align-items: center; background-color: #ffffff; border: 1px solid #ddd; border-radius: 5px; padding: 5px 10px;">
        <?php
        // Search input
        $value = set_value('q');
        echo form_input([
            'name' => 'q',
            'id' => 'q',
            'value' => $value,
            'placeholder' => 'Search users...',
            'style' => 'flex: 1; border: none; padding: 8px 10px; font-size: 16px; outline: none; background: transparent;'
        ]);

        // Clear link
        echo anchor('users', 'Clear', [
            'class' => 'button',
            'style' => 'padding: 8px 15px; background-color: #f0f0f0; color: #555; text-decoration: none; font-size: 14px; border-radius: 5px; transition: background-color 0.3s;'
        ]);
        ?>
    </div>

    <?php echo form_close(); ?>

</div>