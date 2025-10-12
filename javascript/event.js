// Sample event data
const eventsData = [
    {
        id: 1,
        title: "Beach Clean-up Drive 2025",
        date: "Dec 15, 2025",
        location: "Batu Ferringhi Beach, Penang",
        description: "Join us for a morning of beach cleaning and environmental awareness. Help protect our oceans and marine life.",
        attendees: 156,
        type: "In-Person",
        category: "cleanup",
        eventType: "inperson",
        status: "upcoming",
        image: "../../assets/images/event1.jpg"
    },
    {
        id: 2,
        title: "Sustainable Living Workshop",
        date: "Jan 20, 2025",
        location: "Online via Zoom",
        description: "Learn practical tips for sustainable living, from reducing waste to eco-friendly product choices.",
        attendees: 89,
        type: "Online",
        category: "workshop",
        eventType: "online",
        status: "open",
        image: "../../assets/images/event2.jpg"
    },
    {
        id: 3,
        title: "Urban Tree Planting Initiative",
        date: "Feb 5, 2025",
        location: "KLCC Park, Kuala Lumpur",
        description: "Be part of our mission to green the city. We'll plant 500 trees in KLCC Park.",
        attendees: 234,
        type: "In-Person",
        category: "planting",
        eventType: "inperson",
        status: "upcoming",
        image: "../../assets/images/event3.jpg"
    },
    {
        id: 4,
        title: "Climate Change Awareness Seminar",
        date: "Jan 25, 2025",
        location: "Hybrid - KL Convention Centre",
        description: "Expert speakers discuss the latest climate science and actionable solutions for a sustainable future.",
        attendees: 312,
        type: "Hybrid",
        category: "seminar",
        eventType: "hybrid",
        status: "open",
        image: "../../assets/images/event4.jpg"
    },
    {
        id: 5,
        title: "Plastic-Free Campaign Launch",
        date: "Jan 18, 2025",
        location: "Pavilion KL, Kuala Lumpur",
        description: "Join our campaign to reduce single-use plastics. Get your reusable bag and bottle at the event!",
        attendees: 178,
        type: "In-Person",
        category: "awareness",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event5.jpg"
    },
    {
        id: 6,
        title: "Composting 101 Workshop",
        date: "Feb 10, 2025",
        location: "Online via Google Meet",
        description: "Master the art of composting at home. Turn your food waste into nutrient-rich soil for your garden.",
        attendees: 67,
        type: "Online",
        category: "workshop",
        eventType: "online",
        status: "upcoming",
        image: "../../assets/images/event6.jpg"
    },
    {
        id: 7,
        title: "River Clean-up & Restoration",
        date: "Jan 22, 2025",
        location: "Sungai Klang, Selangor",
        description: "Help restore our river ecosystem by removing debris and planting native vegetation along the banks.",
        attendees: 145,
        type: "In-Person",
        category: "cleanup",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event7.jpg"
    },
    {
        id: 8,
        title: "Eco-Friendly Home Seminar",
        date: "Feb 1, 2025",
        location: "Hybrid - Sunway Convention Centre",
        description: "Discover how to transform your home into an eco-friendly haven with sustainable design and energy solutions.",
        attendees: 198,
        type: "Hybrid",
        category: "seminar",
        eventType: "hybrid",
        status: "open",
        image: "../../assets/images/event8.jpg"
    },
    {
        id: 9,
        title: "Community Garden Project",
        date: "Jan 28, 2025",
        location: "Taman Tun Dr Ismail, KL",
        description: "Start a community garden with us! Learn urban farming techniques and grow your own organic vegetables.",
        attendees: 92,
        type: "In-Person",
        category: "planting",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event9.jpg"
    },
    {
        id: 10,
        title: "Youth Environmental Summit",
        date: "Feb 15, 2025",
        location: "Online via Zoom",
        description: "Empowering young leaders to drive environmental change. Network, learn, and take action together.",
        attendees: 421,
        type: "Online",
        category: "seminar",
        eventType: "online",
        status: "upcoming",
        image: "../../assets/images/event10.jpg"
    },
    {
        id: 11,
        title: "Zero Waste Living Workshop",
        date: "Jan 30, 2025",
        location: "The Bee, Publika",
        description: "Learn to live a zero-waste lifestyle with practical tips on reducing, reusing, and recycling effectively.",
        attendees: 76,
        type: "In-Person",
        category: "workshop",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event11.jpg"
    },
    {
        id: 12,
        title: "Marine Conservation Talk",
        date: "Feb 8, 2025",
        location: "Hybrid - Aquaria KLCC",
        description: "Dive into marine conservation with marine biologists. Learn about coral reefs and ocean protection.",
        attendees: 167,
        type: "Hybrid",
        category: "awareness",
        eventType: "hybrid",
        status: "upcoming",
        image: "../../assets/images/event12.jpg"
    },
    {
        id: 13,
        title: "Recycling Center Tour",
        date: "Jan 26, 2025",
        location: "Alam Flora, Petaling Jaya",
        description: "Get an exclusive behind-the-scenes tour of a recycling facility and see where your recyclables go.",
        attendees: 54,
        type: "In-Person",
        category: "awareness",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event13.jpg"
    },
    {
        id: 14,
        title: "Green Energy Solutions Seminar",
        date: "Feb 12, 2025",
        location: "Online via Microsoft Teams",
        description: "Explore renewable energy options for homes and businesses. Solar, wind, and more.",
        attendees: 203,
        type: "Online",
        category: "seminar",
        eventType: "online",
        status: "upcoming",
        image: "../../assets/images/event14.jpg"
    },
    {
        id: 15,
        title: "Forest Restoration Project",
        date: "Feb 20, 2025",
        location: "Bukit Nanas Forest, KL",
        description: "Join our forest restoration effort. Plant native trees and help restore biodiversity in urban forests.",
        attendees: 287,
        type: "In-Person",
        category: "planting",
        eventType: "inperson",
        status: "upcoming",
        image: "../../assets/images/event15.jpg"
    },
    {
        id: 16,
        title: "Sustainable Fashion Workshop",
        date: "Jan 24, 2025",
        location: "Hybrid - APW Bangsar",
        description: "Discover sustainable fashion choices and learn to upcycle old clothes into trendy new pieces.",
        attendees: 112,
        type: "Hybrid",
        category: "workshop",
        eventType: "hybrid",
        status: "open",
        image: "../../assets/images/event16.jpg"
    },
    {
        id: 17,
        title: "School Cleanup Campaign",
        date: "Dec 15, 2025",
        location: "SMK Bandar Utama, Selangor",
        description: "A successful school cleanup campaign that engaged over 200 students in environmental action.",
        attendees: 245,
        type: "In-Person",
        category: "cleanup",
        eventType: "inperson",
        status: "past",
        image: "../../assets/images/event17.jpg"
    },
    {
        id: 18,
        title: "Eco-Tourism Awareness Campaign",
        date: "Feb 3, 2025",
        location: "Online via Zoom",
        description: "Learn about responsible tourism practices that protect natural habitats and local communities.",
        attendees: 134,
        type: "Online",
        category: "awareness",
        eventType: "online",
        status: "upcoming",
        image: "../../assets/images/event18.jpg"
    },
    {
        id: 19,
        title: "Biodiversity Workshop",
        date: "Dec 29, 2025",
        location: "National Zoo, KL",
        description: "Understand the importance of biodiversity and what we can do to protect endangered species.",
        attendees: 98,
        type: "In-Person",
        category: "workshop",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event19.jpg"
    },
    {
        id: 20,
        title: "Green Building Seminar",
        date: "Feb 18, 2025",
        location: "Hybrid - KLCC Convention Centre",
        description: "Explore sustainable architecture and green building certifications for eco-conscious construction.",
        attendees: 276,
        type: "Hybrid",
        category: "seminar",
        eventType: "hybrid",
        status: "upcoming",
        image: "../../assets/images/event20.jpg"
    },
    {
        id: 21,
        title: "Park Beautification Project",
        date: "Jan 31, 2025",
        location: "Taman Botani Perdana, KL",
        description: "Help beautify our local parks by planting flowers, cleaning paths, and creating green spaces.",
        attendees: 189,
        type: "In-Person",
        category: "planting",
        eventType: "inperson",
        status: "open",
        image: "../../assets/images/event21.jpg"
    },
    {
        id: 22,
        title: "Water Conservation Workshop",
        date: "Feb 7, 2025",
        location: "Online via Google Meet",
        description: "Learn effective water conservation techniques for home and garden use.",
        attendees: 143,
        type: "Online",
        category: "workshop",
        eventType: "online",
        status: "upcoming",
        image: "../../assets/images/event22.jpg"
    },
    {
        id: 23,
        title: "Environmental Film Screening",
        date: "Dec 20, 2024",
        location: "GSC Pavilion, Kuala Lumpur",
        description: "A thought-provoking documentary screening followed by panel discussion on environmental issues.",
        attendees: 321,
        type: "In-Person",
        category: "awareness",
        eventType: "inperson",
        status: "past",
        image: "../../assets/images/event23.jpg"
    },
    {
        id: 24,
        title: "Mangrove Restoration Drive",
        date: "Feb 25, 2025",
        location: "Kuala Selangor Nature Park",
        description: "Help restore vital mangrove ecosystems that protect coastlines and support marine biodiversity.",
        attendees: 167,
        type: "In-Person",
        category: "planting",
        eventType: "inperson",
        status: "upcoming",
        image: "../../assets/images/event24.jpg"
    }
];

