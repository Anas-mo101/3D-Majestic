<?php 

function create_custom_3dm_fields($fields_to_create, $threedm_prefix){ 
    global $post; 
    wp_enqueue_media();?>
    <div class="form-wrap-1">
        <?php wp_nonce_field( '3dm-custom-fields', '3dm-custom-fields_wpnonce', false, true ); ?>

        <div class="form-field form">
            <div style='display: block;' class="upload-plugin">
                <p class="install-help">Upload your model .zip file here</p>
                <p id="<?php echo $threedm_prefix . 'file-path_display'; ?>"> </p>
                <label class="screen-reader-text" for="<?php echo $threedm_prefix . 'model-file-name-data' ?>">Plugin zip file</label>
                <input type="hidden" id="<?php echo $threedm_prefix . 'model-file-name-data' ?>" name="<?php echo $threedm_prefix . 'model-file-name-data' ?>" value="<?php echo htmlspecialchars(get_post_meta( $post->ID, $threedm_prefix .'model-file-name-data', true )) ?>" accept=".zip">
                <button type="button" onclick="model_file_selector('<?php echo $post->ID ?>')" class="button button-primary button-large"> Select file </button>
            </div>
        </div>

        <?php if (get_post_meta( $post->ID, $threedm_prefix .'main-model-file-data', true ) !== '') {  ?>
            <div style="display: flex;justify-content: center;"> 
                <button type="button" onclick="edit_current_model('<?php echo $post->ID; ?>')" class="button button-primary button-large"> 
                    Edit & Preview Model
                </button>
            </div>
        <?php } ?>

        <div id="<?php echo $threedm_prefix ?>popup-underlay">
            <div id="<?php echo $threedm_prefix ?>popup-container">
                <div id="<?php echo $threedm_prefix ?>table-popup">
                    <div id="<?php echo $threedm_prefix ?>popup-topbar-container">  
                        <div id="<?php echo $threedm_prefix ?>popup-title-container"> <h2 id="<?php echo $threedm_prefix ?>popup-title"> <?php the_title(); ?> Model Preview </h2> </div>
                        <button type="button" id="<?php echo $threedm_prefix ?>popup-close-button" onclick="close_popup()"> 
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </button>
                    </div>
                    <div id="<?php echo $threedm_prefix ?>popup-table-container">
                        <div id="<?php echo $threedm_prefix . 'model-customization-panel' ?>">
                            <div style="pointer-events: none;" id="<?php echo $threedm_prefix . 'model-customization-controller' ?>">
                                <div class="three-dm-controller-container" id="<?php echo $threedm_prefix . 'model-controller-top-right' ?>">
                                    <div class="three-dm-controller-inner-container" id="<?php echo $threedm_prefix . 'model-controller-top-right-inner' ?>">
                                    <?php
                                        $checked_x = get_post_meta( $post->ID, $threedm_prefix . 'rotation-x-animate-data', true ) === 'on' ? 'checked' : '';
                                        $checked_y = get_post_meta( $post->ID, $threedm_prefix . 'rotation-y-animate-data', true ) === 'on' ? 'checked' : '';
                                    ?>
                                        <p style=" text-align: center; font-weight: 600; color: #656565;">Animations</p>
                                        <div style="display: flex; justify-content: center; gap: 20px;align-items: center;">
                                            <p style="margin: 5px 0;color: #656565;">Rotate on X</p>
                                            <input class="three-dm-model-controls" <?php echo $checked_x ?> type="checkbox" name="<?php echo $threedm_prefix .'rotation-x-animate-data' ?>" id="<?php echo $threedm_prefix .'rotation-x-animate-data' ?>">
                                        </div>
                                        <div style="display: flex; justify-content: center; gap: 20px;align-items: center;">
                                            <p style="margin: 5px 0;color: #656565;">Rotate on Y</p>
                                            <input class="three-dm-model-controls" <?php echo $checked_y ?> type="checkbox" name="<?php echo $threedm_prefix .'rotation-y-animate-data' ?>" id="<?php echo $threedm_prefix .'rotation-y-animate-data' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="three-dm-controller-container" id="<?php echo $threedm_prefix . 'model-controller-top-left' ?>">
                                    <div class="three-dm-controller-inner-container" id="<?php echo $threedm_prefix . 'model-controller-top-left-inner' ?>">
                                    <?php
                                        $checked_en = get_post_meta( $post->ID, $threedm_prefix . 'orbital-tools-enabled-data', true ) === 'on' ? 'checked' : '';
                                        $checked_zoom = get_post_meta( $post->ID, $threedm_prefix . 'orbital-tools-zoom-data', true ) === 'on' ? 'checked' : '';
                                        $checked_pan = get_post_meta( $post->ID, $threedm_prefix . 'orbital-tools-pan-data', true ) === 'on' ? 'checked' : '';
                                    ?>
                                        <p style=" text-align: center; font-weight: 600; color: #656565;">Orbital Tools</p>
                                        <div style="display: flex; justify-content: center; gap: 20px;align-items: center;">
                                            <p style="margin: 5px 0;color: #656565;">Enable Orbital Tools</p>
                                            <input class="three-dm-model-controls" <?php echo $checked_en ?> type="checkbox" name="<?php echo $threedm_prefix .'orbital-tools-enabled-data' ?>" id="<?php echo $threedm_prefix .'orbital-tools-enabled-data' ?>">
                                        </div>
                                        <div style="display: flex; justify-content: center; gap: 20px;align-items: center;">
                                            <p style="margin: 5px 0;color: #656565;">Enable Zoom</p>
                                            <input class="three-dm-model-controls" <?php echo $checked_zoom ?> type="checkbox" name="<?php echo $threedm_prefix .'orbital-tools-zoom-data' ?>" id="<?php echo $threedm_prefix .'orbital-tools-zoom-data' ?>">
                                        </div>
                                        <div style="display: flex; justify-content: center; gap: 20px;align-items: center;">
                                            <p style="margin: 5px 0;color: #656565;">Enable Pan</p>
                                            <input class="three-dm-model-controls" <?php echo $checked_pan ?> type="checkbox" name="<?php echo $threedm_prefix .'orbital-tools-pan-data' ?>" id="<?php echo $threedm_prefix .'orbital-tools-pan-data' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="three-dm-controller-container" id="<?php echo $threedm_prefix . 'model-controller-bottom-right' ?>">
                                    <div class="three-dm-controller-inner-container" id="<?php echo $threedm_prefix . 'model-controller-bottom-right-inner' ?>">
                                        <p style=" text-align: center; font-weight: 600; color: #656565;">Camera</p>
                                    </div>
                                </div>
                                <div class="three-dm-controller-container" id="<?php echo $threedm_prefix . 'model-controller-bottom-left' ?>">
                                    <div class="three-dm-controller-inner-container" id="<?php echo $threedm_prefix . 'model-controller-bottom-left-inner' ?>">
                                        <p style=" text-align: center; font-weight: 600; color: #656565;">Lights</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="<?php echo $threedm_prefix ?>popup-bottombar-container">
                        <button id="<?php echo $threedm_prefix ?>popup-submit-button"> Save </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php } ?>



