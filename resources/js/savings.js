import Chart from 'chart.js/auto';

const completedToggle = document.getElementById('completedToggle');
const completedGoals = document.getElementById('completedGoals');
const completedArrow = document.getElementById('completedArrow');

if (completedToggle) {
    completedToggle.addEventListener('click', () => {
        completedGoals.classList.toggle('open');

        if (completedGoals.classList.contains('open')) {
            completedArrow.textContent = '▲';
        } else {
            completedArrow.textContent = '▼';
        }
    });
}

const chart = document.getElementById('savingsGrowthChart');

if (chart) {

    const goals = window.savingsGrowth || {};
    const activeGoals = window.activeGoals || [];

    function cumulativeAmounts(goalData, dates) {
        let runningTotal = 0;

        return dates.map(date => {
            const pointsForDate = goalData.filter(point => point.date === date);

            if (pointsForDate.length > 0) {
                runningTotal = Number(pointsForDate[pointsForDate.length - 1].amount);
            }

            return runningTotal;
        });
    }

    let allDates = [];

    activeGoals.forEach(goalName => {
        if (goals[goalName]) {
            goals[goalName].forEach(point => {
                if (!allDates.includes(point.date)) {
                    allDates.push(point.date);
                }
            });
        }
    });

    allDates.sort((firstDate, secondDate) => {
        return new Date(firstDate).getTime() - new Date(secondDate).getTime();
    });

    function createDatasets() {
        const goalsToShow = {};

        activeGoals.forEach(goalName => {
            if (goals[goalName]) {
                goalsToShow[goalName] = goals[goalName];
            }
        });

        return Object.entries(goalsToShow).map(
            ([goalName, goalData]) => {
                const data = cumulativeAmounts(goalData, allDates);

                return {
                    label: goalName,
                    data: data,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                };
            }
        );
    }

    const initialDatasets = createDatasets();
    const largestInitialAmount = Math.max(
        0,
        ...initialDatasets.flatMap(dataset => dataset.data)
    );

    const savingsChart = new Chart(chart, {

        type: 'line',

        data: {
            labels: allDates,
            datasets: initialDatasets
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
                    suggestedMax: largestInitialAmount > 0
                        ? Math.ceil(largestInitialAmount * 1.1)
                        : 10,

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

    const seeGrowthButtons =
        document.querySelectorAll('.see-growth-btn');

        seeGrowthButtons.forEach(button => {

        button.addEventListener('click', () => {

            const goalName = button.dataset.goal;
            const goalData = goals[goalName];

            if (!goalData || goalData.length === 0) {
                return;
            }

            const goalDates = [...new Set(goalData.map(point => point.date))];
            const goalAmounts = cumulativeAmounts(goalData, goalDates);

            savingsChart.data.labels = goalDates;
            savingsChart.data.datasets = [
                {
                    label: goalName,
                    data: goalAmounts,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    borderWidth: 2,
                }
            ];

            savingsChart.options.plugins.legend.display = false;
            savingsChart.options.scales.x.title.text = `${goalName} growth`;
            savingsChart.update();

            const growthTitle = document.querySelector('.savings-growth h2');

            if (growthTitle) {
                growthTitle.innerHTML = `
                    <button
                        type="button"
                        class="back-to-growth"
                        id="backToGrowth"
                    >
                        ← Back
                    </button>

                    ${goalName} — Growth
                `;
            }

            const backButton = document.getElementById('backToGrowth');

            if (backButton) {

                backButton.addEventListener('click', () => {
                    savingsChart.data.labels = allDates;
                    savingsChart.data.datasets = createDatasets();
                    savingsChart.options.plugins.legend.display = true;
                    savingsChart.options.scales.x.title.text = 'Savings activity';
                    savingsChart.update();

                    if (growthTitle) {
                        growthTitle.textContent = 'Savings Growth';
                    }
                });
            }
        });
    });
}