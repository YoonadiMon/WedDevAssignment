document.addEventListener('DOMContentLoaded', function() {
    // Add filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    [categoryFilter, priorityFilter, statusFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    function applyFilters() {
        const category = categoryFilter.value;
        const priority = priorityFilter.value;
        const status = statusFilter.value;
        
        const ticketItems = document.querySelectorAll('.ticket-item');
        let visibleCount = 0;
        
        ticketItems.forEach(item => {
            // Get actual data from the ticket item
            const itemCategory = item.querySelector('.ticket-category').textContent.toLowerCase().replace(/\s+/g, '');
            const itemPriority = item.querySelector('.ticket-priority').textContent.toLowerCase();
            
            // Get status from data attribute or button text
            const ticketId = item.getAttribute('data-ticket-id');
            const statusButton = item.querySelector('.c-btn');
            const itemStatus = statusButton.textContent.includes('Reopen') ? 'solved' : 
                            statusButton.textContent.includes('In Progress') ? 'in_progress' : 'open';
            
            // Normalize values for comparison
            const normalizedCategory = itemCategory === 'others' ? 'other' : itemCategory;
            const normalizedPriority = itemPriority === 'urgent' ? 'urgent' : 
                                    itemPriority === 'high' ? 'high' :
                                    itemPriority === 'medium' ? 'medium' : 'low';
            
            const categoryMatch = category === 'all' || normalizedCategory === category;
            const priorityMatch = priority === 'all' || normalizedPriority === priority;
            const statusMatch = status === 'all' || itemStatus === status;
            
            if (categoryMatch && priorityMatch && statusMatch) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update badge count
        const badge = document.querySelector('.section-header .badge');
        if (badge) {
            badge.textContent = visibleCount;
        }
        
        // Show empty state if no tickets visible
        const ticketsList = document.getElementById('helpTicketsList');
        let emptyState = ticketsList.querySelector('.empty-state');
        
        if (visibleCount === 0) {
            if (!emptyState) {
                ticketsList.innerHTML = `
                    <div class="empty-state">
                        <p>No tickets found matching your filters</p>
                        <button class="c-btn c-btn-primary" onclick="resetFilters()">Reset Filters</button>
                    </div>
                `;
            }
        } else {
            // Remove empty state if it exists and we have visible tickets
            if (emptyState) {
                emptyState.remove();
            }
        }
    }
    
    // Initialize filters on page load
    applyFilters();
});

function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function toggleTicketStatus(ticketId, event) {
    event.stopPropagation();
    
    // In a real application, you would make an AJAX call to update the database
    const button = event.target;
    const isCurrentlySolved = button.textContent.includes('Reopen');
    
    if (isCurrentlySolved) {
        button.textContent = 'Mark Solved';
        showNotification(`Ticket #${ticketId} reopened`);
    } else {
        button.textContent = 'Reopen';
        showNotification(`Ticket #${ticketId} marked as solved`);
    }
    
    // Reapply filters to update the display
    applyFilters();
}

function resetFilters() {
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('priorityFilter').value = 'all';
    document.getElementById('statusFilter').value = 'all';
    
    // Reapply filters
    const applyFilters = window.applyFilters;
    if (typeof applyFilters === 'function') {
        applyFilters();
    }
}

window.applyFilters = applyFilters;