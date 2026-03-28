<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\MotTestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\PublicQuoteReviewController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\VehicleHealthCheckController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\EcuJobController;
use App\Http\Controllers\WhatsAppSupportController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\SitemapController;

// Sitemap & SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// About Us Page
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/our-services', [LandingController::class, 'servicesPage'])->name('public.services');
Route::get('/contact', [LandingController::class, 'contactPage'])->name('public.contact');
Route::get('/testimonials', [LandingController::class, 'testimonialsPage'])->name('public.testimonials');

// Public Booking (No authentication required)
Route::get('/book-online', [PublicBookingController::class, 'create'])->name('public.booking.create');
Route::post('/book-online', [PublicBookingController::class, 'store'])->name('public.booking.store');
Route::get('/booking-confirmation/{appointment}', [PublicBookingController::class, 'confirmation'])->name('booking.confirmation');
Route::post('/api/vehicle-lookup', [PublicBookingController::class, 'lookupVehicle'])->name('api.vehicle.lookup');

// Reschedule accept/decline (public, token-based)
Route::get('/bookings/accept-reschedule/{token}', [AppointmentController::class, 'acceptReschedule'])->name('bookings.accept-reschedule');
Route::get('/bookings/decline-reschedule/{token}', [AppointmentController::class, 'declineReschedule'])->name('bookings.decline-reschedule');

// Public quote review (no login required — secure token-based)
Route::get('/quote/review/{token}', [PublicQuoteReviewController::class, 'show'])->name('quote.review');
Route::post('/quote/review/{token}/approve', [PublicQuoteReviewController::class, 'approve'])->name('quote.approve');
Route::post('/quote/review/{token}/decline', [PublicQuoteReviewController::class, 'decline'])->name('quote.decline');
Route::post('/quote/review/{token}/suggest-date', [PublicQuoteReviewController::class, 'suggestDate'])->name('quote.suggest-date');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Stripe Webhook (no CSRF)
Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook'])->name('stripe.webhook');

