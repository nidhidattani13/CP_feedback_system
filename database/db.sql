CREATE DATABASE IF NOT EXISTS feedback_system;
USE feedback_system;

-- Users table (students, faculty, HOD)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','faculty','hod') NOT NULL,
  enrollment_no VARCHAR(16),
  sgpa1 DECIMAL(3,2),
  sgpa2 DECIMAL(3,2),
  sgpa3 DECIMAL(3,2),
  sgpa4 DECIMAL(3,2),
  sgpa5 DECIMAL(3,2),
  sgpa6 DECIMAL(3,2),
  sgpa7 DECIMAL(3,2),
  sgpa8 DECIMAL(3,2),
  cgpa DECIMAL(3,2),
  category VARCHAR(32),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Feedback Questions
CREATE TABLE IF NOT EXISTS questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_text VARCHAR(255) NOT NULL,
  question_type ENUM('text','yesno','mcq','rating') NOT NULL
);

CREATE TABLE IF NOT EXISTS question_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  option_text VARCHAR(128) NOT NULL,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  faculty_id INT NOT NULL,
  subject_id INT NOT NULL,
  question_id INT NOT NULL,
  response TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Trigger: Auto-calculate CGPA and category for students
DELIMITER //
CREATE TRIGGER calc_cgpa_category BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
  IF NEW.role = 'student' THEN
    SET NEW.cgpa = ROUND((IFNULL(NEW.sgpa1,0)+IFNULL(NEW.sgpa2,0)+IFNULL(NEW.sgpa3,0)+IFNULL(NEW.sgpa4,0)+IFNULL(NEW.sgpa5,0)+IFNULL(NEW.sgpa6,0)+IFNULL(NEW.sgpa7,0)+IFNULL(NEW.sgpa8,0)) /
      (IF(NEW.sgpa1 IS NOT NULL,1,0)+IF(NEW.sgpa2 IS NOT NULL,1,0)+IF(NEW.sgpa3 IS NOT NULL,1,0)+IF(NEW.sgpa4 IS NOT NULL,1,0)+IF(NEW.sgpa5 IS NOT NULL,1,0)+IF(NEW.sgpa6 IS NOT NULL,1,0)+IF(NEW.sgpa7 IS NOT NULL,1,0)+IF(NEW.sgpa8 IS NOT NULL,1,0)),2);
    IF NEW.cgpa >= 9 THEN
      SET NEW.category = 'Excellent';
    ELSEIF NEW.cgpa >= 8 THEN
      SET NEW.category = 'Very Good';
    ELSEIF NEW.cgpa >= 7 THEN
      SET NEW.category = 'Good';
    ELSEIF NEW.cgpa >= 6 THEN
      SET NEW.category = 'Average';
    ELSE
      SET NEW.category = 'Below Average';
    END IF;
  END IF;
END;//
DELIMITER ;

-- Procedure: Random daily selection of n students
DELIMITER //
CREATE PROCEDURE generate_daily_selection(IN n INT)
BEGIN
  DELETE FROM daily_selected_students WHERE selection_date = CURDATE();
  INSERT INTO daily_selected_students (student_id, selection_date)
  SELECT id, CURDATE() FROM users WHERE role='student' ORDER BY RAND() LIMIT n;
END;//
DELIMITER ;

-- Table to store daily selected students
CREATE TABLE IF NOT EXISTS daily_selected_students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  selection_date DATE NOT NULL,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  faculty_id INT,
  subject VARCHAR(150),
  rating TINYINT,
  comments TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Assessments table
CREATE TABLE IF NOT EXISTS assessments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT NOT NULL,
  title VARCHAR(150),
  week_no INT,
  status ENUM('planned','verified') DEFAULT 'planned',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150),
  description TEXT,
  date DATE,
  status ENUM('planned','verified') DEFAULT 'planned',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notices table (visible to all dashboards, created by HOD)
CREATE TABLE IF NOT EXISTS notices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  body TEXT,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table (faculty can add/manage subjects)
CREATE TABLE IF NOT EXISTS subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT NOT NULL,
  subject_name VARCHAR(100) NOT NULL,
  semester INT NOT NULL,
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Student-Subjects mapping table
CREATE TABLE IF NOT EXISTS student_subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Optionally insert sample accounts (use register page alternatively)
-- You can create users via the register page included in frontend/auth/register.php
