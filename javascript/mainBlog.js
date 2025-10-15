



/// Sample mainBlog data
const blogsData = [
  {
    id: 1,
    title: "Sustainable Living: 10 Ways to Reduce Your Carbon Footprint",
    excerpt: "Discover practical ways to live more sustainably and help protect our environment for future generations. Small changes can make a big difference.",
    category: "Environment",
    date: "13 March 2025",
    image: "Sustainability Tips",
    popularity: 95,
    dateNum: 20250313,
    tags: ["sustainability", "eco-friendly", "green living"]
  },
  {
    id: 2,
    title: "The Future of Renewable Energy: Solar and Wind Power",
    excerpt: "Exploring the latest innovations in renewable energy technology and how they're reshaping our world's energy landscape.",
    category: "Technology",
    date: "10 March 2025",
    image: "Renewable Energy",
    popularity: 89,
    dateNum: 20250310,
    tags: ["renewable energy", "climate change"]
  },
  {
    id: 3,
    title: "Urban Gardening: Growing Green in Small Spaces",
    excerpt: "Learn how to create your own green oasis in urban environments. Perfect for apartments and small homes.",
    category: "Gardening",
    date: "8 March 2025",
    image: "Urban Garden",
    popularity: 76,
    dateNum: 20250308,
    tags: ["organic", "sustainability"]
  },
  {
    id: 4,
    title: "Climate Change and Its Impact on Wildlife",
    excerpt: "Understanding how climate change affects wildlife populations and what we can do to help protect endangered species.",
    category: "Environment",
    date: "5 March 2025",
    image: "Wildlife Impact",
    popularity: 94,
    dateNum: 20250305,
    tags: ["climate change", "conservation"]
  },
  {
    id: 5,
    title: "Green Technology Innovations Changing the World",
    excerpt: "Explore the latest green technology innovations that are helping to create a more sustainable future for our planet.",
    category: "Technology",
    date: "2 March 2025",
    image: "Green Tech",
    popularity: 83,
    dateNum: 20250302,
    tags: ["renewable energy", "sustainability"]
  },
  {
    id: 6,
    title: "The Benefits of Organic Farming for the Environment",
    excerpt: "Discover how organic farming practices contribute to environmental sustainability and healthier ecosystems.",
    category: "Agriculture",
    date: "28 February 2025",
    image: "Organic Farm",
    popularity: 81,
    dateNum: 20250228,
    tags: ["organic", "sustainability"]
  },
  {
    id: 7,
    title: "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
    excerpt: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend lacus quam, et lacinia turpis fringilla a. Curabitur consequat velit scelerisque, varius neque.",
    category: "Travel",
    date: "25 February 2025",
    image: "Blog Image 1",
    popularity: 87,
    dateNum: 20250225,
    tags: ["green living"]
  },
  {
    id: 8,
    title: "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
    excerpt: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend lacus quam, et lacinia turpis fringilla a. Curabitur consequat velit scelerisque, varius neque.",
    category: "Travel",
    date: "22 February 2025",
    image: "Blog Image 2",
    popularity: 92,
    dateNum: 20250222,
    tags: ["eco-friendly", "recycling"]
  },
  {
    id: 9,
    title: "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
    excerpt: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend lacus quam, et lacinia turpis fringilla a. Curabitur consequat velit scelerisque, varius neque.",
    category: "Travel",
    date: "20 February 2025",
    image: "Blog Image 3",
    popularity: 78,
    dateNum: 20250220,
    tags: ["conservation"]
  }
];

// Available tags matching addBlog page
const availableTags = [
  "sustainability",
  "climate change",
  "renewable energy",
  "eco-friendly",
  "green living",
  "organic",
  "recycling",
  "conservation"
];

let displayedBlogs = [...blogsData];
let currentFilter = 'all';
let currentTag = 'all';
let searchTerm = '';

