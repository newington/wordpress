<?php
/**
 * Class and methods to handle individual options for the ADAuthInt plug-in
 * @package wordpress
 * @subpackage ADAuthInt
 * @version 0.6
 */

/**
 * A class to help define the way options are constructed for the ADAuthInt plugin
 */
class ADAuthInt_Option {
	/**#@+
	 * @var string
	 * @default NULL
	 */
	/**
	 * The label (properly translated through WordPress) to use for this option field
	 */
	public $opt_label = NULL;
	/**
	 * The ID/name of this option
	 */
	public $opt_name = NULL;
	/**
	 * The type of variable/input to use for this option
	 */
	public $opt_type = 'string';
	/**
	 * The default value for this option
	 */
	public $opt_default = NULL;
	/**
	 * The user-defined value for this option
	 */
	public $opt_value = NULL;
	/**
	 * The options section in which this option belongs
	 */
	public $opt_section = 'default';
	/**
	 * If this option has pre-defined value choices, those are listed here
	 */
	public $opt_choices = NULL;
	/**#@-*/
	/**
	 * @var string|array $optnote If any notes need to be displayed with this option field, those are defined here
	 * @default NULL
	 */
	public $opt_note = NULL;
	
	/**
	 * PHP4 Constructor function
	 * @deprecated
	 */
	function ADAuthInt_Option( $vals ) {
		return $this->__construct( $vals );
	}
	
	/**
	 * Create our ADAuthInt_Option object
	 * @param array $vals an associative array of the properties to assign to this object
	 */
	function __construct( $vals ) {
		if( !is_array( $vals ) )
			return $vals;
		
		$this->opt_name = $vals['opt_name'];
		$this->opt_label = __( $vals['opt_label'], ADAUTHINT_TEXT_DOMAIN );
		$this->opt_section = $vals['opt_section'];
		if( array_key_exists( 'opt_choices', $vals ) && is_array( $vals['opt_choices'] ) )
			$this->opt_choices = $vals['opt_choices'];
		if( array_key_exists( 'opt_note', $vals ) && !empty( $vals['opt_note'] ) )
			$this->opt_note = $vals['opt_note'];
		
		$this->_format_values( $vals );
	} /* __construct function */
	
	function _format_values( $vals=array() ) {
		switch( $vals['opt_type'] ) {
			case 'int' :
				$this->opt_default = (int)$vals['opt_default'];
				$this->opt_value = (int)$vals['opt_val'];
				$this->opt_type = 'int';
			break; /* int */
			case 'bool' :
				$this->opt_default = (bool)$vals['opt_default'];
				$this->opt_value = ( $vals['opt_val'] == 'true' || $vals['opt_val'] === true ) ? true : false;
				$this->opt_type = 'bool';
			break; /* bool */
			case 'select' :
			case 'password' :
			case 'textarea' :
				$this->opt_type = $vals['opt_type'];
			default :
				if( $vals['opt_val'] == 'NULL' )
					$vals['opt_val'] = '';
				$this->opt_default = (string)$vals['opt_default'];
				$this->opt_value = (string)$vals['opt_val'];
		} /* End switch */
	}
	
	/**
	 * Add a new WordPress settings field for the admin options page
	 * @uses add_settings_field()
	 * @deprecated ADAuthInt_Plugin::init_admin performs this task instead
	 */
	function add_settings_field() {
		add_settings_field( 
			/*$id =*/ $this->opt_name, 
			/*$title =*/ __( $this->opt_label, ADAUTHINT_TEXT_DOMAIN ), 
			/*$callback =*/ array( $this, 'build_field' ), 
			/*$page =*/ ADAUTHINT_OPTIONS_PAGE, 
			/*$section =*/ $this->opt_section, 
			/*$args =*/ array( 'label_for' => $this->opt_section . '_' . $this->opt_name )
		);
		return;
	} /* add_settings_field function */
	
