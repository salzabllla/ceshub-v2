(() => {
    const app = document.getElementById('lab-app');
    const chartMessage = document.getElementById('chart-message');
    const charts = new Map();
    const filtered = ['from', 'to'].some(name => new URLSearchParams(location.search).has(name));
    const refreshInterval = 5000;
    let refreshing = false;

    const number = value => value == null || Number.isNaN(Number(value)) ? '—' : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
    const lampText = value => value == null ? 'Tidak tercatat' : value === 1 ? 'ON' : 'OFF';
    const period = new URLSearchParams();
    for (const name of ['from', 'to']) period.set(name, document.querySelector(`[name="${name}"]`).value);
    async function request(url, options = {}) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 15000);
        try {
            const response = await fetch(url, { ...options, cache: 'no-store', signal: controller.signal, headers: { Accept: 'application/json', ...options.headers } });
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Permintaan gagal.');
            return data;
        } finally {
            clearTimeout(timeout);
        }
    }
    function draw(rows) {
        if (!window.Chart) throw new Error('Grafik belum dapat dimuat. Periksa koneksi internet lalu muat ulang.');
        for (const [id,metric,label,unit,color] of [
            ['production-chart','daya','Daya','W','#0865b5'],
            ['voltage-chart','tegangan','Tegangan','V','#168b88'],
            ['current-chart','arus','Arus','A','#ba7b24'],
            ['light-chart','irradiansi','Estimasi irradiansi','W/m²','#8b5bb5'],
            ['temperature-chart','suhu','Suhu','°C','#cb6440']
        ]) {
            const canvas=document.getElementById(id);
            let chart=charts.get(id);
            if(!chart){chart=new Chart(canvas,{type:'line',data:{labels:[],datasets:[{label,borderColor:color,backgroundColor:color+'18',data:[],fill:true,borderWidth:2,pointRadius:0,tension:.15,spanGaps:false}]},options:{responsive:true,maintainAspectRatio:false,animation:false,plugins:{legend:{display:false}},scales:{x:{ticks:{maxTicksLimit:5,maxRotation:0}},y:{title:{display:true,text:unit},grid:{color:'#e2e8f0'}}}}});charts.set(id,chart);}
            chart.data.labels=rows.map(row=>row.label);chart.data.datasets[0].data=rows.map(row=>row[metric]);chart.data.datasets[0].pointRadius=rows.length===1?3:0;chart.update('none');
            canvas.parentElement.querySelector('.sensor-chart-empty').hidden=rows.some(row=>row[metric]!=null);
        }
        drawAnomaly(rows);
    }
    function drawAnomaly(rows) {
        const canvas = document.getElementById('anomaly-chart');
        if (!canvas) return;
        const comparable = rows.filter(row => Number.isFinite(Number(row.daya)) && Number.isFinite(Number(row.ss2_prediksi)));
        let chart = charts.get('anomaly-chart');
        if (!chart) {
            chart = new Chart(canvas, {
                type: 'line',
                data: { labels: [], datasets: [
                    { label: 'Aktual', data: [], borderColor: '#0865b5', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, tension: .38 },
                    { label: 'Prediksi', data: [], borderColor: '#d97706', backgroundColor: 'transparent', borderDash: [6, 5], borderWidth: 1.5, pointRadius: 0, pointHoverRadius: 4, tension: .38, fill: false }
                ]},
                options: { responsive: true, maintainAspectRatio: false, animation: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 7 } } }, scales: { x: { ticks: { maxTicksLimit: 6, maxRotation: 0 }, grid: { display: false } }, y: { title: { display: true, text: 'Daya (W)' }, grid: { color: '#e2e8f0' } } } }
            });
            charts.set('anomaly-chart', chart);
        }
        chart.data.labels = comparable.map(row => row.label);
        chart.data.datasets[0].data = comparable.map(row => row.daya);
        chart.data.datasets[1].data = comparable.map(row => row.ss2_prediksi);
        chart.update('none');
        canvas.parentElement.querySelector('.sensor-chart-empty').hidden = comparable.length > 0;
    }
    // Fetch the complete selected range when exporting, including readings
    // received since this page was opened. Charts use the bounded live window.
    window.labReport = { get data() { return request(`${app.dataset.readings}?${period}`); }, lampText, request };
    async function refreshCharts() {
        try {
            const url = filtered ? `${app.dataset.readings}?${period}` : `${app.dataset.readings}?latest=1`;
            const data = await request(url);
            draw(data.rows);
            chartMessage.textContent = filtered ? 'Grafik mengikuti rentang waktu yang dipilih.' : '';
        } catch {
            chartMessage.textContent = 'Grafik belum dapat diperbarui. Mencoba lagi otomatis…';
        }
    }
    async function refreshHistory() {
        const body = document.getElementById('lab-history-body');
        const pagination = document.getElementById('lab-history-pagination');
        const message = document.getElementById('history-message');
        if (!body || !pagination || !message) return;
        const query = new URLSearchParams(location.search);
        query.set('table', '1');
        try {
            const data = await request(`${app.dataset.readings}?${query}`);
            // Keep links stable when the rows/count have not changed.
            if (body.innerHTML !== data.html) body.innerHTML = data.html;
            if (pagination.innerHTML !== data.pagination) pagination.innerHTML = data.pagination;
            if (!filtered && data.period) {
                for (const name of ['from', 'to']) {
                    const input = document.querySelector(`[name="${name}"]`);
                    if (input.value === period.get(name)) input.value = data.period[name];
                    period.set(name, data.period[name]);
                }
            }
            message.textContent = '';
        } catch {
            message.textContent = 'Rekap belum dapat diperbarui. Mencoba lagi otomatis…';
        }
    }
    async function refreshReading() {
        try {
            const data = await request(app.dataset.live);
            const status = document.getElementById('connection');
            status.textContent = data.online ? 'Sistem Online' : 'Sistem Offline';
            status.className = data.online ? 'online' : 'offline';
            document.getElementById('last-reading').textContent = data.reading ? `Data terakhir: ${data.reading.time}` : 'Belum ada pembacaan perangkat';
            document.querySelectorAll('[data-reading]:not([data-reading="ss2_status"])').forEach(node => { node.textContent = data.reading ? number(data.reading[node.dataset.reading]) : '—'; });
            const predictionStatus = document.querySelector('[data-reading="ss2_status"]');
            if (predictionStatus) {
                predictionStatus.textContent = typeof data.reading?.ss2_status === 'string' ? data.reading.ss2_status : 'Menunggu data pembanding';
                predictionStatus.className = `pv-prediction-status is-${data.reading?.ss2_level || 'waiting'}`;
            }

        } catch {
            document.getElementById('connection').textContent = 'Sistem Offline';
            document.getElementById('connection').className = 'offline';
        }
    }

    async function refresh() {
        if (refreshing || document.hidden) return;
        refreshing = true;
        try {
            await Promise.allSettled([refreshReading(), refreshCharts(), refreshHistory()]);
        } finally {
            refreshing = false;
        }
    }

    refresh();
    setInterval(refresh, refreshInterval);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('online', refresh);
})();
