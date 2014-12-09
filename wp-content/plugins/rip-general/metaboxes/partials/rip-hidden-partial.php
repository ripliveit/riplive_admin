<fieldset>
    <input type="hidden" 
           id="<?php echo $field['id'] ?>" 
           name="<?php echo $field['id'] ?>" 
           value="<?php echo wp_create_nonce($field['id'] . '-nonce'); ?>" />
</fieldset>