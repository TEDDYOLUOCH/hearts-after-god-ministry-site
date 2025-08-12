# Blog Management System

This document provides an overview of the blog management system implemented for the Hearts After God Ministry website.

## Features

- **Blog Post Management**: Create, read, update, and delete blog posts
- **Rich Text Editing**: TinyMCE integration for content editing
- **Featured Images**: Upload and manage featured images with drag-and-drop support
- **Categories**: Organize posts with categories
- **SEO Optimization**: Meta title and description for better search engine visibility
- **Responsive Design**: Works on desktop and mobile devices
- **File Management**: Automatic image resizing and thumbnail generation

## Database Schema

The following tables are used by the blog system:

### blog_posts
- `id` (int, primary key)
- `title` (varchar)
- `slug` (varchar, unique)
- `excerpt` (text)
- `content` (longtext)
- `status` (enum: 'draft', 'published', 'archived')
- `featured_image` (varchar, nullable)
- `meta_title` (varchar, nullable)
- `meta_description` (text, nullable)
- `author_id` (int, foreign key to users.id)
- `created_at` (datetime)
- `updated_at` (datetime)

### categories
- `id` (int, primary key)
- `name` (varchar)
- `slug` (varchar, unique)
- `type` (varchar, e.g., 'blog')
- `description` (text, nullable)

### blog_post_categories
- `post_id` (int, foreign key to blog_posts.id)
- `category_id` (int, foreign key to categories.id)
- Primary key: (post_id, category_id)

## Setup Instructions

1. **Database Setup**
   - Import the SQL schema from `database/blog_schema.sql`
   - Update the database configuration in `config/db.php`

2. **File Permissions**
   - Ensure the `uploads/blog/` directory is writable by the web server
   - Create the directory structure if it doesn't exist:
     ```
     mkdir -p uploads/blog/{Y/m,thumbs}
     chmod -R 755 uploads/
     ```

3. **TinyMCE API Key**
   - Get a free API key from [TinyMCE](https://www.tiny.cloud/)
   - Update the script URL in `admin_manage_blog.php` with your API key

4. **URL Rewriting**
   - Ensure mod_rewrite is enabled in your Apache configuration
   - The `.htaccess` file should include rules for clean URLs

## Usage

### Admin Dashboard
- Access the blog management section at `/admin/blog`
- Requires admin privileges

### Creating a New Post
1. Click "New Post"
2. Enter the post title, content, and other details
3. Upload a featured image (optional)
4. Select categories
5. Set the post status (draft/published/archived)
6. Click "Publish" or "Save Draft"

### Managing Posts
- View all posts in the posts list
- Edit a post by clicking the edit icon
- Delete a post using the trash icon (with confirmation)
- Filter and search posts using the controls at the top of the list

## Security Considerations

- All database queries use prepared statements to prevent SQL injection
- File uploads are validated for type and size
- User authentication and authorization are required for all admin functions
- CSRF protection is implemented for all form submissions
- File uploads are stored outside the web root where possible

## Customization

### Styling
- The interface uses Tailwind CSS for styling
- Custom styles can be added in the `<style>` section of the admin templates

### Editor Configuration
- The TinyMCE editor can be customized by modifying the initialization options in `admin_manage_blog.php`
- Additional plugins can be added by including them in the `plugins` option

## Troubleshooting

### Common Issues
1. **Images not uploading**
   - Check file permissions on the uploads directory
   - Verify that the PHP `file_uploads` directive is enabled
   - Check PHP error logs for specific error messages

2. **Editor not loading**
   - Verify that the TinyMCE API key is valid
   - Check browser console for JavaScript errors
   - Ensure internet connectivity for loading external resources

3. **Database connection issues**
   - Verify database credentials in `config/db.php`
   - Check that the database server is running
   - Ensure the database user has the necessary permissions

## Dependencies

- PHP 7.4 or higher
- MySQL 5.7 or higher
- TinyMCE 6.x (loaded via CDN)
- Tailwind CSS 2.2.x (loaded via CDN)
- Font Awesome 6.x (loaded via CDN)

## License

This project is licensed under the MIT License - see the LICENSE file for details.
