-- ============================================================
-- Job Portal - Database Schema (Updated)
-- GROUP 14 | SSE2304 CAT II
-- ============================================================

CREATE DATABASE IF NOT EXISTS job_portal;
USE job_portal;

-- Central users table
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('seeker','employer','admin') NOT NULL,
    status        ENUM('active','inactive') DEFAULT 'active',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
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
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    description   TEXT,
    icon_path     VARCHAR(300),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
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

-- Job postings  (job_type + featured columns added to match PHP functions and UI)
CREATE TABLE jobs (
    job_id      INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    category_id INT,
    location    VARCHAR(100),
    job_type    ENUM('Full Time','Part Time','Internship','Contract','Remote','Freelance','Temporary') DEFAULT 'Full Time',
    salary_min  DECIMAL(10,2),
    salary_max  DECIMAL(10,2),
    description TEXT NOT NULL,
    featured    TINYINT(1) DEFAULT 0,
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
    FOREIGN KEY (job_id)    REFERENCES jobs(job_id)    ON DELETE CASCADE,
    FOREIGN KEY (seeker_id) REFERENCES users(user_id)  ON DELETE CASCADE
);

-- Saved jobs
CREATE TABLE saved_jobs (
    save_id   INT AUTO_INCREMENT PRIMARY KEY,
    seeker_id INT NOT NULL,
    job_id    INT NOT NULL,
    saved_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_save (seeker_id, job_id),
    FOREIGN KEY (seeker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)    REFERENCES jobs(job_id)   ON DELETE CASCADE
);

-- ============================================================
-- SEED: Job Categories
-- ============================================================
INSERT INTO job_categories (category_name, description) VALUES
('Engineering',          'Software engineering, backend, frontend, and full-stack development roles'),
('Data & Analytics',     'Data analysis, business intelligence, and analytics positions'),
('Design',               'UX/UI design, graphic design, and product design roles'),
('Sales & Marketing',    'Sales, marketing, business development, and growth roles'),
('Product Management',   'Product management, strategy, and operations roles'),
('Finance & Accounting', 'Finance, accounting, auditing, and CFO-level positions'),
('HR & Recruitment',     'Human resources, recruitment, and talent management roles'),
('Operations',           'Operations, logistics, supply chain, and project management roles'),
('Education & Training', 'Teaching, tutoring, curriculum design, and e-learning roles'),
('Healthcare & Medical', 'Medical, nursing, public health, and clinical roles'),
('Legal',                'Legal counsel, compliance, contract management roles'),
('Customer Support',     'Customer service, helpdesk, and CRM roles');

-- ============================================================
-- SEED: Users  (ALL passwords are:  Password123!)
-- ============================================================

