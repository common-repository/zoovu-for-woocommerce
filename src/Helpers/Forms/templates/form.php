<div class="wrap woocommerce">
    <div id="icon-woocommerce" class="icon32 icon32-woocommerce-email"></div>

    <form class="add form-generator" method="post">
        <table class="form-table">
            <?php foreach ($this->fields as $field) : ?>

                <?php extract($field) ?>

                <tr
                    class="type_options <?php echo esc_attr( $class ); ?> field-cont <?php echo $type === 'hidden' ? 'hidden' : '' ?>"
                    data-field="<?php echo esc_attr( $name ) ?>"
                    data-type="<?php echo esc_attr( $type ) ?>"
                    data-conditions='<?php echo ! empty($conditions) ? json_encode($conditions) : false ?>'
                    data-ajax_route="<?php echo isset($ajax_route) ? $ajax_route : false ?>"
                    data-value='<?php echo json_encode($value) ?>'
                >

                    <?php include sprintf('fields/%s.php', $type) ?>

                </tr>

            <?php endforeach ?>
        </table>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save changes', ZoovuFeeds::LOCALE_SLUG) ?>"></p>
    </form>
</div>