<th>
    <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
</th>
<td>
    <input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="input-text regular-text" <?php checked( $value, '1' ); ?> value="1" />
    <label for="<?php echo esc_attr( $name ); ?>"><span class="description"><?php echo esc_html( $help ); ?></span></label>
</td>