-- Create team_members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    bio TEXT,
    image_url VARCHAR(255),
    facebook_url VARCHAR(255),
    twitter_url VARCHAR(255),
    instagram_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample team members
INSERT INTO team_members (name, role, bio, image_url, facebook_url, twitter_url, instagram_url, linkedin_url, display_order) VALUES
('Apostle Teddy O. Luo', 'Founder & Lead Pastor', 'Visionary leader with a passion for global revival and discipleship.', 'assets/images/leadership/apostle-teddy.jpg', '#', '#', '#', '#', 1),
('Minister Cyrilla Chikamai', 'Worship Leader', 'Anointed worship leader with a heart for ushering in God\'s presence.', 'assets/images/leadership/cyrilla.jpg', '#', '#', '#', '#', 2),
('Minister Palm Naomi', 'Youth Pastor', 'Dedicated to raising the next generation of kingdom leaders.', 'assets/images/leadership/palm-naomi.jpg', '#', '#', '#', '#', 3),
('Elder John Mwangi', 'Elder & Prayer Coordinator', 'Man of prayer and spiritual covering for the ministry.', 'assets/images/leadership/elder-john.jpg', '#', '#', '#', '#', 4);
