// Get colors from style.css
const styles = getComputedStyle(document.documentElement);
const mainGreen = styles.getPropertyValue('--MainGreen').trim();
const lowGreen = styles.getPropertyValue('--LowGreen').trim();
const white = styles.getPropertyValue('--White').trim();
const gray = styles.getPropertyValue('--Gray').trim();
const darkGray = styles.getPropertyValue('--DarkGray').trim();
const black = styles.getPropertyValue('--Black').trim();

document.addEventListener('DOMContentLoaded', function() {
    // Fake Data (Temporary)
    const totalUsers = 7100;
    const inactiveUsers = 1100;
    const activeUsers = totalUsers - inactiveUsers;
    const todaySignUps = 21;
    const topCountry = "Malaysia";
    const topCountryPercentage = 70;
    const totalEvents = 1100;
    const ongoingEvents = 10;
    const totalBlogs = 1115;
    const finishedTrades = 987;
    const successTrades = 700;
    const successTradesPercent = successTrades / finishedTrades * 100;
    const totalTickets = 522;
    const pendingTickets = 32;
    const resolvedTickets = totalTickets - pendingTickets;
    const mostCommonIssue = "Login Issues";
    const mostCommonPercentage = 40;

    const weeklySignups = [
        { week: "Week 1", signups: 145 },
        { week: "Week 2", signups: 178 },
        { week: "Week 3", signups: 210 },
        { week: "Week 4", signups: 189 },
        { week: "Week 5", signups: 234 },
        { week: "Week 6", signups: 267 },
        { week: "Week 7", signups: 298 }
    ];

    const tickets = [
        { id: "2025-2112A", category: "Log In Issue", user: "Alice", dateTime: "2025-01-11 00:11 MYT", priority: "high", status: "Pending" },
        { id: "2025-2122A", category: "Log In Issue", user: "Joe", dateTime: "2025-01-10 21:04 MYT", priority: "medium", status: "Pending" },
        { id: "2025-2132A", category: "Log In Issue", user: "John", dateTime: "2025-01-09 10:03 MYT", priority: "medium", status: "Pending" },
        { id: "2025-2102A", category: "Log In Issue", user: "Bob", dateTime: "2025-01-07 20:01 MYT", priority: "low", status: "Pending" },
        { id: "2025-2152A", category: "Log In Issue", user: "Steve", dateTime: "2025-01-06 19:22 MYT", priority: "-", status: "Resolved" }
    ];

    function formatNumber(num) {
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + ' k';
        }
        return num.toString();
    }

    // User Summary
    document.getElementById('totalUsers').textContent = formatNumber(totalUsers);
    document.getElementById('activeUsers').textContent = formatNumber(activeUsers);
    document.getElementById('todaySignUps').textContent = formatNumber(todaySignUps);
    document.getElementById('inactiveUsers').textContent = formatNumber(inactiveUsers);
    document.getElementById('topCountry').textContent = `${topCountry} (${topCountryPercentage}%)`;
    document.getElementById('topCountryBar').style.width = topCountryPercentage + '%';

    // Activity Summary
    document.getElementById('totalEvents').textContent = formatNumber(totalEvents);
    document.getElementById('ongoingEvents').textContent = formatNumber(ongoingEvents);
    document.getElementById('totalBlogs').textContent = formatNumber(totalBlogs);
    document.getElementById('finishedTrades').textContent = formatNumber(finishedTrades);
    document.getElementById('tradesBar').style.width = successTradesPercent + '%';

    // Ticket Summary
    document.getElementById('totalTickets').textContent = totalTickets;
    document.getElementById('pendingTickets').textContent = pendingTickets;
    document.getElementById('resolvedTickets').textContent = resolvedTickets;
    document.getElementById('mostCommonIssue').textContent = mostCommonIssue;
    document.getElementById('commonIssueBar').style.width = mostCommonPercentage + '%';

    // Tickets Table
    const tableBody = document.getElementById('ticketsTableBody');
    tickets.forEach(ticket => {
        const row = document.createElement('tr');
        const priorityClass = ticket.priority.toLowerCase();
        const statusClass = ticket.status.toLowerCase(); 

        ticket.priority = ticket.priority.toUpperCase();
        row.innerHTML = `
            <td><strong>${ticket.id}</strong></td>
            <td>${ticket.category}</td>
            <td>${ticket.user}</td>
            <td>${ticket.dateTime}</td>
            <td class="${priorityClass}">${ticket.priority}</td>
            <td class="${statusClass}">${ticket.status}</td>
        `;
        tableBody.appendChild(row);
    });

    // Chart
    const ctx = document.getElementById('signupsChart').getContext('2d');
    const signupsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklySignups.map(w => w.week),
            datasets: [{
                label: 'New Signups',
                data: weeklySignups.map(w => w.signups),
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
                    backgroundColor: black,
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
                            return context.parsed.y + ' signups';
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
                        color: gray
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