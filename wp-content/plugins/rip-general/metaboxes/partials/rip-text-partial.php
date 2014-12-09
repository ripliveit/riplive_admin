<fieldset>
    <h2>
        <?php echo $field['description']; ?>
    </h2>

    <input type="text" 
           id="<?php echo $field['id']; ?>"
           name="<?php echo $field['id']; ?>"  
           value="<?php echo ($meta['value'] ? $meta['value'] : $field['default']); ?>" 
           placeholder="<?php echo $field['placeholder']; ?>"
           style="width:97%"/>
</fieldset>