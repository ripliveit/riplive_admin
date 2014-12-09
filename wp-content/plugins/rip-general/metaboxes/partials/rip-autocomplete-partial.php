<fieldset>
    <h2>
        <?php echo $field['description']; ?>
    </h2>

    <input type="text" 
           id="<?php echo $field['id']; ?>"
           class="autocomplete"
           name="<?php echo $field['id']; ?>"  
           value="<?php echo ($meta['value'] ? $meta['value'] : $field['default']); ?>" 
           style="width:97%"/>
</fieldset>

<script>
    jQuery(function() {
        jQuery.ajax({
            type: 'GET',
            url: '/wp-admin/admin-ajax.php',
            data: {
                action: '<?php echo $field['data-action'] ?>'
            },
            dataType: 'json',
            success: function(data) {
                /**
                 * All data returned from General Ajax Front Controller,
                 * and Label Ajax Front Controller are wrapped in an items array:
                 * {
                    status: "ok",
                    count: 20,
                    count_total: 20,
                    items: [] <-------- This contains the data for the autocomplete.
                 }
                 * 
                 */
                jQuery("#<?php echo $field['id']; ?>").autocomplete({
                    source: data.items
                });
            }
        });
    });
</script>