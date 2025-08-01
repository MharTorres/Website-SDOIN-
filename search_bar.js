// SDOIN Search Bar Component
// This component provides a consistent search experience across all pages

class SDOINSearchBar {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            placeholder: 'Search SDOIN...',
            showSuggestions: true,
            autoComplete: true,
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.render();
        this.bindEvents();
        this.loadSuggestions();
    }
    
    render() {
        this.container.innerHTML = `
            <div class="search-bar-container">
                <form class="search-bar d-flex" action="search.html" method="GET" role="search">
                    <div class="input-group position-relative">
                        <input 
                            type="text" 
                            name="q" 
                            placeholder="${this.options.placeholder}" 
                            class="form-control search-input" 
                            required 
                            aria-label="Search query"
                            autocomplete="off"
                        />
                        <button type="submit" class="btn btn-primary" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <div class="search-suggestions-dropdown" style="display: none;">
                            <div class="suggestions-list"></div>
                        </div>
                    </div>
                </form>
            </div>
        `;
        
        this.searchInput = this.container.querySelector('.search-input');
        this.suggestionsDropdown = this.container.querySelector('.search-suggestions-dropdown');
        this.suggestionsList = this.container.querySelector('.suggestions-list');
    }
    
    bindEvents() {
        // Handle input changes for autocomplete
        if (this.options.autoComplete) {
            this.searchInput.addEventListener('input', (e) => {
                this.handleInputChange(e.target.value);
            });
            
            this.searchInput.addEventListener('focus', () => {
                this.showSuggestions();
            });
        }
        
        // Handle form submission
        this.container.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });
        
        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hideSuggestions();
            }
        });
        
        // Handle keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeyboardNavigation(e);
        });
    }
    
    async handleInputChange(query) {
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }
        
        try {
            const response = await fetch(`backend/search_api.php?q=${encodeURIComponent(query)}&limit=5`);
            if (response.ok) {
                const data = await response.json();
                this.displaySuggestions(data.results, query);
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }
    
    displaySuggestions(results, query) {
        if (results.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        const suggestionsHTML = results.map(result => `
            <div class="suggestion-item" data-url="${result.url}" data-title="${result.title}">
                <div class="suggestion-icon">
                    <i class="${this.getIconClass(result.type)}"></i>
                </div>
                <div class="suggestion-content">
                    <div class="suggestion-title">${this.highlightQuery(result.title, query)}</div>
                    <div class="suggestion-type">${result.type} • ${result.category || 'General'}</div>
                </div>
            </div>
        `).join('');
        
        this.suggestionsList.innerHTML = suggestionsHTML;
        this.showSuggestions();
        
        // Bind click events to suggestions
        this.suggestionsList.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                window.location.href = item.dataset.url;
            });
        });
    }
    
    highlightQuery(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    getIconClass(type) {
        const icons = {
            'gallery': 'fas fa-images',
            'resource': 'fas fa-file-alt',
            'page': 'fas fa-file-text'
        };
        return icons[type] || 'fas fa-file';
    }
    
    showSuggestions() {
        if (this.options.showSuggestions) {
            this.suggestionsDropdown.style.display = 'block';
        }
    }
    
    hideSuggestions() {
        this.suggestionsDropdown.style.display = 'none';
    }
    
    handleKeyboardNavigation(e) {
        const suggestions = this.suggestionsList.querySelectorAll('.suggestion-item');
        const currentIndex = Array.from(suggestions).findIndex(item => item.classList.contains('active'));
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.navigateSuggestions(suggestions, currentIndex, 1);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.navigateSuggestions(suggestions, currentIndex, -1);
                break;
            case 'Enter':
                if (currentIndex >= 0) {
                    e.preventDefault();
                    suggestions[currentIndex].click();
                }
                break;
            case 'Escape':
                this.hideSuggestions();
                break;
        }
    }
    
    navigateSuggestions(suggestions, currentIndex, direction) {
        suggestions.forEach(item => item.classList.remove('active'));
        
        let newIndex = currentIndex + direction;
        if (newIndex < 0) newIndex = suggestions.length - 1;
        if (newIndex >= suggestions.length) newIndex = 0;
        
        if (suggestions[newIndex]) {
            suggestions[newIndex].classList.add('active');
            suggestions[newIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    
    performSearch() {
        const query = this.searchInput.value.trim();
        if (query) {
            window.location.href = `search.html?q=${encodeURIComponent(query)}`;
        }
    }
    
    loadSuggestions() {
        // Load popular searches for initial suggestions
        const popularSearches = [
            { title: 'About SDOIN', url: 'about.html', type: 'page' },
            { title: 'Educational Programs', url: 'programs.html', type: 'page' },
            { title: 'Resources', url: 'resources.php', type: 'page' },
            { title: 'News', url: 'news.html', type: 'page' },
            { title: 'Gallery', url: 'gallery.php', type: 'page' }
        ];
        
        this.popularSearches = popularSearches;
    }
}

// Add CSS styles for the search bar component
const searchBarStyles = `
<style>
.search-bar-container {
    position: relative;
}

.search-suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.suggestions-list {
    padding: 0;
    margin: 0;
    list-style: none;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item:hover,
.suggestion-item.active {
    background-color: #f8f9fa;
}

.suggestion-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    border-radius: 6px;
    margin-right: 0.75rem;
    color: #6c757d;
}

.suggestion-content {
    flex: 1;
}

.suggestion-title {
    font-weight: 500;
    color: #212529;
    margin-bottom: 0.25rem;
}

.suggestion-type {
    font-size: 0.875rem;
    color: #6c757d;
}

.suggestion-title strong {
    color: var(--primary-color, #004aad);
}

@media (max-width: 768px) {
    .search-suggestions-dropdown {
        position: fixed;
        top: auto;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 12px 12px 0 0;
        max-height: 50vh;
    }
}
</style>
`;

// Inject styles into the document
if (!document.querySelector('#search-bar-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'search-bar-styles';
    styleElement.innerHTML = searchBarStyles;
    document.head.appendChild(styleElement);
}

// Export for use in other scripts
window.SDOINSearchBar = SDOINSearchBar; 