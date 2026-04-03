

let chartInstance = null;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {

    if (typeof sedes !== 'undefined') {
        ['', '_b'].forEach(sfx => {
            const sSel = document.getElementById('sel_sede' + sfx);
            if (sSel) sedes.forEach(s => sSel.add(new Option(s.GLOSA_FISCALIA, s.ID_SEDE)));
        });
    }


    const formInputs = document.querySelectorAll('#filterForm input, #filterForm select');
    formInputs.forEach(input => {
        if(input.type === 'checkbox') return; 
        if (input.type === 'text' || input.type === 'number' || input.type === 'date') {
            input.addEventListener('input', triggerSearch);
        } else {
            input.addEventListener('change', triggerSearch);
        }
    });

    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
    });

    doSearch(1);

    if (typeof $ !== 'undefined') {
        $('#collapseGrafico').on('hidden.bs.collapse', function () { $('#toggleIconGrafico').removeClass('fa-chevron-up').addClass('fa-chevron-down'); });
        $('#collapseGrafico').on('shown.bs.collapse', function () { $('#toggleIconGrafico').removeClass('fa-chevron-down').addClass('fa-chevron-up'); });
    }
});

function triggerSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { doSearch(1); }, 300);
}

function limpiarFiltros() {
    document.getElementById('filterForm').reset();
    resetSel('sel_edificio'); resetSel('sel_division'); resetSel('sel_ubicacion');
    resetSel('sel_edificio_b'); resetSel('sel_division_b'); resetSel('sel_ubicacion_b');
    
    document.getElementById('cont_comp').classList.remove('active-comp');
    document.getElementById('cont_baja').classList.remove('active-baja');
    document.getElementById('cont_bodega').classList.remove('active-bodega');
    document.getElementById('filtros_b').classList.replace('anim-show', 'anim-hide');
    
    doSearch(1);
}

function updateMando(tipo, sfx, reset = true) {
    if(tipo === 'sede') {
        const id = document.getElementById('sel_sede' + sfx).value;
        populate(document.getElementById('sel_edificio' + sfx), edificios.filter(x => x.ID_SEDE == id), 'ID_EDIFICIO', 'GLOSA_EDIFICIO', 'Todos');
        if(reset) { resetSel('sel_division' + sfx); resetSel('sel_ubicacion' + sfx); }
    } else if(tipo === 'edificio') {
        const id = document.getElementById('sel_edificio' + sfx).value;
        populate(document.getElementById('sel_division' + sfx), divisiones.filter(x => x.ID_EDIFICIO == id), 'ID_DIVISION', 'GLOSA_DIVISION', 'Todas');
        if(reset) resetSel('sel_ubicacion' + sfx);
    } else if(tipo === 'division') {
        const id = document.getElementById('sel_division' + sfx).value;
        populate(document.getElementById('sel_ubicacion' + sfx), ubicaciones.filter(x => x.DIVISION_UBICACION == id), 'ID_UBICACION', 'GLOSA_UBICACION', 'Todas');
    }
    triggerSearch();
}

function populate(sel, data, valKey, textKey, defaultText) {
    if(!sel) return;
    sel.innerHTML = `<option value="">${defaultText}</option>`;
    if(data.length > 0) { sel.disabled = false; data.forEach(x => sel.add(new Option(x[textKey], x[valKey]))); } 
    else { sel.disabled = true; }
}

function resetSel(id) { 
    const sel = document.getElementById(id); 
    if(sel){ sel.innerHTML = '<option value="">Todas</option>'; sel.disabled = true; }
}

function toggleStyle(checkbox, containerId, activeClass) {
    const container = document.getElementById(containerId);
    checkbox.checked ? container.classList.add(activeClass) : container.classList.remove(activeClass);
}

function toggleComparativo(checkbox) {
    toggleStyle(checkbox, 'cont_comp', 'active-comp');
    const panelB = document.getElementById('filtros_b');
    if (checkbox.checked) {
        panelB.classList.remove('anim-hide'); panelB.classList.add('anim-show');
    } else {
        panelB.classList.remove('anim-show'); panelB.classList.add('anim-hide');
        document.getElementById('sel_sede_b').value = '';
        resetSel('sel_edificio_b'); resetSel('sel_division_b'); resetSel('sel_ubicacion_b');
    }
    triggerSearch();
}

function toggleActions(id) { 
    const panel = document.getElementById(id); 
    const isVisible = panel.classList.contains('show'); 
    document.querySelectorAll('.actions-panel').forEach(p => p.classList.remove('show')); 
    if (!isVisible) panel.classList.add('show'); 
}

function doSearch(pageIndex) {
    document.getElementById('currentPage').value = pageIndex;
    const form = document.getElementById('filterForm');
    const loadingIcon = document.getElementById('loadingIcon');
    
    if(loadingIcon) loadingIcon.style.display = 'inline-block';

    const params = new URLSearchParams(new FormData(form));
    params.append('ajax', '1');


    fetch('/SIUGI/consultar_bienes?' + params.toString())
        .then(res => res.json())
        .then(data => {
            renderTable(data.data);
            renderPagination(data.page, data.pages);
            renderChart(data.chart);
            
            const tr = document.getElementById('textResultados');
            if(tr) tr.innerHTML = `Resultados (${data.total})`;
            
            const pdfBtn = document.getElementById('btnExportPDF');
            if(pdfBtn) {
                if(data.data.length > 0) {
                    params.delete('ajax');

                    pdfBtn.href = '/SIUGI/exportar_bienes?' + params.toString();
                    pdfBtn.style.display = 'inline-block';
                } else {
                    pdfBtn.style.display = 'none';
                }
            }
        })
        .catch(err => console.error("Error en búsqueda:", err))
        .finally(() => {
            if(loadingIcon) loadingIcon.style.display = 'none';
        });
}

