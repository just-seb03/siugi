function resetForms() {
    document.getElementById('selector_entidad').value = "";
    document.getElementById('grupo_buscador_avanzado').classList.add('anim-hide');
    document.getElementById('container_formularios').classList.add('anim-hide');
    document.querySelectorAll('.entity-form').forEach(el => el.classList.add('anim-hide'));
}

function previewImagenSeleccionada(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar_preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}

function actualizarRutaPreviewSiAgregando(usuarioInput) {
    const preview = document.getElementById('avatar_preview');
    
    if (usuarioInput.trim() === '') {
       preview.src = 'https://ui-avatars.com/api/?name=U&background=cbd5e1&color=fff&size=120';
       return;
    }

    const nick = usuarioInput.toLowerCase();
    const initial = nick.charAt(0).toUpperCase();
    const timestamp = new Date().getTime(); 
    
    preview.onerror = function() {
        this.src = `https://ui-avatars.com/api/?name=${initial}&background=cbd5e1&color=fff&size=120`;
    };
    
    preview.src = `/SIUGI/public/avatar/${nick}.jpg?v=${timestamp}`;
}

function mostrarFormulario() {
    const accion = document.getElementById('selector_accion').value;
    const tipo = document.getElementById('selector_entidad').value;
    const buscador = document.getElementById('buscador_elemento');
    const btn = document.getElementById('btn_submit');
    const containerForms = document.getElementById('container_formularios');
    const buscadorAvanzado = document.getElementById('grupo_buscador_avanzado');
    
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';

    if (!tipo) {
        buscadorAvanzado.classList.add('anim-hide');
        containerForms.classList.add('anim-hide');
        return;
    }

    let icon = 'fa-plus'; let btnClass = 'btn-agregar'; let textAction = 'Guardar Nuevo';
    if(accion === 'anular') { icon = 'fa-trash-alt'; btnClass = 'btn-anular'; textAction = 'Confirmar Anulación de'; }
    else if(accion === 'modificar') { icon = 'fa-save'; btnClass = 'btn-modificar'; textAction = 'Guardar Cambios en'; }
    
    btn.className = `btn-submit-custom mt-4 ${btnClass}`;
    btn.innerHTML = `<i class="fas ${icon}"></i> ${textAction} Registro`;

    if (accion !== 'agregar') {
        buscadorAvanzado.classList.remove('anim-hide');
        containerForms.classList.add('anim-hide');
        
        buscador.innerHTML = '<option value="">Seleccione elemento...</option>';
        construirFiltrosBusqueda(tipo);
    } else {
        buscadorAvanzado.classList.add('anim-hide');
        
        document.querySelectorAll('.entity-form').forEach(el => el.classList.add('anim-hide'));
        document.getElementById('form_' + tipo).classList.remove('anim-hide');
        
        limpiarInputsFormulario();
        
        if (tipo === 'usuario') {
            document.getElementById('avatar_preview').src = 'https://ui-avatars.com/api/?name=U&background=cbd5e1&color=fff&size=120';
            document.getElementById('box_eliminar_foto').style.display = 'none';
            document.getElementById('foto_usuario').value = '';
            
            document.getElementById('panel_ubicacion_inventario').style.display = 'block';
            document.getElementById('usr_id').readOnly = false;
        }
        
        containerForms.classList.remove('anim-hide');
    }
}

function construirFiltrosBusqueda(tipo) {
    const container = document.getElementById('filtros_dinamicos');
    container.innerHTML = '';
    
    if (tipo === 'usuario') {
        container.innerHTML = `
            <div class="input-group-custom">
                <label>Filtrar por Sede</label>
                <select id="filter_sede" onchange="actualizarDropdownFinal()">
                    <option value="">Todas las Sedes...</option>
                </select>
            </div>
            <div class="input-group-custom">
                <label>Filtrar por Tipo</label>
                <select id="filter_tipo" onchange="actualizarDropdownFinal()">
                    <option value="">Todos los Tipos...</option>
                    <option value="1">Fiscal</option>
                    <option value="2">Funcionario</option>
                    <option value="3">Alumno</option>
                </select>
            </div>
        `;
        DB.sede.forEach(s => document.getElementById('filter_sede').add(new Option(s.GLOSA_FISCALIA, s.ID_SEDE)));
    } else {
        container.innerHTML = '<span class="text-muted" style="font-size: 0.8rem; font-style: italic; grid-column: 1/-1;">No hay filtros requeridos. Seleccione directamente el elemento en la lista inferior.</span>';
    }
    
    actualizarDropdownFinal();
}

function actualizarDropdownFinal() {
    const tipo = document.getElementById('selector_entidad').value;
    let data = DB[tipo];

    if (tipo === 'usuario') {
        const fSede = document.getElementById('filter_sede') ? document.getElementById('filter_sede').value : '';
        const fTipo = document.getElementById('filter_tipo') ? document.getElementById('filter_tipo').value : '';

        if (fSede) data = data.filter(x => x.cod_fiscalia == fSede);
        if (fTipo) data = data.filter(x => x.tipo_usuario == fTipo);
    }

    const buscador = document.getElementById('buscador_elemento');
    buscador.innerHTML = '<option value="">Seleccione elemento final...</option>';
    
    data.forEach(item => {
        let label = item.nombre || item.GLOSA_SOFTWARE || item.GLOSA_FISCALIA;
        let id = item.id || item.ID_SOFTWARE || item.ID_SEDE;
        if(tipo === 'usuario') label = `${label} (${item.rut})`;
        
        buscador.add(new Option(label, id));
    });
    
    document.getElementById('container_formularios').classList.add('anim-hide');
}

