<?php include 'includes/api_config.php'; ?>
<script src="online.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="police.css">

<script type="importmap">
{
    "imports": {
        "three": "https://unpkg.com/three@0.158.0/build/three.module.js",
        "three/addons/": "https://unpkg.com/three@0.158.0/examples/jsm/"
    }
}
</script>

<style>
#scene3d {
    width: 65vw;
    height: 75vh;

    border: 2px solid black;
    border-radius: 15px;
    overflow: hidden;

    cursor: move;
}

.affichage {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 2%;
}

.coords {
    display: flex;
    flex-direction: column;
    gap: 15px;
    font-family: Arial;
    min-width: 160px;
}

.coords div {
    background: #f0f0f0;
    color: black;

    padding: 8px 10px;
    border-radius: 8px;

    font-size: 18px;
}
.coords button {
    padding: 8px;
    font-size: 14px;
}

#bouton {
    margin-top : 15%;
}

#bouton,
#bouton2 {
    padding: 8px 12px;

    background: black;
    color: white;

    border: 1px solid black;
    border-radius: 8px;

    cursor: pointer;
}

#bouton:hover,
#bouton2:hover {
    background: white;
    color: black;
}

.mb-custom{
    margin-bottom: 2rem
}
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h1 class='mb-custom text-center ms-4 mb-custom' style='font-size:50px' data-i18n>Decouverte 3D</h1>
    
    <p class="text-center mb-custom">
        Bienvenue dans la découverte en 3D.
        <br>Utilisez votre souris pour vous déplacer librement et explorer la maison à votre rythme.
        <br>Vous pouvez simuler une extinction des lumières, ainsi que réinitialiser la position pour revenir à son état initial.
    </p>

    <div class="affichage">
        <div id="scene3d"></div>

        <div class="coords">
            <div id="xc">X: 0</div>
            <div id="yc">Y: 0</div>
            <div id="zc">Z: 0</div>
            <button id="bouton">Eteindre la lumière</button>
            <button id="bouton2">Reinitialiser la position</button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php';?>

<script type="module">
    import * as THREE from 'three';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

    const container = document.getElementById("scene3d");
    
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf0f0f0);

    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(10, 0, 0);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    container.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);

    let lumiere_hemisphere  = new THREE.HemisphereLight(0xffffff, 0xbbbbbb, 0.8);
    scene.add(lumiere_hemisphere);
    let lumiere_direction = new THREE.DirectionalLight(0xffffff, 1);
    lumiere_direction.position.set(5, 10, 5);
    scene.add(lumiere_direction);
    
    const loader = new GLTFLoader();
    loader.load('maison.glb', (gltf) => {
        const model = gltf.scene;
        
        const box = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        model.position.sub(center); 
        
        scene.add(model);
    });

function etat_lumiere(){
    let intensiteActuelle = lumiere_direction.intensity;

    if (intensiteActuelle > 0.2) {
        lumiere_direction.intensity = 0.2;
        lumiere_hemisphere.intensity = 0.15;
        document.getElementById('bouton').textContent = "Allumer la lumière"
        scene.background = new THREE.Color(0x050505);
    } else {
        lumiere_direction.intensity = 1;
        lumiere_hemisphere.intensity = 0.8;
        document.getElementById('bouton').textContent = "Eteindre la lumière"
        scene.background = new THREE.Color(0xf0f0f0);
    }
}

function reset_position(){
    camera.position.set(10, 0, 0);
    controls.target.set(0, 0, 0); // ou le centre de ta scène
}

const xdiv = document.getElementById("xc")
const ydiv = document.getElementById("yc")
const zdiv = document.getElementById("zc")
function animate() {
    requestAnimationFrame(animate);
    controls.update(); 
    

    const posX = camera.position.x.toFixed(2);
    const posY = camera.position.y.toFixed(2);
    const posZ = camera.position.z.toFixed(2);
    xdiv.innerHTML = `X : ${posX}`;
    ydiv.innerHTML = `Y : ${posY}`;
    zdiv.innerHTML = `Z : ${posZ}`;
    
    renderer.render(scene, camera);
}

function onResize() {
    const width = container.clientWidth;
    const height = container.clientHeight;

    camera.aspect = width / height;
    camera.updateProjectionMatrix();

    renderer.setSize(width, height);
}

    animate();
    document.getElementById('bouton').addEventListener('click', etat_lumiere);
    document.getElementById('bouton2').addEventListener('click', reset_position);
    window.addEventListener('resize', onResize);
</script>
</body>
</html>