function renderTable(dataArray) {
    const tbody = document.querySelector('#tablaResultados tbody');
    if(!tbody) return;
    
    let html = '';
    if (dataArray.length === 0) {
        html = `<tr><td colspan="9" class="text-center" style="padding: 3rem; color: var(--text-muted);">No se encontraron registros que coincidan con los filtros.</td></tr>`;
    } else {
        dataArray.forEach(r => {
            const bStatus = r.ESTADO === 'Baja' ? 'bg-danger-soft' : 'bg-success-soft';
            const serieH = r.SERIE ? `<span class="table-serie">${r.SERIE}</span>` : `<span class="text-muted font-italic" style="font-size: 0.65rem;">S/N</span>`;
            const sede = r.NOMBRE_SEDE ? r.NOMBRE_SEDE : 'No Asignada';
            const ubi = r.UBICACION_DETALLE ? r.UBICACION_DETALLE : '';
            
            html += `
                <tr onclick="toggleActions('actions-${r.ID}')" style="cursor:pointer; border-bottom: 1px solid var(--border);">
                    <td class="text-center"><span class="table-id-badge">#${r.ID}</span></td>
                    <td><strong class="text-dark">${r.SUBCATEGORIA || ''}</strong></td>
                    <td><span class="font-weight-bold text-dark" style="font-size: 0.75rem;">${sede}</span></td>
                    <td><small class="text-muted">${ubi}</small></td>
                    <td><span class="text-dark font-weight-bold" style="font-size: 0.75rem;">${r.MARCA || '-'}</span></td>
                    <td><small class="text-muted">${r.MODELO || '-'}</small></td>
                    <td><span class="badge-status ${bStatus}">${r.ESTADO || ''}</span></td>
                    <td>${serieH}</td>
                    <td class="text-muted"><i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i></td>
                </tr>
                <tr class="actions-panel" id="actions-${r.ID}">
                    <td colspan="9" style="padding: 0;">
                        <div style="padding: 0.8rem 1rem; background: #f8fafc; border-bottom: 2px solid var(--border); display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="/SIUGI/editar_bien?id=${r.ID}" class="btn btn-sm font-weight-bold" style="background: var(--info-bg); color: var(--info); border: 1px solid #bfdbfe; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-edit mr-1"></i> Editar</a>
                            <a href="/SIUGI/ficha_bien?id=${r.ID}" target='_blank' class="btn btn-sm font-weight-bold" style="background: var(--success-bg); color: var(--success); border: 1px solid #bbf7d0; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-file-alt mr-1"></i> Ficha</a>
                            <a href="/SIUGI/src/anular_bien?id=${r.ID}" class="btn btn-sm font-weight-bold" style="background: var(--danger-bg); color: var(--danger); border: 1px solid #fecaca; border-radius: 6px; font-size: 0.75rem;" onclick="return confirm('¿Está seguro de anular/dar de baja este registro?')"><i class="fas fa-trash-alt mr-1"></i> Anular</a>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    tbody.innerHTML = html;
}

function renderPagination(current, total) {
    const container = document.getElementById('containerPaginacion');
    if(!container) return;
    if (total <= 1) { container.innerHTML = ''; return; }
    
    let html = '<ul class="pagination pagination-sm m-0 justify-content-end">';
    html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link" onclick="${current > 1 ? `doSearch(${current-1})` : ''}">&laquo; Ant</a></li>`;
    
    const start = Math.max(1, current - 2);
    const end = Math.min(total, current + 2);
    for(let i=start; i<=end; i++){
        html += `<li class="page-item ${current === i ? 'active' : ''}"><a class="page-link" onclick="doSearch(${i})">${i}</a></li>`;
    }
    
    html += `<li class="page-item ${current >= total ? 'disabled' : ''}"><a class="page-link" onclick="${current < total ? `doSearch(${current+1})` : ''}">Sig &raquo;</a></li></ul>`;
    container.innerHTML = html;
}

function renderChart(chartSets) {
    const cContainer = document.getElementById('containerGraficoWrapper');
    if(!cContainer) return;
    
    if (!chartSets || Object.keys(chartSets.principal).length === 0) {
        cContainer.style.display = 'none';
        return;
    }
    
    cContainer.style.display = 'block';
    if (chartInstance) chartInstance.destroy();

    let labelsSet = new Set();
    if (chartSets.principal) Object.keys(chartSets.principal).forEach(k => labelsSet.add(k));
    if (chartSets.secundario) Object.keys(chartSets.secundario).forEach(k => labelsSet.add(k));
    
    const allLabels = Array.from(labelsSet).sort();
    let dataA = [], dataB = [];
    
    allLabels.forEach(l => {
        dataA.push(chartSets.principal[l] || 0);
        if (chartSets.secundario) dataB.push(chartSets.secundario[l] || 0);
    });

    const datasets = [{ label: 'Grupo A', data: dataA, backgroundColor: '#475569', borderRadius: 4 }];
    if (chartSets.secundario && Object.keys(chartSets.secundario).length > 0) {
        datasets.push({ label: 'Grupo B', data: dataB, backgroundColor: '#f59e0b', borderRadius: 4 });
    }

    chartInstance = new Chart(document.getElementById('mainChart'), {
        type: 'bar',
        data: { labels: allLabels, datasets: datasets },
        options: { 
            responsive: true, maintainAspectRatio: false, 
            plugins: { legend: { display: datasets.length > 1, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } }
        }
    });
}