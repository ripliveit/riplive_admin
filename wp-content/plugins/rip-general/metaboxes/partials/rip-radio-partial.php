<fieldset>
    <h2>
        <?php echo $field['description']; ?>
    </h2>

    <?php foreach ($field['options'] as $option) : ?>

        <input type="radio" 
               name="<?php echo $field['id'] ?>" 
               value="<?php echo $option['value'] ?>"  
               <?php echo ( $meta['value'] == $option['value'] ? ' checked="checked"' : '' ) ?>/>     

        <?php echo ucfirst($option['label']); ?>
        
        <br/>

    <?php endforeach; ?>
</fieldset>