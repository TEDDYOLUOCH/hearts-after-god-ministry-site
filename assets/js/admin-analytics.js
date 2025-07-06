// Admin Analytics System
class AdminAnalytics {
    constructor() {
        this.charts = {};
        this.metrics = {};
        this.updateInterval = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.updateMetrics();
        this.startRealTimeUpdates();
    }

    setupEventListeners() {
        // Export buttons
        document.getElementById('export-user-analytics')?.addEventListener('click', () => this.exportUserAnalytics());
        document.getElementById('export-course-analytics')?.addEventListener('click', () => this.exportCourseAnalytics());
        document.getElementById('export-full-report')?.addEventListener('click', () => this.exportFullReport());
        
        // Period selector
        document.getElementById('user-growth-period')?.addEventListener('change', (e) => {
            this.updateUserGrowthChart(parseInt(e.target.value));
        });
    }

    initializeCharts() {
        this.createUserGrowthChart();
        this.createCompletionChart();
    }

    createUserGrowthChart() {
        const ctx = document.getElementById('user-growth-chart');
        if (!ctx) return;

        // Sample data - in real app, this would come from backend
        const data = this.generateUserGrowthData(30);
        
        this.charts.userGrowth = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'New Users',
                    data: data.values,
                    borderColor: '#7C3AED',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    createCompletionChart() {
        const ctx = document.getElementById('completion-chart');
        if (!ctx) return;

        const data = this.getCompletionData();
        
        this.charts.completion = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Not Started'],
                datasets: [{
                    data: [data.completed, data.inProgress, data.notStarted],
                    backgroundColor: [
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ],
                    borderWidth: 0
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

    generateUserGrowthData(days) {
        const labels = [];
        const values = [];
        const today = new Date();
        
        for (let i = days - 1; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            
            // Generate realistic growth data
            const baseValue = 50 + (days - i) * 2;
            const randomFactor = 0.8 + Math.random() * 0.4;
            values.push(Math.floor(baseValue * randomFactor));
        }
        
        return { labels, values };
    }

    getCompletionData() {
        // Get data from backend or localStorage
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const courses = JSON.parse(localStorage.getItem('courses') || '[]');
        
        let completed = 0;
        let inProgress = 0;
        let notStarted = 0;
        
        users.forEach(user => {
            if (user.progress) {
                Object.values(user.progress).forEach(status => {
                    if (status === 'completed') completed++;
                    else if (status === 'in-progress') inProgress++;
                    else notStarted++;
                });
            }
        });
        
        return { completed, inProgress, notStarted };
    }

    updateMetrics() {
        this.updateKeyMetrics();
        this.updateEngagementMetrics();
        this.updatePerformanceIndicators();
        this.updateRecentActivity();
    }

    updateKeyMetrics() {
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const courses = JSON.parse(localStorage.getItem('courses') || '[]');
        
        // Total Users
        const totalUsers = users.length;
        document.getElementById('analytics-total-users').textContent = totalUsers;
        
        // Active Learners (users with any progress)
        const activeLearners = users.filter(user => user.progress && Object.keys(user.progress).length > 0).length;
        document.getElementById('analytics-active-learners').textContent = activeLearners;
        
        // Completion Rate
        const totalProgress = users.reduce((total, user) => {
            if (user.progress) {
                return total + Object.values(user.progress).filter(status => status === 'completed').length;
            }
            return total;
        }, 0);
        
        const totalPossible = users.length * courses.length;
        const completionRate = totalPossible > 0 ? Math.round((totalProgress / totalPossible) * 100) : 0;
        document.getElementById('analytics-completion-rate').textContent = completionRate + '%';
        
        // Average Session Time (simulated)
        const avgSession = Math.floor(Math.random() * 45) + 15; // 15-60 minutes
        document.getElementById('analytics-avg-session').textContent = avgSession + 'm';
    }

    updateEngagementMetrics() {
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        
        // Daily Active Users (simulated)
        const dailyActive = Math.floor(users.length * 0.3) + Math.floor(Math.random() * 10);
        document.getElementById('daily-active-users').textContent = dailyActive;
        
        // Weekly Active Users
        const weeklyActive = Math.floor(users.length * 0.6) + Math.floor(Math.random() * 15);
        document.getElementById('weekly-active-users').textContent = weeklyActive;
        
        // Monthly Active Users
        const monthlyActive = Math.floor(users.length * 0.8) + Math.floor(Math.random() * 20);
        document.getElementById('monthly-active-users').textContent = monthlyActive;
        
        // Average Time on Platform
        const avgTime = Math.floor(Math.random() * 30) + 20; // 20-50 minutes
        document.getElementById('avg-time-platform').textContent = avgTime + 'm';
    }

    updatePerformanceIndicators() {
        // Course Success Rate
        const successRate = 75 + Math.floor(Math.random() * 20); // 75-95%
        document.getElementById('course-success-rate').textContent = successRate + '%';
        
        // User Retention
        const retention = 60 + Math.floor(Math.random() * 30); // 60-90%
        document.getElementById('user-retention').textContent = retention + '%';
        
        // Support Resolution
        const resolution = 85 + Math.floor(Math.random() * 10); // 85-95%
        document.getElementById('support-resolution').textContent = resolution + '%';
        
        // Platform Uptime (static)
        document.getElementById('platform-uptime').textContent = '99.9%';
    }

    updateRecentActivity() {
        const activities = this.generateRecentActivities();
        const container = document.getElementById('recent-activity-list');
        if (!container) return;
        
        container.innerHTML = activities.map(activity => `
            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50">
                <div class="w-8 h-8 rounded-full flex items-center justify-center ${activity.iconBg}">
                    <i class="fas ${activity.icon} text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">${activity.title}</p>
                    <p class="text-xs text-gray-500">${activity.time}</p>
                </div>
            </div>
        `).join('');
    }

    generateRecentActivities() {
        const activities = [
            { title: 'New user registered', time: '2 minutes ago', icon: 'fa-user-plus', iconBg: 'bg-green-500' },
            { title: 'Course completed', time: '5 minutes ago', icon: 'fa-check-circle', iconBg: 'bg-blue-500' },
            { title: 'Support ticket resolved', time: '12 minutes ago', icon: 'fa-headset', iconBg: 'bg-purple-500' },
            { title: 'New course added', time: '1 hour ago', icon: 'fa-book', iconBg: 'bg-yellow-500' },
            { title: 'Certificate issued', time: '2 hours ago', icon: 'fa-certificate', iconBg: 'bg-indigo-500' }
        ];
        
        return activities;
    }

    updateUserGrowthChart(days = 30) {
        if (this.charts.userGrowth) {
            const data = this.generateUserGrowthData(days);
            this.charts.userGrowth.data.labels = data.labels;
            this.charts.userGrowth.data.datasets[0].data = data.values;
            this.charts.userGrowth.update();
        }
    }

    startRealTimeUpdates() {
        this.updateInterval = setInterval(() => {
            this.updateMetrics();
            this.updateCharts();
        }, 30000); // Update every 30 seconds
    }

    updateCharts() {
        // Update completion chart
        if (this.charts.completion) {
            const data = this.getCompletionData();
            this.charts.completion.data.datasets[0].data = [data.completed, data.inProgress, data.notStarted];
            this.charts.completion.update();
        }
    }

    exportUserAnalytics() {
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const csv = this.convertToCSV(users, ['name', 'email', 'role', 'joinDate']);
        this.downloadCSV(csv, 'user-analytics.csv');
    }

    exportCourseAnalytics() {
        const courses = JSON.parse(localStorage.getItem('courses') || '[]');
        const csv = this.convertToCSV(courses, ['title', 'description', 'status']);
        this.downloadCSV(csv, 'course-analytics.csv');
    }

    exportFullReport() {
        const report = {
            timestamp: new Date().toISOString(),
            metrics: {
                totalUsers: document.getElementById('analytics-total-users').textContent,
                activeLearners: document.getElementById('analytics-active-learners').textContent,
                completionRate: document.getElementById('analytics-completion-rate').textContent,
                avgSessionTime: document.getElementById('analytics-avg-session').textContent
            },
            users: JSON.parse(localStorage.getItem('users') || '[]'),
            courses: JSON.parse(localStorage.getItem('courses') || '[]')
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'full-analytics-report.json';
        a.click();
        URL.revokeObjectURL(url);
    }

    convertToCSV(data, fields) {
        const headers = fields.join(',');
        const rows = data.map(item => 
            fields.map(field => `"${item[field] || ''}"`).join(',')
        );
        return [headers, ...rows].join('\n');
    }

    downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        
        Object.values(this.charts).forEach(chart => {
            if (chart && chart.destroy) {
                chart.destroy();
            }
        });
    }
}

// Initialize analytics when the page loads
document.addEventListener('DOMContentLoaded', () => {
    // Check if Chart.js is available
    if (typeof Chart !== 'undefined') {
        window.adminAnalytics = new AdminAnalytics();
    } else {
        console.warn('Chart.js not loaded. Analytics charts will not be available.');
    }
});

// Clean up when leaving the page
window.addEventListener('beforeunload', () => {
    if (window.adminAnalytics) {
        window.adminAnalytics.destroy();
    }
}); 