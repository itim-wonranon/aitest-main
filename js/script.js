$(document).ready(function () {
    
    // 1. DYNAMIC COMPONENT LOADING
    // Using jQuery to load structural partials (Simulating Server-side includes)
    const loadComponents = async () => {
        try {
            // Load components sequentially to map DOM events correctly
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            await $('#sidebar-placeholder').load('includes/sidebar.php?page=' + currentPage).promise();
            await $('#header-placeholder').load('includes/header.php').promise();
            await $('#footer-placeholder').load('includes/footer.php').promise();
            
            // Re-bind Toggle Event after header is injected
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });

            // Re-init Bootstrap Drodowns (if needed)
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
              return new bootstrap.Dropdown(dropdownToggleEl)
            });
            
            // 2. Initialize Dashboard Charts (Chart.js)
            initDashboardCharts();
            
        } catch(e) {
            console.error("[Frontend Error]: Failed to load includes. Ensure you are running under a web server (e.g., http://localhost/aitest/)", e);
        }
    };

    // Execute loaders
    loadComponents();

    // -------------------------------------------------------------
    // CHAT.JS INITIALIZATIONS FOR DASHBOARD (Module 6)
    // -------------------------------------------------------------
    function initDashboardCharts() {
        // A. Attendance Chart (Doughnut)
        const attendanceCtxElem = document.getElementById('attendanceChart');
        if (attendanceCtxElem) {
            const attendanceCtx = attendanceCtxElem.getContext('2d');
            new Chart(attendanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['มาเรียน (Present)', 'ขาด (Absent)', 'สาย (Late)', 'ลา (Leave)'],
                    datasets: [{
                        data: [82, 8, 7, 3], // Mocked Real-time Data
                        backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e', '#36b9cc'],
                        hoverOffset: 4,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: false, // Hidden standard legend to use custom HTML legend
                        },
                        tooltip: { backgroundColor: '#333', bodyFont: { family: 'Prompt' } }
                    },
                    cutout: '70%',
                }
            });
        }

        // B. Grade Distribution Chart (Bar)
        const gradeCtxElem = document.getElementById('gradeChart');
        if (gradeCtxElem) {
            const gradeCtx = gradeCtxElem.getContext('2d');
            new Chart(gradeCtx, {
                type: 'bar',
                data: {
                    labels: ['เกรด 4', 'เกรด 3', 'เกรด 2', 'เกรด 1', 'เกรด 0'],
                    datasets: [{
                        label: 'จำนวนนักเรียน (คน)',
                        data: [500, 850, 420, 180, 50], // Mocked Real-time Data
                        backgroundColor: [
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(246, 140, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ],
                        borderColor: [
                            '#1cc88a', '#36b9cc', '#f6c23e', '#f68c3e', '#e74a3b'
                        ],
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                font: { family: 'Prompt' }
                            }
                        },
                        x: {
                            ticks: {
                                font: { family: 'Prompt' }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#333', titleFont: { family: 'Prompt' }, bodyFont: { family: 'Prompt'} }
                    }
                }
            });
        }
    }

    // -------------------------------------------------------------
    // AJAX TOGGLE HANDLER (Module 5: Attendance)
    // -------------------------------------------------------------
    // Globally accessible function to trigger attendance via UI (Mock)
    window.toggleAttendanceStatus = function(studentId, newStatus) {
        // e.g. $.ajax({ url: 'api/attendance/update', method: 'POST', data: {id: studentId, status: newStatus} ... })
        console.log(`[AJAX] Student ID ${studentId} status updated to: ${newStatus} without reloading view.`);
    };
});
