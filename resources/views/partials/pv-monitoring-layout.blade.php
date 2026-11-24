<style>
#history-message:empty { display: none; }
@media (min-width: 901px) {
    .pv-monitoring-grid {
        grid-template-columns: repeat(6, minmax(0, 1fr));
    }
    .pv-monitoring-grid > .lab-main-column,
    .pv-monitoring-grid > .lab-main-column > .pv-charts {
        display: contents;
    }
    .pv-monitoring-grid > .lab-main-column > .lab-overview {
        grid-column: 1 / span 4;
        grid-row: 1;
    }
    .pv-monitoring-grid > .lab-main-column > .pv-prediction {
        grid-column: 1 / span 4;
        grid-row: 2;
        margin-top: 0;
    }
    .pv-monitoring-grid > .lab-side-column {
        grid-column: 5 / -1;
        grid-row: 1 / span 2;
        align-self: start;
    }
    .pv-monitoring-grid > .lab-main-column > .pv-charts > .pv-chart:first-child {
        grid-column: 1 / span 4;
        grid-row: 3;
    }
    .pv-monitoring-grid > .lab-main-column > .pv-charts > .pv-chart:not(:first-child) {
        grid-column: span 3;
    }
    .pv-monitoring-grid > .lab-main-column > #chart-message {
        grid-column: 1 / -1;
    }
}
</style>
