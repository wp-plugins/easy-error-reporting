<?php
/**
 * Plugin Name: Easy Error Reporting
 * Plugin URI: http://manojroka.com/extensions/
 * Description: Sets the error reporting level according to user role.
 * Version: 1.0
 * Author: Manoj Roka
 * Author URI: http://manojroka.com/
 * License: GPL2
 */
 defined( 'ABSPATH' ) or die( 'No direct Access Allowed!' );
 /**
  * Check if the User logged in and his/her role
  * 
  */
define('NONE', 0);
function get_current_user_role(){
	global $current_user;
	get_currentuserinfo();
	
	if($current_user->ID != 0 && isset($current_user->roles[0])){
		$role = $current_user->roles[0];
		$level = 0;
                if(get_option('see_'.strtolower($role))){
                    $level = get_option('see_'.strtolower($role));
                }
                
				if(constant($level) != "0"){
					ini_set('display_errors','On');
				}
                error_reporting(constant($level));
				
	}else{ 
		//they are just visitors
                $level = 0;
                if(get_option('see_subscriber')){
                    $level = esc_attr( get_option('see_subscriber'));
                }
				if(constant($level) != "0"){
					ini_set('display_errors','On');
				}
		error_reporting(constant($level));
	}
}
add_action('init','get_current_user_role');
/*
 * Plugin Activataion hook
 */
function see_activate() {
    global $wp_roles;
    $roles = $wp_roles->get_names();
    //register our settings
    foreach($roles as $role=>$roleName){
        if($role == 'subscriber'){
            add_option( 'see_'.$role, 'NONE');
        }else{
            add_option( 'see_'.$role, 'E_ALL');
        }
    }
}
register_activation_hook( __FILE__, 'see_activate' );
/*
 * Adding Setting submenu
 */
// create custom plugin settings menu
add_action('admin_menu', 'see_create_menu');

function see_create_menu() {

	//create new top-level menu
	add_menu_page('Simple Error Reporting Setting', 'EER Settings', 'administrator', __FILE__, 'see_settings_page',plugins_url('/images/icon.png', __FILE__),81);

	//call register settings function
	add_action( 'admin_init', 'register_seesettings' );
}


function register_seesettings() {
        
        global $wp_roles;
        $roles = $wp_roles->get_names();
	//register our settings
        foreach($roles as $role=>$roleName){
            register_setting( 'see-settings-group', 'see_'.$role );
        }
}

if(!function_exists('see_settings_page')){
    function see_settings_page(){
        
        $error_reporting_levels = array('E_ALL','E_ERROR','E_WARNING','E_PARSE','E_NOTICE','NONE');
        global $wp_roles;
        $roles = $wp_roles->get_names();
            ?>
    <div class="wrap">
    <h2>Simple Error Reporting</h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'see-settings-group' ); ?>
        <?php do_settings_sections( 'see-settings-group' ); ?>
        <table class="form-table">
            
            <?php foreach($roles as $role=>$roleName): ?>
            <tr valign="top">
            <th scope="row"><?php echo $roleName; ?></th>
                <td>
                    <select name="<?php echo 'see_'.$role; ?>">
                        <?php foreach($error_reporting_levels as $lev): ?>
                        <option <?php if(esc_attr( get_option('see_'.$role) ) == $lev) echo 'selected'; ?> value="<?php echo $lev; ?>"><?php echo $lev; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
             
            <?php endforeach; ?>
           
        </table>
        <?php submit_button(); ?>

    </form>
    </div>
<?php
    }
}