<?php 


if( ! defined('WP_UNINSTALL_PLUGIN') ){
    die;
}

$threes = get_posts( array('post_type' => '3DM', 'numberposts' => -1 ) );

foreach($threes as $three){
    wp_delete_post($three->ID, true);
}