// Twilio / WhatsApp Webhooks (no CSRF)
Route::post('/webhooks/whatsapp/inbound', [WhatsAppWebhookController::class, 'inbound'])->name('webhooks.whatsapp.inbound');
Route::post('/webhooks/whatsapp/status', [WhatsAppWebhookController::class, 'statusCallback'])->name('webhooks.whatsapp.status');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/send-portal-invite', [CustomerPortalController::class, 'sendPortalInvite'])->name('customers.portal-invite');

    // Vehicles
    Route::resource('vehicles', VehicleController::class);

    // Bookings
    Route::get('/bookings/calendar', [AppointmentController::class, 'calendar'])->name('bookings.calendar');
    Route::resource('bookings', AppointmentController::class)->parameters(['bookings' => 'booking']);
    Route::post('/bookings/{booking}/confirm', [AppointmentController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/cancel', [AppointmentController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/complete', [AppointmentController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/convert-to-job', [AppointmentController::class, 'convertToJob'])->name('bookings.convert-to-job');
    Route::post('/bookings/{booking}/reschedule', [AppointmentController::class, 'reschedule'])->name('bookings.reschedule');
    Route::post('/bookings/{booking}/generate-quote', [AppointmentController::class, 'generateQuote'])->name('bookings.generate-quote');

    // Job Cards
    Route::resource('job-cards', JobCardController::class);
    Route::post('/job-cards/{jobCard}/add-labour', [JobCardController::class, 'addLabour'])->name('job-cards.add-labour');
    Route::post('/job-cards/{jobCard}/add-part', [JobCardController::class, 'addPart'])->name('job-cards.add-part');
    Route::post('/job-cards/{jobCard}/complete', [JobCardController::class, 'complete'])->name('job-cards.complete');
    Route::post('/job-cards/{jobCard}/generate-invoice', [JobCardController::class, 'generateInvoice'])->name('job-cards.generate-invoice');

    // MOT Tests
    Route::resource('mot-tests', MotTestController::class);
    Route::post('/mot-tests/{motTest}/pass', [MotTestController::class, 'pass'])->name('mot-tests.pass');
    Route::post('/mot-tests/{motTest}/fail', [MotTestController::class, 'fail'])->name('mot-tests.fail');
    Route::post('/mot-tests/{motTest}/retest', [MotTestController::class, 'retest'])->name('mot-tests.retest');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/{invoice}/credit-note', [InvoiceController::class, 'creditNote'])->name('invoices.credit-note');

    // Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/payments/stripe', [PaymentController::class, 'stripePayment'])->name('payments.stripe');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

    // Inventory (Parts)
    Route::resource('inventory', PartController::class)->parameters(['inventory' => 'part']);
    Route::post('/inventory/{part}/adjust-stock', [PartController::class, 'adjustStock'])->name('inventory.adjust-stock');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // ECU Jobs
    Route::resource('ecu-jobs', EcuJobController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/sms-test', [SettingsController::class, 'smsTest'])->name('settings.sms-test');
    Route::post('/settings/sms-test', [SettingsController::class, 'sendTestSms'])->name('settings.sms-test.send');
    Route::post('/settings/booking-availability', [SettingsController::class, 'updateAvailability'])->name('settings.availability.update');
    Route::post('/settings/closed-dates/add', [SettingsController::class, 'addClosedDate'])->name('settings.closed-dates.add');
    Route::post('/settings/closed-dates/remove', [SettingsController::class, 'removeClosedDate'])->name('settings.closed-dates.remove');

    // Website Content Management
    Route::get('/website', [WebsiteController::class, 'index'])->name('website.index');
    Route::post('/website/{section}', [WebsiteController::class, 'update'])->name('website.update');

    // Services Management
    Route::resource('services', ServiceController::class);
    Route::post('/services/{service}/toggle-approve', [ServiceController::class, 'toggleApprove'])->name('services.toggle-approve');
    Route::post('/services/{service}/toggle-website', [ServiceController::class, 'toggleWebsite'])->name('services.toggle-website');
    Route::post('/services/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggle-active');
    Route::post('/services/bulk-action', [ServiceController::class, 'bulkAction'])->name('services.bulk-action');
    Route::post('/services/bulk-prices', [ServiceController::class, 'bulkUpdatePrices'])->name('services.bulk-prices');
    Route::get('/services/{service}/pricing', [ServiceController::class, 'getPricing'])->name('services.pricing');

    // Quotes / Estimates
    Route::resource('quotes', QuoteController::class);
    Route::post('/quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('/quotes/{quote}/send-for-review', [QuoteController::class, 'sendForReview'])->name('quotes.send-for-review');
    Route::post('/quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::post('/quotes/{quote}/decline', [QuoteController::class, 'decline'])->name('quotes.decline');
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');
    Route::get('/quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');

    // Staff Management
    Route::resource('staff', StaffController::class);
    Route::get('/staff/{staff}/schedule', [StaffController::class, 'schedule'])->name('staff.schedule');
    Route::post('/staff/{staff}/schedule', [StaffController::class, 'storeSchedule'])->name('staff.storeSchedule');
    Route::post('/staff/{staff}/clock-in', [StaffController::class, 'clockIn'])->name('staff.clock-in');
    Route::post('/staff/{staff}/clock-out', [StaffController::class, 'clockOut'])->name('staff.clock-out');
    Route::get('/staff/{staff}/commissions', [StaffController::class, 'commissions'])->name('staff.commissions');
    Route::post('/commissions/{commission}/approve', [StaffController::class, 'approveCommission'])->name('commissions.approve');
    Route::post('/commissions/pay', [StaffController::class, 'payCommission'])->name('commissions.pay');
    Route::get('/workload', [StaffController::class, 'workload'])->name('staff.workload');

    // Vehicle Health Checks
    Route::resource('health-checks', VehicleHealthCheckController::class)->except(['edit', 'update']);
    Route::post('/health-checks/{healthCheck}/email', [VehicleHealthCheckController::class, 'email'])->name('health-checks.email');
    Route::get('/health-checks-template', [VehicleHealthCheckController::class, 'template'])->name('health-checks.template');

    // WhatsApp Support Module
    Route::get('/whatsapp-support', [WhatsAppSupportController::class, 'index'])->name('whatsapp.support.index');
    Route::get('/whatsapp-support/{conversation}', [WhatsAppSupportController::class, 'show'])->name('whatsapp.support.show');
    Route::post('/whatsapp-support/{conversation}/reply', [WhatsAppSupportController::class, 'reply'])->name('whatsapp.support.reply');
    Route::patch('/whatsapp-support/{conversation}', [WhatsAppSupportController::class, 'update'])->name('whatsapp.support.update');
    Route::delete('/whatsapp-support/{conversation}', [WhatsAppSupportController::class, 'destroy'])->name('whatsapp.support.destroy');
    Route::post('/whatsapp-support/compose', [WhatsAppSupportController::class, 'compose'])->name('whatsapp.support.compose');

    // Support Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
});

// Customer Portal (separate session auth)
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/login', [CustomerPortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerPortalController::class, 'login'])->name('login.post');
    Route::get('/registration', fn() => redirect('/customer/register', 301))->name('registration');
    Route::get('/register', [CustomerPortalController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerPortalController::class, 'register'])->name('register.post');
    Route::get('/set-password', [CustomerPortalController::class, 'showSetPassword'])->name('set-password');
    Route::post('/set-password', [CustomerPortalController::class, 'setPassword'])->name('set-password.post');
    Route::post('/forgot-password', [CustomerPortalController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/logout', [CustomerPortalController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [CustomerPortalController::class, 'appointments'])->name('appointments');
    Route::get('/appointments/book', [CustomerPortalController::class, 'bookAppointment'])->name('appointments.book');
    Route::post('/appointments/book', [CustomerPortalController::class, 'storeAppointment'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [CustomerPortalController::class, 'cancelAppointment'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/accept-proposed', [CustomerPortalController::class, 'acceptProposedTime'])->name('appointments.accept-proposed');
    Route::post('/appointments/{appointment}/request-new-time', [CustomerPortalController::class, 'requestNewTime'])->name('appointments.request-new-time');
    Route::post('/appointments/{appointment}/keep-original', [CustomerPortalController::class, 'keepOriginalTime'])->name('appointments.keep-original');
    Route::get('/vehicles', [CustomerPortalController::class, 'vehicles'])->name('vehicles');
    Route::post('/vehicles', [CustomerPortalController::class, 'storeVehicle'])->name('vehicles.store');
    Route::delete('/vehicles/{vehicle}', [CustomerPortalController::class, 'deleteVehicle'])->name('vehicles.delete');
    Route::get('/invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}/download', [CustomerPortalController::class, 'downloadInvoice'])->name('invoices.download');
    Route::get('/service-history', [CustomerPortalController::class, 'serviceHistory'])->name('service-history');
    Route::get('/quotes', [CustomerPortalController::class, 'quotes'])->name('quotes');
    Route::post('/quotes/{quote}/approve', [CustomerPortalController::class, 'approveQuote'])->name('quotes.approve');
    Route::post('/quotes/{quote}/reject', [CustomerPortalController::class, 'rejectQuote'])->name('quotes.reject');
    Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [CustomerPortalController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/notifications', [CustomerPortalController::class, 'updateNotifications'])->name('profile.notifications');

    // Support Tickets
    Route::get('/tickets', [CustomerPortalController::class, 'tickets'])->name('tickets');
    Route::get('/tickets/create', [CustomerPortalController::class, 'createTicket'])->name('tickets.create');
    Route::post('/tickets', [CustomerPortalController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [CustomerPortalController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [CustomerPortalController::class, 'replyTicket'])->name('tickets.reply');
});
