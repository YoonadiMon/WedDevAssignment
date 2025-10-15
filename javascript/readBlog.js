

// Sample blog data array for reading page
const readBlogsData = [
    {
        id: 1,
        title: "Sustainable Living: 10 Ways to Reduce Your Carbon Footprint",
        excerpt: "Discover practical ways to live more sustainably and help protect our environment for future generations. Small changes can make a big difference.",
        category: "Environment",
        date: "13 March 2025",
        author: "ReLeaf Team",
        image: "Sustainability Tips",
        content: `
            <h2>Introduction</h2>
            <p>Living sustainably has become more important than ever as we face the growing challenges of climate change. Every small action we take in our daily lives can contribute to a larger positive impact on our planet. In this comprehensive guide, we'll explore ten practical ways you can reduce your carbon footprint and live more sustainably.</p>
            
            <blockquote>
                "The Earth does not belong to us; we belong to the Earth. All things are connected like the blood that unites one family." - Chief Seattle
            </blockquote>
            
            <h2>1. Reduce Energy Consumption at Home</h2>
            <p>One of the most effective ways to reduce your carbon footprint is to minimize energy consumption in your home. This includes switching to LED light bulbs, unplugging electronics when not in use, and investing in energy-efficient appliances.</p>
            
            <h3>Simple Energy-Saving Tips:</h3>
            <ul>
                <li>Use programmable thermostats to optimize heating and cooling</li>
                <li>Seal air leaks around doors and windows</li>
                <li>Install solar panels if possible</li>
                <li>Use cold water for washing clothes when appropriate</li>
            </ul>
            
            <h2>2. Choose Sustainable Transportation</h2>
            <p>Transportation accounts for a significant portion of global carbon emissions. By making conscious choices about how we travel, we can significantly reduce our environmental impact.</p>
            
            <p>Consider walking, cycling, or using public transportation for short trips. For longer distances, carpooling or choosing fuel-efficient vehicles can make a substantial difference.</p>
            
            <h2>3. Adopt a Plant-Based Diet</h2>
            <p>The food industry, particularly livestock farming, is a major contributor to greenhouse gas emissions. Incorporating more plant-based meals into your diet can significantly reduce your carbon footprint while also providing health benefits.</p>
            
            <h2>Conclusion</h2>
            <p>Sustainable living is not about making drastic changes overnight, but rather about making conscious choices that, when combined, create a meaningful impact. By implementing these strategies, you're not only reducing your carbon footprint but also inspiring others to do the same.</p>
        `,
        tags: ["sustainability", "climate change", "eco-friendly", "carbon footprint", "green living"]
    },
    {
        id: 2,
        title: "The Future of Renewable Energy: Solar and Wind Power",
        excerpt: "Exploring the latest innovations in renewable energy technology and how they're reshaping our world's energy landscape.",
        category: "Technology",
        date: "10 March 2025",
        author: "Dr. Sarah Green",
        image: "Renewable Energy",
        content: `
            <h2>The Dawn of a New Energy Era</h2>
            <p>As the world grapples with climate change and the urgent need to reduce carbon emissions, renewable energy technologies are emerging as the cornerstone of our sustainable future. Solar and wind power, in particular, have seen remarkable advancements that are revolutionizing how we generate and consume energy.</p>
            
            <blockquote>
                "The shift to renewable energy is not just an environmental imperative; it's an economic opportunity that will define the next century." - Dr. Sarah Green
            </blockquote>
            
            <h2>Solar Power Innovations</h2>
            <p>Solar technology has advanced tremendously in recent years. From traditional silicon panels to cutting-edge perovskite cells, efficiency rates continue to improve while costs plummet.</p>
            
            <h3>Key Solar Developments:</h3>
            <ul>
                <li>Floating solar farms that utilize water surfaces</li>
                <li>Building-integrated photovoltaics (BIPV)</li>
                <li>Solar storage solutions for 24/7 clean energy</li>
                <li>Concentrated solar power (CSP) systems</li>
            </ul>
            
            <h2>Wind Power Revolution</h2>
            <p>Wind energy has also experienced significant growth, with offshore wind farms leading the charge. These installations can harness stronger and more consistent winds, generating massive amounts of clean electricity.</p>
            
            <p>Modern wind turbines are larger, more efficient, and capable of generating power even in low-wind conditions. Smart grid integration allows for better distribution and storage of wind-generated electricity.</p>
            
            <h2>The Path Forward</h2>
            <p>The future of renewable energy looks brighter than ever. With continued investment in research and development, along with supportive government policies, we're moving closer to a world powered entirely by clean, renewable sources.</p>
        `,
        tags: ["renewable energy", "solar power", "wind energy", "clean technology", "sustainability"]
    },
    {
        id: 3,
        title: "Urban Gardening: Growing Green in Small Spaces",
        excerpt: "Learn how to create your own green oasis in urban environments. Perfect for apartments and small homes.",
        category: "Gardening",
        date: "8 March 2025",
        author: "Maria Rodriguez",
        image: "Urban Garden",
        content: `
            <h2>Bringing Nature to the City</h2>
            <p>Urban gardening has become more than just a hobby; it's a movement that's transforming cities and improving lives. Whether you have a small balcony, a rooftop, or just a sunny windowsill, you can create your own green sanctuary in the heart of the city.</p>
            
            <h2>Getting Started: Essential Tips</h2>
            <p>The key to successful urban gardening lies in understanding your space, light conditions, and choosing the right plants for your environment. Start small and gradually expand as you gain confidence and experience.</p>
            
            <h3>Essential Urban Gardening Supplies:</h3>
            <ul>
                <li>Quality potting soil and containers with drainage</li>
                <li>Basic gardening tools (trowel, watering can, pruning shears)</li>
                <li>Seeds or seedlings appropriate for your climate</li>
                <li>Natural fertilizers and pest control methods</li>
            </ul>
            
            <blockquote>
                "A garden is a grand teacher. It teaches patience and careful watchfulness; it teaches industry and thrift; above all, it teaches entire trust." - Gertrude Jekyll
            </blockquote>
            
            <h2>Maximizing Small Spaces</h2>
            <p>Vertical gardening is a game-changer for urban dwellers. Wall-mounted planters, hanging baskets, and tiered plant stands can dramatically increase your growing space without requiring additional floor area.</p>
            
            <h2>Best Plants for Urban Gardens</h2>
            <p>Choose plants that thrive in containers and can tolerate urban conditions. Herbs like basil, mint, and cilantro are perfect for beginners, while vegetables like tomatoes, peppers, and lettuce can provide fresh produce for your table.</p>
            
            <h2>The Benefits Go Beyond Beauty</h2>
            <p>Urban gardening offers numerous benefits beyond just fresh produce and beautiful plants. It improves air quality, provides mental health benefits, connects you with nature, and can even help reduce your grocery bills.</p>
        `,
        tags: ["urban gardening", "container gardening", "small space", "herbs", "vegetables"]
    },
    {
        id: 4,
        title: "Climate Change and Its Impact on Wildlife",
        excerpt: "Understanding how climate change affects wildlife populations and what we can do to help protect endangered species.",
        category: "Environment",
        date: "5 March 2025",
        author: "ReLeaf Team",
        image: "Wildlife Impact",
        content: `
            <h2>The Wildlife Crisis</h2>
            <p>Climate change is one of the most pressing threats facing wildlife today. Rising temperatures, changing precipitation patterns, and extreme weather events are dramatically altering ecosystems worldwide, forcing countless species to adapt, migrate, or face extinction.</p>
            
            <blockquote>
                "In the end, we will conserve only what we love, we will love only what we understand, and we will understand only what we are taught." - Baba Dioum
            </blockquote>
            
            <h2>Effects on Different Ecosystems</h2>
            <p>Arctic regions are warming twice as fast as the global average, causing sea ice to melt and threatening polar bears, seals, and Arctic foxes. Meanwhile, coral reefs are experiencing massive bleaching events due to rising ocean temperatures.</p>
            
            <h3>Key Impacts:</h3>
            <ul>
                <li>Habitat loss and fragmentation</li>
                <li>Altered migration patterns</li>
                <li>Changes in food availability</li>
                <li>Ocean acidification affecting marine life</li>
            </ul>
            
            <h2>What We Can Do</h2>
            <p>Protecting wildlife requires both individual and collective action. Support conservation organizations, reduce your carbon footprint, and advocate for stronger environmental policies to help protect endangered species and their habitats.</p>
        `,
        tags: ["wildlife", "climate change", "conservation", "endangered species", "ecosystems"]
    },
    {
        id: 5,
        title: "Green Technology Innovations Changing the World",
        excerpt: "Explore the latest green technology innovations that are helping to create a more sustainable future for our planet.",
        category: "Technology",
        date: "2 March 2025",
        author: "Tech Team",
        image: "Green Tech",
        content: `
            <h2>The Green Revolution</h2>
            <p>Green technology is rapidly transforming how we live, work, and interact with our environment. From revolutionary battery storage systems to innovative carbon capture technologies, these innovations are paving the way for a sustainable future.</p>
            
            <h2>Breakthrough Technologies</h2>
            <p>Electric vehicles are becoming mainstream, with improving battery technology extending range and reducing charging times. Smart grids are optimizing energy distribution, while artificial intelligence helps predict and reduce energy waste.</p>
            
            <h3>Key Innovations:</h3>
            <ul>
                <li>Advanced battery storage systems</li>
                <li>Carbon capture and utilization technology</li>
                <li>Smart building management systems</li>
                <li>Hydrogen fuel cells for transportation</li>
            </ul>
            
            <h2>The Future is Green</h2>
            <p>These technological advances are not just reducing our environmental impact - they're creating new economic opportunities and improving quality of life. The green technology sector is expected to continue growing rapidly as we work toward a net-zero future.</p>
        `,
        tags: ["green technology", "innovation", "sustainable tech", "clean energy", "future"]
    },
    {
        id: 6,
        title: "The Benefits of Organic Farming for the Environment",
        excerpt: "Discover how organic farming practices contribute to environmental sustainability and healthier ecosystems.",
        category: "Agriculture",
        date: "28 February 2025",
        author: "Agriculture Expert",
        image: "Organic Farm",
        content: `
            <h2>Understanding Organic Farming</h2>
            <p>Organic farming represents a holistic approach to agriculture that works in harmony with natural ecosystems. By avoiding synthetic pesticides and fertilizers, organic farmers protect soil health, water quality, and biodiversity while producing nutritious food.</p>
            
            <blockquote>
                "The soil is the great connector of lives, the source and destination of all." - Wendell Berry
            </blockquote>
            
            <h2>Environmental Benefits</h2>
            <p>Organic farming practices improve soil structure and fertility through natural composting and crop rotation. This leads to better water retention, reduced erosion, and increased carbon sequestration in agricultural soils.</p>
            
            <h3>Key Advantages:</h3>
            <ul>
                <li>Improved soil health and biodiversity</li>
                <li>Reduced water pollution from chemical runoff</li>
                <li>Enhanced wildlife habitat on farmland</li>
                <li>Lower greenhouse gas emissions</li>
            </ul>
            
            <h2>Supporting Sustainable Agriculture</h2>
            <p>Consumers can support organic farming by choosing organic products, supporting local farmers markets, and advocating for policies that promote sustainable agricultural practices. Every purchase is a vote for the kind of food system we want to see.</p>
        `,
        tags: ["organic farming", "sustainable agriculture", "soil health", "biodiversity", "environmental protection"]
    }
];

