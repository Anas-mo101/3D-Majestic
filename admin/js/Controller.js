class ThreeD_Controller_{
    constructor(model) {
        this.model = model;
        this.controls = document.getElementsByClassName('three-dm-model-controls');
        Array.from(this.controls).forEach((element) => {
            element.addEventListener('input', (e) => {
                this.do_action(e.target);
            });
        });
    }

    do_action(control){
        let x_val = control.checked; //control.value == 'on' ? true : false
        switch (control.id) {
            case 'three-dm-rotation-x-animate-data':
                this.model.set_animation({
                    x: x_val
                });
                break;
            case 'three-dm-rotation-y-animate-data':
                this.model.set_animation({
                    y: x_val
                });
                break;
            case 'three-dm-orbital-tools-enabled-data':
                this.model.set_orbital_tools({
                    enabled: x_val
                });
                break;
            case 'three-dm-orbital-tools-zoom-data':
                this.model.set_orbital_tools({
                    zoom: x_val
                });
                break;
            case 'three-dm-orbital-tools-pan-data':
                this.model.set_orbital_tools({
                    pan: x_val
                });
                break;
            default: break;
        }
    }
}


