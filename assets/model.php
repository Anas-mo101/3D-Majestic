<?php 


function set_model_template($id){ 
    $config = get_post_meta($id, "three-dm-model-config-obj-data", true); 
    ?> 

    <div id='3dm-<?php echo $id ?>' ></div> 

    <script defer type="module"> 
        <?php echo 'const threedm_config_' . $id . ' = `' . $config . '`; ';  ?>

        const threedm_cooked_config_<?php echo $id ?> = threedm_config_<?php echo $id ?> == '' || threedm_config_<?php echo $id ?> == null ? false : JSON.parse(threedm_config_<?php echo $id ?>); 
        
        if(threedm_cooked_config_<?php echo $id ?>){
            new ThreeD_Model_(threedm_cooked_config_<?php echo $id ?>);
        }
        
    </script>   <?php
}