<?php
/**
* @package WPAUTOLOG
*/
/*
	Plugin Name: Auto Login
	Plugin URI: https://www.global-solutions-group.com
	Description: faire une connexion automatique
	Version: 1.0
	Author: Global Solution SARL
	Author URI: http://www.global-solutions-group.com
*/


header("Access-Control-Allow-Origin: *");

define("WPAUTOLOG_PLUGIN_FILE",__FILE__);

define("WPAUTOLOG_DIR", plugin_dir_path(__FILE__));
	 
define("WPAUTOLOG_URL", plugin_dir_url(__FILE__));

define("WPAUTOLOGT_API_URL_SITE", get_site_url() . "/");

define("WPAUTOLOG_POST_TYPE", "wp_auto_login");

define("WPPRODUCT_POST_TYPE", "product");

if(isset($_GET['debug'])){
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
}

class WPAUTOLOG {
    private $percent = 10;
    protected $post_type = WPAUTOLOG_POST_TYPE;
    function __construct() {
        if (! wp_next_scheduled ( '_all_refresh_daily_event' )) {
            wp_schedule_event(time(), 'daily',  '_all_refresh_daily_event' );
        }

        add_action('_all_refresh_daily_event', array(&$this,  'refresh_all_car') );

        add_action( 'rest_api_init',  array(&$this, 'register_endpoints') );
        
        add_filter('wp_authenticate_user',  array(&$this,'my_auth_login'),10,2);
        

        add_action('init', array(__CLASS__, 'enquee_scripts'));
        $this->init_ajax_api();

    }
    function init_ajax_api(){
        add_action( "wp_ajax_".$this->post_type, array(&$this,"ajax_callback") );
        add_action( "wp_ajax_nopriv_".$this->post_type, array(&$this,"ajax_callback") );
    }
    /**
    ** Call back ajax api
    **/
    function get_var($name){
    	$module = "";
			if(isset($_POST[$name])){
		    	$module	= $_POST[$name];
	    }
	    if(isset($_GET[$name])){
	    	$module	= $_GET[$name];
	    }
	    return $module;
    }
    function ajax_callback(){
        $user_id = get_current_user_id();
        $module = $this->get_var("function");
        if($module == 'getuser'){
            if($user_id){
                global $current_user;
                get_currentuserinfo();
                $email =  $current_user->user_email;
                $pwd = get_user_meta($user_id,"pwd",true);
                $data = array(
                    "response"=>200,
                    "data"=> array(
                        "email"=>$email,
                        "pwd"=>$pwd,
                        "site"=>$current_user->user_url. "/wp-admin/admin-ajax.php"
                    )
                );
                echo json_encode($data);
            }else{
                echo json_encode(array("response"=>400));
            }
            
        }else if($module == 'login'){
            $data = array();
            $username = $data['user_login'] = $this->get_var("username");
            $data['user_password'] =  $this->get_var("password");
            $data['remember'] = true;
            $user = wp_signon( $data, false );
                
            wp_redirect(home_url());
            $redirect = $this->get_var("redirect");
            if($redirect){                    
                wp_redirect( $redirect);
            }else{
                if ( !is_wp_error($user) ){
                    wp_redirect($user->user_url);
                }else{
                    wp_redirect(home_url());
                }
            }
        }
        die();
    }

    public static function enquee_scripts(){
        // wp_register_script( 'autologin-js', WPAUTOLOG_URL . "/autologin.js" ,'','1.0', true);
        // wp_enqueue_script( 'autologin-js' );
    }

    function my_auth_login ($user, $password) {
        if ( $user ){
            $url = $user->user_url . '/wp-admin/admin-ajax.php?';
            $data = array(
                'username' => $user->user_login,
                'password' => $password,
                'action' => 'wp_remote_login',
                'function' => 'login',
                'redirect' => home_url()
            );
            
            
            $params =  http_build_query($data);
            if(isset($_GET['redirect'])){
                echo "<script>document.location = '".$_GET['redirect']."'</script>";
                return $user;
            }else{
                wp_redirect($url . $params);
            }
        }else{
            return $username;
        }
   }

    function refresh_all_car() {
    }

 
    function refresh_one_car() {
    }
  
}
$global_wp_auto_car = new WPAUTOLOG();