// Get current blog ID from URL parameters, default to 1
function getCurrentBlogId() {
    const urlParams = new URLSearchParams(window.location.search);
    const blogId = urlParams.get("id") || "1";
    return parseInt(blogId);
}

// Load and display blog content with sessionStorage newBlogPost check
function loadBlogContent() {
    const loading = document.getElementById("loading");
    const mainContent = document.getElementById("mainContent");

    if (loading) loading.style.display = "block";
    if (mainContent) mainContent.style.display = "none";

    // Simulate loading delay
    setTimeout(() => {
        const blogId = getCurrentBlogId();

        // Check for new blog post in sessionStorage
        const newBlogJSON = sessionStorage.getItem('newBlogPost');
        let newBlog = null;
        if (newBlogJSON) {
            newBlog = JSON.parse(newBlogJSON);
        }

        // Find blog either in readBlogsData or sessionStorage newBlog
        let blog = readBlogsData.find((b) => b.id === blogId);
        if (!blog && newBlog && newBlog.id === blogId) {
            blog = newBlog;
        }

        // Fallback to first blog if none found
        if (!blog) {
            blog = readBlogsData[0];
        }

        // Populate blog content elements
        const blogCategory = document.getElementById("blogCategory");
        const blogDate = document.getElementById("blogDate");
        const blogAuthor = document.getElementById("blogAuthor");
        const blogTitle = document.getElementById("blogTitle");
        const blogExcerpt = document.getElementById("blogExcerpt");
        const blogImage = document.getElementById("blogImage");
        const blogContent = document.getElementById("blogContent");

        if (blogCategory) blogCategory.textContent = blog.category;
        if (blogDate) blogDate.textContent = blog.date;
        if (blogAuthor) blogAuthor.textContent = `By ${blog.author}`;
        if (blogTitle) blogTitle.textContent = blog.title;
        if (blogExcerpt) blogExcerpt.textContent = blog.excerpt;
        if (blogImage) blogImage.textContent = blog.image;
        if (blogContent) blogContent.innerHTML = blog.content;

        // Populate tags
        const tagsList = document.getElementById("tagsList");
        if (tagsList) {
            tagsList.innerHTML = "";
            blog.tags.forEach((tag) => {
                const tagElement = document.createElement("span");
                tagElement.className = "tag";
                tagElement.textContent = tag;
                tagsList.appendChild(tagElement);
            });
        }

        // Set up navigation - only next blog
        setupNavigation(blogId);

        // Update page title
        document.title = `${blog.title} - ReLeaf`;

        // Hide loading and show main content
        if (loading) loading.style.display = "none";
        if (mainContent) mainContent.style.display = "block";

        // Animate content appearance
        animateContent();

        // Remove newBlogPost from sessionStorage if displayed to avoid duplicate reading
        if (newBlogJSON && newBlog && newBlog.id === blogId) {
            sessionStorage.removeItem('newBlogPost');
        }

    }, 1000);
}

