let isFetching = false;

document.addEventListener("DOMContentLoaded", function() {
    if (typeof idUsuarioLocal !== 'undefined') {
        fetchCuentas(1);
        fetchBienes(1);
    }
});

function togglePanel(panelId) {
    if (panelId === 'panelCrear') {
        $('#panelAutoAsignar').slideUp(300);
        $('#panelCrear').slideToggle(300);
    } else if (panelId === 'panelAutoAsignar') {
        $('#panelCrear').slideUp(300);
        $('#panelAutoAsignar').slideToggle(300);
    }
}


function updateSwitchLabels() {
    const bajas = document.getElementById('ver_bajas_switch').checked;
    
    document.getElementById('lblActivas').classList.toggle('active-label', !bajas);
    document.getElementById('lblBajas').classList.toggle('active-label', bajas);
    
    const titulo = document.getElementById('tituloTabla');
    if(bajas) {
        titulo.innerHTML = '<i class="fas fa-key mr-2" style="color: var(--text-muted);"></i> Cuentas <span style="color: var(--danger); font-weight: normal; font-size: 1rem;">(Dadas de Baja)</span>';
    } else {
        titulo.innerHTML = '<i class="fas fa-key mr-2" style="color: var(--text-muted);"></i> Cuentas <span style="color: var(--primary); font-weight: normal; font-size: 1rem;">(Activas / Pendientes)</span>';
    }
}


function updateBienesSwitchLabels() {
    const bajas = document.getElementById('ver_bajas_bienes_switch').checked;

    document.getElementById('lblActivasBienes').classList.toggle('active-label', !bajas);
    document.getElementById('lblBajasBienes').classList.toggle('active-label', bajas);

    const titulo = document.getElementById('tituloTablaBienes');
    if(bajas) {
        titulo.innerHTML = '<i class="fas fa-desktop mr-2" style="color: var(--text-muted);"></i> Bienes <span style="color: var(--danger); font-weight: normal; font-size: 1rem;">(Bajas)</span>';
    } else {
        titulo.innerHTML = '<i class="fas fa-desktop mr-2" style="color: var(--text-muted);"></i> Bienes <span style="color: var(--primary); font-weight: normal; font-size: 1rem;">(Alta)</span>';
    }
}

async function fetchCuentas(page) {
    if (isFetching) return;
    isFetching = true;

    const tableWrapper = document.getElementById('tableContainer');
    tableWrapper.classList.add('ajax-loading');

    const isBajas = document.getElementById('ver_bajas_switch').checked ? 1 : 0;
    updateSwitchLabels();

    const params = new URLSearchParams();
    params.append('ajax_cuentas', '1');
    params.append('id_usuario', idUsuarioLocal);
    params.append('page', page);
    params.append('ver_bajas', isBajas);

    try {
        const response = await fetch(`/SIUGI/src/menu_usuario?${params.toString()}`);
        if (!response.ok) throw new Error(`Error del servidor: ${response.status}`);
        
        const data = await response.json();
        document.getElementById('cuentasTbody').innerHTML = data.tbody;
        document.getElementById('paginationContainer').innerHTML = data.pagination;
    } catch (error) {
        console.error('Error al actualizar tabla de cuentas:', error);
        document.getElementById('cuentasTbody').innerHTML = `<tr><td colspan="10"><div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error al cargar: ${error.message}</div></td></tr>`;
    } finally {
        tableWrapper.classList.remove('ajax-loading');
        isFetching = false;
    }
}

async function fetchBienes(page) {
    const tableWrapper = document.getElementById('bienesTableContainer');
    tableWrapper.classList.add('ajax-loading');
    
    const isBajas = document.getElementById('ver_bajas_bienes_switch').checked ? 1 : 0;
    updateBienesSwitchLabels();

    const params = new URLSearchParams();
    params.append('ajax_bienes', '1');
    params.append('id_usuario', idUsuarioLocal);
    params.append('page', page);
    params.append('ver_bajas', isBajas);
    
    try {
        const response = await fetch(`/SIUGI/src/menu_usuario?${params.toString()}`);
        if (!response.ok) {
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const data = await response.json();
        document.getElementById('bienesTbody').innerHTML = data.tbody;
        document.getElementById('bienesPaginationContainer').innerHTML = data.pagination;
    } catch (error) {
        console.error('Error al cargar bienes:', error);
        document.getElementById('bienesTbody').innerHTML = `<tr><td colspan="8"><div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error al cargar: ${error.message}</div></td></tr>`;
    } finally {
        tableWrapper.classList.remove('ajax-loading');
    }
}



function openModalFormularios() {
    const modal = document.getElementById('modalFormularios');
    modal.style.display = 'flex';
}

function closeModalFormularios() {
    const modal = document.getElementById('modalFormularios');
    modal.style.display = 'none';
}


window.onclick = function(event) {
    const modal = document.getElementById('modalFormularios');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}