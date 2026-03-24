<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\CronJobController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\LeaveRuleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Employee\PunchController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use App\Http\Controllers\Manager\TaskController as ManagerTaskController;
use App\Http\Controllers\Manager\MyTaskController;
use App\Http\Controllers\Manager\LeaveRequestController as ManagerLeaveRequestController;
use App\Http\Controllers\Manager\TeamAttendanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HolidayListController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\OptionalHolidayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\EmployeeDocumentController;
use App\Http\Controllers\Admin\OptionalHolidaySettingController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\OfferLetterController;
use App\Http\Controllers\Manager\TeamOptionalHolidayController;
use App\Http\Controllers\Employee\TimesheetController as EmployeeTimesheetController;
use App\Http\Controllers\Employee\AttendanceRegularizationController as EmployeeAttendanceRegularizationController;
use App\Http\Controllers\Manager\TimesheetController as ManagerTimesheetController;
use App\Http\Controllers\Manager\AttendanceRegularizationController as ManagerAttendanceRegularizationController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Session keep-alive ping — touches the session without any DB writes; returns 204
Route::get('session/ping', fn() => response()->noContent())->name('session.ping')->middleware('auth');

// Shared routes (all authenticated roles)
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('profile',      [ProfileController::class,   'show'])  ->name('profile');
    Route::get('profile/edit', [ProfileController::class,   'edit'])  ->name('profile.edit');
    Route::put('profile',      [ProfileController::class,   'update'])->name('profile.update');
    // Change password
    Route::get('password/change',  [ProfileController::class, 'changePasswordForm'])->name('password.change');
    Route::post('password/change', [ProfileController::class, 'changePassword'])    ->name('password.change.post');
    // Leave & Permission Requests
    Route::get('leave-requests',                          [LeaveRequestController::class, 'index'])  ->name('leave-requests.index');
    Route::get('leave-requests/create',                   [LeaveRequestController::class, 'create']) ->name('leave-requests.create');
    Route::post('leave-requests',                         [LeaveRequestController::class, 'store'])  ->name('leave-requests.store');
    Route::get('leave-requests/{leaveRequest}',           [LeaveRequestController::class, 'show'])   ->name('leave-requests.show');
    Route::post('leave-requests/{leaveRequest}/approve',  [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject',   [LeaveRequestController::class, 'reject']) ->name('leave-requests.reject');
    // Attendance history
    Route::get('attendance/history',        [AttendanceController::class, 'index']) ->name('attendance.history');
    Route::get('attendance/export/{format}', [AttendanceController::class, 'export'])->name('attendance.export');
    // Holiday list (view-only for employees & managers)
    Route::get('holidays', [HolidayListController::class, 'index'])->name('holidays.index');
    // Optional Holidays (employee + manager)
    Route::get('optional-holidays',         [OptionalHolidayController::class, 'index'])   ->name('optional-holidays.index');
    Route::post('optional-holidays/select', [OptionalHolidayController::class, 'select'])  ->name('optional-holidays.select');
    Route::post('optional-holidays/deselect',[OptionalHolidayController::class, 'deselect'])->name('optional-holidays.deselect');

    // Notifications (AJAX)
    Route::get('notifications',                         [NotificationController::class, 'index'])       ->name('notifications.index');
    Route::get('notifications/unread-count',            [NotificationController::class, 'unreadCount']) ->name('notifications.unread-count');
    Route::post('notifications/read-all',               [NotificationController::class, 'markAllRead']) ->name('notifications.read-all');
    Route::post('notifications/{notification}/read',    [NotificationController::class, 'markRead'])    ->name('notifications.read');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('settings',  [SettingsController::class, 'index'])->name('settings');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Departments
    Route::resource('departments', DepartmentController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle');

    // Leave Rules (per-department configuration)
    Route::get('leave-rules', [LeaveRuleController::class, 'index'])->name('leave-rules.index');
    Route::get('leave-rules/{leaveRule}/edit', [LeaveRuleController::class, 'edit'])->name('leave-rules.edit');
    Route::put('leave-rules/{leaveRule}', [LeaveRuleController::class, 'update'])->name('leave-rules.update');

    // Employees
    Route::get('employees/check-username', [EmployeeController::class, 'checkUsername'])->name('employees.check-username');
    Route::resource('employees', EmployeeController::class, ['parameters' => ['employees' => 'employee']])
         ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    // Employee Documents (admin-only, private storage)
    Route::prefix('employees/{employee}/documents')->name('employees.documents.')->group(function () {
        Route::get('/',                [EmployeeDocumentController::class, 'index'])   ->name('index');
        Route::post('/',               [EmployeeDocumentController::class, 'store'])   ->name('store');
        Route::get('/{document}/view', [EmployeeDocumentController::class, 'view'])    ->name('view');
        Route::get('/{document}/download', [EmployeeDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}',   [EmployeeDocumentController::class, 'destroy']) ->name('destroy');
    });

    // Holidays
    Route::resource('holidays', HolidayController::class)
         ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Optional Holiday Settings (admin)
    Route::get('optional-holiday-settings',                          [OptionalHolidaySettingController::class, 'index'])  ->name('optional-holiday-settings.index');
    Route::post('optional-holiday-settings',                         [OptionalHolidaySettingController::class, 'store'])  ->name('optional-holiday-settings.store');
    Route::put('optional-holiday-settings/{optionalHolidaySetting}', [OptionalHolidaySettingController::class, 'update']) ->name('optional-holiday-settings.update');
    Route::delete('optional-holiday-settings/{optionalHolidaySetting}',[OptionalHolidaySettingController::class,'destroy'])->name('optional-holiday-settings.destroy');

    // Payroll
    Route::get('payroll',                          [PayrollController::class, 'index'])    ->name('payroll.index');
    Route::get('payroll/generate',                 [PayrollController::class, 'generate']) ->name('payroll.generate');
    Route::post('payroll/store',                   [PayrollController::class, 'store'])    ->name('payroll.store');
    Route::get('payroll/export/csv',               [PayrollController::class, 'exportCsv'])->name('payroll.export.csv');
    Route::get('payroll/print',                    [PayrollController::class, 'print'])    ->name('payroll.print');
    Route::post('payroll/{month}/{year}/lock',     [PayrollController::class, 'lock'])     ->name('payroll.lock')
         ->where(['month' => '[0-9]+', 'year' => '[0-9]+']);
    Route::get('payroll/{id}/edit',                [PayrollController::class, 'edit'])     ->name('payroll.edit')
         ->where('id', '[0-9]+');
    Route::put('payroll/{id}',                     [PayrollController::class, 'update'])   ->name('payroll.update')
         ->where('id', '[0-9]+');

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('attendance',             [ReportController::class, 'attendance'])          ->name('attendance');
        Route::get('attendance/export',      [ReportController::class, 'exportAttendanceCsv']) ->name('attendance.export');
        Route::get('missed-punchout',        [ReportController::class, 'missedPunchout'])      ->name('missed-punchout');
        Route::get('missed-punchout/export', [ReportController::class, 'exportMissedPunchoutCsv'])->name('missed-punchout.export');
        Route::get('regularization',         [ReportController::class, 'regularization'])      ->name('regularization');
        Route::get('regularization/export',  [ReportController::class, 'exportRegularizationCsv'])->name('regularization.export');
        Route::get('payroll',                [ReportController::class, 'payrollReport'])       ->name('payroll');
        Route::get('payroll/export',         [ReportController::class, 'exportPayrollCsv'])    ->name('payroll.export');
        Route::get('analytics',              [ReportController::class, 'analytics'])           ->name('analytics');
    });

    // Offer Letters
    Route::resource('offer-letters', OfferLetterController::class)
         ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
         ->parameters(['offer-letters' => 'offerLetter']);
    Route::get ('offer-letters/{offerLetter}/pdf',          [OfferLetterController::class, 'downloadPdf']) ->name('offer-letters.pdf');
    Route::post('offer-letters/{offerLetter}/send-email',   [OfferLetterController::class, 'sendEmail'])   ->name('offer-letters.send-email');
    Route::post('offer-letters/{offerLetter}/resend-email', [OfferLetterController::class, 'resendEmail']) ->name('offer-letters.resend-email');
    Route::get ('offer-letters/{offerLetter}/email-history',[OfferLetterController::class, 'emailHistory'])->name('offer-letters.email-history');

    // Audit Logs (read-only, admin only)
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Cron Jobs
    Route::get('cron-jobs',              [CronJobController::class, 'index'])->name('cron-jobs.index');
    Route::post('cron-jobs/{cronJob}/run', [CronJobController::class, 'run'])->name('cron-jobs.run');
    Route::get('cron-jobs/log/{log}',    [CronJobController::class, 'showLog'])->name('cron-jobs.log');
});