function cargarDatosElemento() {
    const tipo = document.getElementById('selector_entidad').value;
    const id = document.getElementById('buscador_elemento').value;
    const accion = document.getElementById('selector_accion').value;
    const containerForms = document.getElementById('container_formularios');
    
    if (!id) {
        containerForms.classList.add('anim-hide');
        return;
    }

    limpiarInputsFormulario(accion === 'anular');
    document.querySelectorAll('.entity-form').forEach(el => el.classList.add('anim-hide'));
    document.getElementById('form_' + tipo).classList.remove('anim-hide');

    const data = DB[tipo].find(x => (x.id || x.ID_SOFTWARE || x.ID_SEDE) == id);
    
    if (tipo === 'sede') document.getElementById('input_glosa_sede').value = data.GLOSA_FISCALIA;
    if (tipo === 'software') {
        document.getElementById('input_glosa_software').value = data.GLOSA_SOFTWARE;
        document.getElementById('input_estado_software').value = data.ESTADO_SOFTWARE;
    }
    if (tipo === 'usuario') {
        document.getElementById('usr_id').value = data.id || '';
        document.getElementById('usr_id').readOnly = true;

        document.getElementById('usr_nombre').value = data.nombre || '';
        document.getElementById('usr_rut').value = data.rut || '';
        document.getElementById('usr_usuario').value = data.usuario || '';
        document.getElementById('usr_sede').value = data.cod_fiscalia || '0';
        document.getElementById('usr_correo').value = data.correo_electronico || '';
        document.getElementById('usr_cargo').value = data.cargo || '';
        document.getElementById('usr_ip').value = data.ip || '';

        document.getElementById('usr_telefono').value = data.telefono || '';
        document.getElementById('usr_cod_unidad').value = data.cod_unidad || '0';
        document.getElementById('usr_tipo').value = data.tipo_usuario || '';
        document.getElementById('usr_estado').value = data.estado || '0';
        document.getElementById('usr_fiscal_func').value = data.fiscal_func || '';
        
        document.getElementById('usr_mostrar_intranet').value = data.mostrar_intranet !== undefined && data.mostrar_intranet !== null ? data.mostrar_intranet : '';

        document.getElementById('usr_fec_nac').value = data.fec_nacimiento ? data.fec_nacimiento.split(' ')[0] : '';
        document.getElementById('usr_fec_ini').value = data.fec_inicio_funciones ? data.fec_inicio_funciones.split(' ')[0] : '';
        document.getElementById('usr_fec_fin').value = data.fec_termino_funciones ? data.fec_termino_funciones.split(' ')[0] : '';

        let nick = (data.usuario || '').toLowerCase();
        let cacheBuster = new Date().getTime(); 
        let preview = document.getElementById('avatar_preview');
        
        preview.onerror = function() {
            let initial = nick ? nick.charAt(0).toUpperCase() : 'U';
            this.src = `https://ui-avatars.com/api/?name=${initial}&background=cbd5e1&color=fff&size=120`;
        };
        
        preview.src = `/SIUGI/public/avatar/${nick}.jpg?v=${cacheBuster}`;
        document.getElementById('foto_usuario').value = '';
        
        if (accion === 'modificar') {
            document.getElementById('box_eliminar_foto').style.display = 'flex';
            document.getElementById('eliminar_foto').checked = false;
        } else {
            document.getElementById('box_eliminar_foto').style.display = 'none';
        }

        document.getElementById('panel_ubicacion_inventario').style.display = 'none';
    }
    
    containerForms.classList.remove('anim-hide');
}

function limpiarInputsFormulario(disabled = false) {
    const inputs = document.querySelectorAll('.entity-form input:not([type="checkbox"]), .entity-form select');
    inputs.forEach(i => {
        i.disabled = disabled;
        i.value = ''; 
    });
    
    const foto = document.getElementById('foto_usuario');
    const delFoto = document.getElementById('eliminar_foto');
    if (foto) foto.disabled = disabled;
    if (delFoto) delFoto.disabled = disabled;
}

function updateInvEdificios() {
    const idSede = document.getElementById('inv_sede').value;
    const target = document.getElementById('inv_edificio');
    target.innerHTML = '<option value="">Seleccione Edificio...</option>';
    target.disabled = !idSede;
    
    if(idSede) DB_INV.edificio.filter(x => x.ID_SEDE == idSede).forEach(x => target.add(new Option(x.GLOSA_EDIFICIO, x.ID_EDIFICIO)));
    
    const divTarget = document.getElementById('inv_division');
    divTarget.innerHTML = '<option value="">Seleccione Edificio primero</option>';
    divTarget.disabled = true;
}

function updateInvDivisiones() {
    const idEdi = document.getElementById('inv_edificio').value;
    const target = document.getElementById('inv_division');
    target.innerHTML = '<option value="">Seleccione División...</option>';
    target.disabled = !idEdi;
    
    if(idEdi) DB_INV.division.filter(x => x.ID_EDIFICIO == idEdi).forEach(x => target.add(new Option(x.GLOSA_DIVISION, x.ID_DIVISION)));
}