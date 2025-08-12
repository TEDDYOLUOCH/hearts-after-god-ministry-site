-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME,
    location VARCHAR(255),
    image_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    registration_url VARCHAR(255),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create event_categories table
CREATE TABLE IF NOT EXISTS event_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create event_category_mapping junction table
CREATE TABLE IF NOT EXISTS event_category_mapping (
    event_id INT,
    category_id INT,
    PRIMARY KEY (event_id, category_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default event categories
INSERT IGNORE INTO event_categories (name, slug, description) VALUES
('Sunday Service', 'sunday-service', 'Weekly Sunday worship services'),
('Bible Study', 'bible-study', 'Weekly Bible study groups'),
('Prayer Meeting', 'prayer-meeting', 'Corporate prayer gatherings'),
('Outreach', 'outreach', 'Community outreach events'),
('Conference', 'conference', 'Special conferences and seminars'),
('Youth', 'youth', 'Youth group events'),
('Children', 'children', 'Children''s ministry events'),
('Women''s Ministry', 'womens-ministry', 'Events for women'),
('Men''s Ministry', 'mens-ministry', 'Events for men'),
('Special Event', 'special-event', 'Other special events');
