document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const keyword = urlParams.get("q")?.toLowerCase() || "";
  const resultsContainer = document.getElementById("results");

  // Dummy content (you can replace this with real data or fetch from JSON/API)
  const data = [
    { title: "About SDOIN", content: "Information about Schools Division of Ilocos Norte." },
    { title: "Programs", content: "Learn about our DepEd programs and services." },
    { title: "Gallery", content: "Photos and videos from various events." },
    { title: "Key Initiatives", content: "Latest key initiatives in education and innovation." },
    { title: "News", content: "Read the latest news and announcements." },
  ];

  const matched = data.filter(item =>
    item.title.toLowerCase().includes(keyword) ||
    item.content.toLowerCase().includes(keyword)
  );

  if (matched.length > 0) {
    matched.forEach(item => {
      const div = document.createElement("div");
      div.classList.add("result-item");
      div.innerHTML = `<h4>${item.title}</h4><p>${item.content}</p>`;
      resultsContainer.appendChild(div);
    });
  } else {
    resultsContainer.innerHTML = `<p>No results found for "<strong>${keyword}</strong>".</p>`;
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const ids = [
    "programs_title",
    "toured_desc1", "toured_desc2",
    "toured_focus1", "toured_focus2", "toured_focus3", "toured_focus4",
    "inherited_desc1", "inherited_desc2",
    "inherited_focus1", "inherited_focus2", "inherited_focus3",
    "inherited_focus4", "inherited_focus5", "inherited_focus6", "inherited_focus7"
  ];

  ids.forEach(id => {
    fetch(`get_content.php?key=${id}`)
      .then(res => res.text())
      .then(text => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
      });
  });
});

// Enhanced Search and Navigation Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced Search Functionality
    const searchInput = document.querySelector('input[name="q"]');
    const searchForm = document.querySelector('.search-bar');
    
    if (searchInput && searchForm) {
        // Add search suggestions container
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        suggestionsContainer.style.display = 'none';
        searchForm.appendChild(suggestionsContainer);
        
        // Search suggestions data
        const searchSuggestions = [
            'About SDOIN',
            'Educational Programs',
            'Key Initiatives',
            'Resources',
            'News and Updates',
            'Gallery',
            'Contact Information',
            'Send Inquiry',
            'Admin Login'
        ];
        
        // Handle search input
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length < 2) {
                suggestionsContainer.style.display = 'none';
                return;
            }
            
            // Filter suggestions
            const filteredSuggestions = searchSuggestions.filter(suggestion => 
                suggestion.toLowerCase().includes(query)
            );
            
            if (filteredSuggestions.length > 0) {
                displaySuggestions(filteredSuggestions, query);
            } else {
                suggestionsContainer.style.display = 'none';
            }
        });
        
        // Display suggestions
        function displaySuggestions(suggestions, query) {
            suggestionsContainer.innerHTML = '';
            
            suggestions.forEach(suggestion => {
                const item = document.createElement('div');
                item.className = 'search-suggestion-item';
                item.textContent = suggestion;
                
                // Highlight matching text
                const regex = new RegExp(`(${query})`, 'gi');
                item.innerHTML = suggestion.replace(regex, '<strong>$1</strong>');
                
                item.addEventListener('click', function() {
                    searchInput.value = suggestion;
                    suggestionsContainer.style.display = 'none';
                    searchForm.submit();
                });
                
                suggestionsContainer.appendChild(item);
            });
            
            suggestionsContainer.style.display = 'block';
        }
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
        
        // Handle search form submission
        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                e.preventDefault();
                searchInput.focus();
                return;
            }
            
            // Add loading state
            searchForm.classList.add('search-loading');
            searchInput.disabled = true;
            
            // Remove loading state after a short delay (simulating search)
            setTimeout(() => {
                searchForm.classList.remove('search-loading');
                searchInput.disabled = false;
            }, 1000);
        });
    }
    
    // Enhanced Mobile Navigation
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        // Add smooth animation to navbar collapse
        navbarCollapse.addEventListener('show.bs.collapse', function() {
            this.style.transition = 'all 0.3s ease';
        });
        
        navbarCollapse.addEventListener('hide.bs.collapse', function() {
            this.style.transition = 'all 0.3s ease';
        });
        
        // Close navbar when clicking on a link (mobile)
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                        hide: true
                    });
                }
            });
        });
        
        // Add active state to current page
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage || (currentPage === 'index.html' && href === 'index.html')) {
                link.classList.add('active');
            }
        });
    }
    
    // Enhanced Touch Interactions
    const touchElements = document.querySelectorAll('.nav-link, .btn, .search-bar .btn');
    
    touchElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        element.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });
    
    // Keyboard Navigation Improvements
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Escape to close search suggestions
        if (e.key === 'Escape') {
            const suggestions = document.querySelector('.search-suggestions');
            if (suggestions) {
                suggestions.style.display = 'none';
            }
            if (searchInput) {
                searchInput.blur();
            }
        }
    });
    
    // Responsive Navigation Improvements
    function handleResize() {
        const isMobile = window.innerWidth < 992;
        const navbar = document.querySelector('.main-navbar');
        
        if (navbar) {
            if (isMobile) {
                navbar.classList.add('mobile-nav');
            } else {
                navbar.classList.remove('mobile-nav');
            }
        }
    }
    
    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(handleResize, 100);
    });
    
    window.addEventListener('resize', handleResize);
    handleResize();
    
    // Add loading states for better UX
    const links = document.querySelectorAll('a[href]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't add loading for external links or anchors
            if (this.hostname !== window.location.hostname || this.hash) {
                return;
            }
            
            // Add loading indicator
            const originalText = this.textContent;
            this.textContent = 'Loading...';
            this.style.pointerEvents = 'none';
            
            // Reset after navigation (or timeout)
            setTimeout(() => {
                this.textContent = originalText;
                this.style.pointerEvents = '';
            }, 2000);
        });
    });
    
    // Enhanced Scroll Behavior
    let lastScrollTop = 0;
    const navbar = document.querySelector('.main-navbar');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (navbar) {
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                navbar.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                navbar.style.transform = 'translateY(0)';
            }
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Performance optimization: Debounce search input
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Apply debouncing to search input
    if (searchInput) {
        const debouncedSearch = debounce(function(query) {
            // Handle search logic here
            console.log('Searching for:', query);
        }, 300);
        
        searchInput.addEventListener('input', function() {
            debouncedSearch(this.value);
        });
    }
});



