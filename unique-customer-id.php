<?php
/**
 * Plugin Name: Unique Customer ID
 * Plugin URI: https://cloudsofdream.com
 * Description: This plugin is use to create Unique ID for All Users(Customers).
 * Version: 1.0
 * Author: Sumit Sharma
 * Author URI: https://cloudsofdream.com
 **/

add_action('admin_menu', 'uci_setup_page');

function uci_setup_page(){
    add_menu_page( 'Unique Customer ID', 'Unique Customer ID', 'manage_options', 'uci-option', 'uci_init' );
}

function uci_init(){
    echo "<h1>Unique Customer ID Plugin</h1>";

    echo "Use Shortcode or function to show Unique Customer ID : <code>[uci_hash]</code> OR <code> echo uci_hash_show(); </code>";
}


/*********************** This hook will add hash in user meta when user is register $uci_hashing ***********************/

add_action( 'user_register', 'uci_registration_save', 10, 1 );

function uci_registration_save( $user_id ) {

        update_user_meta($user_id, 'user_hash', $_POST['user_email']);

}

/*********************** This hook will add hash in user meta when user is updating (if hash is not there) $uci_hashing ***********************/

add_action( 'profile_update', 'uci_hash_update', 10, 2 );

function uci_hash_update( $user_id, $old_user_data ) {

    if(AUTH_SALT){
        $uci_hash = md5($old_user_data->data->user_email.AUTH_SALT);
    }else{
        $uci_hash = md5($old_user_data->data->user_email);
    }
    $uci_hash_exist = get_user_meta($user_id, 'user_hash', false);

    if($uci_hash_exist){
        update_user_meta($user_id, 'user_hash', $uci_hash);
    }else{
        add_user_meta($user_id, 'user_hash', $uci_hash, true);
    }

}


/************************** ShortCode to show UCI ***************************/



function uci_hash_show($atts=null){
    $user_id = get_current_user_id();
    $uci_hash = get_user_meta($user_id, 'user_hash', false);
    if($uci_hash){
        return $uci_hash[0];
    }else{
        $user_data = wp_get_current_user();
        uci_hash_update($user_id,$user_data->data->user_email);
        $uci_hash = get_user_meta($user_id, 'user_hash', false);
        return $uci_hash[0];
    }
}

add_shortcode('uci_hash', 'uci_hash_show');


add_action('manage_users_columns', 'add_uci_to_users_columns', 10, 1 );
function add_uci_to_users_columns( $columns ) {
    $columns['user_hash'] = __('Unique Customer ID');
    return $columns;
}


add_filter('manage_users_custom_column',  'add_data_to_uci_users_columns', 10, 3);
function add_data_to_uci_users_columns( $value, $column_name, $user_id )
{
    if ('user_hash' == $column_name) {
        $uci_hash = get_user_meta($user_id, 'user_hash', false);
        if ($uci_hash[0]) {
            $value = $uci_hash[0];
        } else {
            $value = '<span class="na" style="color:grey;"><em>No Hash</em></span>';
        }
    }
    return $value;
}