// Setup navigation buttons - only next
function setupNavigation(currentId) {
    const currentIndex = readBlogsData.findIndex((b) => b.id === currentId);
    const nextBlog = document.getElementById("nextBlog");

    if (nextBlog) {
        if (currentIndex < readBlogsData.length - 1) {
            const next = readBlogsData[currentIndex + 1];
            nextBlog.style.display = "block";
            const navBtnText = nextBlog.querySelector(".nav-btn-text");
            if (navBtnText) navBtnText.textContent = next.title;
            nextBlog.onclick = (e) => {
                e.preventDefault();
                navigateToBlog(next.id);
            };
        } else {
            nextBlog.style.display = "none";
        }
    }
}

// Navigate to another blog by changing URL search param
function navigateToBlog(blogId) {
    window.location.search = `?id=${blogId}`;
}

// Animate content on load
function animateContent() {
    const elements = document.querySelectorAll("main > *");
    elements.forEach((el, index) => {
        el.style.opacity = "0";
        el.style.transform = "translateY(20px)";
        el.style.transition = "opacity 0.6s ease, transform 0.6s ease";

        setTimeout(() => {
            el.style.opacity = "1";
            el.style.transform = "translateY(0)";
        }, index * 100);
    });
}

// Initialize page on DOM ready
document.addEventListener("DOMContentLoaded", function () {
    loadBlogContent();
});

// Handle browser back/forward buttons
window.addEventListener("popstate", function () {
    loadBlogContent();
});



