// Manager routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('dashboard',     [DashboardController::class, 'manager'])->name('dashboard');
    Route::get('calendar-data', [DashboardController::class, 'managerCalendarData'])->name('calendar-data');
    Route::post('punch/in',     [PunchController::class,     'punchIn'])->name('punch.in');
    Route::post('punch/out',    [PunchController::class,     'punchOut'])->name('punch.out');

    // Task Management
    Route::get('tasks',                          [ManagerTaskController::class, 'index'])     ->name('tasks.index');
    Route::get('tasks/create',                   [ManagerTaskController::class, 'create'])    ->name('tasks.create');
    Route::post('tasks',                         [ManagerTaskController::class, 'store'])     ->name('tasks.store');
    Route::get('tasks/{task}',                   [ManagerTaskController::class, 'show'])      ->name('tasks.show');
    Route::get('tasks/{task}/edit',              [ManagerTaskController::class, 'edit'])      ->name('tasks.edit');
    Route::put('tasks/{task}',                   [ManagerTaskController::class, 'update'])    ->name('tasks.update');
    Route::delete('tasks/{task}',                [ManagerTaskController::class, 'destroy'])   ->name('tasks.destroy');
    Route::post('tasks/{task}/comment',          [ManagerTaskController::class, 'addComment'])->name('tasks.comment');

    // My Tasks (tasks assigned TO the manager)
    Route::get('my-tasks',                        [MyTaskController::class, 'index'])       ->name('my-tasks.index');
    Route::get('my-tasks/{task}',                 [MyTaskController::class, 'show'])        ->name('my-tasks.show');
    Route::patch('my-tasks/{task}/status',        [MyTaskController::class, 'updateStatus'])->name('my-tasks.status');
    Route::post('my-tasks/{task}/comment',        [MyTaskController::class, 'addComment'])  ->name('my-tasks.comment');

    // Team Leave & Permission Requests
    Route::get('leave-requests', [ManagerLeaveRequestController::class, 'index'])->name('leave-requests.index');

    // Team Attendance History
    Route::get('team-attendance', [TeamAttendanceController::class, 'index'])->name('team-attendance.index');

    // Team Optional Holidays visibility
    Route::get('team-optional-holidays', [TeamOptionalHolidayController::class, 'index'])->name('team-optional-holidays.index');

    // Team Timesheets
    Route::get('timesheets',                              [ManagerTimesheetController::class, 'index'])     ->name('timesheets.index');
    Route::get('timesheets/{timesheet}',                  [ManagerTimesheetController::class, 'show'])      ->name('timesheets.show');
    Route::post('timesheets/{timesheet}/approve',         [ManagerTimesheetController::class, 'approve'])   ->name('timesheets.approve');
    Route::post('timesheets/{timesheet}/reject',          [ManagerTimesheetController::class, 'reject'])    ->name('timesheets.reject');
    Route::post('timesheets/{timesheet}/comment',         [ManagerTimesheetController::class, 'addComment'])->name('timesheets.comment');

    // My Timesheets
    Route::get('my-timesheets',                                                   [EmployeeTimesheetController::class, 'index'])      ->name('my-timesheets.index');
    Route::get('my-timesheets/{date}',                                            [EmployeeTimesheetController::class, 'show'])       ->name('my-timesheets.show');
    Route::post('my-timesheets/{timesheet}/submit',                               [EmployeeTimesheetController::class, 'submit'])     ->name('my-timesheets.submit');
    Route::post('my-timesheets/{timesheet}/comment',                              [EmployeeTimesheetController::class, 'addComment'])->name('my-timesheets.comment');
    Route::post('my-timesheets/{timesheet}/entries',                              [EmployeeTimesheetController::class, 'storeEntry'])->name('my-timesheets.entries.store');
    Route::put('my-timesheets/{timesheet}/entries/{entry}',                       [EmployeeTimesheetController::class, 'updateEntry'])->name('my-timesheets.entries.update');
    Route::delete('my-timesheets/{timesheet}/entries/{entry}',                    [EmployeeTimesheetController::class, 'deleteEntry'])->name('my-timesheets.entries.delete');

    // My Attendance Regularization
    Route::get('my-regularizations',                                              [EmployeeAttendanceRegularizationController::class, 'index'])      ->name('my-regularizations.index');
    Route::get('my-regularizations/create',                                       [EmployeeAttendanceRegularizationController::class, 'create'])     ->name('my-regularizations.create');
    Route::get('my-regularizations/snapshot',                                     [EmployeeAttendanceRegularizationController::class, 'snapshot'])   ->name('my-regularizations.snapshot');
    Route::post('my-regularizations',                                             [EmployeeAttendanceRegularizationController::class, 'store'])      ->name('my-regularizations.store');
    Route::get('my-regularizations/{regularization}',                             [EmployeeAttendanceRegularizationController::class, 'show'])       ->name('my-regularizations.show');
    Route::put('my-regularizations/{regularization}',                             [EmployeeAttendanceRegularizationController::class, 'update'])     ->name('my-regularizations.update');
    Route::post('my-regularizations/{regularization}/submit',                     [EmployeeAttendanceRegularizationController::class, 'submit'])     ->name('my-regularizations.submit');
    Route::post('my-regularizations/{regularization}/comment',                    [EmployeeAttendanceRegularizationController::class, 'addComment']) ->name('my-regularizations.comment');

    // Team Attendance Regularization
    Route::get('regularizations',                                                 [ManagerAttendanceRegularizationController::class, 'index'])       ->name('regularizations.index');
    Route::get('regularizations/{regularization}',                                [ManagerAttendanceRegularizationController::class, 'show'])        ->name('regularizations.show');
    Route::post('regularizations/{regularization}/approve',                       [ManagerAttendanceRegularizationController::class, 'approve'])     ->name('regularizations.approve');
    Route::post('regularizations/{regularization}/reject',                        [ManagerAttendanceRegularizationController::class, 'reject'])      ->name('regularizations.reject');
    Route::post('regularizations/{regularization}/comment',                       [ManagerAttendanceRegularizationController::class, 'addComment'])  ->name('regularizations.comment');
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('dashboard',      [DashboardController::class, 'employee'])    ->name('dashboard');
    Route::get('calendar-data',  [DashboardController::class, 'calendarData'])->name('calendar-data');
    Route::post('punch/in',      [PunchController::class,     'punchIn'])     ->name('punch.in');
    Route::post('punch/out',     [PunchController::class,     'punchOut'])    ->name('punch.out');

    // My Tasks
    Route::get('tasks',                  [EmployeeTaskController::class, 'index'])       ->name('tasks.index');
    Route::get('tasks/{task}',           [EmployeeTaskController::class, 'show'])        ->name('tasks.show');
    Route::patch('tasks/{task}/status',  [EmployeeTaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('tasks/{task}/comment',  [EmployeeTaskController::class, 'addComment'])  ->name('tasks.comment');

    // Timesheets
    Route::get('timesheets',                                                         [EmployeeTimesheetController::class, 'index'])      ->name('timesheets.index');
    Route::get('timesheets/{date}',                                                  [EmployeeTimesheetController::class, 'show'])       ->name('timesheets.show');
    Route::post('timesheets/{timesheet}/submit',                                     [EmployeeTimesheetController::class, 'submit'])     ->name('timesheets.submit');
    Route::post('timesheets/{timesheet}/comment',                                    [EmployeeTimesheetController::class, 'addComment'])->name('timesheets.comment');
    Route::post('timesheets/{timesheet}/entries',                                    [EmployeeTimesheetController::class, 'storeEntry'])->name('timesheets.entries.store');
    Route::put('timesheets/{timesheet}/entries/{entry}',                             [EmployeeTimesheetController::class, 'updateEntry'])->name('timesheets.entries.update');
    Route::delete('timesheets/{timesheet}/entries/{entry}',                          [EmployeeTimesheetController::class, 'deleteEntry'])->name('timesheets.entries.delete');

    // Attendance Regularization
    Route::get('regularizations',                                                    [EmployeeAttendanceRegularizationController::class, 'index'])      ->name('regularizations.index');
    Route::get('regularizations/create',                                             [EmployeeAttendanceRegularizationController::class, 'create'])     ->name('regularizations.create');
    Route::get('regularizations/snapshot',                                           [EmployeeAttendanceRegularizationController::class, 'snapshot'])   ->name('regularizations.snapshot');
    Route::post('regularizations',                                                   [EmployeeAttendanceRegularizationController::class, 'store'])      ->name('regularizations.store');
    Route::get('regularizations/{regularization}',                                   [EmployeeAttendanceRegularizationController::class, 'show'])       ->name('regularizations.show');
    Route::put('regularizations/{regularization}',                                   [EmployeeAttendanceRegularizationController::class, 'update'])     ->name('regularizations.update');
    Route::post('regularizations/{regularization}/submit',                           [EmployeeAttendanceRegularizationController::class, 'submit'])     ->name('regularizations.submit');
    Route::post('regularizations/{regularization}/comment',                          [EmployeeAttendanceRegularizationController::class, 'addComment']) ->name('regularizations.comment');
});
