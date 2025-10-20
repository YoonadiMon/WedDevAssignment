// Add more specific CSS
const clickableStyle = document.createElement('style');
clickableStyle.textContent = `
    .ticket-item.clickable {
        cursor: pointer !important;
        position: relative;
    }
    .ticket-item.clickable:hover {
        background-color: #f0f9ff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-left: 3px solid var(--MainGreen);
    }
    .ticket-item.not-clickable {
        cursor: not-allowed !important;
        opacity: 0.7;
    }
    
    /* Force pointer events for clickable items */
    .ticket-item.clickable * {
        pointer-events: none;
    }
    .ticket-item.clickable .ticket-actions,
    .ticket-item.clickable .ticket-actions * {
        pointer-events: auto !important;
    }
`;
document.head.appendChild(clickableStyle);

// Function to initialize ticket clickability
function makeTicketsClickable() {
    
    const ticketItems = document.querySelectorAll('.ticket-item');
    
    ticketItems.forEach((item, index) => {
        const ticketId = item.getAttribute('data-ticket-id');
        const canModify = item.querySelector('.ticket-actions .c-btn:not(.c-btn-disabled)') !== null;
        
        if (canModify) {
            // Add clickable class
            item.classList.add('clickable');
            
            // Add direct click handler
            item.onclick = function(e) {
                // Check if click was on a button or form
                if (e.target.closest('button') || e.target.closest('form') || e.target.closest('.ticket-actions')) {
                    return;
                }
                
                // console.log('Navigating to ticket:', ticketId);
                window.location.href = `../../pages/commonPages/ticketThread.php?ticket_id=${ticketId}&from=admin`;
            };
            
            // Add hover styles directly
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f0f9ff';
                this.style.cursor = 'pointer';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
            
        } else {
            item.classList.add('not-clickable');
            item.style.cursor = 'not-allowed';
            item.style.opacity = '0.7';
        }
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    makeTicketsClickable();
    
    // Also initialize filters
    const categoryFilter = document.getElementById('categoryFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    [categoryFilter, priorityFilter, statusFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
    
    applyFilters();
});

// Fallback initialization
window.addEventListener('load', function() {
    setTimeout(makeTicketsClickable, 100);
});

// Also initialize after a short delay as backup
setTimeout(function() {
    makeTicketsClickable();
}, 2000);

// Re-initialize after filters are applied
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
    
    // Re-initialize tickets after filtering
    setTimeout(makeTicketsClickable, 100);
}

function resetFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (categoryFilter) categoryFilter.value = 'all';
    if (priorityFilter) priorityFilter.value = 'all';
    if (statusFilter) statusFilter.value = 'all';
    
    applyFilters();
}

function hideMssg() {
    const mssg = document.getElementById('mssg');
    if (mssg) {
        mssg.style.display = 'none';
    }
}