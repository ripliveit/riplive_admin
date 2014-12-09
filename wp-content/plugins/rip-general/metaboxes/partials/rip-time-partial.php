<fieldset>
    <h2>
        <?php echo $field['description']; ?>
    </h2>
    <input type="text" 
           id="<?php echo $field['id'] ?>" 
           class="timepicker"
           name="<?php echo $field['id'] ?>" 
           value="<?php echo ($meta['value'] ? $meta['value'] : $field['default']) ?>" />
</fieldset>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('.timepicker').timepicker({
            timeSeparator: ':',
            showLeadingZero: true
        });
    });
</script>