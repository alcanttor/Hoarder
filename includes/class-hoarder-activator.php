<?php

/**
 * Fired during plugin activation
 *
 * @link       https://metagauss.com
 * @since      1.0.0
 *
 * @package    Hoarder
 * @subpackage Hoarder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Hoarder
 * @subpackage Hoarder/includes
 * @author     Vikas Arora <vikas.arora@metagauss.com>
 */
class Hoarder_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() 
        {
            global $wpdb;
            if ( is_multisite() ) 
            {
                // Get all blogs in the network and activate plugin on each one
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->create_table();
                    restore_current_blog();
                }
            } 
            else 
            { 
                $this->create_table(); 
            }
	}
        
        public function create_table() 
        {
            global $wpdb;
            require_once( ABSPATH . 'wp-includes/wp-db.php');
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $this->get_db_table_name('RULES');
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `rule_id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` longtext DEFAULT NULL,
            `rulesDto` longtext NOT NULL,
            `event` varchar(255) NOT NULL,
            `actionsDto` longtext NOT NULL,
            `ruleExpression` varchar(255) NOT NULL,
            `availableRuleDto` longtext NOT NULL,
             `status` int(11) NOT NULL,
            PRIMARY KEY (`id`)
                    )$charset_collate;";
            dbDelta($sql);
        }
        
        public function get_db_table_name($identifier) {
		global $wpdb;
		$plugin_prefix = $wpdb->prefix.'hoarder_';
                
		switch ($identifier) {
                        case 'WP_OPTION':
				$table_name= $wpdb->prefix."options";
				break;
                        case 'RULES':
				$table_name= $plugin_prefix."rules";
				break;
			default:
				$table_name= $plugin_prefix. strtolower($identifier);
				break;
		}
		return $table_name;
	}
        
        public function get_db_table_unique_field_name($identifier) {
	   
		switch ($identifier) {
			case 'RULES':
				$unique_field_name = 'id';
				break;
                        case 'WP_OPTION':
				$unique_field_name= 'option_id';
				break;
			default:
                                $unique_field_name= 'id';
				break;
				
		}
		return $unique_field_name;
	}

	public function get_db_table_field_type($identifier,$field) {
		$functionname = 'get_field_format_type_'.strtolower($identifier);
		if (method_exists('Hoarder_Activator',$functionname)) 
                {
                    $format = $this->$functionname($field);
		} 
                else 
                {
			return false;
		}
		return $format;
	}
	
        public function get_field_format_type_rules($field) {
		switch ($field) {
			case 'id':
				$format = '%d';
				break;
                        case 'rule_id':
                                $format = '%d';
				break;
			case 'name':
				$format = '%s';
				break;
                        case 'description':
				$format = '%s';
				break;
                        case 'event':
                                $format = '%s';
				break;
                        case 'rulesDto':
                                $format = '%s';
				break;
                        case 'actionsDto':
                                $format = '%s';
				break;
                        case 'ruleExpression':
                                $format = '%s';
				break;
                        case 'availableRuleDto':
                                $format = '%s';
				break;
                        case 'status':
                                $format = '%d';
				break;
			default:
				$format = '%s';
		}
		return $format;
	}
        

}
