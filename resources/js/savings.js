import Chart from 'chart.js/auto';

const chart = document.getElementById('savingsGrowthChart');

if (chart) {

    const goals = window.savingsGrowth;

    const allDates = [];

    Object.values(goals).forEach(goalData => {
        goalData.forEach(point => {

            if (!allDates.includes(point.date)) {
                allDates.push(point.date);
            }

        });
    });

    const datasets = Object.entries(goals).map(([goalName, goalData]) => {

        let lastAmount = 0;

        const data = allDates.map(date => {

            const point = goalData.find(item => item.date === date);

            if (point) {
                lastAmount = point.amount;
            }

            return lastAmount;
        });

        return {
            label: goalName,
            data: data,
            tension: 0.3,
            pointRadius: 4,
            pointHoverRadius: 7,
            borderWidth: 2,
        };
    });

    new Chart(chart, {
        type: 'line',

        data: {
            labels: allDates,
            datasets: datasets
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            interaction: {
                mode: 'index',
                intersect: false,
            },

            plugins: {
                legend: {
                    position: 'bottom'
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    ticks: {
                        callback: function(value) {
                            return '€' + value;
                        }
                    }
                },

                x: {
                    title: {
                        display: true,
                        text: 'Savings activity'
                    }
                }
            }
        }
    });
}