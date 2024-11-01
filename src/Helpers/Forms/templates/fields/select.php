<th>
    <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
</th>
<td>
    <select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" class="wc-enhanced-select chosen_select">

        <?php foreach ( $options as $val => $option_text ) { ?>
            <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $value ); ?>><?php echo esc_html( $option_text ); ?></option>

        <?php }// foreach() ?>

    </select>
    <p class="description"><?php echo esc_html( $help ); ?></p>
</td>