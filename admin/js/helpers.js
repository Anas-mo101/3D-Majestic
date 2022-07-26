var media_selector_frame;
const model_file_selector = (pid) => {
    if (media_selector_frame)  media_selector_frame = null;
    
    media_selector_frame = wp.media({
        title: 'Select Model file',
        button: { text: 'Insert' },
        multiple: false,
        library: { type: 'application/zip'}
    }).on('select', function () {
        var attachment = media_selector_frame.state().get('selection').first().toJSON();

        let data = {
            action: 'gmfi',
            model_url: attachment.url,
            post_id: pid
        }
        get_model_config(data);
    });
    media_selector_frame.open();
}

const edit_current_model = (pid) => {
    let data = {
        action: 'gmfi',
        model_url: 'null',
        post_id: pid
    }
    get_model_config(data);
}

const close_popup = () => {
    jQuery( document ).ready( function( $ ) { 
        $('#three-dm-popup-underlay').css('display', 'none');
    });
}

const preview_model = (config) => {
    const controller = document.getElementById(`three-dm-model-customization-controller`);
    document.getElementById(`three-dm-model-customization-panel`).innerHTML = '';
    document.getElementById(`three-dm-model-customization-panel`).innerHTML = controller.outerHTML;
    // config.orbital_tools.enabled = true;
    // config.orbital_tools.zoom = true;
    // config.camera.position.x = 10;
    // config.camera.position.y = 10;
    let threedm = new ThreeD_Model_(config);
    new ThreeD_Controller_(threedm);
}

const get_model_config = (data) => {
    jQuery( document ).ready( function( $ ) { 
        $.ajaxSetup({ cache: false });
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data,
            cache: false,
            success: function( res ) {
                console.log(res);
                if (res.model_validity) {
                    document.getElementById('three-dm-model-file-name-data').value = data.model_url === 'null' ? document.getElementById('three-dm-model-file-name-data').value : data.model_url;
                    $('#three-dm-popup-underlay').css('display', 'block');
                    preview_model(res.model_config);
                }
            }
        })
        .fail(function(error) {
            console.log("response failed");
            console.log(error);
        });
    });
}