-- Admin
INSERT INTO users (email, password_hash, role, status) VALUES
('admin@mboka.co.ke', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Employers
INSERT INTO users (email, password_hash, role, status) VALUES
('hr@zetech.ac.ke',        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('jobs@safaricom.co.ke',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('careers@zeraki.app',     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('recruit@unep.org',       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('people@twigafoods.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('hr@ncbagroup.com',       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('hr@geminia.co.ke',       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('talent@turing.com',      '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('jobs@triccare.com',      '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active'),
('jobs@aagrowers.co.ke',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'active');

-- Seekers
INSERT INTO users (email, password_hash, role, status) VALUES
('alice.wanjiku@gmail.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'active'),
('brian.otieno@gmail.com',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'active'),
('cynthia.auma@outlook.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'active');

-- ============================================================
-- SEED: Employer Profiles
-- ============================================================
INSERT INTO employer_profiles (user_id, company_name, industry, website, description) VALUES
(2,  'Zetech University',                    'Education & Training', 'https://zetech.ac.ke',     'Zetech University is a leading private university in Kenya offering quality education in science, technology and innovation.'),
(3,  'Safaricom PLC',                        'Telecommunications',   'https://safaricom.co.ke',  'Safaricom is Kenya''s leading telecommunications company, driving digital transformation across Africa.'),
(4,  'Zeraki',                               'EdTech',               'https://zeraki.app',       'Zeraki builds technology to transform learning outcomes across African schools.'),
(5,  'United Nations Environment Programme', 'International Org',    'https://unep.org',         'UNEP is the leading global authority on the environment, championing sustainability globally.'),
(6,  'Twiga Foods',                          'AgriTech',             'https://twiga.com',        'Twiga Foods is transforming African food supply chains using technology.'),
(7,  'NCBA Bank',                            'Banking & Finance',    'https://ncbagroup.com',    'NCBA is one of Africa''s largest banking groups serving retail, corporate and digital customers.'),
(8,  'Geminia Insurance',                    'Insurance',            'https://geminia.co.ke',    'Geminia Insurance provides comprehensive insurance products across Kenya.'),
(9,  'Turing',                               'Technology',           'https://turing.com',       'Turing is a deep-jobs platform that uses AI to hire and manage the world''s best software developers.'),
(10, 'Triccare',                             'Healthcare & ICT',     'https://triccare.com',     'Triccare delivers digital health and ICT solutions that improve service delivery in Africa.'),
(11, 'AA Growers',                           'Agriculture',          'https://aagrowers.co.ke',  'AA Growers is one of the leading flower exporters in East Africa, based in Timau, Kenya.');

-- ============================================================
-- SEED: Seeker Profiles
-- ============================================================
INSERT INTO job_seeker_profiles (user_id, full_name, headline, skills, location, bio) VALUES
(12, 'Alice Wanjiku',  'Full-Stack Developer',       'PHP, Laravel, React, MySQL, Docker',    'Nairobi', 'Passionate software developer with 3 years of experience building scalable web applications.'),
(13, 'Brian Otieno',   'Data Analyst & BI Engineer', 'Python, SQL, Power BI, Tableau, Excel', 'Nairobi', 'Data analyst with a knack for turning raw data into actionable business insights.'),
(14, 'Cynthia Auma',   'UX/UI Designer',             'Figma, Adobe XD, Sketch, HTML, CSS',   'Mombasa', 'Creative designer focused on crafting intuitive digital experiences that users love.');

-- ============================================================
-- SEED: Jobs
-- ============================================================
INSERT INTO jobs (employer_id, title, category_id, location, job_type, salary_min, salary_max, description, featured, status, deadline, created_at) VALUES
(2, 'Tutorial Fellow - Information Technology',
 9, 'Nairobi', 'Full Time', 80000, 120000,
 'The purpose of this position is to carry out teaching roles, student mentoring, research and other duties in liaison with the university''s overall goal.\n\nDuties & Responsibilities:\n- Teach in areas allocated by the Head of Department.\n- Design, develop, plan and deliver programmes of study at various levels.\n- Deliver high-quality lectures and practicals.\n- Review course content and materials on a regular basis.\n- Carry out research and write research grant proposals.\n- Provide mentorship and career guidance to students.\n\nQualifications:\n- Bachelor''s and Master''s degree in Computer Science or Information Technology.\n- At least 3 years of post-qualification work experience.\n- Demonstrated potential for university teaching and research.',
 1, 'open', '2025-05-30', DATE_SUB(NOW(), INTERVAL 5 HOUR)),

(2, 'Webmaster - Computer Science',
 1, 'Nairobi', 'Full Time', 70000, 100000,
 'We are looking for an experienced Webmaster to maintain and manage the university website infrastructure.\n\nResponsibilities:\n- Maintain and update university web portals.\n- Ensure website security, uptime, and performance.\n- Collaborate with departments to publish content.\n\nRequirements:\n- Degree in Computer Science or related field.\n- Experience with CMS platforms (WordPress, Drupal).\n- Knowledge of HTML, CSS, JavaScript, and PHP.\n- Minimum 2 years experience in web management.',
 1, 'open', '2025-05-28', DATE_SUB(NOW(), INTERVAL 8 HOUR)),

(3, 'Support Engineer - Financial Services IT',
 1, 'Nairobi', 'Full Time', 150000, 220000,
 'Safaricom is looking for a Support Engineer to join our Financial Services IT team.\n\nKey Responsibilities:\n- Provide 2nd and 3rd level technical support for financial platforms including M-PESA.\n- Diagnose and resolve complex technical issues.\n- Monitor system health and respond to alerts.\n- Produce incident reports and root cause analyses.\n\nRequirements:\n- Bachelor''s degree in Computer Science, IT or related field.\n- 3+ years experience in IT support or systems administration.\n- Strong knowledge of Linux, databases (Oracle/MySQL), and networking.',
 1, 'open', '2025-06-15', DATE_SUB(NOW(), INTERVAL 7 DAY)),

(4, 'Software Engineering Intern',
 1, 'Nairobi', 'Internship', 20000, 35000,
 'Join Zeraki as a Software Engineering Intern and help us build technology that transforms learning outcomes across Africa.\n\nWhat you''ll do:\n- Work closely with senior engineers on feature development.\n- Write clean, maintainable code in PHP, JavaScript or Python.\n- Participate in code reviews and agile ceremonies.\n\nRequirements:\n- Currently pursuing a degree in Computer Science or Software Engineering.\n- Solid understanding of OOP principles.\n- Familiarity with version control (Git).',
 1, 'open', '2025-04-30', DATE_SUB(NOW(), INTERVAL 23 HOUR)),

(5, 'Spatial Data Application Developer',
 1, 'Nairobi', 'Full Time', 200000, 300000,
 'UNEP is seeking a Spatial Data Application Developer to support environmental data management and analysis.\n\nResponsibilities:\n- Design and develop geospatial applications and APIs.\n- Integrate satellite and remote sensing data into visualization platforms.\n- Maintain PostGIS databases and spatial data pipelines.\n\nRequirements:\n- Advanced degree in Computer Science, GIS or Environmental Informatics.\n- 4+ years experience in GIS software development.\n- Proficiency in Python, JavaScript, PostGIS, and QGIS/ArcGIS.',
 1, 'open', '2025-06-01', DATE_SUB(NOW(), INTERVAL 7 DAY)),

(6, 'DevOps Engineer',
 1, 'Nairobi', 'Full Time', 180000, 260000,
 'Twiga Foods is hiring a DevOps Engineer to strengthen our cloud infrastructure and delivery pipelines.\n\nResponsibilities:\n- Design, implement and manage CI/CD pipelines.\n- Manage Kubernetes clusters and containerised workloads.\n- Maintain AWS infrastructure using Terraform.\n\nRequirements:\n- 3+ years of hands-on DevOps/SRE experience.\n- Strong skills in Docker, Kubernetes, Terraform, and AWS.\n- Excellent scripting skills in Bash or Python.',
 0, 'open', '2025-05-20', DATE_SUB(NOW(), INTERVAL 2 DAY)),

(7, 'Mobile Developer (Flutter)',
 1, 'Nairobi', 'Contract', 120000, 180000,
 'NCBA Bank is looking for a Flutter Developer to build and enhance our mobile banking applications.\n\nResponsibilities:\n- Develop cross-platform mobile apps using Flutter/Dart.\n- Integrate RESTful APIs and payment gateways.\n- Ensure code quality through testing and reviews.\n\nRequirements:\n- 2+ years of Flutter/Dart development experience.\n- Experience with state management (BLoC, Provider, Riverpod).\n- Published apps on Google Play or App Store are a plus.',
 0, 'open', '2025-05-10', DATE_SUB(NOW(), INTERVAL 3 DAY)),

(8, 'Information Security Analyst',
 1, 'Nairobi', 'Full Time', 140000, 200000,
 'Geminia Insurance seeks an Information Security Analyst to protect our digital assets.\n\nResponsibilities:\n- Monitor networks and systems for security threats.\n- Conduct vulnerability assessments and penetration tests.\n- Develop and enforce security policies.\n\nRequirements:\n- Bachelor''s in Computer Science or Cybersecurity.\n- 2+ years in information security roles.\n- Certifications: CISSP, CEH, or CompTIA Security+ preferred.',
 1, 'open', '2025-06-10', DATE_SUB(NOW(), INTERVAL 7 DAY)),

(9, 'LLM Trainer - Agent Function Call',
 1, 'Nairobi/Remote', 'Temporary', 60000, 90000,
 'Turing is looking for engineers to help train Large Language Models by evaluating agent function-calling capabilities.\n\nYour tasks:\n- Review and rate LLM responses for correctness and safety.\n- Write code solutions and provide feedback on model outputs.\n- Design test cases for agentic workflows.\n\nRequirements:\n- Strong programming skills in Python, JavaScript, or TypeScript.\n- Understanding of REST APIs and JSON schemas.\n- Available for at least 20 hours per week.',
 1, 'open', '2025-04-25', DATE_SUB(NOW(), INTERVAL 8 HOUR)),

(10, 'Digital Systems Analyst',
  1, 'Nairobi', 'Full Time', 110000, 160000,
  'Triccare is recruiting a Digital Systems Analyst to support digital health platforms.\n\nResponsibilities:\n- Analyse business requirements and translate into technical specifications.\n- Configure and support health information systems.\n- Coordinate with developers and stakeholders on system enhancements.\n\nRequirements:\n- Degree in IT, Health Informatics or related field.\n- 2+ years in systems analysis or IT project support.\n- Experience with DHIS2, OpenMRS, or similar platforms is an advantage.',
  1, 'open', '2025-05-15', DATE_SUB(NOW(), INTERVAL 14 DAY)),

(11, 'Junior IT Officer',
  1, 'Timau', 'Full Time', 60000, 85000,
  'AA Growers seeks a Junior IT Officer to support ICT operations across our production facilities.\n\nResponsibilities:\n- Provide first-line technical support to staff.\n- Maintain hardware, software, and networking infrastructure.\n- Support ERP systems and farm management software.\n\nRequirements:\n- Diploma or Degree in Information Technology.\n- 1+ years IT support experience.\n- Knowledge of Windows Server, Active Directory, and networking basics.',
  1, 'open', '2025-05-05', DATE_SUB(NOW(), INTERVAL 14 DAY)),

(3, 'Data Engineer - Big Data Platform',
 2, 'Nairobi', 'Full Time', 160000, 240000,
 'Safaricom is expanding its Big Data Platform team and needs an experienced Data Engineer.\n\nResponsibilities:\n- Build and maintain data pipelines using Apache Spark and Kafka.\n- Design and optimise data warehouses on AWS Redshift.\n- Collaborate with analytics teams to deliver data products.\n\nRequirements:\n- 3+ years of data engineering experience.\n- Proficiency in Python, SQL, and Spark.\n- Experience with cloud data platforms (AWS/GCP/Azure).',
 0, 'open', '2025-06-20', DATE_SUB(NOW(), INTERVAL 1 DAY)),

(4, 'Product Designer (UX/UI)',
 3, 'Nairobi', 'Full Time', 100000, 150000,
 'Zeraki is looking for a Product Designer to own end-to-end design for our school management platform.\n\nResponsibilities:\n- Conduct user research and usability testing.\n- Create wireframes, prototypes, and high-fidelity designs in Figma.\n- Define and evolve the design system.\n\nRequirements:\n- 3+ years of product or UX design experience.\n- Expert in Figma and design systems.\n- Portfolio demonstrating complex product design.',
 0, 'open', '2025-05-25', DATE_SUB(NOW(), INTERVAL 4 DAY)),

(6, 'Digital Marketing Manager',
 4, 'Nairobi', 'Full Time', 120000, 180000,
 'Twiga Foods is seeking a Digital Marketing Manager to drive growth across digital channels.\n\nResponsibilities:\n- Own digital marketing strategy across SEO, SEM, social, and email.\n- Manage and optimise paid campaigns (Google Ads, Meta).\n- Track KPIs and produce ROI reports.\n\nRequirements:\n- 4+ years in digital marketing, 2 years in a management role.\n- Google Ads and Meta certified.\n- Strong analytical skills; proficient in Google Analytics.',
 0, 'open', '2025-05-30', DATE_SUB(NOW(), INTERVAL 2 DAY)),

(7, 'Financial Analyst - Retail Banking',
 6, 'Nairobi', 'Full Time', 130000, 190000,
 'NCBA Bank requires a Financial Analyst to support the Retail Banking division.\n\nResponsibilities:\n- Prepare monthly financial reports and variance analyses.\n- Build and maintain financial models for product profitability.\n- Support budget preparation and forecasting cycles.\n\nRequirements:\n- Bachelor''s in Finance, Economics or Accounting.\n- 3+ years of financial analysis experience in banking.\n- Advanced Excel and PowerBI skills.',
 0, 'open', '2025-06-05', DATE_SUB(NOW(), INTERVAL 5 DAY)),

(2, 'Human Resources Officer',
 7, 'Nairobi', 'Full Time', 70000, 100000,
 'Zetech University is hiring an HR Officer to support people operations across the institution.\n\nResponsibilities:\n- Coordinate recruitment, onboarding, and offboarding processes.\n- Maintain employee records and HR information systems.\n- Support performance management cycles.\n\nRequirements:\n- Bachelor''s in HR Management or Business Administration.\n- 2+ years HR experience.\n- Knowledge of Kenyan Labour Laws.',
 0, 'open', '2025-05-20', DATE_SUB(NOW(), INTERVAL 6 DAY));

-- ============================================================
-- SEED: Sample Applications
-- ============================================================
INSERT INTO applications (job_id, seeker_id, cover_letter, status, applied_at) VALUES
(4, 12, 'I am a final-year CS student with solid experience in PHP and React. I am eager to contribute to Zeraki''s mission of transforming education through technology.', 'Shortlisted', DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(7, 12, 'I have built two production Flutter apps and am comfortable with BLoC state management. I would love to bring my mobile skills to NCBA.', 'Pending', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 13, 'With my data analysis background and passion for education, I believe I can contribute meaningfully to Zetech''s academic and research goals.', 'Reviewed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(13, 14, 'I am a UX designer with a deep understanding of African user contexts. My portfolio demonstrates complex product design for mobile-first markets.', 'Pending', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- SEED: Saved Jobs
-- ============================================================
INSERT INTO saved_jobs (seeker_id, job_id, saved_at) VALUES
(12, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(12, 3, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(13, 5, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(14, 13, DATE_SUB(NOW(), INTERVAL 1 DAY));

ALTER TABLE employer_profiles ADD COLUMN location VARCHAR(100) AFTER industry;
UPDATE employer_profiles
SET location = CASE company_name
    WHEN 'Zetech University' THEN 'Nairobi'
    WHEN 'Safaricom PLC' THEN 'Nairobi'
    WHEN 'Zeraki' THEN 'Nairobi'
    WHEN 'United Nations Environment Programme' THEN 'Nairobi'
    WHEN 'Twiga Foods' THEN 'Nairobi'
    WHEN 'NCBA Bank' THEN 'Nairobi'
    WHEN 'Geminia Insurance' THEN 'Nairobi'
    WHEN 'Turing' THEN 'Remote'
    WHEN 'Triccare' THEN 'Nairobi'
    WHEN 'AA Growers' THEN 'Timau'
    ELSE location
END;

-- Career/Experience Level
ALTER TABLE jobs ADD COLUMN IF NOT EXISTS experience_level 
    ENUM('Entry Level','Mid Level','Senior','Lead','Manager','Executive','Not specified') 
    DEFAULT 'Not specified' 
    AFTER job_type;
 
-- Required Qualification
ALTER TABLE jobs ADD COLUMN IF NOT EXISTS required_qualification 
    ENUM('High School','Diploma','Bachelor Degree','Master Degree','PhD','Certification','Not specified') 
    DEFAULT 'Not specified'
    AFTER experience_level;
 
-- Years of Experience Required
ALTER TABLE jobs ADD COLUMN IF NOT EXISTS years_experience_min INT DEFAULT 0 AFTER required_qualification;
ALTER TABLE jobs ADD COLUMN IF NOT EXISTS years_experience_max INT DEFAULT 0 AFTER years_experience_min;
 
-- Salary Currency (for international support)
ALTER TABLE jobs ADD COLUMN IF NOT EXISTS salary_currency VARCHAR(3) DEFAULT 'KES' AFTER salary_max;
 
-- ============================================================
-- UPDATE INDEXES
-- ============================================================
 
ALTER TABLE jobs ADD INDEX IF NOT EXISTS idx_experience_level (experience_level);
ALTER TABLE jobs ADD INDEX IF NOT EXISTS idx_required_qualification (required_qualification);
