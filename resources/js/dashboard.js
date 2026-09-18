import Chart from 'chart.js/auto';

const chart = document.getElementById('expensesChart');

if (chart) {
    new Chart(chart, {
        type: 'pie',

        data: {
            labels: window.expenseCategoryLabels,
            datasets: [
                {
                    data: window.expenseCategoryData
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}