document.addEventListener('DOMContentLoaded', function() {
    // Add filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    [categoryFilter, priorityFilter, statusFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    // Initialize filters on page load
    applyFilters();
});

// Define applyFilters in global scope
function applyFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    const category = categoryFilter ? categoryFilter.value : 'all';
    const priority = priorityFilter ? priorityFilter.value : 'all';
    const status = statusFilter ? statusFilter.value : 'all';
    
    const ticketItems = document.querySelectorAll('.ticket-item');
    let visibleCount = 0;
    
    ticketItems.forEach(item => {
        // Get actual data from the ticket item
        const itemCategory = item.querySelector('.ticket-category').textContent.toLowerCase().replace(/\s+/g, '');
        const itemPriority = item.querySelector('.ticket-priority').textContent.toLowerCase();
        
        // Get status from button text or form
        const statusButton = item.querySelector('.c-btn');
        let itemStatus = 'open';
        if (statusButton) {
            if (statusButton.textContent.includes('Reopen')) {
                itemStatus = 'solved';
            } else if (statusButton.textContent.includes('In Progress')) {
                itemStatus = 'in_progress';
            }
        }
        
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
    if (!ticketsList) return;
    
    let emptyState = ticketsList.querySelector('.empty-state');
    
    if (visibleCount === 0 && ticketItems.length > 0) {
        if (!emptyState) {
            const emptyStateDiv = document.createElement('div');
            emptyStateDiv.className = 'empty-state';
            emptyStateDiv.innerHTML = `
                <p>No tickets found matching your filters</p>
                <button class="c-btn c-btn-primary" onclick="resetFilters()">Reset Filters</button>
            `;
            ticketsList.appendChild(emptyStateDiv);
        }
    } else {
        // Remove empty state if it exists and we have visible tickets
        if (emptyState) {
            emptyState.remove();
        }
    }
}

function resetFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (categoryFilter) categoryFilter.value = 'all';
    if (priorityFilter) priorityFilter.value = 'all';
    if (statusFilter) statusFilter.value = 'all';
    
    // Reapply filters to show all tickets
    applyFilters();
}

function hideMssg() {
    const mssg = document.getElementById('mssg');
    if (mssg) {
        mssg.style.display = 'none';
    }
}