<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Manual PSR-4 Autoloader

// Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Router\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CampaignController;
use App\Controllers\EvaluationController;
use App\Controllers\CorporateClientController;
use App\Controllers\ClientPortalController;
use App\Controllers\UserController;
use App\Controllers\TrainingController;
use App\Controllers\AiCriteriaController;

Config::init();

$router = new Router();

// Auth routes
$router->get('/', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Campaigns
$router->get('/campaigns', [CampaignController::class, 'index']);
$router->get('/campaigns/create', [CampaignController::class, 'create']);
$router->post('/campaigns/store', [CampaignController::class, 'store']);
$router->get('/campaigns/edit', [CampaignController::class, 'edit']);
$router->post('/campaigns/update', [CampaignController::class, 'update']);

// Evaluations
$router->get('/evaluations', [EvaluationController::class, 'index']);
$router->get('/evaluations/show', [EvaluationController::class, 'show']);
$router->get('/evaluations/export-pdf', [EvaluationController::class, 'exportPdf']);
$router->get('/evaluations/create', [EvaluationController::class, 'create']);
$router->post('/evaluations/store', [EvaluationController::class, 'store']);

$router->get('/calls', [\App\Controllers\CallController::class, 'index']);
$router->get('/calls/create', [\App\Controllers\CallController::class, 'create']);
$router->post('/calls/store', [\App\Controllers\CallController::class, 'store']);
$router->get('/calls/show', [\App\Controllers\CallController::class, 'show']);
$router->get('/calls/analyze', [\App\Controllers\CallController::class, 'analyze']);
$router->post('/calls/delete', [\App\Controllers\CallController::class, 'destroy']);

// Form Templates
$router->get('/form-templates', [\App\Controllers\FormTemplateController::class, 'index']);
$router->get('/form-templates/create', [\App\Controllers\FormTemplateController::class, 'create']);
$router->post('/form-templates/store', [\App\Controllers\FormTemplateController::class, 'store']);
$router->get('/form-templates/edit', [\App\Controllers\FormTemplateController::class, 'edit']);
$router->post('/form-templates/update', [\App\Controllers\FormTemplateController::class, 'update']);
$router->post('/form-templates/toggle', [\App\Controllers\FormTemplateController::class, 'toggle']);
$router->post('/form-templates/duplicate', [\App\Controllers\FormTemplateController::class, 'duplicate']);

// Reports
$router->get('/reports', [\App\Controllers\ReportController::class, 'index']);
$router->get('/reports/export-pdf', [\App\Controllers\ReportController::class, 'exportPdf']);

// Settings
$router->get('/settings', [\App\Controllers\SettingsController::class, 'index']);
$router->post('/settings/qa-permissions', [\App\Controllers\SettingsController::class, 'updateQaPermissions']);

// AI Criteria (Admin)
$router->get('/ai-criteria', [AiCriteriaController::class, 'index']);
$router->get('/ai-criteria/edit', [AiCriteriaController::class, 'edit']);
$router->post('/ai-criteria/store', [AiCriteriaController::class, 'store']);
$router->post('/ai-criteria/toggle', [AiCriteriaController::class, 'toggle']);

// Users (Admin)
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users/store', [UserController::class, 'store']);
$router->get('/users/edit', [UserController::class, 'edit']);
$router->post('/users/update', [UserController::class, 'update']);
$router->post('/users/toggle', [UserController::class, 'toggle']);

// Agents
$router->get('/agents', [\App\Controllers\AgentController::class, 'index']);
$router->get('/agents/create', [\App\Controllers\AgentController::class, 'create']);
$router->post('/agents/store', [\App\Controllers\AgentController::class, 'store']);
$router->get('/agents/edit', [\App\Controllers\AgentController::class, 'edit']);
$router->post('/agents/update', [\App\Controllers\AgentController::class, 'update']);
$router->post('/agents/toggle', [\App\Controllers\AgentController::class, 'toggle']);

// Corporate Clients (Admin)
$router->get('/clients', [CorporateClientController::class, 'index']);
$router->get('/clients/create', [CorporateClientController::class, 'create']);
$router->post('/clients/store', [CorporateClientController::class, 'store']);
$router->get('/clients/edit', [CorporateClientController::class, 'edit']);
$router->post('/clients/update', [CorporateClientController::class, 'update']);

// Client Portal
$router->get('/client-portal', [ClientPortalController::class, 'index']);

// Training (AI)
$router->get('/training', [TrainingController::class, 'index']);
$router->post('/training/scripts/upload', [TrainingController::class, 'uploadScript']);
$router->post('/training/scripts/from-best-call', [TrainingController::class, 'createScriptFromBestCall']);
$router->get('/training/roleplay/start', [TrainingController::class, 'startRoleplay']);
$router->get('/training/roleplay', [TrainingController::class, 'showRoleplay']);
$router->post('/training/roleplay/message', [TrainingController::class, 'sendRoleplayMessage']);
$router->post('/training/roleplay/end', [TrainingController::class, 'endRoleplay']);
$router->post('/training/roleplay/coach-note', [TrainingController::class, 'addCoachNote']);
$router->post('/training/roleplay/feedback/update', [TrainingController::class, 'updateFeedback']);
$router->post('/training/roleplay/plan/save', [TrainingController::class, 'savePlan']);
$router->post('/training/rubrics/create', [TrainingController::class, 'createRubric']);
$router->post('/training/exams/generate', [TrainingController::class, 'generateExam']);
$router->get('/training/exams/view', [TrainingController::class, 'viewExam']);
$router->get('/training/exams/take', [TrainingController::class, 'takeExam']);
$router->post('/training/exams/submit', [TrainingController::class, 'submitExam']);
$router->post('/training/exams/public/enable', [TrainingController::class, 'enablePublicExam']);
$router->post('/training/exams/public/disable', [TrainingController::class, 'disablePublicExam']);
$router->get('/training/exams/public', [TrainingController::class, 'publicExam']);
$router->post('/training/exams/public/submit', [TrainingController::class, 'submitPublicExam']);

$router->resolve();
