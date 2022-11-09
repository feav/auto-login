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

class WPAUTOLOG {
    protected $post_type = "";
    protected $site_url = "";
    function __construct() {
        $this->post_type = "wp_auto_login";
        $this->site_url =  get_site_url();
        add_filter('wp_authenticate_user',  array(&$this,'my_auth_login'),10,2);
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
        if($module == 'login'){
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
  
}
$global_wp_auto_car = new WPAUTOLOG();
