

function resetForms() {
    document.getElementById('selector_entidad').value = "";
    document.getElementById('grupo_buscador_avanzado').classList.add('anim-hide');
    document.getElementById('container_formularios').classList.add('anim-hide');
    document.getElementById('alerta_dependencias').classList.add('anim-hide');
    document.getElementById('alerta_bienes').classList.add('anim-hide');
    
    document.querySelectorAll('.entity-form').forEach(el => el.classList.add('anim-hide'));
}

function mostrarFormulario() {
    const accion = document.getElementById('selector_accion').value;
    const tipo = document.getElementById('selector_entidad').value;
    const buscador = document.getElementById('buscador_elemento');
    const btn = document.getElementById('btn_submit');
    const containerForms = document.getElementById('container_formularios');
    const buscadorAvanzado = document.getElementById('grupo_buscador_avanzado');
    
    document.getElementById('alerta_dependencias').classList.add('anim-hide');
    document.getElementById('alerta_bienes').classList.add('anim-hide');
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
        cargarCombosDependenciaFormulario();
        containerForms.classList.remove('anim-hide');
    }
}

function construirFiltrosBusqueda(tipo) {
    const container = document.getElementById('filtros_dinamicos');
    container.innerHTML = '';
    
    if (['ubicacion', 'division', 'edificio'].includes(tipo)) {
        let html = `<div class="input-group-custom"><label>Filtrar por Sede</label><select id="filter_sede" onchange="onSearchFilterChange('sede')"><option value="">Todas las Sedes...</option></select></div>`;
        if (tipo === 'ubicacion' || tipo === 'division') {
            html += `<div class="input-group-custom"><label>Filtrar por Edificio</label><select id="filter_edificio" onchange="onSearchFilterChange('edificio')"><option value="">Todos los Edificios...</option></select></div>`;
        }
        if (tipo === 'ubicacion') {
            html += `<div class="input-group-custom"><label>Filtrar por División</label><select id="filter_division" onchange="onSearchFilterChange('division')"><option value="">Todas las Divisiones...</option></select></div>`;
        }
        container.innerHTML = html;
        
        DB.sede.forEach(s => document.getElementById('filter_sede').add(new Option(s.GLOSA_FISCALIA, s.ID_SEDE)));
        if (document.getElementById('filter_edificio')) DB.edificio.forEach(e => document.getElementById('filter_edificio').add(new Option(e.GLOSA_EDIFICIO, e.ID_EDIFICIO)));
        if (document.getElementById('filter_division')) DB.division.forEach(d => document.getElementById('filter_division').add(new Option(d.GLOSA_DIVISION, d.ID_DIVISION)));

    } else if (tipo === 'subcategoria') {
        container.innerHTML = `<div class="input-group-custom"><label>Filtrar por Categoría</label><select id="filter_categoria" onchange="onSearchFilterChange('categoria')"><option value="">Todas las Categorías...</option></select></div>`;
        DB.categoria.forEach(c => document.getElementById('filter_categoria').add(new Option(c.GLOSA_CATEGORIA, c.ID_CAT)));
    } else {
        container.innerHTML = '<span class="text-muted" style="font-size: 0.8rem; font-style: italic; grid-column: 1/-1;">No hay filtros previos requeridos para esta entidad. Seleccione directamente el elemento en la lista inferior.</span>';
    }
    
    actualizarDropdownFinal();
}

function onSearchFilterChange(changedFilter) {
    if (changedFilter === 'sede') {
        const fSede = document.getElementById('filter_sede').value;
        const selEdi = document.getElementById('filter_edificio');
        if (selEdi) {
            selEdi.innerHTML = '<option value="">Todos los Edificios...</option>';
            if (fSede) { DB.edificio.filter(e => e.ID_SEDE == fSede).forEach(e => selEdi.add(new Option(e.GLOSA_EDIFICIO, e.ID_EDIFICIO))); } 
            else { DB.edificio.forEach(e => selEdi.add(new Option(e.GLOSA_EDIFICIO, e.ID_EDIFICIO))); }
        }
        
        const selDiv = document.getElementById('filter_division');
        if (selDiv) {
            selDiv.innerHTML = '<option value="">Todas las Divisiones...</option>';
            if(!fSede) { DB.division.forEach(d => selDiv.add(new Option(d.GLOSA_DIVISION, d.ID_DIVISION))); } 
            else {
                const edisSede = DB.edificio.filter(e => e.ID_SEDE == fSede).map(e => e.ID_EDIFICIO);
                DB.division.filter(d => edisSede.includes(d.ID_EDIFICIO)).forEach(d => selDiv.add(new Option(d.GLOSA_DIVISION, d.ID_DIVISION)));
            }
        }
    } 
    else if (changedFilter === 'edificio') {
        const fEdi = document.getElementById('filter_edificio').value;
        const selDiv = document.getElementById('filter_division');
        if (selDiv) {
            selDiv.innerHTML = '<option value="">Todas las Divisiones...</option>';
            if (fEdi) {
                DB.division.filter(d => d.ID_EDIFICIO == fEdi).forEach(d => selDiv.add(new Option(d.GLOSA_DIVISION, d.ID_DIVISION)));
            } else {
                const fSede = document.getElementById('filter_sede').value;
                if (fSede) {
                     const edisSede = DB.edificio.filter(e => e.ID_SEDE == fSede).map(e => e.ID_EDIFICIO);
                     DB.division.filter(d => edisSede.includes(d.ID_EDIFICIO)).forEach(d => selDiv.add(new Option(d.GLOSA_DIVISION, d.ID_DIVISION)));
                } else {
                     DB.division.forEach(d => selDiv.add(new Option(d.GLOSA_DIVISION, d.ID_DIVISION)));
                }
            }
        }
    }
    actualizarDropdownFinal();
}

