export function d3charts() {
    return { drawBar, drawDonut, drawGroupedBar };
}

const COLORS = {
    DRAFT: '#6c757d',
    REVIEW: '#ffc107',
    APPROVED: '#28a745',
    REJECTED: '#dc3545',
    GOOD: '#39ff14',
    SUSPECT: '#ffc107',
    BAD: '#dc3545',
    MAINTENANCE: '#17a2b8',
    SIMULATED: '#6f42c1',
    WARNING: '#ffc107',
    ALERT: '#fd7e14',
    EMERGENCY: '#dc3545',
    ALARM: '#17a2b8',
    SCRAM: '#dc3545',
};

const COLOR_CATEGORICAL = [
    '#39ff14', '#ffd700', '#17a2b8', '#dc3545', '#6f42c1',
    '#fd7e14', '#20c997', '#e83e8c', '#007bff', '#6c757d',
];

function colorFor(label) {
    if (COLORS[label]) return COLORS[label];
    const idx = Math.abs(hashCode(label)) % COLOR_CATEGORICAL.length;
    return COLOR_CATEGORICAL[idx];
}

function hashCode(s) {
    let h = 0;
    for (let i = 0; i < s.length; i++) {
        h = (h * 31 + s.charCodeAt(i)) | 0;
    }
    return h;
}

function svgContainer(selector, margin = {}) {
    const el = document.querySelector(selector);
    if (!el) return null;
    const rect = el.getBoundingClientRect();
    const width = rect.width || 400;
    const height = rect.height || 300;
    const m = { top: 20, right: 20, bottom: 40, left: 50, ...margin };
    const innerW = width - m.left - m.right;
    const innerH = height - m.top - m.bottom;

    let svg = d3.select(selector).select('svg');
    if (svg.empty()) {
        svg = d3.select(selector).append('svg')
            .attr('width', width)
            .attr('height', height);
    } else {
        svg.attr('width', width).attr('height', height);
    }

    const g = svg.select('g.chart-group');
    if (g.empty()) {
        return {
            svg, width, height, m, innerW, innerH,
            g: svg.append('g').attr('class', 'chart-group')
                .attr('transform', `translate(${m.left},${m.top})`),
        };
    }
    return { svg, width, height, m, innerW, innerH, g };
}

function drawBar(selector, data, { labelKey = 'label', valueKey = 'value', title = '' } = {}) {
    const sc = svgContainer(selector, { bottom: 50, left: 10 });
    if (!sc) return;
    const { g, innerW, innerH } = sc;
    g.selectAll('*').remove();

    if (!data || data.length === 0) {
        g.append('text').attr('x', innerW / 2).attr('y', innerH / 2)
            .attr('text-anchor', 'middle').attr('fill', '#666')
            .text('No data');
        return;
    }

    const x = d3.scaleBand()
        .domain(data.map(d => d[labelKey]))
        .range([0, innerW])
        .padding(0.25);

    const y = d3.scaleLinear()
        .domain([0, d3.max(data, d => d[valueKey]) * 1.1 || 1])
        .range([innerH, 0]);

    g.append('g').call(d3.axisLeft(y).ticks(5).tickFormat(d3.format('d')))
        .attr('color', '#666').attr('font-size', '10px');

    g.append('g').call(d3.axisBottom(x))
        .attr('transform', `translate(0,${innerH})`)
        .attr('color', '#666').attr('font-size', '10px')
        .selectAll('text').attr('transform', 'rotate(-25)').style('text-anchor', 'end');

    g.selectAll('rect').data(data).enter().append('rect')
        .attr('x', d => x(d[labelKey]))
        .attr('y', d => y(d[valueKey]))
        .attr('width', x.bandwidth())
        .attr('height', d => innerH - y(d[valueKey]))
        .attr('fill', d => colorFor(d[labelKey]))
        .attr('opacity', 0.85)
        .append('title').text(d => `${d[labelKey]}: ${d[valueKey]}`);
}