	/**
	 * Build the option input field on our WordPress admin page
	 */
	function build_field() {
		if( ADAI_IS_NETWORK_ACTIVE && is_network_admin() )
			$options = get_site_option( $this->opt_section, array() );
		else
			$options = get_option( $this->opt_section, array() );
		$options = maybe_unserialize( $options );
		if( array_key_exists( $this->opt_name, $options ) ) {
			$this->opt_value = $options[$this->opt_name];
			switch( $this->opt_type ) {
				case 'int' :
					$this->opt_value = (int)$this->opt_value;
				break; /* int */
				case 'bool' :
					$this->opt_value = ( $this->opt_value == 'true' || $this->opt_value === true ) ? true : false;
				break; /* bool */
				case 'password':
					$this->opt_value = base64_decode( $this->opt_value );
				case 'textarea':
					$this->opt_value = esc_textarea( $this->opt_value );
				case 'select' :
				default :
					if( $this->opt_value == 'NULL' )
						$this->opt_value = '';
					$this->opt_value = (string)$this->opt_value;
			} /* End switch */
		}
			
		$rt = '';
		
		switch( $this->opt_type ) {
			case 'bool': /* Generate a single checkbox */
				$rt .= '
	<input type="checkbox" name="' . $this->opt_section . '[' . $this->opt_name . ']" id="' . $this->opt_section . '_' . $this->opt_name . '" value="true"' . ( ( $this->opt_value ) ? ' checked="checked"' : '' ) . '/>';
			break;
			case 'select': /* Generate a select element */
				$rt .= '
	<select name="' . $this->opt_section . '[' . $this->opt_name . ']" id="' . $this->opt_section . '_' . $this->opt_name . '">';
				foreach( $this->opt_choices as $val=>$label ) {
					$rt .= '
		<option value="' . $val . '"' . ( ( $this->opt_value == $val ) ? ' selected="selected"' : '' ) . '>' . __( $label, ADAUTHINT_TEXT_DOMAIN ) . '</option>';
				}
				$rt .= '
    </select>';
			break;
			case 'textarea': /* Generate a textarea element */
				$rt .= '
	<textarea class="widefat" name="' . $this->opt_section . '[' . $this->opt_name . ']" id="' . $this->opt_section . '_' . $this->opt_name . '">' . $this->opt_value . '</textarea>';
			break;
			default: /* Generate a text field for either an int or string */
				$rt .= '
	<input class="widefat" type="' . ( ( $this->opt_type == 'password' ) ? 'password' : 'text' ) . '" name="' . $this->opt_section . '[' . $this->opt_name . ']" id="' . $this->opt_section . '_' . $this->opt_name . '" value="' . $this->opt_value . '"/>';
		}
		if( !empty( $this->opt_note ) ) {
			$rt .= '
	<br/>
	<div class="description">
		';
			if( !is_array( $this->opt_note ) ) { 
				$rt .= __( $this->opt_note, ADAUTHINT_TEXT_DOMAIN);
			} else { 
				foreach( $this->opt_note as $k=>$note ) {
					$this->opt_note[$k] = __( $note, ADAUTHINT_TEXT_DOMAIN );
				}
				$rt .= implode( '<br/>', $this->opt_note );
			}
            $rt .= '
	</div>';
		}
		return print( $rt );
	} /* Build Field Function */
	
	/**
	 * Validate the input for this option
	 * @param array $input the array of form values that were submitted
	 */
	function validate_field( $input ) {
		/*echo "\nThe " . $this->opt_name . " field is being validated.\n";*/
		switch( $this->opt_type ) {
			case 'int':
				$this->opt_value = absint( $input );
				return absint( $input );
			break;
			case 'bool':
				$this->opt_value = (bool)$input;
				return ($input == 'true' || $input === true) ? 'true' : 'false';
			break;
			case 'password':
				$this->opt_value = $input;
				return wp_filter_kses( (!empty($input)) ? base64_encode( $input ) : '' );
			case 'textarea':
				$this->opt_value = wp_kses_data( $input );
				return $this->opt_value;
			default:
				if( !empty( $input ) )
					$this->opt_value = $input;
				else
					$this->opt_value = '';
				return wp_filter_kses( $this->opt_value );
			break;
		}
	} /* validate_field() function */
} /* ADAuthInt_Option class */
?>