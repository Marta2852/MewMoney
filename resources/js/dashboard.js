import Chart from 'chart.js/auto';

const completedToggle = document.getElementById('completedToggle');
const completedGoals = document.getElementById('completedGoals');
const completedArrow = document.getElementById('completedArrow');

if (completedToggle && completedGoals && completedArrow) {
    completedToggle.addEventListener('click', () => {
        const isOpen = completedGoals.hidden;

        completedGoals.hidden = !isOpen;
        completedToggle.setAttribute('aria-expanded', String(isOpen));
        completedArrow.textContent = isOpen ? '▲' : '▼';
    });
}

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