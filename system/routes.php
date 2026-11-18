<?php
declare(strict_types=1);

/** @var Router $router */

/* ---------- Публичная часть ---------- */
$router->get('/', [PublicController::class, 'home']);
$router->get('/about', [PublicController::class, 'about']);
$router->get('/rules', [PublicController::class, 'rules']);
$router->get('/news', [PublicController::class, 'newsIndex']);
$router->get('/news/{slug}', [PublicController::class, 'newsShow']);
$router->get('/apply', [PublicController::class, 'applyForm']);
$router->post('/apply', [PublicController::class, 'apply']);

/* ---------- Авторизация ---------- */
/* ---------- Язык ---------- */
$router->get('/lang/{code}', [LanguageController::class, 'switch']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

/* ---------- Кабинет ---------- */
$router->get('/app', [DashboardController::class, 'index']);
$router->get('/app/profile', [ProfileController::class, 'index']);
$router->post('/app/profile', [ProfileController::class, 'updatePassword']);
$router->get('/app/logs', [LogController::class, 'index']);

/* ---------- Заявки ---------- */
$router->get('/app/applications', [ApplicationController::class, 'index']);
$router->get('/app/applications/{id}', [ApplicationController::class, 'show']);
$router->post('/app/applications/{id}/status', [ApplicationController::class, 'updateStatus']);
$router->post('/app/applications/{id}/account', [ApplicationController::class, 'createAccount']);

/* ---------- Пользователи ---------- */
$router->get('/app/users', [UserController::class, 'index']);
$router->get('/app/users/create', [UserController::class, 'create']);
$router->post('/app/users', [UserController::class, 'store']);
$router->get('/app/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/app/users/{id}', [UserController::class, 'update']);
$router->post('/app/users/{id}/reset', [UserController::class, 'resetPassword']);

/* ---------- Потоки и группы ---------- */
$router->get('/app/streams', [StreamController::class, 'index']);
$router->get('/app/streams/create', [StreamController::class, 'create']);
$router->post('/app/streams', [StreamController::class, 'store']);
$router->get('/app/streams/{id}', [StreamController::class, 'show']);
$router->post('/app/streams/{id}', [StreamController::class, 'update']);
$router->post('/app/streams/{id}/groups', [StreamController::class, 'addGroup']);
$router->post('/app/groups/{id}/teacher', [StreamController::class, 'assignTeacher']);
$router->post('/app/groups/{id}/students', [StreamController::class, 'addStudent']);
$router->post('/app/groups/{id}/students/{studentId}/remove', [StreamController::class, 'removeStudent']);
$router->post('/app/groups/{id}/progress', [StreamController::class, 'setProgress']);

/* ---------- Группы (отдельный раздел) ---------- */
$router->get('/app/groups', [GroupController::class, 'index']);
$router->get('/app/groups/create', [GroupController::class, 'create']);
$router->post('/app/groups', [GroupController::class, 'store']);
$router->get('/app/groups/{id}', [GroupController::class, 'show']);
$router->post('/app/groups/{id}', [GroupController::class, 'update']);
$router->post('/app/groups/{id}/delete', [GroupController::class, 'destroy']);
$router->post('/app/groups/{id}/materials', [GroupController::class, 'attachMaterial']);
$router->post('/app/groups/{id}/materials/{materialId}/delete', [GroupController::class, 'detachMaterial']);

/* ---------- Пособия ---------- */
/* ---------- Презентации (Google Диск) ---------- */
$router->get('/app/resources', [ResourceController::class, 'index']);
$router->get('/app/resources/create', [ResourceController::class, 'create']);
$router->post('/app/resources', [ResourceController::class, 'store']);
$router->get('/app/resources/{id}/edit', [ResourceController::class, 'edit']);
$router->post('/app/resources/{id}', [ResourceController::class, 'update']);
$router->post('/app/resources/{id}/delete', [ResourceController::class, 'destroy']);

/* ---------- Документы правил ---------- */
$router->get('/app/documents', [DocumentController::class, 'index']);
$router->get('/app/documents/create', [DocumentController::class, 'create']);
$router->post('/app/documents', [DocumentController::class, 'store']);
$router->get('/app/documents/{id}/edit', [DocumentController::class, 'edit']);
$router->post('/app/documents/{id}', [DocumentController::class, 'update']);
$router->post('/app/documents/{id}/delete', [DocumentController::class, 'destroy']);

/* ---------- Сертификаты ---------- */
$router->post('/app/certificate/{id}/issue', [CertificateController::class, 'issue']);
$router->get('/app/certificate/{id}', [CertificateController::class, 'show']);

$router->get('/app/materials', [MaterialController::class, 'index']);
$router->get('/app/materials/create', [MaterialController::class, 'create']);
$router->post('/app/materials', [MaterialController::class, 'store']);
$router->get('/app/materials/{id}/edit', [MaterialController::class, 'edit']);
$router->post('/app/materials/{id}', [MaterialController::class, 'update']);
$router->post('/app/materials/{id}/delete', [MaterialController::class, 'destroy']);

/* ---------- Занятия и отчёты ---------- */
$router->get('/app/schedule', [LessonController::class, 'index']);
$router->get('/app/lessons/{id}', [LessonController::class, 'show']);
$router->post('/app/lessons/{id}', [LessonController::class, 'update']);
$router->post('/app/lessons/{id}/report', [ReportController::class, 'save']);

$router->get('/app/reports', [ReportController::class, 'index']);
$router->get('/app/reports/{id}', [ReportController::class, 'show']);
$router->post('/app/reports/{id}/review', [ReportController::class, 'review']);

/* ---------- Домашние задания ---------- */
$router->get('/app/homework', [HomeworkController::class, 'index']);
$router->get('/app/homework/create', [HomeworkController::class, 'create']);
$router->post('/app/homework', [HomeworkController::class, 'store']);
$router->get('/app/homework/{id}', [HomeworkController::class, 'show']);
$router->post('/app/homework/{id}/submit', [HomeworkController::class, 'submit']);
$router->post('/app/homework/{id}/check', [HomeworkController::class, 'check']);

/* ---------- SMM ---------- */
$router->get('/app/smm', [SmmController::class, 'tasks']);
$router->post('/app/smm/tasks', [SmmController::class, 'storeTask']);
$router->post('/app/smm/tasks/{id}', [SmmController::class, 'updateTask']);
$router->get('/app/smm/stats', [SmmController::class, 'stats']);
$router->post('/app/smm/stats', [SmmController::class, 'saveStats']);

/* ---------- Новости (управление) ---------- */
$router->get('/app/news', [NewsAdminController::class, 'index']);
$router->get('/app/news/create', [NewsAdminController::class, 'create']);
$router->post('/app/news', [NewsAdminController::class, 'store']);
$router->get('/app/news/{id}/edit', [NewsAdminController::class, 'edit']);
$router->post('/app/news/{id}', [NewsAdminController::class, 'update']);
$router->post('/app/news/{id}/delete', [NewsAdminController::class, 'destroy']);
