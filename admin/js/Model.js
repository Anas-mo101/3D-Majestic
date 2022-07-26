class ThreeD_Model_{
  constructor(config) {
    if(! this.validate_config(config)){
      console.log("config invalid")
      return;
    }

    this.config = config;
    this.rotate_y_req = false;
    this.rotate_x_req = false;
    this.controls;
    
    this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, });
    this.renderer.shadowMap.enabled = true;
    this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    this.renderer.physicallyCorrectLights = true;
    this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    this.renderer.outputEncoding = THREE.sRGBEncoding;

    this.modelDiv = document.getElementById(config.container);
    this.modelDiv.appendChild(this.renderer.domElement);
    this.renderer.setSize(this.modelDiv.offsetWidth, this.modelDiv.offsetHeight);
    this.scene = new THREE.Scene();
    this.model;

    // const color = parseInt(config.light.color.replace(/^#/, ''), 16)
    const light = new THREE.AmbientLight(config.light.color, config.light.intensity);
    this.scene.add(light);

    const loader = new THREE.GLTFLoader();

    let filename = config.file.replace(/^.*[\\\/]/, '');
    let path_to_file = config.file.substring(0, config.file.lastIndexOf("/"));

    loader.setPath(path_to_file + '/');
    loader.load(filename, (gltf) => {
      gltf.scene.traverse(c => {
        c.castShadow = false;
      });

      this.model = gltf.scene;
      this.scene.add(gltf.scene);
    });

    this.cameraProperties(config.camera);
    if(config.orbital_tools.enabled){ this.orbitTools(config.orbital_tools); }

    if(config.rotate_y){
      this.rotate_y_req = true;
      this.rotate_animate_y();
    }

    if(config.rotate_x){
      this.rotate_x_req = true;
      this.rotate_animate_x();
    }
  }

  cameraProperties(conf){
    const aspect = this.modelDiv.offsetWidth / this.modelDiv.offsetHeight;
    const near = 1;
    const far = 100.0;
    //fov = 2 * Math.atan( height / ( 2 * dist ) ) * ( 180 / Math.PI ); // in degrees
    this.camera = new THREE.PerspectiveCamera(conf.frustum, aspect, near, far);
    this.camera.position.set(conf.position.x, conf.position.y, conf.position.z);
    this.camera.lookAt(0, 0, 0);
  }

  rotate_animate_y() {
    if ( this.rotate_y_req === false ) return;
    requestAnimationFrame( () => { this.rotate_animate_y() } );
    this.renderer.render(this.scene, this.camera);
    this.model.rotation.y += 0.01;
  }

  rotate_animate_x() {
    if ( this.rotate_x_req === false ) return;
    requestAnimationFrame( () => { this.rotate_animate_x() } );
    this.renderer.render(this.scene, this.camera);
    this.model.rotation.x += 0.01;
  }

  orbitTools(conf){
    this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
    this.controls.addEventListener('change', () => { this.renderer.render(this.scene, this.camera) });
    this.controls.target.set(conf.target.x, conf.target.y, conf.target.z);
    this.controls.update();
    this.controls.enableZoom = conf.zoom;
    this.controls.enablePan = conf.pan;
  }

  validate_config(config){
      if(config.hasOwnProperty('container')){
        if(config.container === ''){
          return false;
        }
      }else{
          return false
      }

      if(config.hasOwnProperty('file')){
        if(config.file === 'null'){
          return false;
        }
      }else{
          return false
      }
      return true
  }

  set_animation(input){
    if(input.hasOwnProperty('y') && input.y === true){
      this.rotate_y_req = true;
      this.rotate_animate_y();
    }else{
      this.rotate_y_req = false;
    }

    if(input.hasOwnProperty('x') && input.x === true){
      this.rotate_x_req = true;
      this.rotate_animate_x();
    }else{
      this.rotate_x_req = false;
    }
  }

  set_orbital_tools(input){
    if(input.hasOwnProperty('enable') && input.enabled === true){
      this.controls.addEventListener('change', () => { this.renderer.render(this.scene, this.camera) });
    }else{
      this.controls.removeEventListener('change', () => { this.renderer.render(this.scene, this.camera) });
    }

    if(input.hasOwnProperty('zoom')){
      this.controls.enableZoom = input.zoom;
    }

    if(input.hasOwnProperty('pan')){
      this.controls.enablePan = input.pan;
    }
    this.controls.update();
  }
}