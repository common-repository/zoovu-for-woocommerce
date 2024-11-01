<th>
    <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
</th>
<td>
    <input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="input-text regular-text" value="<?php echo esc_attr( $value ); ?>" />
    <p class="description">
        <?php echo esc_html( $help ); ?>
    </p>
</td>