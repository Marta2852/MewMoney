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
    const palette = getComputedStyle(document.documentElement);
    const chartColors = [
        '--color-peach',
        '--color-sage',
        '--color-pink',
        '--color-cat-brown',
        '--color-soft-red',
        '--color-dark-brown'
    ].map((color) => palette.getPropertyValue(color).trim());
    const categoryColors = window.expenseCategoryData.map(
        (_, index) => chartColors[index % chartColors.length]
    );
    const chartBorderColor = palette.getPropertyValue('--color-beige').trim();

    new Chart(chart, {
        type: 'pie',

        data: {
            labels: window.expenseCategoryLabels,
            datasets: [
                {
                    data: window.expenseCategoryData,
                    backgroundColor: categoryColors,
                    borderColor: chartBorderColor,
                    borderWidth: 2
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}