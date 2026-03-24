-- ============================================================
-- Job Portal - Database Schema
-- GROUP 14 | SSE2304 CAT II
-- ============================================================
 
CREATE DATABASE IF NOT EXISTS job_portal;
USE job_portal;
 
-- Central users table
CREATE TABLE users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role        ENUM('seeker','employer','admin') NOT NULL,
    status      ENUM('active','inactive') DEFAULT 'active',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);
 
-- Job seeker profile (1-to-1 with users where role=seeker)
CREATE TABLE job_seeker_profiles (
    profile_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNIQUE NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    headline    VARCHAR(200),
    skills      TEXT,
    location    VARCHAR(100),
    bio         TEXT,
    resume_path VARCHAR(300),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
 
-- Job categories (normalization for easy filtering)
CREATE TABLE job_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon_path   VARCHAR(300),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);
 
-- Employer profile (1-to-1 with users where role=employer)
CREATE TABLE employer_profiles (
    profile_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNIQUE NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    industry     VARCHAR(100),
    website      VARCHAR(200),
    logo_path    VARCHAR(300),
    description  TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
 
-- Job postings
CREATE TABLE jobs (
    job_id      INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    category_id INT,
    location    VARCHAR(100),
    salary_min  DECIMAL(10,2),
    salary_max  DECIMAL(10,2),
    description TEXT NOT NULL,
    status      ENUM('open','closed') DEFAULT 'open',
    deadline    DATE,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES job_categories(category_id) ON DELETE SET NULL
);
 
-- Applications
CREATE TABLE applications (
    app_id               INT AUTO_INCREMENT PRIMARY KEY,
    job_id               INT NOT NULL,
    seeker_id            INT NOT NULL,
    resume_snapshot_path VARCHAR(300),
    cover_letter         TEXT,
    status               ENUM('Pending','Reviewed','Shortlisted','Rejected','Hired') DEFAULT 'Pending',
    applied_at           DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_apply (job_id, seeker_id),
    FOREIGN KEY (job_id)     REFERENCES jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id)  REFERENCES users(user_id) ON DELETE CASCADE
);
 
-- Saved jobs
CREATE TABLE saved_jobs (
    save_id    INT AUTO_INCREMENT PRIMARY KEY,
    seeker_id  INT NOT NULL,
    job_id     INT NOT NULL,
    saved_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_save (seeker_id, job_id),
    FOREIGN KEY (seeker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)    REFERENCES jobs(job_id)   ON DELETE CASCADE
);
 
-- ============================================================
-- Seed data for job categories
-- ============================================================
 
-- Job categories
INSERT INTO job_categories (category_name, description) VALUES
('Engineering', 'Software engineering, backend, frontend, and full-stack development roles'),
('Data & Analytics', 'Data analysis, business intelligence, and analytics positions'),
('Design', 'UX/UI design, graphic design, and product design roles'),
('Sales & Marketing', 'Sales, marketing, business development, and growth roles'),
('Product Management', 'Product management, strategy, and operations roles'),
('Finance & Accounting', 'Finance, accounting, auditing, and CFO-level positions'),
('HR & Recruitment', 'Human resources, recruitment, and talent management roles'),
('Operations', 'Operations, logistics, supply chain, and project management roles');