let currentEvents = [...eventsData];
let activeFilters = {
    type: [],
    category: [],
    status: []
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    renderEvents(currentEvents);
    setupFilterListeners();
});

// Render event cards
function renderEvents(events) {
    const grid = document.getElementById('eventGrid');
    const countElement = document.getElementById('eventCount');
    
    countElement.textContent = events.length;
    
    if (events.length === 0) {
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--DarkerGray);">No events found matching your filters.</p>';
        return;
    }
    
    grid.innerHTML = events.map(event => `
        <a href="joinEvent.html?id=${event.id}" class="event-card">
            <img src="${event.image}" alt="${event.title}" class="event-card-image" onerror="this.src='../../assets/images/Logo.png'">
            <div class="event-card-content">
                <div class="event-card-date">${event.date}</div>
                <h3 class="event-card-title">${event.title}</h3>
                <div class="event-card-location">📍 ${event.location}</div>
                <p class="event-card-description">${event.description}</p>
                <div class="event-card-footer">
                    <div class="event-card-attendees">👥 ${event.attendees} attendees</div>
                    <div class="event-card-type">${event.type}</div>
                </div>
            </div>
        </a>
    `).join('');
}

// Setup filter event listeners
function setupFilterListeners() {
    const checkboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const filterName = this.name;
            const filterValue = this.value;
            
            if (this.checked) {
                if (!activeFilters[filterName].includes(filterValue)) {
                    activeFilters[filterName].push(filterValue);
                }
            } else {
                activeFilters[filterName] = activeFilters[filterName].filter(v => v !== filterValue);
            }
            
            applyFilters();
        });
    });
}

