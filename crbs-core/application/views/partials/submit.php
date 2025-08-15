<!-- <div class="submit" style="margin-top: 20px;">
<?php
    if (isset($submit)) {
        $submit_attributes = array(
            'value' => $submit['value'],
            'tabindex' => $submit['tabindex']
        );
        if (isset($submit['style'])) {
            $submit_attributes['style'] = $submit['style'];
        }
        
        echo form_submit($submit_attributes);
    }
    echo "  ";
    if (isset($cancel)) {
        echo anchor($cancel[2], $cancel[0], array('tabindex' => $cancel[1]));
    }
    ?>
</div> -->

<!-- <div style="display: flex; gap: 10px;">
    <?php
    echo form_submit($submit);
    echo anchor($cancel['url'], $cancel['value'], [
        'tabindex' => $cancel['tabindex'],
        'style' => $cancel['style']
    ]);
    ?>
</div> -->

<div style="display: flex; gap: 10px;">
    <?php
    // Render the submit button
    echo form_submit($submit);

    // Render the cancel link only if $cancel is provided
    if (isset($cancel)) {
        echo anchor($cancel['url'], $cancel['value'], [
            'tabindex' => $cancel['tabindex'],
            'style' => $cancel['style']
        ]);
    }
    ?>
</div>