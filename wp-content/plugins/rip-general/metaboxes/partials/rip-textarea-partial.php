<fieldset>
    <h2>
        <?php echo $field['description']; ?>
    </h2>

    <textarea 
        id="<?php echo $field['id'] ?>"
        name="<?php echo $field['id'] ?>"  
        cols="60" 
        rows="4" 
        style="width:97%">
        <?php echo ($meta['value'] ? $meta['value'] : $field['default']) ?>
    </textarea>
</fieldset>