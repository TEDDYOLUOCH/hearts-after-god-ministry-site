-- Create sermons table
CREATE TABLE IF NOT EXISTS sermons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    preacher VARCHAR(255) NOT NULL,
    bible_reference VARCHAR(100),
    sermon_date DATE NOT NULL,
    audio_url VARCHAR(255),
    video_url VARCHAR(255),
    description TEXT,
    thumbnail_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (optional)
INSERT INTO sermons (title, preacher, bible_reference, sermon_date, description, thumbnail_url) 
VALUES 
('The Power of Faith', 'Pastor John Doe', 'Hebrews 11:1-6', '2023-10-15', 'Exploring the depths of faith and how it transforms lives.', 'assets/images/sermons/faith.jpg'),
('Walking in Love', 'Pastor Jane Smith', '1 Corinthians 13:1-13', '2023-10-08', 'Understanding God''s love and how to walk in it daily.', 'assets/images/sermons/love.jpg'),
('Overcoming Challenges', 'Pastor Mike Johnson', 'James 1:2-4', '2023-10-01', 'Finding strength in God during difficult times.', 'assets/images/sermons/challenges.jpg');
