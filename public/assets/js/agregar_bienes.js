

document.addEventListener("DOMContentLoaded", () => {

    const selSede = document.getElementById('sel_sede');
    if (selSede && typeof sedesData !== 'undefined') {
        sedesData.forEach(s => selSede.add(new Option(s.GLOSA_FISCALIA, s.ID_SEDE)));
    }


    if (typeof autoData !== 'undefined' && autoData.sede) {
        document.getElementById('sel_sede').value = autoData.sede;
        updateEdificios();
        document.getElementById('sel_edificio').value = autoData.edificio;
        updateDivisiones();
        document.getElementById('sel_division').value = autoData.division;
        updateUbicaciones();
        document.getElementById('sel_ubicacion').value = autoData.ubicacion;
    }
});


function updateEdificios() {
    const id = document.getElementById('sel_sede').value;
    const target = document.getElementById('sel_edificio');
    resetSelect(target); 
    resetSelect(document.getElementById('sel_division')); 
    resetSelect(document.getElementById('sel_ubicacion'));
    
    if(id && typeof edificiosData !== 'undefined') { 
        target.disabled = false; 
        edificiosData.filter(x => x.ID_SEDE == id).forEach(x => target.add(new Option(x.GLOSA_EDIFICIO, x.ID_EDIFICIO))); 
    }
}

function updateDivisiones() {
    const id = document.getElementById('sel_edificio').value;
    const target = document.getElementById('sel_division');
    resetSelect(target); 
    resetSelect(document.getElementById('sel_ubicacion'));
    
    if(id && typeof divisionesData !== 'undefined') { 
        target.disabled = false; 
        divisionesData.filter(x => x.ID_EDIFICIO == id).forEach(x => target.add(new Option(x.GLOSA_DIVISION, x.ID_DIVISION))); 
    }
}

function updateUbicaciones() {
    const id = document.getElementById('sel_division').value;
    const target = document.getElementById('sel_ubicacion');
    resetSelect(target);
    
    if(id && typeof ubicacionesData !== 'undefined') { 
        target.disabled = false; 
        ubicacionesData.filter(x => x.DIVISION_UBICACION == id).forEach(x => target.add(new Option(x.GLOSA_UBICACION, x.ID_UBICACION))); 
    }
}

function resetSelect(el) { 
    if(el) {
        el.innerHTML='<option value="">- Seleccione anterior -</option>'; 
        el.disabled = true; 
    }
}


function toggleMasivo() { 
    const isChecked = document.getElementById('tipoRegistro').checked; 
    document.getElementById('es_masivo_hidden').value = isChecked ? "1" : "0"; 
    
    const contUbicaciones = document.getElementById('ubicaciones_container');
    const contDetalles = document.getElementById('detalles_container');
    const contObservacion = document.getElementById('observacion_container');
    const contCantidad = document.getElementById('cantidad_container');
    
    if (isChecked) {
        contUbicaciones.classList.replace('anim-show', 'anim-hide');
        contDetalles.classList.replace('anim-show', 'anim-hide');
        contObservacion.classList.replace('anim-show', 'anim-hide');
        contCantidad.classList.replace('anim-hide', 'anim-show');
        document.querySelectorAll('.req-ubicacion').forEach(el => el.required = false);
    } else {
        contCantidad.classList.replace('anim-show', 'anim-hide');
        contUbicaciones.classList.replace('anim-hide', 'anim-show');
        contDetalles.classList.replace('anim-hide', 'anim-show');
        contObservacion.classList.replace('anim-hide', 'anim-show');
        document.querySelectorAll('.req-ubicacion').forEach(el => el.required = true);
    }
}