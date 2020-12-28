<?php
class HOARDER_sanitizer
{
	public function get_sanitized_fields($identifier,$field,$value)
	{
            $sanitize_method = 'get_sanitized_' . strtolower($identifier) . '_field';

            if ( method_exists($this, $sanitize_method) ) 
            {
                $sanitized_value = $this->$sanitize_method($field, $value);
            } 
            else 
            {
                $sanitized_value = '';
            }
		
		return $sanitized_value;
	}
	
	public function get_sanitized_rules_field($field,$value)
	{
	    switch($field)
            {
                case 'id':
                    $value = sanitize_text_field($value);
                    break;	
                default:
                       $value = sanitize_text_field($value);
						
            }
            return $value;
	}

        public function get_sanitized_settings_field($field,$value)
	{
	    switch($field)
            {
                case 'hoarder_site_token':
                    $value = sanitize_text_field($value);
                    break;
                default:
                    $value = sanitize_text_field($value);
	    }
            return $value;
	}
    
    
    public function remove_magic_quotes($input)
    {
        foreach ($input as $key => $value) {
            if ( is_array( $value ) ) {
                $input[$key] = $this->remove_magic_quotes( $value );
            } elseif ( is_string( $value ) ) {
                $input[$key] = stripslashes( $value );
            }
        }
        return $input;
    }
    
}