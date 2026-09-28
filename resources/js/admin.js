import Chart from 'chart.js/auto';

const chart = document.getElementById('userRegistrationsChart');

if (chart) {

    const registrations = window.userRegistrations;

    const monthlyData = Array(12).fill(0);

    registrations.forEach(registration => {
        monthlyData[registration.month - 1] = registration.total;
    });

    new Chart(chart, {
        type: 'line',

        data: {
            labels: [
                'Jan', 'Feb', 'Mar', 'Apr',
                'May', 'Jun', 'Jul', 'Aug',
                'Sep', 'Oct', 'Nov', 'Dec'
            ],

            datasets: [
                {
                    label: 'New Users',
                    data: monthlyData,
                    tension: 0.3
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

}