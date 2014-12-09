<fieldset>
    <h2>
        <?php echo $field['description']; ?>
    </h2>

    <select 
        id="<?php echo $field['id'] ?>"
        name="<?php echo $field['id'] ?>" 
        style="width: 50%">
        
        <?php foreach ($field['options'] as $option) : ?>
            <option <?php echo ( $meta['value'] === $option ? ' selected="selected"' : '' ) ?>>
                <?php echo $option ?>
            </option>
        <?php endforeach; ?>
    </select>

</fieldset>