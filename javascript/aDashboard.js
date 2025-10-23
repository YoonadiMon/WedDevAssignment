// Get colors from style.css
const styles = getComputedStyle(document.documentElement);
const mainGreen = styles.getPropertyValue('--MainGreen').trim();
const lowGreen = styles.getPropertyValue('--LowGreen').trim();
const white = styles.getPropertyValue('--White').trim();
const gray = styles.getPropertyValue('--Gray').trim();
const darkGray = styles.getPropertyValue('--DarkGray').trim();
const black = styles.getPropertyValue('--Black').trim();

document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('signupsChart');
    
    if (!chartElement) return;
    
    // Get data from data attributes
    const weeklyLabels = JSON.parse(chartElement.dataset.weeklyLabels || '[]');
    const weeklyData = JSON.parse(chartElement.dataset.weeklyData || '[]');

    function formatNumber(num) {
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + ' k';
        }
        return num.toString();
    }
    
    // Chart
    const ctx = chartElement.getContext('2d');
    const signupsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklyLabels.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Active Users',
                data: weeklyData,
                borderColor: mainGreen,
                backgroundColor: lowGreen,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: mainGreen,
                pointBorderColor: white,
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: mainGreen,
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold',
                    },
                    bodyFont: {
                        size: 13
                    },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' active users';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: darkGray,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: gray,
                        callback: function(value) {
                            return formatNumber(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        color: gray
                    }
                }
            }
        }
    });
});