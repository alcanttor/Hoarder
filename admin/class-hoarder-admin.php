<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://metagauss.com
 * @since      1.0.0
 *
 * @package    Hoarder
 * @subpackage Hoarder/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Hoarder
 * @subpackage Hoarder/admin
 * @author     Vikas Arora <vikas.arora@metagauss.com>
 */
class Hoarder_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $hoarder    The ID of this plugin.
	 */
	private $hoarder;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
        
        private $url;

        /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $hoarder       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $hoarder, $version ,$url) {

		$this->hoarder = $hoarder;
		$this->version = $version;
                $this->url = $url;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hoarder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hoarder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->hoarder, plugin_dir_url( __FILE__ ) . 'css/hoarder-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hoarder_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hoarder_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
                wp_enqueue_script('jquery'); 
		wp_enqueue_script( $this->hoarder, plugin_dir_url( __FILE__ ) . 'js/hoarder-admin.js', array( 'jquery' ), $this->version, false );
                wp_localize_script( $this->hoarder, 'hoarder_ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php')) );
                

	}
        
        public function hoarder_admin_menu()
        {
            add_menu_page(__('Hoarder','hoarder'),__('Hoarder','hoarder'),"manage_options","hoarder_settings",array( $this, 'hoarder_settings' ),'dashicons-admin-generic');
            add_submenu_page("",__('Rules','hoarder'),__('Rules','hoarder'),"manage_options","hoarder_rules",array( $this, 'hoarder_rules' ));
		
        }
        
        public function hoarder_settings()
        {
            include 'partials/hoarder-admin-display.php';
        }
        
        public function hoarder_rules()
        {
            include 'partials/hoarder-rules.php';
        }
        
        public function hoarder_fetch_rules()
        {
            $request = new HOARDER_request;
            $dbhandler = new HOARDER_DBhandler;
            $url = 'https://profilegrid.co/api-rules.php';
            $rules = $request->hoarder_fetch_rules($url);
           // $rules = $request->hoarder_fetch_rules($this->url);
            $request->hoarder_save_rules($rules);
            
        }


        public function hoarder_api_verification()
        {
            $request = new HOARDER_request;
            $dbhandler = new HOARDER_DBhandler;
            $api_key = $_POST['apikey'];     
            $result = $request->hoarder_api_verification($api_key, $this->url);
            //echo 'test';
            //print_r($result);
            if(isset($result) && !empty($result['jwt']))
            {
               $dbhandler->update_global_option_value('hoarder_token',$result->jwt);
               $dbhandler->update_global_option_value('hoarder_userId',$result->userId);
               $dbhandler->update_global_option_value('hoarder_siteId',$result->siteId);
               _e('Verified','hoarder');
            }
            else 
            {
                _e('Authentication Failed','hoarder');
            }
            
            die;
        }
        
        public function hoarder_add_hooks()
        {
            $request = new HOARDER_request;
            $dbhandler = new HOARDER_DBhandler;
            $identifier = 'RULES';
            $where = array('status'=>1);
            $rules = $dbhandler->get_all_result($identifier,'*', $where);
            foreach($rules as $rule)
            {
                $this->hoarder_add_rule($rule);
            }
            
        }
        
        public function hoarder_add_rule($rule)
        {
            switch($rule->event)
            {
                case 'ROLE_CHANGE':
                    add_action( 'set_user_role', array( $this, 'hoarder_change_user_role' ), 10, 3 ); 
                    break;
                case 'INVETORY_CHANGE':
                    
                    break;
            }
        }
        
        public function hoarder_change_user_role($user_id,$role,$old_roles)
        {
            $dbhandler = new HOARDER_DBhandler;
            $request = new HOARDER_request;
            $identifier = 'RULES';
            $where = array('status'=>1,'event'=>'ROLE_CHANGE');
            $roles = $dbhandler->get_all_result($identifier,'*', $where);
            $parameters = array('user_id'=>$user_id,'new_role'=>$role,'previous_role'=>$old_roles);
            if(!empty($roles))
            {
                foreach ($roles as $role) 
                {
                    $rulesDto = maybe_unserialize($role->rulesDto);
                    $ruleExpression = $role->ruleExpression;
                    $conditions = $request->hoarder_check_conditions($rulesDto,$parameters);
                    $end_condition = $request->hoarder_final_condition_result($conditions,$ruleExpression);
                    
                    if($end_condition)
                    {
                        echo 'true';
                       $url = 'https://profilegrid.co/get_response.php';
                       $response = array('userid'=>1,'username'=>'test');
                        $request->hoarder_send_response($url, $response);
                        
                    }
                    else
                    {
                        echo 'false';
                    }        
                    
                }
                
                
               
                
                die;
            }
        }
        

}
