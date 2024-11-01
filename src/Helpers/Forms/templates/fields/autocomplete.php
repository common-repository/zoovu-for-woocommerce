<th>
    <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
</th>
<td>
    <select id="<?php echo esc_attr( $name ); ?>" data-value='<?php echo json_encode($value) ?>' name="<?php echo $multiple ? esc_attr( $name ) . '[]' : esc_attr( $name ); ?>" <?php echo $multiple ? ' multiple' : '' ?>>
    	
    	<?php if ( $value ) { ?>

    		<?php foreach ( $value as $val => $option ) { ?>

	            <option value="<?php echo esc_attr( $option['id'] ); ?>"  selected="selected" <?php selected( $val, $value ); ?>><?php echo esc_html( $option['text'] ); ?></option>

	        <?php }// foreach() ?>
	        
    	<?php }// if() ?>
        

    </select>
    <p class="description"><?php echo esc_html( $help ); ?></p>
</td>