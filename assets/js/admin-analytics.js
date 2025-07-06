// Admin Analytics System with Real-Time Data and PDF Export
class AdminAnalytics {
    constructor() {
        this.charts = {};
        this.metrics = {};
        this.updateInterval = null;
        this.pdfLibrary = null;
        this.backend = null;
        this.init();
    }

    init() {
        this.loadPDFLibrary();
        this.waitForBackend();
        this.setupEventListeners();
        this.initializeCharts();
        this.updateMetrics();
        this.startRealTimeUpdates();
    }

    async loadPDFLibrary() {
        // Load jsPDF library dynamically
        if (typeof window.jsPDF === 'undefined') {
            try {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
                script.onload = () => {
                    this.pdfLibrary = window.jsPDF;
                    console.log('PDF library loaded successfully');
                };
                document.head.appendChild(script);
            } catch (error) {
                console.error('Failed to load PDF library:', error);
            }
        } else {
            this.pdfLibrary = window.jsPDF;
        }
    }

    waitForBackend() {
        // Wait for backend to be available
        if (window.DiscipleshipBackend) {
            this.backend = window.DiscipleshipBackend;
            console.log('✅ Backend connected for real-time analytics');
        } else {
            console.log('Waiting for backend...');
            setTimeout(() => this.waitForBackend(), 100);
        }
    }