// Apply filters
function applyFilters() {
    let filtered = [...eventsData];
    
    // Filter by event type
    if (activeFilters.type.length > 0) {
        filtered = filtered.filter(event => 
            activeFilters.type.includes(event.eventType)
        );
    }
    
    // Filter by category
    if (activeFilters.category.length > 0) {
        filtered = filtered.filter(event => 
            activeFilters.category.includes(event.category)
        );
    }
    
    // Filter by status
    if (activeFilters.status.length > 0) {
        filtered = filtered.filter(event => 
            activeFilters.status.includes(event.status)
        );
    }
    
    currentEvents = filtered;
    renderEvents(currentEvents);
}

// Clear all filters
function clearFilters() {
    activeFilters = {
        type: [],
        category: [],
        status: []
    };
    
    const checkboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    currentEvents = [...eventsData];
    renderEvents(currentEvents);
}

// Sort events
function sortEvents() {
    const sortBy = document.getElementById('sortBy').value;
    
    switch(sortBy) {
        case 'newest':
            currentEvents.sort((a, b) => b.id - a.id);
            break;
        case 'oldest':
            currentEvents.sort((a, b) => a.id - b.id);
            break;
        case 'popular':
            currentEvents.sort((a, b) => b.attendees - a.attendees);
            break;
        case 'attendees':
            currentEvents.sort((a, b) => b.attendees - a.attendees);
            break;
        case 'date':
            currentEvents.sort((a, b) => {
                const dateA = new Date(a.date);
                const dateB = new Date(b.date);
                return dateA - dateB;
            });
            break;
    }
    
    renderEvents(currentEvents);
}

// Toggle filters on mobile
function toggleFilters() {
    const sidebar = document.getElementById('filterSidebar');
    sidebar.classList.toggle('active');
    
    const button = document.querySelector('.filter-toggle');
    if (sidebar.classList.contains('active')) {
        button.textContent = 'Hide Filters';
    } else {
        button.textContent = 'Show Filters';
    }
}