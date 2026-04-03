

let isFetching = false;
let filterTimeout = null;
let currentPageCuentas = 1;
let currentPageBienes = 1;

document.addEventListener("DOMContentLoaded", function() {
    const zoomRange = document.getElementById('zoomRange');
    const zoomWrapper = document.getElementById('usersZoomWrapper');

    if(zoomRange && zoomWrapper) {
        zoomRange.addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            zoomWrapper.style.transform = `scale(${val})`;
            
            if(val >= 1.5) {
                zoomWrapper.classList.add('vertical-list');
            } else {
                zoomWrapper.classList.remove('vertical-list');
            }
            zoomWrapper.style.width = (100 / val) + "%";
        });
    }


    fetchData(1, 1);
});

function triggerFilter() {
    fetchData(1, 1);
}

function triggerFilterDebounced() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        fetchData(1, 1);
    }, 500);
}

function updateSwitchLabels() {
    const alertas = document.getElementById('alerta_bajas').checked;
    document.getElementById('lblAlertasTodas').classList.toggle('active-label', !alertas);
    document.getElementById('lblAlertas').classList.toggle('active-label', alertas);

    const bajas = document.getElementById('ver_bajas').checked;
    document.getElementById('lblActivas').classList.toggle('active-label', !bajas);
    document.getElementById('lblBajas').classList.toggle('active-label', bajas);

    const pendientes = document.getElementById('ver_pendientes').checked;
    document.getElementById('lblPendientesTodas').classList.toggle('active-label', !pendientes);
    document.getElementById('lblPendientes').classList.toggle('active-label', pendientes);
}

async function fetchData(pageCuentas = null, pageBienes = null) {
    if (isFetching) return;
    isFetching = true;

    if (pageCuentas !== null) currentPageCuentas = pageCuentas;
    if (pageBienes !== null) currentPageBienes = pageBienes;

    const tableWrapper = document.getElementById('tableContainer');
    const usersWrapper = document.getElementById('usersContainerWrapper');
    const tableBienesWrapper = document.getElementById('tableBienesContainer');
    
    if(tableWrapper) tableWrapper.classList.add('ajax-loading');
    if(usersWrapper) usersWrapper.classList.add('ajax-loading');
    if(tableBienesWrapper) tableBienesWrapper.classList.add('ajax-loading');

    const params = new URLSearchParams();
    params.append('ajax', '1');
    params.append('page', currentPageCuentas);
    params.append('page_bienes', currentPageBienes);
    
    const q_usr = document.getElementById('q_usr').value;
    const sw = document.getElementById('sw').value;
    const sd = document.getElementById('sd').value;
    const f_gen = document.getElementById('f_gen').value;
    const tipo_usr = document.getElementById('tipo_usr').value;
    const glosa = document.getElementById('glosa').value;
    const req = document.getElementById('req').value;

    if (q_usr) params.append('q_usr', q_usr);
    if (sw) params.append('sw', sw);
    if (sd) params.append('sd', sd);
    if (f_gen !== '') params.append('f_gen', f_gen);
    if (tipo_usr) params.append('tipo_usr', tipo_usr);
    if (glosa) params.append('glosa', glosa);
    if (req) params.append('req', req);
    
    if (document.getElementById('alerta_bajas').checked) params.append('alerta_bajas', '1');
    if (document.getElementById('ver_bajas').checked) params.append('ver_bajas', '1');
    if (document.getElementById('ver_pendientes').checked) params.append('ver_pendientes', '1');

    updateSwitchLabels();

    try {

        const response = await fetch(`cuentas_usuarios?${params.toString()}`);
        if (!response.ok) throw new Error('Error en la red');
        
        const data = await response.json();
        
        document.getElementById('cuentasTbody').innerHTML = data.tbody;
        document.getElementById('paginationContainer').innerHTML = data.pagination;
        
        document.getElementById('bienesTbody').innerHTML = data.tbody_bienes;
        document.getElementById('paginationBienesContainer').innerHTML = data.pagination_bienes;
        
        if (data.users_html) {
            const zWrap = document.getElementById('usersZoomWrapper');
            if(zWrap) zWrap.innerHTML = data.users_html;
            

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.users_html;
            const count = tempDiv.querySelectorAll('.user-card-link').length;
            document.getElementById('userCountBadge').innerText = `${count} encontrados`;
        }

        const exportParams = new URLSearchParams(params);
        exportParams.delete('ajax');
        exportParams.delete('page');
        exportParams.delete('page_bienes');
        

        document.getElementById('btnExportar').href = `/SIUGI/exportar_cuentas?${exportParams.toString()}`;

        window.history.replaceState({}, '', `?${exportParams.toString()}`);

    } catch (error) {
        console.error('Error al actualizar datos:', error);
    } finally {
        setTimeout(() => {
            if(tableWrapper) tableWrapper.classList.remove('ajax-loading');
            if(usersWrapper) usersWrapper.classList.remove('ajax-loading');
            if(tableBienesWrapper) tableBienesWrapper.classList.remove('ajax-loading');
            isFetching = false;
        }, 150);
    }
}

function limpiarFiltros() {
    document.getElementById('filterForm').reset();
    fetchData(1, 1);
}