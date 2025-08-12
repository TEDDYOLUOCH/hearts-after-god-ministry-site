document.addEventListener('DOMContentLoaded', function() {
    // Analytics Chart
    const analyticsChart = document.getElementById('analyticsChart');
    if (analyticsChart) {
        const chart = new Chart(analyticsChart, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Website Views',
                    data: [120, 190, 300, 500, 200, 300, 450],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Engagement Chart
    const engagementChart = document.getElementById('engagementChart');
    if (engagementChart) {
        const chart = new Chart(engagementChart, {
            type: 'doughnut',
            data: {
                labels: ['Posts', 'Events', 'Sermons'],
                datasets: [{
                    data: [65, 20, 15],
                    backgroundColor: ['#667eea', '#34d399', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});