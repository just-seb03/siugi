

document.addEventListener("DOMContentLoaded", function() {
    

    if (typeof swLabels !== 'undefined' && swLabels.length > 0 && document.getElementById('softwareDoughnutChart')) {
        const ctx = document.getElementById('softwareDoughnutChart').getContext('2d');
        

        Chart.register({
            id: 'cursorPointer',
            afterEvent: function(chart, args) {
                const event = args.event;
                if (event.type === 'mousemove') {
                    const activeElements = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, false);
                    chart.canvas.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                }
            }
        });

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: swLabels,
                datasets: [{
                    data: swValues,
                    backgroundColor: ['#475569', '#64748b', '#8b5cf6', '#d97706', '#059669', '#dc2626', '#0284c7', '#ea580c'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: 0 },
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'right', 
                        labels: { 
                            usePointStyle: true, 
                            boxWidth: 8, 
                            font: { size: 10, family: 'Inter' }, 
                            padding: 12 
                        } 
                    },
                    tooltip: { 
                        callbacks: { 
                            label: function(context) { 
                                return (context.label || '') + ': ' + context.parsed + ' Cuentas'; 
                            } 
                        } 
                    }
                },
                onClick: (e, activeElements) => {
                    if (activeElements.length > 0) {
                        const softwareName = swLabels[activeElements[0].index];
                        

                        window.location.href = `/SIUGI/cuentas_usuarios?sw=${encodeURIComponent(softwareName)}`;
                    }
                }
            }
        });
    } else if (document.getElementById('softwareDoughnutChart')) {
        const chartCanvas = document.getElementById('softwareDoughnutChart');
        chartCanvas.parentElement.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted" style="font-size: 0.8rem;"><i class="fas fa-chart-pie mr-2"></i>Sin datos suficientes</div>';
    }


    function setupCollapseIcon(targetId, iconId) {
        if (typeof $ !== 'undefined') {
            $(targetId).on('hidden.bs.collapse', function () { 
                $(iconId).removeClass('fa-chevron-up').addClass('fa-chevron-down'); 
            });
            $(targetId).on('shown.bs.collapse', function () { 
                $(iconId).removeClass('fa-chevron-down').addClass('fa-chevron-up'); 
            });
        }
    }
    setupCollapseIcon('#collapseIngresos', '#toggleIconIngresos');
    setupCollapseIcon('#collapseActividad', '#toggleIconActividad');
});