let initialFormState = "";
const form = document.getElementById('formEditar');
const saveBtn = document.getElementById('btnGuardar');

function getSnapshot() {
    const formData = new FormData(form);
    formData.append('tmp_sede', document.getElementById('sel_sede').value);
    formData.append('tmp_edificio', document.getElementById('sel_edificio').value);
    formData.append('tmp_division', document.getElementById('sel_division').value);
    
    const fileInput = document.getElementById('imagen_dispositivo');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('tmp_file', fileInput.files[0].name);
    }
    
    return JSON.stringify(Object.fromEntries(formData));
}

function checkChanges() {
    saveBtn.disabled = (getSnapshot() === initialFormState);
}

document.addEventListener('DOMContentLoaded', function() {
    const sSel = document.getElementById('sel_sede');
    
    if (typeof sedes !== 'undefined' && sSel) {
        sedes.forEach(s => sSel.add(new Option(s.GLOSA_FISCALIA, s.ID_SEDE)));
        sSel.value = currentSede;

        updateEdificios(false); 
        document.getElementById('sel_edificio').value = currentEdificio;

        updateDivisiones(false); 
        document.getElementById('sel_division').value = currentDivision;

        updateUbicaciones(false);
        document.getElementById('sel_ubicacion').value = currentUbicacion;

        initialFormState = getSnapshot();
        saveBtn.disabled = true;

        form.addEventListener('input', checkChanges);
        form.querySelectorAll('select').forEach(s => s.addEventListener('change', checkChanges));
    }
    
    // Lógica para hacer clic en el cuadro de imagen y previsualizar
    const wrapperImagen = document.getElementById('wrapper_imagen');
    const inputImagen = document.getElementById('imagen_dispositivo');
    const previewImagen = document.getElementById('preview_imagen');
    const placeholderImagen = document.getElementById('placeholder_imagen');

    if (wrapperImagen && inputImagen) {
        wrapperImagen.addEventListener('click', () => {
            inputImagen.click();
        });

        inputImagen.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImagen.src = e.target.result;
                    previewImagen.style.display = 'block';
                    if (placeholderImagen) placeholderImagen.style.display = 'none';
                }
                reader.readAsDataURL(file);
                checkChanges(); // Activa el botón de guardar
            }
        });
    }
});

function updateEdificios(resetChildren = true) {
    const id = document.getElementById('sel_sede').value;
    const target = document.getElementById('sel_edificio');
    populate(target, edificios.filter(x => x.ID_SEDE == id), 'ID_EDIFICIO', 'GLOSA_EDIFICIO');
    if(resetChildren) { 
        reset(document.getElementById('sel_division')); 
        reset(document.getElementById('sel_ubicacion'));
        checkChanges();
    }
}

function updateDivisiones(resetChildren = true) {
    const id = document.getElementById('sel_edificio').value;
    const target = document.getElementById('sel_division');
    populate(target, divisiones.filter(x => x.ID_EDIFICIO == id), 'ID_DIVISION', 'GLOSA_DIVISION');
    if(resetChildren) { 
        reset(document.getElementById('sel_ubicacion'));
        checkChanges();
    }
}

function updateUbicaciones(resetChildren = true) {
    const id = document.getElementById('sel_division').value;
    const target = document.getElementById('sel_ubicacion');
    populate(target, ubicaciones.filter(x => x.DIVISION_UBICACION == id), 'ID_UBICACION', 'GLOSA_UBICACION');
    if(resetChildren) checkChanges();
}

function populate(sel, data, valKey, textKey) {
    if(!sel) return;
    sel.innerHTML = '<option value="">Seleccione...</option>';
    if(data.length > 0) {
        sel.disabled = false;
        data.forEach(x => sel.add(new Option(x[textKey], x[valKey])));
    } else {
        sel.disabled = true;
    }
}

function reset(sel) { 
    if(sel) { sel.innerHTML='<option value="">-</option>'; sel.disabled = true; }
}