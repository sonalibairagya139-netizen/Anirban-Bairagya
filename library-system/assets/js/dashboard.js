document.addEventListener('DOMContentLoaded', function() {
    // Chart initialization for reports
    if(document.getElementById('issuesChart')) {
        initIssuesChart();
    }

    // Search functionality
    const searchForms = document.querySelectorAll('.dashboard-search');
    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchInput = this.querySelector('input[name="search"]');
            const searchValue = searchInput.value.trim();
            // Implement your search logic here
            console.log('Searching for:', searchValue);
        });
    });

    // Date range picker for reports
    if(document.getElementById('dateRange')) {
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            maxDate: "today"
        });
    }

    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if(sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.dashboard-sidebar').classList.toggle('active');
        });
    }
});

// Initialize chart for reports
function initIssuesChart() {
    const ctx = document.getElementById('issuesChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Books Issued',
                data: [12, 19, 15, 21, 14, 16, 18, 17, 20, 25, 22, 28],
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            }, {
                label: 'Books Returned',
                data: [10, 15, 13, 18, 12, 14, 16, 15, 18, 22, 20, 25],
                backgroundColor: 'rgba(46, 204, 113, 0.7)',
                borderColor: 'rgba(46, 204, 113, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Book Issues'
                }
            }
        }
    });
}

// Export data function for reports
function exportReportData(format) {
    console.log('Exporting data as:', format);
    // Implement your export functionality here
    alert(`Exporting report data as ${format.toUpperCase()}...`);
}