// Check if blog was added in last 3 days
function isRecentlyAdded(dateNum) {
  const today = new Date();
  const blogDate = new Date(
    Math.floor(dateNum / 10000),
    Math.floor((dateNum % 10000) / 100) - 1,
    dateNum % 100
  );
  const diffTime = today - blogDate;
  const diffDays = diffTime / (1000 * 60 * 60 * 24);
  return diffDays <= 3;
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function () {
  // Check for newly published blog in sessionStorage
  const newBlogJSON = sessionStorage.getItem('newBlogPost');
  if (newBlogJSON) {
    const newBlog = JSON.parse(newBlogJSON);
    blogsData.unshift(newBlog);
    sessionStorage.removeItem('newBlogPost');
  }

  displayedBlogs = [...blogsData];
  
  renderBlogs();
  updateBlogCount();
  setupSearchSuggestions();
  populateTagDropdown();

  setTimeout(() => {
    animateOnScroll();
  }, 100);
});

// Populate tag dropdown with tags from addBlog page
function populateTagDropdown() {
  const tagSelect = document.getElementById('tagFilter');
  if (!tagSelect) return;

  // Clear existing options except "All Tags"
  tagSelect.innerHTML = '<option value="all">All Tags</option>';
  
  // Add tag options from availableTags
  availableTags.forEach(tag => {
    const option = document.createElement('option');
    option.value = tag;
    option.textContent = tag.charAt(0).toUpperCase() + tag.slice(1);
    tagSelect.appendChild(option);
  });
}

// Navigate to blog read page
function navigateToReadBlog(blogId) {
  window.location.href = `readBlog.html?id=${blogId}`;
}

// Render blog cards
function renderBlogs() {
  const blogGrid = document.getElementById('blogGrid');
  blogGrid.innerHTML = '';

  displayedBlogs.forEach(blog => {
    const blogCard = document.createElement('div');
    blogCard.className = 'blog-card';
    blogCard.innerHTML = `
      <div class="blog-image">${blog.image}</div>
      <div class="blog-content">
        <div class="blog-meta">
          <span class="blog-category">${blog.category}</span>
          <span class="blog-date">${blog.date}</span>
        </div>
        <h3 class="blog-title">${blog.title}</h3>
        <p class="blog-excerpt">${blog.excerpt}</p>
        <a href="#" class="read-more" data-blog-id="${blog.id}">Read More...</a>
      </div>
    `;

    // Add click event for the entire card
    blogCard.addEventListener('click', function(e) {
      if (e.target.classList.contains('read-more')) {
        return;
      }
      navigateToReadBlog(blog.id);
    });

    // Add specific click event for read more link
    const readMoreLink = blogCard.querySelector('.read-more');
    readMoreLink.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const blogId = parseInt(this.getAttribute('data-blog-id'));
      navigateToReadBlog(blogId);
    });

    blogGrid.appendChild(blogCard);
  });
}

// Update blog count
function updateBlogCount() {
  document.getElementById('blogCount').textContent = displayedBlogs.length;
}

// Enhanced search with no results handling
function searchBlogs() {
  const searchInput = document.getElementById('searchInput');
  searchTerm = searchInput.value.toLowerCase().trim();

  applyFilters();
}

// Apply all filters
function applyFilters() {
  let filteredBlogs = [...blogsData];

  // Apply search filter
  if (searchTerm !== '') {
    filteredBlogs = filteredBlogs.filter(blog =>
      blog.title.toLowerCase().includes(searchTerm) ||
      blog.excerpt.toLowerCase().includes(searchTerm) ||
      blog.category.toLowerCase().includes(searchTerm) ||
      blog.tags.some(tag => tag.toLowerCase().includes(searchTerm))
    );
  }

  // Apply time filter (all or recent)
  if (currentFilter === 'recent') {
    filteredBlogs = filteredBlogs.filter(blog => isRecentlyAdded(blog.dateNum));
  }

  // Apply tag filter
  if (currentTag !== 'all') {
    filteredBlogs = filteredBlogs.filter(blog => 
      blog.tags.includes(currentTag)
    );
  }

  displayedBlogs = filteredBlogs;

  if (displayedBlogs.length === 0) {
    showNoResults();
  } else {
    renderBlogs();
  }
  
  updateBlogCount();
  setTimeout(animateOnScroll, 100);
}

