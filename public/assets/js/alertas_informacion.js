

async function fetchBajasData(page) {
    const wrapper = document.getElementById('bajasTableWrapper');
    wrapper.classList.add('ajax-loading');
    
    try {

        const response = await fetch(`alertas_informacion?ajax_bajas=1&p_baja=${page}`);
        if (!response.ok) throw new Error('Error en la red');
        
        const data = await response.json();
        
        if (data.count > 0) {
            document.getElementById('bajasTbody').innerHTML = data.tbody;
            document.getElementById('bajasPaginationContainer').innerHTML = data.pagination;
        }
    } catch (error) {
        console.error('Error al paginar bajas:', error);
    } finally {
        wrapper.classList.remove('ajax-loading');
    }
}

async function fetchProxData(page) {
    const wrapper = document.getElementById('proxTableWrapper');
    wrapper.classList.add('ajax-loading');
    
    try {

        const response = await fetch(`alertas_informacion?ajax_prox=1&p_prox=${page}`);
        if (!response.ok) throw new Error('Error en la red');
        
        const data = await response.json();
        
        if (data.count > 0) {
            document.getElementById('proxTbody').innerHTML = data.tbody;
            document.getElementById('proxPaginationContainer').innerHTML = data.pagination;
        }
    } catch (error) {
        console.error('Error al paginar próximos términos:', error);
    } finally {
        wrapper.classList.remove('ajax-loading');
    }
}


Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.color = '#64748b';

let myChart = null;

function initChart() {
    const ctx = document.getElementById('cuentasChart').getContext('2d');
    myChart = new Chart(ctx, {
        type: 'bar',
        data: { labels: initialLabels, datasets: initialDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 600, easing: 'easeOutQuart' },
            scales: {
                x: { stacked: false, grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#e2e8f0' } }
            },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, padding: 20 } },
                tooltip: { backgroundColor: '#334155', padding: 12, cornerRadius: 8, displayColors: true }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    if (typeof initialLabels !== 'undefined' && initialLabels.length > 0) {
        initChart();
    }
});

async function fetchChartData() {
    const sedeVal = document.getElementById('filtroSede').value;
    const softwareVal = document.getElementById('filtroSoftware').value;
    const wrapper = document.getElementById('chartWrapper');
    const canvas = document.getElementById('cuentasChart');
    const emptyState = document.getElementById('chartEmptyState');

    wrapper.classList.add('ajax-loading');

    try {

        const url = `alertas_informacion?ajax_chart=1&sede=${encodeURIComponent(sedeVal)}&software=${encodeURIComponent(softwareVal)}`;
        const response = await fetch(url);
        
        if (!response.ok) throw new Error('Network response error');
        const data = await response.json();

        if (data.empty || data.labels.length === 0) {
            emptyState.style.display = 'block';
            canvas.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            canvas.style.display = 'block';
            
            if (!myChart) { initChart(); }
            
            myChart.data.labels = data.labels;
            myChart.data.datasets = data.datasets;
            myChart.update(); 
        }
    } catch (error) {
        console.error('Error al actualizar gráfico:', error);
    } finally {
        wrapper.classList.remove('ajax-loading');
    }
}

function resetFilters() {
    document.getElementById('filtroSede').value = '';
    document.getElementById('filtroSoftware').value = '';
    fetchChartData(); 
}