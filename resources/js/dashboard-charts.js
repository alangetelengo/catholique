import Chart from 'chart.js/auto';

function readPayload() {
    const el = document.getElementById('dashboard-chart-data');
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

function formatLabels(payload) {
    const { granularity, labels } = payload;
    if (granularity === 'month') {
        const mo = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        return labels.map((ym) => {
            const [y, m] = ym.split('-');
            const mi = parseInt(m, 10) - 1;
            return `${mo[mi] ?? m} ${y}`;
        });
    }
    return labels.map((d) => {
        const [y, m, day] = d.split('-');
        return `${day}/${m}`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const payload = readPayload();
    const canvas = document.getElementById('chart-dashboard-trend');
    if (!payload?.labels?.length || !canvas) {
        return;
    }

    const d = darkMode();
    const grid = d ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.08)';
    const text = d ? '#cbd5e1' : '#475569';

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: formatLabels(payload),
            datasets: [
                {
                    label: 'Recettes',
                    data: payload.revenues,
                    borderColor: 'rgba(16, 185, 129, 0.95)',
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    fill: true,
                    tension: 0.2,
                },
                {
                    label: 'Popote (déductible)',
                    data: payload.popote,
                    borderColor: 'rgba(244, 63, 94, 0.85)',
                    backgroundColor: 'transparent',
                    tension: 0.2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: text } },
                title: {
                    display: true,
                    text:
                        payload.granularity === 'month'
                            ? 'Tendance par mois'
                            : 'Tendance par jour',
                    color: text,
                },
            },
            scales: {
                x: { ticks: { color: text, maxRotation: 45 }, grid: { color: grid } },
                y: { ticks: { color: text }, grid: { color: grid } },
            },
        },
    });
});
