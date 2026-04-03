

document.addEventListener("DOMContentLoaded", function() {
    

    const chartDefaults = { 
        maintainAspectRatio: false, 
        animation: false, 
        plugins: { legend: { display: false } }, 
        scales: { 
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, 
            x: { grid: { display: false } } 
        } 
    };


    if (document.getElementById('chartSedes') && typeof sedesLabels !== 'undefined') {
        new Chart(document.getElementById('chartSedes'), { 
            type: 'bar', 
            data: { labels: sedesLabels, datasets: [{ data: sedesValues, backgroundColor: '#475569', borderRadius: 4 }] }, 
            options: { ...chartDefaults, indexAxis: 'y' } 
        });
    }

    if (document.getElementById('chartDonut') && typeof estadosLabels !== 'undefined') {
        new Chart(document.getElementById('chartDonut'), { 
            type: 'doughnut', 
            data: { labels: estadosLabels, datasets: [{ data: estadosValues, backgroundColor: ['#22c55e', '#f59e0b'], borderWeight: 0 }] }, 
            options: { animation: false, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: true, position: 'right' } } } 
        });
    }

    if (document.getElementById('chartBodega') && typeof bodCatLabels !== 'undefined') {
        new Chart(document.getElementById('chartBodega'), { 
            type: 'bar', 
            data: { labels: bodCatLabels, datasets: [{ data: bodCatValues, backgroundColor: '#f59e0b', borderRadius: 4 }] }, 
            options: chartDefaults 
        });
    }

    if (document.getElementById('chartAlta') && typeof altCatLabels !== 'undefined') {
        new Chart(document.getElementById('chartAlta'), { 
            type: 'bar', 
            data: { labels: altCatLabels, datasets: [{ data: altCatValues, backgroundColor: '#22c55e', borderRadius: 4 }] }, 
            options: chartDefaults 
        });
    }

    if (document.getElementById('chartMiSede') && typeof miSedeLabels !== 'undefined' && miSedeLabels.length > 0) {
        new Chart(document.getElementById('chartMiSede'), {
            type: 'bar',
            data: { 
                labels: miSedeLabels, 
                datasets: [{ data: miSedeValues, backgroundColor: '#3b82f6', borderRadius: 6 }] 
            },
            options: chartDefaults
        });
    }


    function setupCollapseIcon(targetId, iconId) {
        if (typeof $ !== 'undefined') {
            $(targetId).on('hidden.bs.collapse', function () { $(iconId).removeClass('fa-chevron-up').addClass('fa-chevron-down'); });
            $(targetId).on('shown.bs.collapse', function () { $(iconId).removeClass('fa-chevron-down').addClass('fa-chevron-up'); });
        }
    }

    setupCollapseIcon('#statsArea', '#toggleIconStats');
    setupCollapseIcon('#collapseIngresos', '#toggleIconIngresos');
    setupCollapseIcon('#collapseMiSede', '#toggleIconMiSede');
    setupCollapseIcon('#collapseActividad', '#toggleIconActividad');
});