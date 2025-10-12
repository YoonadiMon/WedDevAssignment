// Import event data from eventBrowse.js
// Note: Make sure eventBrowse.js is loaded before this script

// Sample user data (in production, this would come from logged-in user session/account)
const currentUser = {
    id: 12345,
    name: "John Doe",
    email: "john.doe@email.com",
    phone: "+60 12 345 6789"
};

// Get event ID from URL parameter
function getEventIdFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id') || '1'; // Default to event 1 if no ID provided
}

// Load event data on page load
document.addEventListener('DOMContentLoaded', function() {
    const eventId = parseInt(getEventIdFromURL());
    loadEventData(eventId);
    loadUserData();
});

// Load event details
function loadEventData(eventId) {
    // Get event from eventsData array
    const event = eventsData.find(e => e.id === eventId);
    
    if (!event) {
        // If event not found, redirect to events page
        alert('Event not found!');
        window.location.href = 'mainEvent.html';
        return;
    }
    
    // Update page content
    document.getElementById('eventTitle').textContent = event.title;
    document.getElementById('eventTypeBadge').textContent = event.type;
    document.getElementById('eventDate').textContent = event.date;
    document.getElementById('eventTime').textContent = '9:00 AM - 5:00 PM'; // Default time
    document.getElementById('eventLocation').textContent = event.location;
    
    // Format description with line breaks
    const description = event.description + '\n\nWe will provide all necessary equipment and materials. Please wear comfortable clothing and bring your own water bottle. Refreshments will be provided.\n\nAll ages are welcome! This is a perfect activity to make a positive impact on our environment.';
    document.getElementById('eventDescription').innerHTML = description.replace(/\n\n/g, '<br><br>');
    
    // Set host information (default to ReLeaf Foundation)
    const hostName = 'ReLeaf Foundation';
    const hostEmail = 'contact@releaf.org';
    document.getElementById('hostName').textContent = hostName;
    document.getElementById('hostEmail').textContent = hostEmail;
    document.getElementById('hostAvatar').textContent = hostName.charAt(0);
    
    // Set attendee count
    document.getElementById('attendeeCount').textContent = event.attendees;
    
    // Set event type info
    document.getElementById('eventTypeInfo').textContent = event.type;
    document.getElementById('eventTypeBadge').textContent = event.type;
    
    // Set timezone (default to GMT+8)
    document.getElementById('eventTimezone').textContent = 'GMT+8';
    
    // Set event status
    const status = event.status === 'past' ? 'Event Ended' : 'Open for Registration';
    document.getElementById('eventStatus').textContent = status;
    
    // Set event banner
    document.getElementById('eventBanner').src = event.image;
    
    // Update tags based on category
    const tagContainer = document.getElementById('eventTags');
    tagContainer.innerHTML = '';
    
    const tagMap = {
        'cleanup': ['General Clean Up', 'Recycling'],
        'workshop': ['Workshop', 'Learning'],
        'planting': ['Tree Planting', 'Gardening'],
        'awareness': ['Awareness Campaign', 'Education'],
        'seminar': ['Seminar', 'Educational Talk']
    };
    
    const tags = tagMap[event.category] || ['Environmental'];
    tags.forEach(tag => {
        const tagElement = document.createElement('span');
        tagElement.className = 'event-tag';
        tagElement.textContent = tag;
        tagContainer.appendChild(tagElement);
    });
    
    // Update modal event name
    document.getElementById('modalEventName').textContent = event.title;
    
    // Disable register button if event has passed
    if (event.status === 'past') {
        const registerBtn = document.getElementById('registerBtn');
        registerBtn.textContent = 'Event Has Ended';
        registerBtn.disabled = true;
    }
}

// Load user data into modal
function loadUserData() {
    document.getElementById('userName').textContent = currentUser.name;
    document.getElementById('userEmail').textContent = currentUser.email;
}

// Show confirmation modal
function showConfirmation() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('active');
    document.body.classList.add('stopScroll');
}

// Hide confirmation modal
function hideConfirmation() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    document.body.classList.remove('stopScroll');
}

// Confirm registration
function confirmRegistration() {
    const eventId = parseInt(getEventIdFromURL());
    const event = eventsData.find(e => e.id === eventId);
    
    // Collect registration data
    const registrationData = {
        eventId: eventId,
        eventTitle: event.title,
        userId: currentUser.id,
        userName: currentUser.name,
        userEmail: currentUser.email,
        userPhone: currentUser.phone,
        registrationDate: new Date().toISOString()
    };
    
    // Log registration data (will be sent to backend via PHP later)
    console.log('Registration Data:', registrationData);
    
    // Hide confirmation modal
    hideConfirmation();
    
    // Show success modal
    setTimeout(() => {
        showSuccess(event.title);
    }, 300);
    
    // Update attendee count
    event.attendees += 1;
    document.getElementById('attendeeCount').textContent = event.attendees;
    
    // Disable register button
    const registerBtn = document.getElementById('registerBtn');
    registerBtn.textContent = 'Already Registered';
    registerBtn.disabled = true;
}

// Show success modal
function showSuccess(eventTitle) {
    const modal = document.getElementById('successModal');
    document.getElementById('successEventName').textContent = eventTitle;
    document.getElementById('successUserEmail').textContent = currentUser.email;
    modal.classList.add('active');
    document.body.classList.add('stopScroll');
}

// Close success modal
function closeSuccess() {
    const modal = document.getElementById('successModal');
    modal.classList.remove('active');
    document.body.classList.remove('stopScroll');
    
    // Optional: Redirect to user's event list or stay on page
    // window.location.href = 'myEvents.html';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const confirmModal = document.getElementById('confirmModal');
    const successModal = document.getElementById('successModal');
    
    if (e.target === confirmModal) {
        hideConfirmation();
    }
    
    if (e.target === successModal) {
        closeSuccess();
    }
});