    setupEventListeners() {
        // Export buttons
        document.getElementById('export-user-analytics')?.addEventListener('click', () => this.exportUserAnalytics());
        document.getElementById('export-course-analytics')?.addEventListener('click', () => this.exportCourseAnalytics());
        document.getElementById('export-full-report')?.addEventListener('click', () => this.exportFullReport());
        
        // PDF Export buttons
        document.getElementById('export-user-pdf')?.addEventListener('click', () => this.exportUserAnalyticsPDF());
        document.getElementById('export-course-pdf')?.addEventListener('click', () => this.exportCourseAnalyticsPDF());
        document.getElementById('export-full-pdf')?.addEventListener('click', () => this.exportFullReportPDF());
        
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

        // Get real user growth data
        const data = this.getRealUserGrowthData(30);
        
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

        const data = this.getRealCompletionData();
        
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

    getRealUserGrowthData(days) {
        const labels = [];
        const values = [];
        const today = new Date();
        
        // Get real user data from backend
        const users = this.getRealUsers();
        const userJoinDates = users.map(user => new Date(user.joinedDate || user.createdAt || Date.now()));
        
        for (let i = days - 1; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(date.getDate() - i);
            labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            
            // Count users who joined on this date
            const usersOnDate = userJoinDates.filter(joinDate => {
                const joinDay = new Date(joinDate);
                return joinDay.toDateString() === date.toDateString();
            }).length;
            
            values.push(usersOnDate);
        }
        
        return { labels, values };
    }

    getRealCompletionData() {
        const users = this.getRealUsers();
        const courses = this.getRealCourses();
        
        let completed = 0;
        let inProgress = 0;
        let notStarted = 0;
        
        users.forEach(user => {
            if (user.progress && typeof user.progress === 'object') {
                Object.values(user.progress).forEach(status => {
                    if (status === 'completed') completed++;
                    else if (status === 'in-progress' || status === 'in_progress') inProgress++;
                    else notStarted++;
                });
            } else if (user.completedLessons) {
                // Alternative progress tracking
                completed += user.completedLessons || 0;
                inProgress += user.inProgressLessons || 0;
                notStarted += (courses.length * 5) - (user.completedLessons || 0) - (user.inProgressLessons || 0);
            }
        });
        
        return { completed, inProgress, notStarted };
    }

    getRealUsers() {
        if (this.backend) {
            return this.backend.getAllUsers();
        }
        // Fallback to localStorage
        return JSON.parse(localStorage.getItem('users') || '[]');
    }

    getRealCourses() {
        if (this.backend) {
            return this.backend.getAllCourses();
        }
        // Fallback to localStorage
        return JSON.parse(localStorage.getItem('courses') || '[]');
    }

    getRealCertificates() {
        if (this.backend) {
            return this.backend.getAllCertificates();
        }
        // Fallback to localStorage
        return JSON.parse(localStorage.getItem('certificates') || '[]');
    }

    getRealSupportTickets() {
        if (this.backend) {
            return this.backend.getAllSupportTickets();
        }
        // Fallback to localStorage
        return JSON.parse(localStorage.getItem('supportTickets') || '[]');
    }

    updateMetrics() {
        this.updateKeyMetrics();
        this.updateEngagementMetrics();
        this.updatePerformanceIndicators();
        this.updateRecentActivity();
    }

    updateKeyMetrics() {
        const users = this.getRealUsers();
        const courses = this.getRealCourses();
        const certificates = this.getRealCertificates();
        const tickets = this.getRealSupportTickets();
        
        // Total Users
        const totalUsers = users.length;
        document.getElementById('analytics-total-users').textContent = totalUsers;
        
        // Active Learners (users with any progress)
        const activeLearners = users.filter(user => {
            return (user.progress && Object.keys(user.progress).length > 0) || 
                   (user.completedLessons && user.completedLessons > 0) ||
                   (user.lastActivity && this.isUserActive(user.lastActivity));
        }).length;
        document.getElementById('analytics-active-learners').textContent = activeLearners;
        
        // Completion Rate
        const totalProgress = users.reduce((total, user) => {
            if (user.progress && typeof user.progress === 'object') {
                return total + Object.values(user.progress).filter(status => status === 'completed').length;
            } else if (user.completedLessons) {
                return total + (user.completedLessons || 0);
            }
            return total;
        }, 0);
        
        const totalPossible = users.length * courses.length * 5; // Assuming 5 lessons per course
        const completionRate = totalPossible > 0 ? Math.round((totalProgress / totalPossible) * 100) : 0;
        document.getElementById('analytics-completion-rate').textContent = completionRate + '%';
        
        // Average Session Time (calculated from user activity)
        const avgSession = this.calculateAverageSessionTime(users);
        document.getElementById('analytics-avg-session').textContent = avgSession + 'm';
    }

    updateEngagementMetrics() {
        const users = this.getRealUsers();
        
        // Calculate real engagement metrics
        const now = new Date();
        const oneDayAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        const oneWeekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
        const oneMonthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
        
        // Daily Active Users
        const dailyActive = users.filter(user => {
            return user.lastActivity && new Date(user.lastActivity) > oneDayAgo;
        }).length;
        document.getElementById('daily-active-users').textContent = dailyActive;
        
        // Weekly Active Users
        const weeklyActive = users.filter(user => {
            return user.lastActivity && new Date(user.lastActivity) > oneWeekAgo;
        }).length;
        document.getElementById('weekly-active-users').textContent = weeklyActive;
        
        // Monthly Active Users
        const monthlyActive = users.filter(user => {
            return user.lastActivity && new Date(user.lastActivity) > oneMonthAgo;
        }).length;
        document.getElementById('monthly-active-users').textContent = monthlyActive;
        
        // Average Time on Platform
        const avgTime = this.calculateAverageTimeOnPlatform(users);
        document.getElementById('avg-time-platform').textContent = avgTime + 'm';
    }

    updatePerformanceIndicators() {
        const users = this.getRealUsers();
        const courses = this.getRealCourses();
        const tickets = this.getRealSupportTickets();
        
        // Course Success Rate (based on completion)
        const totalEnrollments = users.reduce((total, user) => {
            return total + (user.enrolledCourses?.length || 0);
        }, 0);
        
        const totalCompletions = users.reduce((total, user) => {
            return total + (user.completedCourses?.length || 0);
        }, 0);
        
        const successRate = totalEnrollments > 0 ? Math.round((totalCompletions / totalEnrollments) * 100) : 0;
        document.getElementById('course-success-rate').textContent = successRate + '%';
        
        // User Retention (users who returned after first session)
        const retention = this.calculateUserRetention(users);
        document.getElementById('user-retention').textContent = retention + '%';
        
        // Support Resolution Rate
        const resolvedTickets = tickets.filter(ticket => ticket.status === 'resolved').length;
        const resolutionRate = tickets.length > 0 ? Math.round((resolvedTickets / tickets.length) * 100) : 0;
        document.getElementById('support-resolution').textContent = resolutionRate + '%';
        
        // Platform Uptime (static for now)
        document.getElementById('platform-uptime').textContent = '99.9%';
    }

    updateRecentActivity() {
        const activities = this.getRealRecentActivities();
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

    getRealRecentActivities() {
        const users = this.getRealUsers();
        const activities = [];
        
        // Get recent user registrations
        const recentUsers = users
            .filter(user => user.joinedDate || user.createdAt)
            .sort((a, b) => new Date(b.joinedDate || b.createdAt) - new Date(a.joinedDate || a.createdAt))
            .slice(0, 3);
        
        recentUsers.forEach(user => {
            activities.push({
                title: `New user registered: ${user.name}`,
                time: this.getTimeAgo(user.joinedDate || user.createdAt),
                icon: 'fa-user-plus',
                iconBg: 'bg-green-500'
            });
        });
        
        // Get recent course completions
        const usersWithProgress = users.filter(user => user.completedLessons > 0);
        if (usersWithProgress.length > 0) {
            const recentCompletion = usersWithProgress[0];
            activities.push({
                title: `Course completed by ${recentCompletion.name}`,
                time: this.getTimeAgo(recentCompletion.lastActivity),
                icon: 'fa-check-circle',
                iconBg: 'bg-blue-500'
            });
        }
        
        // Get recent support tickets
        const tickets = this.getRealSupportTickets();
        const recentTickets = tickets
            .sort((a, b) => new Date(b.createdAt || b.date) - new Date(a.createdAt || a.date))
            .slice(0, 2);
        
        recentTickets.forEach(ticket => {
            activities.push({
                title: `Support ticket: ${ticket.subject}`,
                time: this.getTimeAgo(ticket.createdAt || ticket.date),
                icon: 'fa-headset',
                iconBg: 'bg-purple-500'
            });
        });
        
        // Get recent certificates
        const certificates = this.getRealCertificates();
        const recentCertificates = certificates
            .sort((a, b) => new Date(b.issuedDate) - new Date(a.issuedDate))
            .slice(0, 1);
        
        recentCertificates.forEach(cert => {
            activities.push({
                title: `Certificate issued to ${cert.user?.name || 'Student'}`,
                time: this.getTimeAgo(cert.issuedDate),
                icon: 'fa-certificate',
                iconBg: 'bg-indigo-500'
            });
        });
        
        return activities.slice(0, 5); // Return top 5 activities
    }

    calculateAverageSessionTime(users) {
        const activeUsers = users.filter(user => user.lastActivity);
        if (activeUsers.length === 0) return 0;
        
        // Calculate average session time based on user activity patterns
        const totalTime = activeUsers.reduce((total, user) => {
            return total + (user.sessionTime || 30); // Default 30 minutes if not specified
        }, 0);
        
        return Math.round(totalTime / activeUsers.length);
    }

    calculateAverageTimeOnPlatform(users) {
        const activeUsers = users.filter(user => user.lastActivity);
        if (activeUsers.length === 0) return 0;
        
        // Calculate average time based on user engagement
        const totalTime = activeUsers.reduce((total, user) => {
            return total + (user.totalTimeOnPlatform || 45); // Default 45 minutes
        }, 0);
        
        return Math.round(totalTime / activeUsers.length);
    }

    calculateUserRetention(users) {
        const usersWithActivity = users.filter(user => user.lastActivity);
        if (usersWithActivity.length === 0) return 0;
        
        // Calculate retention based on users who returned after first session
        const returningUsers = usersWithActivity.filter(user => {
            const firstActivity = new Date(user.joinedDate || user.createdAt);
            const lastActivity = new Date(user.lastActivity);
            const daysDiff = (lastActivity - firstActivity) / (1000 * 60 * 60 * 24);
            return daysDiff > 1; // Returned after first day
        });
        
        return Math.round((returningUsers.length / usersWithActivity.length) * 100);
    }

    isUserActive(lastActivity) {
        if (!lastActivity) return false;
        const lastActivityTime = new Date(lastActivity);
        const now = new Date();
        const diffHours = (now - lastActivityTime) / (1000 * 60 * 60);
        return diffHours < 24; // Active if last activity was within 24 hours
    }

    getTimeAgo(timestamp) {
        if (!timestamp) return 'Unknown';
        
        const now = new Date();
        const time = new Date(timestamp);
        const diffMs = now - time;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        return `${Math.floor(diffDays / 7)}w ago`;
    }

    updateUserGrowthChart(days = 30) {
        if (this.charts.userGrowth) {
            const data = this.getRealUserGrowthData(days);
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
        // Update completion chart with real data
        if (this.charts.completion) {
            const data = this.getRealCompletionData();
            this.charts.completion.data.datasets[0].data = [data.completed, data.inProgress, data.notStarted];
            this.charts.completion.update();
        }
    }

    // CSV Export Methods (existing)
    exportUserAnalytics() {
        const users = this.getRealUsers();
        const csv = this.convertToCSV(users, ['name', 'email', 'role', 'joinDate', 'lastActivity', 'completedLessons']);
        this.downloadCSV(csv, 'user-analytics.csv');
    }

    exportCourseAnalytics() {
        const courses = this.getRealCourses();
        const csv = this.convertToCSV(courses, ['title', 'description', 'status', 'enrolledUsers', 'lessons']);
        this.downloadCSV(csv, 'course-analytics.csv');
    }

    exportFullReport() {
        const report = {
            timestamp: new Date().toISOString(),
            metrics: this.getCurrentMetrics(),
            users: this.getRealUsers(),
            courses: this.getRealCourses(),
            certificates: this.getRealCertificates(),
            supportTickets: this.getRealSupportTickets()
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'full-analytics-report.json';
        a.click();
        URL.revokeObjectURL(url);
    }

    // PDF Export Methods (updated with real data)
    async exportUserAnalyticsPDF() {
        if (!this.pdfLibrary) {
            alert('PDF library not loaded. Please wait a moment and try again.');
            return;
        }

        try {
            const { jsPDF } = this.pdfLibrary;
            const doc = new jsPDF();
            
            // Header
            doc.setFontSize(20);
            doc.setTextColor(124, 58, 237); // Ministry Purple
            doc.text('User Analytics Report', 20, 20);
            
            doc.setFontSize(12);
            doc.setTextColor(100, 100, 100);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 30);
            
            // Key Metrics
            doc.setFontSize(16);
            doc.setTextColor(32, 70, 179); // Ministry Blue
            doc.text('Key Metrics', 20, 50);
            
            const metrics = this.getCurrentMetrics();
            let yPos = 65;
            
            Object.entries(metrics).forEach(([key, value]) => {
                doc.setFontSize(12);
                doc.setTextColor(50, 50, 50);
                doc.text(`${key}: ${value}`, 25, yPos);
                yPos += 8;
            });
            
            // User Data Table
            yPos += 10;
            doc.setFontSize(16);
            doc.setTextColor(32, 70, 179);
            doc.text('User Data', 20, yPos);
            
            const users = this.getRealUsers();
            yPos += 15;
            
            // Table headers
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text('Name', 20, yPos);
            doc.text('Email', 60, yPos);
            doc.text('Role', 120, yPos);
            doc.text('Progress', 150, yPos);
            
            yPos += 8;
            
            // Table data
            users.slice(0, 20).forEach(user => { // Limit to first 20 users
                if (yPos > 250) {
                    doc.addPage();
                    yPos = 20;
                }
                
                doc.setTextColor(50, 50, 50);
                doc.text(user.name || 'N/A', 20, yPos);
                doc.text(user.email || 'N/A', 60, yPos);
                doc.text(user.role || 'student', 120, yPos);
                doc.text(`${user.completedLessons || 0} lessons`, 150, yPos);
                
                yPos += 6;
            });
            
            // Footer
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text('Hearts After God Ministry - Analytics Report', 20, 280);
            
            doc.save('user-analytics-report.pdf');
            
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
        }
    }

    async exportCourseAnalyticsPDF() {
        if (!this.pdfLibrary) {
            alert('PDF library not loaded. Please wait a moment and try again.');
            return;
        }

        try {
            const { jsPDF } = this.pdfLibrary;
            const doc = new jsPDF();
            
            // Header
            doc.setFontSize(20);
            doc.setTextColor(124, 58, 237); // Ministry Purple
            doc.text('Course Analytics Report', 20, 20);
            
            doc.setFontSize(12);
            doc.setTextColor(100, 100, 100);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 30);
            
            // Course Data
            const courses = this.getRealCourses();
            let yPos = 50;
            
            doc.setFontSize(16);
            doc.setTextColor(32, 70, 179);
            doc.text('Course Overview', 20, yPos);
            
            yPos += 15;
            
            courses.forEach((course, index) => {
                if (yPos > 250) {
                    doc.addPage();
                    yPos = 20;
                }
                
                doc.setFontSize(14);
                doc.setTextColor(253, 186, 23); // Ministry Gold
                doc.text(`${index + 1}. ${course.title || 'Untitled Course'}`, 20, yPos);
                
                yPos += 8;
                
                doc.setFontSize(10);
                doc.setTextColor(50, 50, 50);
                const description = course.description || 'No description available';
                const wrappedText = doc.splitTextToSize(description, 170);
                doc.text(wrappedText, 25, yPos);
                
                yPos += (wrappedText.length * 4) + 5;
                
                doc.setTextColor(100, 100, 100);
                doc.text(`Status: ${course.status || 'Active'}`, 25, yPos);
                doc.text(`Lessons: ${course.lessons?.length || 0}`, 80, yPos);
                doc.text(`Enrolled: ${course.enrolledUsers || 0}`, 130, yPos);
                
                yPos += 10;
            });
            
            // Footer
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text('Hearts After God Ministry - Course Analytics', 20, 280);
            
            doc.save('course-analytics-report.pdf');
            
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
        }
    }

    async exportFullReportPDF() {
        if (!this.pdfLibrary) {
            alert('PDF library not loaded. Please wait a moment and try again.');
            return;
        }

        try {
            const { jsPDF } = this.pdfLibrary;
            const doc = new jsPDF();
            
            // Title Page
            doc.setFontSize(24);
            doc.setTextColor(124, 58, 237); // Ministry Purple
            doc.text('Hearts After God Ministry', 20, 40);
            
            doc.setFontSize(20);
            doc.text('Complete Analytics Report', 20, 55);
            
            doc.setFontSize(12);
            doc.setTextColor(100, 100, 100);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 70);
            
            // Page 2: Executive Summary
            doc.addPage();
            doc.setFontSize(18);
            doc.setTextColor(32, 70, 179);
            doc.text('Executive Summary', 20, 20);
            
            const metrics = this.getCurrentMetrics();
            let yPos = 35;
            
            Object.entries(metrics).forEach(([key, value]) => {
                doc.setFontSize(12);
                doc.setTextColor(50, 50, 50);
                doc.text(`${key}: ${value}`, 25, yPos);
                yPos += 8;
            });
            
            // Page 3: User Analytics
            doc.addPage();
            doc.setFontSize(18);
            doc.setTextColor(32, 70, 179);
            doc.text('User Analytics', 20, 20);
            
            const users = this.getRealUsers();
            yPos = 35;
            
            // User statistics
            const userStats = {
                'Total Users': users.length,
                'Active Users': users.filter(u => this.isUserActive(u.lastActivity)).length,
                'Admin Users': users.filter(u => u.role === 'admin').length,
                'Student Users': users.filter(u => u.role === 'student').length
            };
            
            Object.entries(userStats).forEach(([key, value]) => {
                doc.setFontSize(12);
                doc.setTextColor(50, 50, 50);
                doc.text(`${key}: ${value}`, 25, yPos);
                yPos += 8;
            });
            
            // Page 4: Course Analytics
            doc.addPage();
            doc.setFontSize(18);
            doc.setTextColor(32, 70, 179);
            doc.text('Course Analytics', 20, 20);
            
            const courses = this.getRealCourses();
            yPos = 35;
            
            const courseStats = {
                'Total Courses': courses.length,
                'Active Courses': courses.filter(c => c.status === 'active').length,
                'Total Lessons': courses.reduce((total, c) => total + (c.lessons?.length || 0), 0),
                'Average Lessons per Course': courses.length > 0 ? Math.round(courses.reduce((total, c) => total + (c.lessons?.length || 0), 0) / courses.length) : 0
            };
            
            Object.entries(courseStats).forEach(([key, value]) => {
                doc.setFontSize(12);
                doc.setTextColor(50, 50, 50);
                doc.text(`${key}: ${value}`, 25, yPos);
                yPos += 8;
            });
            
            // Page 5: Recent Activity
            doc.addPage();
            doc.setFontSize(18);
            doc.setTextColor(32, 70, 179);
            doc.text('Recent Activity', 20, 20);
            
            const activities = this.getRealRecentActivities();
            yPos = 35;
            
            activities.forEach(activity => {
                doc.setFontSize(10);
                doc.setTextColor(50, 50, 50);
                doc.text(`• ${activity.title}`, 25, yPos);
                
                yPos += 6;
                doc.setTextColor(100, 100, 100);
                doc.text(`  ${activity.time}`, 30, yPos);
                
                yPos += 8;
            });
            
            // Footer on all pages
            for (let i = 1; i <= doc.getNumberOfPages(); i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(100, 100, 100);
                doc.text(`Page ${i} of ${doc.getNumberOfPages()}`, 20, 290);
                doc.text('Hearts After God Ministry - Complete Analytics Report', 20, 295);
            }
            
            doc.save('complete-analytics-report.pdf');
            
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
        }
    }

    getCurrentMetrics() {
        return {
            'Total Users': document.getElementById('analytics-total-users')?.textContent || '0',
            'Active Learners': document.getElementById('analytics-active-learners')?.textContent || '0',
            'Completion Rate': document.getElementById('analytics-completion-rate')?.textContent || '0%',
            'Average Session Time': document.getElementById('analytics-avg-session')?.textContent || '0m',
            'Daily Active Users': document.getElementById('daily-active-users')?.textContent || '0',
            'Weekly Active Users': document.getElementById('weekly-active-users')?.textContent || '0',
            'Monthly Active Users': document.getElementById('monthly-active-users')?.textContent || '0',
            'Course Success Rate': document.getElementById('course-success-rate')?.textContent || '0%',
            'User Retention': document.getElementById('user-retention')?.textContent || '0%',
            'Support Resolution': document.getElementById('support-resolution')?.textContent || '0%',
            'Platform Uptime': document.getElementById('platform-uptime')?.textContent || '0%'
        };
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