-- -- Create database and set context
CREATE DATABASE IF NOT EXISTS ccs;
USE ccs;

-- Departments Table
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Users Table (for login credentials)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE, -- Email is mandatory here
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('approved', 'pending') DEFAULT 'pending'
) ENGINE=InnoDB;

-- Students Table (for student-specific data)
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    email VARCHAR(255) NOT NULL, -- Redundant email storage for students
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    department_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE instructors (
    instructor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    email VARCHAR(255) NOT NULL, -- Redundant email storage for instructors
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Admins Table (for admin-specific data)
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Courses Table
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Resources Table
CREATE TABLE resources (
    resource_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    course_id INT,
    instructor_id INT,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Discussion Forums Table
CREATE TABLE discussion_forums (
    forum_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    course_id INT,
    instructor_id INT,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Forum Posts Table
CREATE TABLE forum_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    forum_id INT,
    user_id INT,
    post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content TEXT NOT NULL,
    FOREIGN KEY (forum_id) REFERENCES discussion_forums(forum_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Real-Time Discussion Rooms Table
CREATE TABLE realtime_discussion_rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    course_id INT,
    instructor_id INT,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Enrollments Table (Many-to-Many between Students and Courses)
CREATE TABLE enrollments (
    student_id INT,
    course_id INT,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Instructor-Course Relationship (Many-to-Many)
CREATE TABLE instructor_courses (
    instructor_id INT,
    course_id INT,
    PRIMARY KEY (instructor_id, course_id),
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE course_departments (
    course_id INT NOT NULL,
    department_id INT NOT NULL,
    PRIMARY KEY (course_id, department_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
)ENGINE=InnoDB;
INSERT INTO departments (department_name) VALUES 
('Computer Science'),
('Information Systems'),
('Information Technology');

INSERT INTO users (
    username,
    password,
    email,
    role,
    status
) VALUES (
    'admin',
    '$2y$10$TS/XrLskVRWuY1KDrKV9b.YnwDQ8/hd6Vtbr7G89NfwMroTlBmbpa',  -- Hashed password
    'admin@hu.edu.et',
    'admin',
    'approved'
);
-- Get the last inserted user_id
SET @admin_user_id = LAST_INSERT_ID();

-- Insert into admins table
INSERT INTO admins (
    user_id,
    first_name,
    last_name,
    phone
) VALUES (
    @admin_user_id,
    'System',
    'Administrator',
    '+251911223344'  -- Optional phone number
);

ALTER TABLE courses ADD COLUMN status ENUM('active', 'completed') NOT NULL DEFAULT 'active';
ALTER TABLE courses ADD COLUMN status ENUM('active', 'completed') NOT NULL DEFAULT 'active';
CREATE TABLE resource_views (
    view_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    view_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE
);
CREATE TABLE assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    course_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);
CREATE TABLE submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    file_path VARCHAR(255),
    submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5, 2),
    feedback TEXT,
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);
ALTER TABLE courses ADD COLUMN start_date DATE;
ALTER TABLE courses ADD COLUMN end_date DATE;
CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT DEFAULT NULL, -- Optional: For resource ratings
    course_id INT DEFAULT NULL,   -- Optional: For course ratings
    forum_post_id INT DEFAULT NULL, -- Optional: For forum post ratings
    user_id INT NOT NULL,         -- User who submitted the rating
    rating DECIMAL(3, 2) NOT NULL, -- Rating value (e.g., 4.5)
    rated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (forum_post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT DEFAULT NULL, -- Optional: For resource comments
    forum_post_id INT DEFAULT NULL, -- Optional: For forum post comments
    user_id INT NOT NULL,         -- User who posted the comment
    comment TEXT NOT NULL,        -- Comment content
    commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    FOREIGN KEY (forum_post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
CREATE TABLE likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT DEFAULT NULL, -- Optional: For resource likes
    forum_post_id INT DEFAULT NULL, -- Optional: For forum post likes
    user_id INT NOT NULL,         -- User who liked the content
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(resource_id) ON DELETE CASCADE,
    FOREIGN KEY (forum_post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
ALTER TABLE forum_posts ADD COLUMN is_approved BOOLEAN DEFAULT FALSE;
ALTER TABLE forum_posts ADD COLUMN is_flagged BOOLEAN DEFAULT FALSE;
ALTER TABLE resources ADD COLUMN file_size BIGINT DEFAULT NULL;
ALTER TABLE resources ADD COLUMN mime_type VARCHAR(255) DEFAULT NULL;
ALTER TABLE resources ADD COLUMN storage_type ENUM('local', 'cloud') DEFAULT 'local';
ALTER TABLE enrollments ADD COLUMN status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled';
ALTER TABLE submissions ADD COLUMN is_reviewed BOOLEAN DEFAULT FALSE;
CREATE TABLE realtime_discussion_rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    course_id INT,
    instructor_id INT,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES realtime_discussion_rooms(room_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

ALTER TABLE courses ADD COLUMN instructor_id INT NOT NULL;
ALTER TABLE realtime_discussion_rooms
ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active';
ALTER TABLE realtime_discussion_rooms
ADD COLUMN max_participants INT NOT NULL DEFAULT 10 AFTER instructor_id;
