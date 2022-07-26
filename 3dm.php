<?php 
/**
 * Plugin Name:       3D Majestic
 * Description:       Manage and preview 3D models 
 * Version:           0.0.1 
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Anmo
 */

if( ! defined('ABSPATH') ){ die; }

if ( !class_exists('WP_ThreeDimensional_M') ) {

    class WP_ThreeDimensional_M{

        var $post_type = array("3dm");

        var $prefix = "three-dm-";

        var $custom_fields;

        function __construct($c_f){
            $this->custom_fields = $c_f;

            add_action('init', array($this, 'reg_post_type') );

            add_action('admin_menu', array($this,'sub_menu_callback'));

            add_action( 'admin_menu', array($this, 'create_custom_fields' ) );
            add_action( 'do_meta_boxes', array($this, 'remove_default_custom_fields' ), 10, 3 );
            add_action( 'save_post', array($this, 'save_custom_fields' ), 1, 2 );

            add_action( 'wp_enqueue_scripts', array($this,'reg_req_scripts') );
            add_action( 'admin_enqueue_scripts', array($this, 'admin_edit_scripts') );

            add_shortcode( '3dm', array($this,'set_model'));

            add_action( 'wp_ajax_gmfi', array($this, 'import_model_req'));
        }

        function set_model($atts = [], $content = null, $tag = ''){
            ob_start();
            $atts = array_change_key_case( (array) $atts, CASE_LOWER );
 
            // override default attributes with user attributes
            $shortcode_atts = shortcode_atts(
                array(
                    'title' => '',
                ), $atts, $tag
            );

            if(isset($shortcode_atts['title']) && $shortcode_atts['title'] !== ''){
                $model = get_page_by_title( $shortcode_atts['title'] , OBJECT, '3DM' );
                if(is_object($model)){
                    $this->check_enqueued();
                    set_model_template($model->ID);
                }
            }

            return ob_get_clean();
        }

        function check_enqueued() {
            $scripts = array('3js','OrbitControls','GLTFLoader', '3DModelRender');
            $css = array('threeDMStyling');

            $result = []; $result['scripts'] = []; $result['styles'] = [];
        
            // Print all loaded Scripts
            global $wp_scripts;
            foreach( $wp_scripts->queue as $script ) :
               $result['scripts'][] =  $wp_scripts->registered[$script]->handle;
            endforeach;

            //Print all loaded Styles (CSS)
            global $wp_styles;
            foreach( $wp_styles->queue as $style ) :
               $result['styles'][] =  $wp_styles->registered[$style];
            endforeach;

            foreach ($scripts as $script) {
                if( ! in_array($script,$result['scripts'])){
                    wp_enqueue_script($script);
                }
            }

            // foreach ($css as $sheet) {
            //     if( ! in_array($sheet,$result['styles'])){
            //          wp_enqueue_style( $sheet );
            //     }
            // }
        }

        function activiate(){
            $this->reg_post_type();
            flush_rewrite_rules();
        }

        function deactivate(){
            flush_rewrite_rules();
        }

        function sub_menu_callback(){
            add_submenu_page(
                'edit.php?post_type=3dm',  
                'Settings',   
                'Settings',      
                'manage_options',           
                '3dm_settings', 
                function () {  
                    include 'assets/settings.php';
                }
            );
        }

        function reg_post_type(){
            register_post_type('3dm',array(
                'labels' => array(
                'name' => _x('3DM', 'post type general name'),
                'singular_name' => _x('3DMs', 'post type singular name'),
                'add_new' => _x('Add New', '3DM'),
                'add_new_item' => __('Add New 3DM'),
                'edit_item' => __('Edit 3DM'),
                'new_item' => __('New 3DM'),
                'view_item' => __('View 3DM'),
                'search_items' => __('Search 3DM'),
                'not_found' =>  __('No 3DMs found'),
                'not_found_in_trash' => __('No 3DM found in Trash'),
                'parent_item_colon' => '',
                'menu_name' => '3D Majestic'
              ),
              'public' => false,
              'publicly_queryable' => false,
              'post_status' =>  'publish',
              'show_ui' => true,
              'show_in_menu' => true,
              'query_var' => true,
              'rewrite' => array('slug'=>'3DMs'),
              'capability_type' => 'post',
              'hierarchical' => false,
              'supports' => array('title','custom-fields')
            ));
        }

        function create_custom_fields() {
            if ( function_exists( 'add_meta_box' ) ) {
                foreach ( $this->post_type as $p_t ) {
                    add_meta_box( 
                        '3dm-custom-fields', 
                        'Model settings',
                        function () {  
                            create_custom_3dm_fields($this->custom_fields,$this->prefix); 
                        },
                        $p_t, 
                        'normal', 
                        'high' 
                    );
                }
            }
        }

        function remove_default_custom_fields( $type, $context, $post ) {
            foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
                foreach ( $this->post_type as $p_t ) {
                    remove_meta_box( 'postcustom', $p_t, $context );
                }
            }
        }

        function reg_req_scripts( $hook ) { 
            $model_dir = plugin_dir_url( __FILE__ );
            // wp_enqueue_style( 'threeDMStyling', $model_dir . 'admin/css/SettingStyling.css' );
            wp_register_script('3DModelRender', $model_dir . 'admin/js/Model.js' );
            wp_register_script('3js','https://cdn.jsdelivr.net/npm/three@0.126.1/build/three.js' );
            wp_register_script('OrbitControls','https://cdn.jsdelivr.net/npm/three@0.116.0/examples/js/controls/OrbitControls.js' );
            wp_register_script('GLTFLoader','https://cdn.jsdelivr.net/npm/three@0.122.0/examples/js/loaders/GLTFLoader.js' );
        }

        function admin_edit_scripts( $hook ) {
            global $post;
            $model_dir = plugin_dir_url( __FILE__ );
            if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
                if ( in_array($post->post_type, $this->post_type)  ) {    
                    wp_enqueue_style( 'threedmpopupstyling', $model_dir . 'admin/css/threedm-popup.css' );
                    wp_enqueue_script('helpers', $model_dir . 'admin/js/helpers.js', NULL, NULL,true );
                    wp_enqueue_script('3DModelRender', $model_dir . 'admin/js/Model.js' );
                    wp_enqueue_script('3DMController', $model_dir . 'admin/js/Controller.js' );
                    wp_enqueue_script('3js','https://cdn.jsdelivr.net/npm/three@0.126.1/build/three.js' );
                    wp_enqueue_script('OrbitControls','https://cdn.jsdelivr.net/npm/three@0.116.0/examples/js/controls/OrbitControls.js' );
                    wp_enqueue_script('GLTFLoader','https://cdn.jsdelivr.net/npm/three@0.122.0/examples/js/loaders/GLTFLoader.js' );
                }
            }elseif ( $hook == '3dm_page_3dm_settings') {
                // wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com');
            }
        }

        function clean_model_dir($model_dir){
            foreach(scandir($model_dir) as $file) {
                if ('.' === $file || '..' === $file) continue;
                if (is_dir("$model_dir/$file")) $this->clean_model_dir("$model_dir/$file");
                else unlink("$model_dir/$file");
            }
            rmdir($model_dir);
        }

        
        function import_model_req(){
            $url = $_POST['model_url'];
            $pid = $_POST['post_id'];

            $model_res = $url === 'null' ? true : $this->import_model($pid,$url);
            $config = $this->set_model_config($pid, true);

            $res = array(
                'model_validity' => $model_res,
                'model_config' => $config
            );

            wp_send_json($res);
        }

        function import_model($post_id,$value){
            $local_dir = wp_get_upload_dir();
            $local_save_dir = $local_dir["path"];
            $model_dir = $local_save_dir . '/model-' . $post_id;

            if (file_exists($model_dir)) $this->clean_model_dir($model_dir);

            mkdir($model_dir, 0777, true);
            $file_path = str_replace( $local_dir['baseurl'], $local_dir['basedir'], $value );

            WP_Filesystem();
            if($unzip_res = unzip_file( $file_path, $model_dir ) === true){
                $allowed_import = false;
                if (is_dir($model_dir)) {
                    if ($dh = opendir($model_dir)) {
                        while (($file = readdir($dh)) !== false) {
                            $allowed_files = array('bin','gltf');
                            $file_info = pathinfo($model_dir . '/' . $file);
                            if(in_array($file_info['extension'],$allowed_files)){
                                if($file_info['extension'] === 'gltf'){
                                    $value = dirname($value);
                                    $model_file = $value . '/model-' . $post_id . '/' . $file;
                                    update_post_meta( $post_id, $this->prefix . 'main-model-file-data', wp_slash( $model_file ) );
                                    $allowed_import = true;
                                }
                            }
                        }
                    }
                }
                if( ! $allowed_import){
                    if (file_exists($model_dir . '/' . $file)) $this->clean_model_dir($model_dir . '/' . $file);
                    update_post_meta( $post_id, $this->prefix . 'main-model-file-data', '' );
                    Notice_Handler::add_warning('Wrong model format. Try different file.');
                    return false;
                }
                return true;
            }else{
                if (file_exists($local_save_dir . '/' . $post_id)) $this->clean_model_dir($local_save_dir . '/' . $post_id);
                update_post_meta( $post_id, $this->prefix . 'main-model-file-data', '' );
                Notice_Handler::add_error('Error importing model file. Try again later.');
                return false;
            }   
        }

        function set_model_config($id, $flag = false){

            $container = $flag === false ? '3dm-' . $id : 'three-dm-model-customization-panel';
            $file = get_post_meta( $id, $this->prefix . 'main-model-file-data', true ) === '' ? null : get_post_meta( $id, $this->prefix . 'main-model-file-data', true );
            $orbital_tools_enable = get_post_meta( $id, $this->prefix . 'orbital-tools-enabled-data', true ) === 'on' ? true : false;
            $orbital_tools_zoom = get_post_meta( $id, $this->prefix . 'orbital-tools-zoom-data', true ) === 'on' ? true : false;
            $orbital_tools_pan = get_post_meta( $id, $this->prefix . 'orbital-tools-pan-data', true ) === 'on' ? true : false;
            $orbital_tools_target_x = get_post_meta( $id, $this->prefix . 'orbital-tools-target-x-data', true ) === '' ? 0 : get_post_meta( $id, $this->prefix . 'orbital-tools-target-x-data', true );
            $orbital_tools_target_y = get_post_meta( $id, $this->prefix . 'orbital-tools-target-y-data', true ) === '' ? 0 : get_post_meta( $id, $this->prefix . 'orbital-tools-target-y-data', true );
            $orbital_tools_target_z = get_post_meta( $id, $this->prefix . 'orbital-tools-target-z-data', true ) === '' ? 0 : get_post_meta( $id, $this->prefix . 'orbital-tools-target-z-data', true );
            $camera_position_x = get_post_meta( $id, $this->prefix . 'camera-position-x-data', true ) === '' ? 0 : get_post_meta( $id, $this->prefix . 'camera-position-x-data', true );
            $camera_position_y = get_post_meta( $id, $this->prefix . 'camera-position-y-data', true ) === '' ? 0 : get_post_meta( $id, $this->prefix . 'camera-position-y-data', true );
            $camera_position_z = get_post_meta( $id, $this->prefix . 'camera-position-z-data', true ) === '' ? 0 : get_post_meta( $id, $this->prefix . 'camera-position-z-data', true );
            $camera_frustum = get_post_meta( $id, $this->prefix . 'camera-frustum-data', true ) === '' ? 75 : get_post_meta( $id, $this->prefix . 'camera-frustum-data', true );
            $rotate_x_animate = get_post_meta( $id, $this->prefix . 'rotation-x-animate-data', true ) === 'on' ? true : false;
            $rotate_y_animate = get_post_meta( $id, $this->prefix . 'rotation-y-animate-data', true ) === 'on' ? true : false;

            $light_color = get_post_meta( $id, $this->prefix . 'light-color-data', true ) === '' ? 0xFFFFFF : get_post_meta( $id, $this->prefix . 'light-color-data', true );
            $light_int = get_post_meta( $id, $this->prefix . 'light-intensity-data', true ) === '' ? 10 : get_post_meta( $id, $this->prefix . 'light-intensity-data', true );

            $_config_ = array(
                'file' => $file,
                'container' => $container,
                'orbital_tools' => array(
                    'enabled' => $orbital_tools_enable,
                    'zoom' => $orbital_tools_zoom,
                    'pan' => $orbital_tools_pan,
                    'target' => array(
                        'x' => $orbital_tools_target_x,
                        'y' => $orbital_tools_target_y,
                        'z' => $orbital_tools_target_z
                    ),

                ),
                'camera' => array(
                    'position' => array(
                        'x' => $camera_position_x,
                        'y' => $camera_position_y,
                        'z' => $camera_position_z
                    ),
                    'frustum' => $camera_frustum
                ),
                'light' => array(
                    'color' => $light_color,
                    'intensity' => $light_int
                ),
                'rotate_y' => $rotate_y_animate,
                'rotate_x' => $rotate_x_animate
            );

            if ($flag) {
                return $_config_;
            }else{
                $_config_json = json_encode($_config_);
                update_post_meta( $id, $this->prefix . 'model-config-obj-data', $_config_json );
            }
        }

        function save_custom_fields( $post_id, $post ) {
            if ( !isset( $_POST[ '3dm-custom-fields_wpnonce' ] ) || !wp_verify_nonce( $_POST[ '3dm-custom-fields_wpnonce' ], '3dm-custom-fields' ) ) return;
            if ( !current_user_can( 'edit_post', $post_id ) ) return;
            if ( ! in_array( $post->post_type, $this->post_type ) ) return;
            foreach ( $this->custom_fields as $custom_field ) {

                $skip_keys = array('main-model-file-data','model-config-obj-data');
                if( in_array($custom_field['name'],$skip_keys) ) continue;

                if ( current_user_can( $custom_field['capability'], $post_id ) ) {
                    if ( isset( $_POST[ $this->prefix . $custom_field['name'] ] ) && trim( $_POST[ $this->prefix . $custom_field['name'] ] ) ) {
                        $value = $_POST[ $this->prefix . $custom_field['name'] ];
                        update_post_meta( $post_id, $this->prefix . $custom_field[ 'name' ], $value );
                    } else {
                        delete_post_meta( $post_id, $this->prefix . $custom_field[ 'name' ] );
                    }
                }
            }
            $this->set_model_config($post_id);
        }

    }
}

if(class_exists('WP_ThreeDimensional_M')){
    include 'autoload.php';
    $three_d = new WP_ThreeDimensional_M($custom_fields);
    register_activation_hook( __FILE__, array($three_d, 'activiate') );
    register_deactivation_hook( __FILE__, array($three_d, 'deactivate') );
}