function actualizarDropdownFinal() {
    const tipo = document.getElementById('selector_entidad').value;
    let data = DB[tipo];

    const fSede = document.getElementById('filter_sede') ? document.getElementById('filter_sede').value : '';
    const fEdi = document.getElementById('filter_edificio') ? document.getElementById('filter_edificio').value : '';
    const fDiv = document.getElementById('filter_division') ? document.getElementById('filter_division').value : '';
    const fCat = document.getElementById('filter_categoria') ? document.getElementById('filter_categoria').value : '';

    if (tipo === 'ubicacion') {
        if (fSede) data = data.filter(x => x.FISCALIA_UBICACION == fSede);
        if (fEdi) data = data.filter(x => x.EDIFICIO_UBICACION == fEdi);
        if (fDiv) data = data.filter(x => x.DIVISION_UBICACION == fDiv);
    } else if (tipo === 'division') {
        if (fSede) {
            const edisSede = DB.edificio.filter(e => e.ID_SEDE == fSede).map(e => e.ID_EDIFICIO);
            data = data.filter(x => edisSede.includes(x.ID_EDIFICIO));
        }
        if (fEdi) data = data.filter(x => x.ID_EDIFICIO == fEdi);
    } else if (tipo === 'edificio') {
        if (fSede) data = data.filter(x => x.ID_SEDE == fSede);
    } else if (tipo === 'subcategoria') {
        if (fCat) data = data.filter(x => x.ID_CAT == fCat);
    }

    const buscador = document.getElementById('buscador_elemento');
    buscador.innerHTML = '<option value="">Seleccione elemento final...</option>';
    
    const glosas = { sede: 'GLOSA_FISCALIA', edificio: 'GLOSA_EDIFICIO', division: 'GLOSA_DIVISION', categoria: 'GLOSA_CATEGORIA', subcategoria: 'GLOSA_SUBCATEGORIA', marca: 'GLOSA_MARCA', proveedor: 'GLOSA_PROVEEDOR', ubicacion: 'GLOSA_UBICACION' };
    const ids = { sede: 'ID_SEDE', edificio: 'ID_EDIFICIO', division: 'ID_DIVISION', categoria: 'ID_CAT', subcategoria: 'ID_SUBCAT', marca: 'ID_MARCA', proveedor: 'ID_PROV', ubicacion: 'ID_UBICACION' };
    
    data.forEach(item => buscador.add(new Option(item[glosas[tipo]], item[ids[tipo]])));
    
    document.getElementById('container_formularios').classList.add('anim-hide');
}