function showNoResults() {
  const blogGrid = document.getElementById('blogGrid');
  let message = 'No blogs found';
  
  if (searchTerm) {
    message += ` matching "${searchTerm}"`;
  }
  if (currentFilter === 'recent') {
    message += ' in the last 3 days';
  }
  if (currentTag !== 'all') {
    message += ` with tag "${currentTag}"`;
  }

  blogGrid.innerHTML = `
    <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
      <h3 style="color: var(--text-heading); margin-bottom: 1rem;">${message}</h3>
      <p style="color: var(--text-color-2); margin-bottom: 2rem;">Try different keywords, filters, or tags to find what you're looking for.</p>
      <button class="btn" onclick="clearAllFilters()">Clear All Filters</button>
    </div>
  `;
}

function clearAllFilters() {
  // Clear search
  document.getElementById('searchInput').value = '';
  const headerSearchBar = document.getElementById('searchBar');
  if (headerSearchBar) {
    headerSearchBar.value = '';
  }
  
  // Reset filters
  searchTerm = '';
  currentFilter = 'all';
  currentTag = 'all';
  
  // Reset UI
  document.querySelectorAll('.sort button').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector('.sort button[onclick*="all"]').classList.add('active');
  
  const tagSelect = document.getElementById('tagFilter');
  if (tagSelect) {
    tagSelect.value = 'all';
  }
  
  displayedBlogs = [...blogsData];
  renderBlogs();
  updateBlogCount();
}

// Search button with loading animation
function showLoading() {
  const blogGrid = document.getElementById('blogGrid');
  blogGrid.innerHTML = `
    <div class="loading" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
      <div class="spinner"></div>
      <p style="color: var(--text-color-2);">Searching blogs...</p>
    </div>
  `;
}

function searchBlogsWithLoading() {
  showLoading();

  setTimeout(() => {
    searchBlogs();
  }, 800);
}

// Filter blogs
function filterBlogs(filterType) {
  // Update active filter button
  document.querySelectorAll('.sort button').forEach(btn => {
    btn.classList.remove('active');
  });
  
  const clickedButton = Array.from(document.querySelectorAll('.sort button')).find(btn => 
    btn.getAttribute('onclick').includes(filterType)
  );
  if (clickedButton) {
    clickedButton.classList.add('active');
  }

  currentFilter = filterType;
  applyFilters();
}

// Filter by tag
function filterByTag() {
  const tagSelect = document.getElementById('tagFilter');
  currentTag = tagSelect.value;
  applyFilters();
}

// Search from header search bar
const headerSearchBar = document.getElementById('searchBar');
if (headerSearchBar) {
  headerSearchBar.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      document.getElementById('searchInput').value = this.value;
      searchBlogsWithLoading();
      setTimeout(scrollToBlogs, 900);
    }
  });
}

// Search input event listener
document.getElementById('searchInput').addEventListener('keypress', function (e) {
  if (e.key === 'Enter') {
    searchBlogsWithLoading();
    setTimeout(scrollToBlogs, 900);
  }
});

// Smooth scroll to blogs section when searching
function scrollToBlogs() {
  document.querySelector('.blogs').scrollIntoView({
    behavior: 'smooth'
  });
}

// Blog card animation on scroll
function animateOnScroll() {
  const blogCards = document.querySelectorAll('.blog-card');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, {
    threshold: 0.1
  });

  blogCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
  });
}

// Write a blog button functionality
const writeBlogBtn = document.querySelector('.btn');
if (writeBlogBtn && writeBlogBtn.textContent.includes('Write')) {
  writeBlogBtn.addEventListener('click', function () {
    window.location.href = 'addBlog.html';
  });
}

// Auto-complete search suggestions
function setupSearchSuggestions() {
  const searchInput = document.getElementById('searchInput');
  const headerSearchInput = document.getElementById('searchBar');

  function createSuggestions(input) {
    const suggestions = [...new Set(blogsData.flatMap(blog => [
      blog.title,
      blog.category,
      ...blog.tags,
      ...blog.excerpt.split(' ').filter(word => word.length > 3)
    ]))].slice(0, 5);

    return suggestions.filter(suggestion =>
      suggestion.toLowerCase().includes(input.toLowerCase())
    );
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const suggestions = createSuggestions(this.value);
      // Could implement dropdown here
    });
  }

  if (headerSearchInput) {
    headerSearchInput.addEventListener('input', function () {
      const suggestions = createSuggestions(this.value);
      // Could implement dropdown here
    });
  }
}