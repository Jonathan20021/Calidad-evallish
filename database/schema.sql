-- Evallish BPO Quality Control System - Database Schema

-- Corporate clients table
CREATE TABLE IF NOT EXISTS corporate_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    industry VARCHAR(120),
    contact_name VARCHAR(120),
    contact_email VARCHAR(120),
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'qa', 'agent', 'client') NOT NULL DEFAULT 'agent',
    client_id INT NULL,
    active TINYINT(1) DEFAULT 1,
    source ENUM('quality', 'ponche') NOT NULL DEFAULT 'quality',
    external_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES corporate_clients(id) ON DELETE SET NULL,
    UNIQUE KEY uidx_users_source_external (source, external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QA permissions (configurable from UI)
CREATE TABLE IF NOT EXISTS qa_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    can_view_users TINYINT(1) DEFAULT 0,
    can_create_users TINYINT(1) DEFAULT 0,
    can_view_clients TINYINT(1) DEFAULT 0,
    can_manage_clients TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User permissions (individual permissions for ponche users)
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    can_view_users TINYINT(1) DEFAULT 0,
    can_create_users TINYINT(1) DEFAULT 0,
    can_view_clients TINYINT(1) DEFAULT 0,
    can_manage_clients TINYINT(1) DEFAULT 0,
    can_view_campaigns TINYINT(1) DEFAULT 0,
    can_manage_campaigns TINYINT(1) DEFAULT 0,
    can_view_evaluations TINYINT(1) DEFAULT 0,
    can_create_evaluations TINYINT(1) DEFAULT 0,
    can_view_reports TINYINT(1) DEFAULT 0,
    can_manage_settings TINYINT(1) DEFAULT 0,
    can_view_training TINYINT(1) DEFAULT 0,
    can_manage_training TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uidx_user_permissions (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaigns table
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form templates (evaluation forms per campaign)
CREATE TABLE IF NOT EXISTS form_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form fields (individual questions/metrics in a form)
CREATE TABLE IF NOT EXISTS form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    label VARCHAR(200) NOT NULL,
    field_type ENUM('score', 'text', 'yes_no', 'select') NOT NULL,
    options TEXT COMMENT 'JSON array for select options',
    max_score INT DEFAULT 10,
    weight DECIMAL(5,2) DEFAULT 1.00 COMMENT 'Weight for scoring calculation',
    field_order INT DEFAULT 0,
    required TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES form_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Corporate clients campaign access
CREATE TABLE IF NOT EXISTS client_campaigns (
    client_id INT NOT NULL,
    campaign_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (client_id, campaign_id),
    FOREIGN KEY (client_id) REFERENCES corporate_clients(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Client portal settings (what the CEO decides to show)
CREATE TABLE IF NOT EXISTS client_portal_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL UNIQUE,
    show_calls TINYINT(1) DEFAULT 1,
    show_evaluations TINYINT(1) DEFAULT 1,
    show_ai_summary TINYINT(1) DEFAULT 0,
    show_recordings TINYINT(1) DEFAULT 0,
    show_agent_scores TINYINT(1) DEFAULT 1,
    metrics_json TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES corporate_clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calls (call recordings and metadata)
CREATE TABLE IF NOT EXISTS calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    project_id INT NULL,
    campaign_id INT NOT NULL,
    call_type VARCHAR(80) NULL,
    call_datetime DATETIME NOT NULL,
    duration_seconds INT COMMENT 'Duration in seconds',
    customer_phone VARCHAR(30),
    lead VARCHAR(200) NULL,
    notes TEXT,
    recording_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES users(id),
    FOREIGN KEY (project_id) REFERENCES corporate_clients(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluations (completed quality assessments)
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    call_id INT NULL,
    agent_id INT NOT NULL,
    qa_id INT NOT NULL,
    campaign_id INT NOT NULL,
    form_template_id INT NOT NULL,
    call_date DATE,
    call_duration INT COMMENT 'Duration in seconds',
    total_score DECIMAL(5,2),
    max_possible_score DECIMAL(5,2),
    percentage DECIMAL(5,2),
    general_comments TEXT,
    action_type ENUM('feedback', 'call_evaluation') NULL,
    improvement_areas TEXT NULL,
    improvement_plan TEXT NULL,
    tasks_commitments TEXT NULL,
    feedback_confirmed TINYINT(1) DEFAULT 0,
    feedback_confirmed_at DATETIME NULL,
    feedback_evidence_path VARCHAR(255) NULL,
    feedback_evidence_name VARCHAR(255) NULL,
    feedback_evidence_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id),
    FOREIGN KEY (qa_id) REFERENCES users(id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
    FOREIGN KEY (form_template_id) REFERENCES form_templates(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluation answers (individual field responses)
CREATE TABLE IF NOT EXISTS evaluation_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    field_id INT NOT NULL,
    score_given DECIMAL(5,2),
    text_answer TEXT,
    comment TEXT,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES form_fields(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Evaluation feedback history (auditable feedback updates)
CREATE TABLE IF NOT EXISTS evaluation_feedback_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    qa_id INT NOT NULL,
    general_comments TEXT,
    action_type ENUM('feedback', 'call_evaluation') NULL,
    improvement_areas TEXT NULL,
    improvement_plan TEXT NULL,
    tasks_commitments TEXT NULL,
    feedback_confirmed TINYINT(1) DEFAULT 0,
    feedback_confirmed_at DATETIME NULL,
    feedback_evidence_path VARCHAR(255) NULL,
    feedback_evidence_name VARCHAR(255) NULL,
    feedback_evidence_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (qa_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Call AI analytics (AI quality insights per call)
CREATE TABLE IF NOT EXISTS call_ai_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    call_id INT NOT NULL,
    model VARCHAR(100) NOT NULL,
    score DECIMAL(5,2) NULL,
    summary TEXT,
    metrics_json LONGTEXT,
    raw_response_json LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_call_model (call_id, model),
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI evaluation criteria (per project/campaign/call type)
CREATE TABLE IF NOT EXISTS ai_evaluation_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL,
    campaign_id INT NULL,
    call_type VARCHAR(80) NULL,
    criteria_text TEXT NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES corporate_clients(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training scripts (AI roleplay sources)
CREATE TABLE IF NOT EXISTS training_scripts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    script_text TEXT NOT NULL,
    scenario_text TEXT NULL,
    persona_json TEXT NULL,
    source_type ENUM('best_call', 'upload', 'manual') NOT NULL DEFAULT 'manual',
    call_id INT NULL,
    campaign_id INT NULL,
    created_by INT NOT NULL,
    file_path VARCHAR(255) NULL,
    original_filename VARCHAR(255) NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training rubrics (per campaign)
CREATE TABLE IF NOT EXISTS training_rubrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NULL,
    title VARCHAR(150) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training rubric items
CREATE TABLE IF NOT EXISTS training_rubric_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rubric_id INT NOT NULL,
    label VARCHAR(200) NOT NULL,
    weight DECIMAL(5,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rubric_id) REFERENCES training_rubrics(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roleplay sessions
CREATE TABLE IF NOT EXISTS training_roleplays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    script_id INT NULL,
    agent_id INT NOT NULL,
    qa_id INT NULL,
    campaign_id INT NULL,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    score DECIMAL(5,2) NULL,
    ai_summary TEXT NULL,
    objectives_text TEXT NULL,
    tone_text TEXT NULL,
    obstacles_text TEXT NULL,
    rubric_id INT NULL,
    ai_actions_json TEXT NULL,
    qa_plan_text TEXT NULL,
    started_at DATETIME NULL,
    ended_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (script_id) REFERENCES training_scripts(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (qa_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (rubric_id) REFERENCES training_rubrics(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roleplay messages
CREATE TABLE IF NOT EXISTS training_roleplay_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roleplay_id INT NOT NULL,
    sender ENUM('agent', 'ai', 'qa') NOT NULL,
    message_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roleplay_id) REFERENCES training_roleplays(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roleplay feedback (per agent turn)
CREATE TABLE IF NOT EXISTS training_roleplay_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roleplay_id INT NOT NULL,
    message_id INT NOT NULL,
    score DECIMAL(5,2) NULL,
    feedback TEXT NULL,
    checklist_json TEXT NULL,
    qa_score DECIMAL(5,2) NULL,
    qa_feedback TEXT NULL,
    qa_checklist_json TEXT NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roleplay_id) REFERENCES training_roleplays(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES training_roleplay_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QA coach notes (private tips to agent)
CREATE TABLE IF NOT EXISTS training_roleplay_coach_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roleplay_id INT NOT NULL,
    qa_id INT NOT NULL,
    note_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roleplay_id) REFERENCES training_roleplays(id) ON DELETE CASCADE,
    FOREIGN KEY (qa_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training notifications (queue)
CREATE TABLE IF NOT EXISTS training_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    agent_id INT NULL,
    qa_id INT NULL,
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    payload_json TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (qa_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training exams
CREATE TABLE IF NOT EXISTS training_exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    qa_id INT NOT NULL,
    campaign_id INT NULL,
    title VARCHAR(150) NOT NULL,
    status ENUM('draft', 'assigned', 'in_progress', 'completed') NOT NULL DEFAULT 'assigned',
    public_token VARCHAR(80) NULL,
    public_enabled TINYINT(1) DEFAULT 0,
    total_score DECIMAL(6,2) NULL,
    max_score DECIMAL(6,2) NULL,
    percentage DECIMAL(6,2) NULL,
    ai_summary TEXT NULL,
    prompt_context TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    UNIQUE KEY uniq_training_exam_token (public_token),
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (qa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training exam questions
CREATE TABLE IF NOT EXISTS training_exam_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('mcq', 'open') NOT NULL DEFAULT 'open',
    options_json TEXT NULL,
    correct_answer TEXT NULL,
    weight DECIMAL(5,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES training_exams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training exam answers
CREATE TABLE IF NOT EXISTS training_exam_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NULL,
    score DECIMAL(5,2) NULL,
    feedback TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES training_exam_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QA retraining plans (one per campaign/agent while active)
CREATE TABLE IF NOT EXISTS qa_retrainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    agent_id INT NOT NULL,
    evaluation_id INT NULL,
    created_by INT NOT NULL,
    supervisor_id INT NULL,
    status ENUM('assigned', 'in_progress', 'approved', 'failed', 'active_in_production') NOT NULL DEFAULT 'assigned',
    progress_percent DECIMAL(5,2) DEFAULT 0.00,
    due_date DATE NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    activation_at DATETIME NULL,
    reminder_sent_at DATETIME NULL,
    reminder_count INT NOT NULL DEFAULT 0,
    reinforcement_required TINYINT(1) NOT NULL DEFAULT 0,
    fail_count INT NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QA retraining modules (built from detected errors)
CREATE TABLE IF NOT EXISTS qa_retraining_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    retraining_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    lesson_text TEXT NULL,
    detected_error VARCHAR(255) NULL,
    sequence_order INT NOT NULL DEFAULT 1,
    pass_score DECIMAL(5,2) NOT NULL DEFAULT 80.00,
    quiz_question TEXT NULL,
    quiz_type ENUM('text', 'mcq') NOT NULL DEFAULT 'text',
    options_json TEXT NULL,
    correct_answer TEXT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (retraining_id) REFERENCES qa_retrainings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QA retraining progress per module and agent
CREATE TABLE IF NOT EXISTS qa_retraining_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    retraining_id INT NOT NULL,
    agent_id INT NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    score DECIMAL(5,2) NULL,
    answer_text TEXT NULL,
    attempts INT NOT NULL DEFAULT 0,
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_retraining_module_agent (module_id, agent_id),
    FOREIGN KEY (module_id) REFERENCES qa_retraining_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (retraining_id) REFERENCES qa_retrainings(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password_hash, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert sample campaigns
INSERT INTO campaigns (name, description) VALUES 
('Ventas Inbound', 'Campaña de ventas entrantes'),
('Soporte Técnico', 'Campaña de soporte al cliente'),
('Retención', 'Campaña de retención de clientes');