function drawGroupedBar(selector, data, { groupKey = 'group', labelKey = 'label', valueKey = 'value', title = '' } = {}) {
    const sc = svgContainer(selector, { bottom: 50, left: 55 });
    if (!sc) return;
    const { g, innerW, innerH } = sc;
    g.selectAll('*').remove();

    if (!data || data.length === 0) {
        g.append('text').attr('x', innerW / 2).attr('y', innerH / 2)
            .attr('text-anchor', 'middle').attr('fill', '#666').text('No data');
        return;
    }

    const groups = [...new Set(data.map(d => d[groupKey]))];
    const labels = [...new Set(data.map(d => d[labelKey]))];

    const x0 = d3.scaleBand().domain(labels).range([0, innerW]).padding(0.15);
    const x1 = d3.scaleBand().domain(groups).range([0, x0.bandwidth()]).padding(0.1);
    const y = d3.scaleLinear()
        .domain([0, d3.max(data, d => d[valueKey]) * 1.1 || 1])
        .range([innerH, 0]);

    g.append('g').call(d3.axisLeft(y).ticks(5).tickFormat(d3.format('d')))
        .attr('color', '#666').attr('font-size', '10px');
    g.append('g').call(d3.axisBottom(x0))
        .attr('transform', `translate(0,${innerH})`)
        .attr('color', '#666').attr('font-size', '10px')
        .selectAll('text').attr('transform', 'rotate(-25)').style('text-anchor', 'end');

    const color = d3.scaleOrdinal().domain(groups).range(COLOR_CATEGORICAL);

    g.selectAll('g.group').data(labels).enter().append('g')
        .attr('transform', d => `translate(${x0(d)},0)`)
        .selectAll('rect').data(d => groups.map(g => ({ key: g, value: data.find(v => v[groupKey] === g && v[labelKey] === d) })))
        .enter().append('rect')
        .attr('x', d => x1(d.key))
        .attr('y', d => d.value ? y(d.value[valueKey]) : 0)
        .attr('width', x1.bandwidth())
        .attr('height', d => d.value ? innerH - y(d.value[valueKey]) : 0)
        .attr('fill', d => color(d.key))
        .attr('opacity', 0.85)
        .append('title').text(d => d.value ? `${d.key} ${d.value[labelKey]}: ${d.value[valueKey]}` : '');

    const legend = g.append('g').attr('transform', `translate(0, ${-20})`);
    groups.forEach((g, i) => {
        const lg = legend.append('g').attr('transform', `translate(${i * 100}, 0)`);
        lg.append('rect').attr('width', 10).attr('height', 10).attr('fill', color(g));
        lg.append('text').attr('x', 14).attr('y', 10).attr('fill', '#aaa').attr('font-size', '10px').text(g);
    });
}

function drawDonut(selector, data, { labelKey = 'label', valueKey = 'value', title = '' } = {}) {
    const sc = svgContainer(selector, { top: 10, bottom: 10, left: 10, right: 10 });
    if (!sc) return;
    const { svg, g, width, height, m, innerW, innerH } = sc;
    g.selectAll('*').remove();

    if (!data || data.length === 0) {
        g.append('text').attr('x', innerW / 2).attr('y', innerH / 2)
            .attr('text-anchor', 'middle').attr('fill', '#666').text('No data');
        return;
    }

    const radius = Math.min(innerW, innerH) / 2;
    const arc = d3.arc().innerRadius(radius * 0.55).outerRadius(radius);
    const pie = d3.pie().value(d => d[valueKey]).sort(null);
    const cg = g.append('g').attr('transform', `translate(${innerW / 2},${innerH / 2})`);

    const color = d3.scaleOrdinal().domain(data.map(d => d[labelKey])).range(data.map(d => colorFor(d[labelKey])));

    cg.selectAll('path').data(pie(data)).enter().append('path')
        .attr('d', arc)
        .attr('fill', d => color(d.data[labelKey]))
        .attr('stroke', '#1a1a2e').attr('stroke-width', 2)
        .append('title').text(d => `${d.data[labelKey]}: ${d.data[valueKey]}`);

    const legend = cg.append('g').attr('transform', `translate(${radius + 15}, ${-radius})`);
    data.forEach((d, i) => {
        const lg = legend.append('g').attr('transform', `translate(0, ${i * 20})`);
        lg.append('rect').attr('width', 10).attr('height', 10).attr('fill', color(d[labelKey]));
        lg.append('text').attr('x', 16).attr('y', 10).attr('fill', '#ccc').attr('font-size', '11px').text(`${d[labelKey]}: ${d[valueKey]}`);
    });
}
