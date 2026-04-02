import Chart from 'chart.js/auto';

/**
 * Graphiques Chart.js pour la page statistiques financières.
 * Données injectées via #financial-stats-chart-data (JSON).
 */
function readPayload() {
    const el = document.getElementById('financial-stats-chart-data');
    if (!el?.textContent?.trim()) {
        return null;
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function darkMode() {
    return (
        document.documentElement.classList.contains('dark') ||
        document.documentElement.getAttribute('data-theme') === 'dark'
    );
}

function chartColors() {
    const d = darkMode();
    return {
        grid: d ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.08)',
        text: d ? '#cbd5e1' : '#475569',
        rev: 'rgba(16, 185, 129, 0.75)',
        pop: 'rgba(244, 63, 94, 0.65)',
        oth: 'rgba(100, 116, 139, 0.55)',
        revPrev: 'rgba(16, 185, 129, 0.35)',
        linePrev: 'rgba(59, 130, 246, 0.9)',
    };
}

function formatMonthLabels(labels) {
    const mo = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    return labels.map((ym) => {
        const [y, m] = ym.split('-');
        const mi = parseInt(m, 10) - 1;
        return `${mo[mi] ?? m} ${y}`;
    });
}

function baseOptions(colors) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: colors.text },
            },
        },
        scales: {
            x: {
                ticks: { color: colors.text },
                grid: { color: colors.grid },
            },
            y: {
                ticks: { color: colors.text },
                grid: { color: colors.grid },
            },
        },
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const payload = readPayload();
    if (!payload?.labels?.length) {
        return;
    }

    const colors = chartColors();
    const labels = formatMonthLabels(payload.labels);
    const ds = payload.datasets;

    const monthlyEl = document.getElementById('chart-financial-monthly');
    if (monthlyEl) {
        new Chart(monthlyEl, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Recettes',
                        data: ds.revenues,
                        backgroundColor: colors.rev,
                    },
                    {
                        label: 'Popote / alimentation (déductible)',
                        data: ds.popote_deductible,
                        backgroundColor: colors.pop,
                    },
                    {
                        label: 'Autres dépenses (info, non déduct.)',
                        data: ds.other_expenses_info ?? [],
                        backgroundColor: colors.oth,
                    },
                ],
            },
            options: {
                ...baseOptions(colors),
                plugins: {
                    ...baseOptions(colors).plugins,
                    title: {
                        display: true,
                        text: 'Flux mensuels sur la période',
                        color: colors.text,
                    },
                },
            },
        });
    }

    const compareEl = document.getElementById('chart-financial-compare');
    if (compareEl && payload.compare && ds.revenues_previous_year) {
        new Chart(compareEl, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Recettes (période)',
                        data: ds.revenues,
                        borderColor: colors.linePrev,
                        backgroundColor: 'rgba(59, 130, 246, 0.15)',
                        fill: true,
                        tension: 0.25,
                    },
                    {
                        label: 'Recettes (N−1, mêmes mois)',
                        data: ds.revenues_previous_year,
                        borderColor: colors.revPrev,
                        backgroundColor: 'transparent',
                        borderDash: [6, 4],
                        tension: 0.25,
                    },
                ],
            },
            options: {
                ...baseOptions(colors),
                plugins: {
                    ...baseOptions(colors).plugins,
                    title: {
                        display: true,
                        text: 'Comparaison recettes / année précédente',
                        color: colors.text,
                    },
                },
            },
        });
    }

    const pieRevEl = document.getElementById('chart-pie-revenues');
    const pr = payload.pie_revenues;
    if (pieRevEl && pr?.labels?.length) {
        new Chart(pieRevEl, {
            type: 'doughnut',
            data: {
                labels: pr.labels,
                datasets: [
                    {
                        data: pr.values,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.75)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(245, 158, 11, 0.75)',
                            'rgba(168, 85, 247, 0.7)',
                            'rgba(244, 63, 94, 0.65)',
                            'rgba(14, 165, 233, 0.7)',
                            'rgba(100, 116, 139, 0.6)',
                        ],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: colors.text } },
                    title: {
                        display: true,
                        text: 'Recettes par catégorie',
                        color: colors.text,
                    },
                },
            },
        });
    }

    const pieExpEl = document.getElementById('chart-pie-expenses');
    const pe = payload.pie_expenses;
    if (pieExpEl && pe?.labels?.length) {
        new Chart(pieExpEl, {
            type: 'doughnut',
            data: {
                labels: pe.labels,
                datasets: [
                    {
                        data: pe.values,
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.65)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(244, 63, 94, 0.75)',
                            'rgba(100, 116, 139, 0.6)',
                        ],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: colors.text } },
                    title: {
                        display: true,
                        text: 'Dépenses par type (toutes, à titre informatif)',
                        color: colors.text,
                    },
                },
            },
        });
    }
});
