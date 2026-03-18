# Doyen Auto Services — Development Changelog
**Project:** Doyen Auto Services Garage Management System  
**Stack:** Laravel 10 · Inertia.js · Vue 3 · TypeScript · Tailwind CSS  
**Period Covered:** February – March 2026  

---

## Table of Contents
1. [Quote Review & Approval Flow](#1-quote-review--approval-flow)
2. [Customer Suggest Alternative Date](#2-customer-suggest-alternative-date)
3. [Booking Availability Management](#3-booking-availability-management)
4. [Website Contact Details Management](#4-website-contact-details-management)
5. [Password Reset](#5-password-reset)
6. [PDF Invoice Generation](#6-pdf-invoice-generation)
7. [Files Changed — Master Reference](#7-files-changed--master-reference)

---

## 1. Quote Review & Approval Flow

### What Was Built
A complete token-based quote review workflow allowing admins to send a quote to the customer for approval before confirming a booking. The customer receives an email with a secure link, reviews the quote, and can approve or decline — no login required.

### Database
- **Migration:** `2026_03_14_000001_add_quote_review_fields`
  - Added `review_token` (unique string) to `quotes` table
  - Added `appointment_id` (nullable FK) to `quotes` table
  - Added `expires_at` (nullable datetime) to `quotes` table

### Backend
| File | Change |
|---|---|
| `app/Http/Controllers/AppointmentController.php` | Added `generateQuote()` method — creates a Quote record, attaches to appointment, links to the customer |
| `app/Http/Controllers/QuoteController.php` | Added `sendForReview()` method — generates a `review_token`, sets `expires_at` to +7 days, dispatches `QuoteReviewRequest` mail to customer |
| `app/Http/Controllers/PublicQuoteReviewController.php` | **New file.** Public (no-auth) controller with `show()`, `approve()`, `decline()` methods. Token-based access. |

### Email Templates
| File | Purpose |
|---|---|
| `resources/views/emails/quote-review-request.blade.php` | Sent to customer — contains quote details and an "Approve / Decline" button |
| `resources/views/emails/admin/quote-approved.blade.php` | Sent to admin when customer approves |
| `resources/views/emails/admin/quote-declined.blade.php` | Sent to admin when customer declines |
| `resources/views/emails/quote-approved-confirmation.blade.php` | Sent to customer confirming their approval |

### Frontend
| File | Change |
|---|---|
| `resources/views/quotes/public-review.blade.php` | **New Blade page** (not Inertia — no login). Shows quote breakdown, dates, pricing with approve/decline actions. |
| `resources/js/Pages/Bookings/Index.vue` | Added "Send for Review" button on appointment cards |
| `resources/js/Components/StatusBadge.vue` | Added new statuses: `quote_sent`, `quote_approved`, `quote_declined` |

### Routes Added
```
GET   /quote/review/{token}              → PublicQuoteReviewController@show
POST  /quote/review/{token}/approve      → PublicQuoteReviewController@approve
POST  /quote/review/{token}/decline      → PublicQuoteReviewController@decline
```

---

## 2. Customer Suggest Alternative Date

### What Was Built
Extended the quote review page so customers can suggest a different date/time instead of just approving or declining. Admin receives an email notification with the suggested date.

### Backend
| File | Change |
|---|---|
| `app/Http/Controllers/PublicQuoteReviewController.php` | Added `suggestDate()` method — validates `suggested_date`, `suggested_time`, `suggestion_notes`; stores on the appointment; sets appointment status to `reschedule_pending`; emails admin |
| `app/Mail/QuoteDateSuggestion.php` | **New Mailable.** Constructor accepts `(Quote $quote, string $suggestedDate, string $suggestedTime, ?string $notes)`. Subject: `"Customer Date Suggestion — Quote {quote_number}"` |

### Email Template
| File | Purpose |
|---|---|
| `resources/views/emails/admin/quote-date-suggestion.blade.php` | Admin email showing original booking date, customer's suggested date/time, customer's notes, and a "View Booking" button |

### Frontend (Public Review Page)
| Change | Detail |
|---|---|
| Replaced JS `prompt()` decline | Inline collapsible form with a `<textarea>` for decline reason |
| Added "📅 Suggest Different Date" panel | Expandable inline form with a date picker (`min=tomorrow`, `max=90 days`), time dropdown (08:00–17:00 in 30-minute slots), and optional notes field |
| JS `toggleForm()` helper | Shows/hides the three action panels (approve / suggest date / decline) so only one is open at a time |

### Route Added
```
POST  /quote/review/{token}/suggest-date  → PublicQuoteReviewController@suggestDate
```

---

## 3. Booking Availability Management

### What Was Built
Admins can now configure which days the garage accepts bookings, set open/close times per day, and block specific dates (holidays, closures). These settings automatically affect the customer-facing booking page's available time slots.

### Backend
| File | Change |
|---|---|
| `app/Http/Controllers/SettingsController.php` | Added `DEFAULT_HOURS` constant (Mon–Fri 08:00–17:30, Sat 09:00–13:00, Sun closed). Added `updateAvailability()`, `addClosedDate()`, `removeClosedDate()` methods. Updated `index()` to pass `bookingHours` and `closedDates` as Inertia props |
| `app/Http/Controllers/Customer/AppointmentController.php` | Rewrote `getAvailableSlots()` — reads `Setting::get('booking_hours')` for per-day config, reads `Setting::get('booking_closed_dates')` for blocked dates, generates 30-minute time slots dynamically using Carbon instead of a hardcoded array |

### Data Storage (Setting model — key/value)
| Key | Format | Description |
|---|---|---|
| `booking_hours` | JSON object | `{ "Monday": { "open": true, "start": "08:00", "end": "17:30" }, ... }` |
| `booking_closed_dates` | JSON array | `["2026-12-25", "2026-01-01", ...]` |

### Frontend
| File | Change |
|---|---|
| `resources/js/Pages/Settings/Index.vue` | Added **Booking Availability** section (per-day toggle + start/end time pickers for Mon–Sun) and **Blocked Dates** section (date picker + list with remove buttons) |

### Routes Added
```
POST  /settings/booking-availability      → SettingsController@updateAvailability
POST  /settings/closed-dates/add          → SettingsController@addClosedDate
POST  /settings/closed-dates/remove       → SettingsController@removeClosedDate
```

---

## 4. Website Contact Details Management

### What Was Built
Admins can now manage the garage's address, phone number, email, and WhatsApp number from the Website Management panel. Changes propagate automatically to every page on the public website (landing page, about page, floating WhatsApp button).

### Backend
| File | Change |
|---|---|
| `app/Http/Controllers/WebsiteController.php` | Added `use App\Models\Setting` import. Added `contact` section to `defaults()`. Updated `index()` to load contact data from `Setting` model. Updated `update()` — when `$section === 'contact'`, saves each field via `Setting::set($key, $value, 'garage')` rather than `WebsiteContent` |
| `app/Http/Middleware/HandleInertiaRequests.php` | Added `whatsapp_number` key to the `garageSettings` shared props (both main return and catch fallback) |

### Why Settings vs WebsiteContent?
Contact details are stored in the `Setting` model (not `WebsiteContent`) so they are available globally via the Inertia middleware and show on every page without duplication.

### Fields Managed
| Field | Key in Setting | Use |
|---|---|---|
| Address | `address` | Footer, PDF invoices, emails |
| City | `city` | Footer, PDF invoices |
| Postcode | `postcode` | Footer, PDF invoices |
| Phone | `phone` | Header, footer, WhatsApp fallback |
| Email | `email` | Footer, emails |
| WhatsApp Number | `whatsapp_number` | Floating WhatsApp button |

### Frontend
| File | Change |
|---|---|
| `resources/js/Pages/Website/Index.vue` | Added `contact` to prop type. Added `{ id: 'contact', label: '📍 Contact Details' }` tab. Added `contactForm` (useForm) and `saveContact()`. Added full Contact tab panel with address/city/postcode/phone/email/WhatsApp fields (WhatsApp highlighted in a green box) |
| `resources/js/Pages/Landing.vue` | `whatsappSupportHref` computed now uses `garage.value.whatsapp_number \|\| garagePhone.value` |
| `resources/js/Pages/About.vue` | `whatsappHref` computed now uses `garage.value.whatsapp_number \|\| garagePhone.value` |

---

## 5. Password Reset

### Status: Already Implemented ✅
All password reset infrastructure was confirmed present and working.

### What Was Verified
| Component | Status |
|---|---|
| `app/Http/Controllers/Auth/ForgotPasswordController.php` | ✅ Present — `show()` renders `Auth/ForgotPassword`, `sendResetLink()` uses Laravel `Password::sendResetLink()` |
| `app/Http/Controllers/Auth/ResetPasswordController.php` | ✅ Present — `show()` renders `Auth/ResetPassword` with token + email, `reset()` hashes new password and fires `PasswordReset` event |
| `resources/js/Pages/Auth/ForgotPassword.vue` | ✅ Present — branded page with email field, success flash message support |
| `resources/js/Pages/Auth/ResetPassword.vue` | ✅ Present — branded page with email (readonly), new password, confirm password fields |
| `resources/views/emails/reset-password.blade.php` | ✅ Present — branded email with reset button and fallback URL |
| `app/Notifications/ResetPasswordNotification.php` | ✅ Present — custom notification using the branded email template |
| `password_reset_tokens` DB table | ✅ Migrated (Laravel default `0001_01_01_000000_create_users_table`) |
| Login page "Forgot password?" link | ✅ Present in `Auth/Login.vue` |

### Routes (all confirmed registered)
```
GET   /forgot-password          password.request  → ForgotPasswordController@show
POST  /forgot-password          password.email    → ForgotPasswordController@sendResetLink
GET   /reset-password/{token}   password.reset    → ResetPasswordController@show
POST  /reset-password           password.update   → ResetPasswordController@reset
```

> **Note:** To enable password reset emails in production, configure `MAIL_MAILER`, `MAIL_HOST`, `MAIL_USERNAME`, and `MAIL_PASSWORD` in `.env`.

---

## 6. PDF Invoice Generation

### Status: Implemented & Fixed ✅
DomPDF was already installed and the route/controller were wired up. Three issues were identified and fixed.

### What Was Already in Place
| Component | Status |
|---|---|
| `barryvdh/laravel-dompdf ^3.1` in `composer.json` | ✅ Installed |
| `GET /invoices/{invoice}/download` route | ✅ Registered |
| `InvoiceController@download()` | ✅ Implemented — loads invoice with relations, passes `$garage` settings array to view |
| `resources/views/pdf/invoice.blade.php` | ✅ Present — professional layout with header, bill-to, vehicle, line items, totals, notes |
| "Download PDF" button on `Invoices/Show.vue` | ✅ Present |

### Fixes Applied

#### Fix 1: Hardcoded Company Details → Dynamic from Settings
**File:** `resources/views/pdf/invoice.blade.php`  
The company name, address, phone, and email were hardcoded strings. Updated to use the `$garage` variable (passed by the controller from `Setting::getAllSettings()`), so changes in Website Settings → Contact Details appear on all PDFs automatically.

```blade
{{-- Before --}}
<h1>🔧 Doyen Auto Services</h1>
<p>59 Southcroft Road</p>
<p>Rutherglen, Glasgow G73 1UG</p>
<p>Tel: +44 141 482 0726</p>

{{-- After --}}
<h1>{{ $garage['garage_name'] ?? 'Doyen Auto Services' }}</h1>
@if(!empty($garage['address']))<p>{{ $garage['address'] }}</p>@endif
@if(!empty($garage['city']))<p>{{ $garage['city'] }} {{ $garage['postcode'] ?? '' }}</p>@endif
@if(!empty($garage['phone']))<p>Tel: {{ $garage['phone'] }}</p>@endif
```

#### Fix 2: Wrong Column Name on Invoice Items
**File:** `resources/views/pdf/invoice.blade.php`  
Template used `$item->type` but the `invoice_items` table column is `item_type`. Fixed to `$item->item_type`.

#### Fix 3: Dynamic VAT Label
**File:** `resources/views/pdf/invoice.blade.php`  
Changed hardcoded "VAT (20%)" to "VAT:" — the £ amount is calculated correctly from `$invoice->vat_amount` already.

---

## 7. Files Changed — Master Reference

### PHP Controllers
| File | Type | Changes |
|---|---|---|
| `app/Http/Controllers/AppointmentController.php` | Modified | Added `generateQuote()` |
| `app/Http/Controllers/QuoteController.php` | Modified | Added `sendForReview()` |
| `app/Http/Controllers/PublicQuoteReviewController.php` | **New** | `show()`, `approve()`, `decline()`, `suggestDate()` |
| `app/Http/Controllers/SettingsController.php` | Modified | `DEFAULT_HOURS`, `updateAvailability()`, `addClosedDate()`, `removeClosedDate()` |
| `app/Http/Controllers/Customer/AppointmentController.php` | Modified | `getAvailableSlots()` rewritten — dynamic slots from settings |
| `app/Http/Controllers/WebsiteController.php` | Modified | Contact section added (reads/writes `Setting` model) |
| `app/Http/Controllers/Auth/ForgotPasswordController.php` | Verified | No changes needed |
| `app/Http/Controllers/Auth/ResetPasswordController.php` | Verified | No changes needed |
| `app/Http/Controllers/InvoiceController.php` | Verified | `download()` was complete and correct |

### PHP Middleware & Models
| File | Type | Changes |
|---|---|---|
| `app/Http/Middleware/HandleInertiaRequests.php` | Modified | Added `whatsapp_number` to both returns of `getGarageSettings()` |
| `app/Mail/QuoteDateSuggestion.php` | **New** | Mailable for customer date suggestion notification |
| `app/Notifications/ResetPasswordNotification.php` | Verified | No changes needed |

### Blade Views
| File | Type | Changes |
|---|---|---|
| `resources/views/quotes/public-review.blade.php` | **New** | Token-based public quote review page with 3-panel actions |
| `resources/views/emails/reset-password.blade.php` | Verified | No changes needed |
| `resources/views/emails/quote-review-request.blade.php` | **New** | Customer quote review email |
| `resources/views/emails/quote-approved-confirmation.blade.php` | **New** | Customer approval confirmation email |
| `resources/views/emails/admin/quote-approved.blade.php` | **New** | Admin notification — quote approved |
| `resources/views/emails/admin/quote-declined.blade.php` | **New** | Admin notification — quote declined |
| `resources/views/emails/admin/quote-date-suggestion.blade.php` | **New** | Admin notification — customer suggested date |
| `resources/views/pdf/invoice.blade.php` | Modified | Dynamic company info, fixed `item_type`, updated VAT label |

### Vue / Inertia Pages
| File | Type | Changes |
|---|---|---|
| `resources/js/Pages/Bookings/Index.vue` | Modified | Added "Send for Review" button on appointments |
| `resources/js/Components/StatusBadge.vue` | Modified | Added `quote_sent`, `quote_approved`, `quote_declined`, `reschedule_pending` statuses |
| `resources/js/Pages/Settings/Index.vue` | Modified | Added Booking Availability and Blocked Dates sections |
| `resources/js/Pages/Website/Index.vue` | Modified | Added Contact Details tab with full form |
| `resources/js/Pages/Landing.vue` | Modified | WhatsApp button uses `whatsapp_number` with phone fallback |
| `resources/js/Pages/About.vue` | Modified | WhatsApp button uses `whatsapp_number` with phone fallback |
| `resources/js/Pages/Auth/ForgotPassword.vue` | Verified | No changes needed |
| `resources/js/Pages/Auth/ResetPassword.vue` | Verified | No changes needed |

### Database Migrations
| File | Batch | Purpose |
|---|---|---|
| `2026_03_14_000001_add_quote_review_fields` | 20 | Adds `review_token`, `appointment_id`, `expires_at` to `quotes` |

### Routes (`routes/web.php`)
| Route | Method | Controller | Feature |
|---|---|---|---|
| `/quote/review/{token}` | GET | `PublicQuoteReviewController@show` | Quote review |
| `/quote/review/{token}/approve` | POST | `PublicQuoteReviewController@approve` | Quote approval |
| `/quote/review/{token}/decline` | POST | `PublicQuoteReviewController@decline` | Quote decline |
| `/quote/review/{token}/suggest-date` | POST | `PublicQuoteReviewController@suggestDate` | Date suggestion |
| `/settings/booking-availability` | POST | `SettingsController@updateAvailability` | Booking hours |
| `/settings/closed-dates/add` | POST | `SettingsController@addClosedDate` | Block date |
| `/settings/closed-dates/remove` | POST | `SettingsController@removeClosedDate` | Unblock date |
| `/website/{section}` | POST | `WebsiteController@update` | Website content incl. contact |
| `/forgot-password` | GET/POST | `ForgotPasswordController` | Password reset |
| `/reset-password/{token}` | GET/POST | `ResetPasswordController` | Password reset |
| `/invoices/{invoice}/download` | GET | `InvoiceController@download` | PDF download |

---

## Deployment Checklist

Before going live, ensure the following are configured in `.env`:

```env
# Email (required for all notifications and password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@doyenautos.co.uk
MAIL_FROM_NAME="Doyen Auto Services"

# App URL (required for password reset links to generate correctly)
APP_URL=https://yourdomain.com
```

Then run:
```bash
php artisan config:cache
php artisan route:cache
npm run build
```
