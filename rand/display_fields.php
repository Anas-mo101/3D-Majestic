<?php 

function create_custom_3dm_fields($fields_to_create, $threedm_prefix, $wrap_no='', $nonce_val=''){ 
    global $post; ?>
    <div class="form-wrap-<?php echo $wrap_no;?>">
        <?php wp_nonce_field( $nonce_val, $nonce_val.'_wpnonce', false, true );
        foreach ( $fields_to_create as $custom_field ) {
            // Check scope
            $scope = $custom_field[ 'scope' ];
            $output = false;
            foreach ( $scope as $scopeItem ) {
                switch ( $scopeItem ) {
                    default: {
                        if ( $post->post_type == $scopeItem )
                            $output = true;
                        break;
                    }
                }
                if ( $output ) break;
            }
            // Check capability
            if ( !current_user_can( $custom_field['capability'], $post->ID ) )
                $output = false;
            // Output if allowed
            if ( $output ) {  ?>
                <div class="form-field form">
                    <?php
                    switch ( $custom_field[ 'type' ] ) {
                        case "text": {
                            echo '<label for="' . $threedm_prefix . $custom_field[ 'name' ] .'"><b>' . $custom_field[ 'title' ] . '</b></label>';
                            echo '<input type="text" name="' . $threedm_prefix . $custom_field[ 'name' ] . '" id="' . $threedm_prefix . $custom_field[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true ) ) . '" />';
                            break;
                        }
                        case "upload": {
                            wp_enqueue_media(); 
                            echo '<label for="' . $threedm_prefix . $custom_field[ 'name' ] .'"><b>' . $custom_field[ 'title' ] . '</b></label>';
                            echo '<div style="display: flex; gap: 10px;"> <input type="url" name="'.$threedm_prefix . $custom_field[ 'name' ].'" id="' . $threedm_prefix . $custom_field[ 'name' ].'"  value="' . htmlspecialchars( get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true ) ) . '" style="width: 100%;" />';
                            echo '<button type="button" onclick="mediaFileSelector(`'.$threedm_prefix . $custom_field[ 'name' ].'`)" class="button button-primary button-large"> Select file </button> </div>';
                            break;
                        }
                        case "checkbox": {
                            $checked = get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true ) === 'on' ? 'checked' : '';
                            echo '<label for="' . $threedm_prefix . $custom_field[ 'name' ] .'"><b>' . $custom_field[ 'title' ] . '</b></label>';
                            echo '<input type="checkbox" name="' . $threedm_prefix . $custom_field[ 'name' ] . '" id="' . $threedm_prefix . $custom_field[ 'name' ] . '" ' . $checked . '/>';
                            break;
                        }
                        case "range": {
                            $range = get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true ) === '' ? 0 : get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true );
                            echo '<label for="' . $threedm_prefix . $custom_field[ 'name' ] .'"><b>' . $custom_field[ 'title' ] . '</b></label>';
                            echo '<input type="range" name="' . $threedm_prefix . $custom_field[ 'name' ] . '" id="' . $threedm_prefix . $custom_field[ 'name' ] . '" value="' . $range . '" />';
                            break;
                        }
                        case "int": {
                            echo '<label for="' . $threedm_prefix . $custom_field[ 'name' ] .'"><b>' . $custom_field[ 'title' ] . '</b></label>';
                            echo '<input type="number" name="' . $threedm_prefix . $custom_field[ 'name' ] . '" id="' . $threedm_prefix . $custom_field[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true ) ) . '" />';
                            break;
                        }
                        case "hidden": {
                            echo '<input type="hidden" name="' . $threedm_prefix . $custom_field[ 'name' ] . '" id="' . $threedm_prefix . $custom_field[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $threedm_prefix . $custom_field[ 'name' ], true ) ) . '" />';
                            break;
                        }
                    } 
                    if ( $custom_field[ 'description' ] ) echo '<p style="margin: 0;">' . $custom_field[ 'description' ] . '</p>'; ?>
                </div>
            <?php
            }
        } ?>
    </div>
<?php } ?>



