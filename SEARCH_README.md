# SDOIN Website Search Functionality

This document describes the enhanced search functionality implemented for the SDOIN (Schools Division of Ilocos Norte) website.

## Features

### 🔍 **Comprehensive Search**
- Search across multiple content types: Gallery images, Resources, and Static pages
- Real-time search suggestions with autocomplete
- Advanced filtering by content type and category
- Relevance-based result ranking

### 🎨 **Modern UI/UX**
- Responsive design that works on all devices
- Loading states and error handling
- Keyboard navigation support
- Beautiful result cards with images and metadata

### ⚡ **Performance Optimized**
- Database indexing for fast searches
- Efficient search algorithms
- Cached suggestions for better user experience

## Setup Instructions

### 1. Database Setup
Run the database setup script to create the necessary tables:

```bash
# Navigate to your project directory
cd /path/to/your/OJTProject

# Run the setup script in your browser
http://localhost/OJTProject/setup_search_db.php
```

This will create:
- `gallery` table (if not exists)
- `resources` table with sample data
- Proper indexes for performance

### 2. File Structure
The search functionality consists of these files:

```
OJTProject/
├── backend/
│   ├── search_api.php          # Main search API
│   └── db.php                  # Database connection
├── database/
│   ├── gallery_table.sql       # Gallery table schema
│   └── resources_table.sql     # Resources table schema
├── search.html                 # Enhanced search page
├── search_bar.js              # Reusable search component
├── setup_search_db.php        # Database setup script
└── SEARCH_README.md           # This documentation
```

### 3. Configuration
Update the database connection in `backend/db.php` if needed:

```php
$host = 'localhost';
$db = 'sdoin';
$user = 'root';
$pass = 'your_password';
```

## How to Use

### Basic Search
1. Navigate to any page with the search bar
2. Type your search query
3. Press Enter or click the search button
4. View results on the search results page

### Advanced Features

#### Filtering Results
- Use the filter buttons to narrow down results by:
  - **All**: Show all content types
  - **Gallery**: Only images and photos
  - **Resources**: Only documents and files
  - **Pages**: Only static website pages
  - **Programs**: Content related to educational programs
  - **Initiatives**: Content related to key initiatives
  - **Events**: Event-related content
  - **Sports**: Sports and athletic content

#### Search Suggestions
- Real-time suggestions appear as you type
- Click on suggestions to go directly to content
- Use arrow keys to navigate suggestions
- Press Enter to select highlighted suggestion

#### Keyboard Shortcuts
- **Enter**: Submit search or select suggestion
- **Arrow Up/Down**: Navigate suggestions
- **Escape**: Close suggestions dropdown

## API Documentation

### Search API Endpoint
`GET /backend/search_api.php`

#### Parameters
- `q` (required): Search query string
- `category` (optional): Filter by category
- `limit` (optional): Maximum number of results (default: all)

#### Example Request
```
GET /backend/search_api.php?q=education&category=programs
```

#### Response Format
```json
{
  "results": [
    {
      "id": 1,
      "title": "Educational Programs",
      "description": "Explore our comprehensive educational programs...",
      "url": "programs.html",
      "type": "page",
      "category": "programs",
      "relevance": 15,
      "matched_terms": ["education", "programs"]
    }
  ],
  "total": 1,
  "query": "education"
}
```

## Content Types

### 1. Gallery Items
- **Source**: `gallery` table
- **Searchable fields**: title, description, category
- **Categories**: programs, initiatives, events, sports
- **URL**: Links to `gallery.php`

### 2. Resources
- **Source**: `resources` table
- **Searchable fields**: title, description, category
- **Categories**: curriculum, guidelines, forms, manuals, reports, general
- **URL**: Links to `resources.php`

### 3. Static Pages
- **Source**: Predefined page definitions
- **Searchable fields**: title, description, keywords
- **Pages**: about, programs, initiatives, news, resources, gallery, contact
- **URL**: Direct links to respective HTML pages

## Customization

### Adding New Content Types
1. Create a new table in the database
2. Add search logic in `backend/search_api.php`
3. Update the search page filters
4. Add appropriate icons and styling

### Modifying Search Logic
Edit the `SearchAPI` class in `backend/search_api.php`:

```php
class SearchAPI {
    // Add new search methods
    private function searchNewContent($query, $filters) {
        // Your custom search logic
    }
    
    // Modify relevance calculation
    private function calculateRelevance($row, $searchTerms) {
        // Custom relevance scoring
    }
}
```

### Styling Customization
The search functionality uses CSS custom properties for theming:

```css
:root {
  --primary-color: #004aad;
  --secondary-color: #3b5bfe;
  --light-color: #f8f9fa;
}
```

## Troubleshooting

### Common Issues

#### 1. Search Not Working
- Check database connection in `backend/db.php`
- Ensure tables exist by running `setup_search_db.php`
- Check browser console for JavaScript errors

#### 2. No Results Found
- Verify content exists in database tables
- Check search query spelling
- Try broader search terms

#### 3. Slow Search Performance
- Ensure database indexes are created
- Check server performance
- Consider implementing caching

#### 4. Search Bar Not Appearing
- Include `search_bar.js` in your HTML
- Check for JavaScript errors
- Verify Bootstrap and Font Awesome are loaded

### Debug Mode
Enable debug mode by adding this to your search page:

```javascript
// Add to search.html before closing </script> tag
window.searchDebug = true;
```

This will log search requests and responses to the browser console.

## Performance Tips

1. **Database Indexing**: Ensure all searchable columns have indexes
2. **Query Optimization**: Use LIMIT clauses for large result sets
3. **Caching**: Consider implementing Redis or Memcached for frequent searches
4. **CDN**: Use CDN for static assets (Bootstrap, Font Awesome)

## Security Considerations

1. **SQL Injection**: All queries use prepared statements
2. **XSS Prevention**: Output is properly escaped
3. **Access Control**: Search API is publicly accessible (modify if needed)
4. **Rate Limiting**: Consider implementing rate limiting for production

## Future Enhancements

- [ ] Full-text search with MySQL FULLTEXT indexes
- [ ] Search analytics and popular searches
- [ ] Advanced filters (date range, file type)
- [ ] Search result highlighting
- [ ] Search history and bookmarks
- [ ] Multi-language search support

## Support

For issues or questions about the search functionality:
1. Check this documentation
2. Review browser console for errors
3. Verify database setup
4. Test with sample data

---

**Last Updated**: January 2025  
**Version**: 1.0  
**Compatibility**: PHP 7.4+, MySQL 5.7+, Modern Browsers 