function cargarDatosElemento() {
    const tipo = document.getElementById('selector_entidad').value;
    const id = document.getElementById('buscador_elemento').value;
    const accion = document.getElementById('selector_accion').value;
    const containerForms = document.getElementById('container_formularios');
    const btnSubmit = document.getElementById('btn_submit');
    const alertaDep = document.getElementById('alerta_dependencias');
    const alertaBienes = document.getElementById('alerta_bienes');
    
    if (!id) {
        containerForms.classList.add('anim-hide');
        return;
    }

    document.querySelectorAll('.entity-form').forEach(el => el.classList.add('anim-hide'));
    document.getElementById('form_' + tipo).classList.remove('anim-hide');
    
    cargarCombosDependenciaFormulario();

    const data = DB[tipo].find(x => x[Object.keys(x)[0]] == id); 

    if(tipo === 'sede') document.getElementById('input_glosa_sede').value = data.GLOSA_FISCALIA;
    if(tipo === 'edificio') {
        document.getElementById('input_glosa_edificio').value = data.GLOSA_EDIFICIO;
        document.getElementById('edi_sel_sede').value = data.ID_SEDE;
    }
    if(tipo === 'division') {
        document.getElementById('input_glosa_division').value = data.GLOSA_DIVISION;
        const edi = DB.edificio.find(e => e.ID_EDIFICIO == data.ID_EDIFICIO);
        document.getElementById('div_sel_sede').value = edi.ID_SEDE;
        updateEdificiosForm('div');
        document.getElementById('div_sel_edificio').value = data.ID_EDIFICIO;
    }
    if(tipo === 'ubicacion') {
        document.getElementById('input_glosa_ubicacion').value = data.GLOSA_UBICACION;
        document.getElementById('input_tipo_ubicacion').value = data.TIPO;
        document.getElementById('ubi_sel_sede').value = data.FISCALIA_UBICACION;
        updateEdificiosForm('ubi');
        document.getElementById('ubi_sel_edificio').value = data.EDIFICIO_UBICACION;
        updateDivisionesForm('ubi');
        document.getElementById('ubi_sel_division').value = data.DIVISION_UBICACION;
        document.getElementById('ubi_sel_usuario').value = data.ID_USUARIO_ASIGNADO || '';
    }
    if(tipo === 'marca') document.getElementById('input_glosa_marca').value = data.GLOSA_MARCA;
    if(tipo === 'proveedor') document.getElementById('input_glosa_proveedor').value = data.GLOSA_PROVEEDOR;
    if(tipo === 'categoria') document.getElementById('input_glosa_categoria').value = data.GLOSA_CATEGORIA;
    if(tipo === 'subcategoria') {
        document.getElementById('input_glosa_subcat').value = data.GLOSA_SUBCATEGORIA;
        document.getElementById('sub_sel_cat').value = data.ID_CAT;
    }

    const inputs = document.querySelectorAll('.entity-form input, .entity-form select');
    inputs.forEach(i => i.disabled = (accion === 'anular'));
    
    alertaDep.classList.add('anim-hide');
    alertaBienes.classList.add('anim-hide');

    if (accion === 'anular') {
        let tieneDependencias = false;
        if (tipo === 'sede' && DB.edificio.some(e => e.ID_SEDE == id)) tieneDependencias = true;
        if (tipo === 'edificio' && DB.division.some(d => d.ID_EDIFICIO == id)) tieneDependencias = true;
        if (tipo === 'division' && DB.ubicacion.some(u => u.DIVISION_UBICACION == id)) tieneDependencias = true;
        if (tipo === 'categoria' && DB.subcategoria.some(s => s.ID_CAT == id)) tieneDependencias = true;

        if (tieneDependencias) {
            alertaDep.classList.remove('anim-hide');
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            btnSubmit.style.cursor = 'not-allowed';
        } else {
            alertaBienes.classList.remove('anim-hide');
            btnSubmit.disabled = false;
            btnSubmit.style.opacity = '1';
            btnSubmit.style.cursor = 'pointer';
        }
    } else {
        btnSubmit.disabled = false;
        btnSubmit.style.opacity = '1';
        btnSubmit.style.cursor = 'pointer';
    }

    containerForms.classList.remove('anim-hide');
}

function cargarCombosDependenciaFormulario() {
    const combosSede = document.querySelectorAll('#edi_sel_sede, #div_sel_sede, #ubi_sel_sede');
    combosSede.forEach(sel => {
        sel.innerHTML = '<option value="">Seleccione Sede...</option>';
        DB.sede.forEach(s => sel.add(new Option(s.GLOSA_FISCALIA, s.ID_SEDE)));
    });
    
    const comboCat = document.getElementById('sub_sel_cat');
    comboCat.innerHTML = '<option value="">Seleccione Categoría...</option>';
    DB.categoria.forEach(c => comboCat.add(new Option(c.GLOSA_CATEGORIA, c.ID_CAT)));
}

function updateEdificiosForm(p) {
    const idSede = document.getElementById(p + '_sel_sede').value;
    const target = document.getElementById(p + '_sel_edificio');
    target.innerHTML = '<option value="">Seleccione Edificio...</option>';
    target.disabled = !idSede;
    
    if(idSede) DB.edificio.filter(x => x.ID_SEDE == idSede).forEach(x => target.add(new Option(x.GLOSA_EDIFICIO, x.ID_EDIFICIO)));
    
    if(p === 'ubi') {
        const divTarget = document.getElementById('ubi_sel_division');
        divTarget.innerHTML = '<option value="">Seleccione Edificio primero</option>';
        divTarget.disabled = true;
    }
}

function updateDivisionesForm(p) {
    const idEdi = document.getElementById(p + '_sel_edificio').value;
    const target = document.getElementById(p + '_sel_division');
    target.innerHTML = '<option value="">Seleccione División...</option>';
    target.disabled = !idEdi;
    if(idEdi) DB.division.filter(x => x.ID_EDIFICIO == idEdi).forEach(x => target.add(new Option(x.GLOSA_DIVISION, x.ID_DIVISION)));
}

function limpiarInputsFormulario() {
    document.querySelectorAll('.entity-form input').forEach(i => {
        i.value = "";
        i.disabled = false;
    });
    
    const ubiSelUsuario = document.getElementById('ubi_sel_usuario');
    if(ubiSelUsuario) { ubiSelUsuario.value = ""; }

    ['div_sel_edificio', 'ubi_sel_edificio', 'ubi_sel_division'].forEach(id => {
        const el = document.getElementById(id);
        if(el) { el.innerHTML = '<option value="">Seleccione el paso anterior...</option>'; el.disabled = true; }
    });
}