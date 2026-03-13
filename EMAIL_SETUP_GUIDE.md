# 📧 EMAIL CONFIGURATION GUIDE
## Doyen Auto Services - Email Setup Instructions

### Current Status
✅ Email templates created (5 templates)  
✅ Mail classes configured  
✅ Controllers updated to send emails  
⚠️ SMTP credentials need configuration  

---

## Quick Setup (5 minutes)

### Option 1: Gmail (Recommended for Testing)

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password:**
   - Go to https://myaccount.google.com/apppasswords
   - Select "Mail" and your device
   - Copy the 16-character password

3. **Update `.env` file:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@doyenauto.co.uk"
MAIL_FROM_NAME="Doyen Auto Services"
```

4. **Test email:**
```bash
php artisan tinker
Mail::raw('Test email', function($message) { $message->to('test@example.com')->subject('Test'); });
```

---

### Option 2: Business Email (Production)

#### If using Microsoft 365/Outlook:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=info@doyenauto.co.uk
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@doyenauto.co.uk"
MAIL_FROM_NAME="Doyen Auto Services"
```

#### If using cPanel/WHM hosting:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=info@doyenauto.co.uk
MAIL_PASSWORD=your-cpanel-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@doyenauto.co.uk"
MAIL_FROM_NAME="Doyen Auto Services"
```

---

### Option 3: SendGrid (High Volume)

1. Sign up at https://sendgrid.com (Free: 100 emails/day)
2. Create API key
3. Configure:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@doyenauto.co.uk"
MAIL_FROM_NAME="Doyen Auto Services"
```

---

### Option 4: Mailgun (Recommended for Production)

1. Sign up at https://mailgun.com
2. Verify domain
3. Get SMTP credentials

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@doyenauto.co.uk"
MAIL_FROM_NAME="Doyen Auto Services"
```

---

## Email Templates Created

All templates are in `resources/views/emails/`:

1. **layout.blade.php** - Base template with Doyen branding
2. **appointment-confirmation.blade.php** - Sent after booking
3. **appointment-reminder.blade.php** - 24h before appointment
4. **appointment-cancelled.blade.php** - When cancelled
5. **invoice-created.blade.php** - When invoice ready
6. **mot-reminder.blade.php** - MOT expiry alerts

---

## Where Emails Are Sent

### 1. Booking Confirmation
**File:** `app/Http/Controllers/PublicBookingController.php`  
**Trigger:** Customer completes booking form  
**Recipient:** Customer email  
**Template:** `appointment-confirmation.blade.php`

### 2. Invoice Created
**File:** `app/Http/Controllers/InvoiceController.php`  
**Trigger:** Invoice sent via dashboard  
**Recipient:** Customer email  
**Template:** `invoice-created.blade.php`

### 3. Appointment Reminders
**File:** `app/Console/Commands/SendAppointmentReminders.php` (to be created)  
**Trigger:** Scheduled task (24h before)  
**Recipient:** Customer email  
**Template:** `appointment-reminder.blade.php`

### 4. MOT Reminders
**File:** `app/Console/Commands/SendMotReminders.php` (to be created)  
**Trigger:** Scheduled task (2 weeks, 1 week, 3 days before expiry)  
**Recipient:** Customer email  
**Template:** `mot-reminder.blade.php`

---

## Testing Emails

### Method 1: Use Mailtrap (Development)
Perfect for testing without sending real emails:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

Sign up free at: https://mailtrap.io

### Method 2: Test with Real Booking
1. Configure SMTP above
2. Go to http://localhost/garage/garage/public/booking
3. Complete booking form with your email
4. Check inbox for confirmation email

### Method 3: Artisan Tinker
```bash
php artisan tinker

# Test basic email
Mail::raw('Test email from Doyen Auto', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});

# Test appointment confirmation
$appointment = \App\Models\Appointment::first();
Mail::to($appointment->customer->email)->send(new \App\Mail\AppointmentConfirmation($appointment));
```

---

## Troubleshooting

### Error: "Connection could not be established"
**Solution:** Check firewall/antivirus blocking port 587/465

### Error: "Authentication failed"
**Solutions:**
- Gmail: Use App Password, not account password
- Enable "Less secure app access" (not recommended)
- Check username is full email address

### Error: "5.7.1 Client was not authenticated"
**Solution:** Username/password incorrect

### Emails go to Spam
**Solutions:**
- Set up SPF record: `v=spf1 include:_spf.google.com ~all`
- Set up DKIM signing
- Use business email matching your domain
- Use professional email service (Mailgun, SendGrid)

### No errors but emails not received
**Solution:** Check `storage/logs/laravel.log` for details

---

## Queue Configuration (Optional but Recommended)

Speed up response time by queuing emails:

1. **Update `.env`:**
```env
QUEUE_CONNECTION=database
```

2. **Run queue worker:**
```bash
php artisan queue:work
```

3. **Update mail sending in controllers:**
```php
// Instead of:
Mail::to($email)->send(new AppointmentConfirmation($appointment));

// Use:
Mail::to($email)->queue(new AppointmentConfirmation($appointment));
```

---

## Production Checklist

Before going live:

- [ ] Business email configured (not Gmail)
- [ ] SPF/DKIM records set up
- [ ] Test all 5 email templates
- [ ] Queue worker running (optional)
- [ ] Monitoring set up (failed jobs)
- [ ] Backup email address configured
- [ ] Email footer has correct contact details
- [ ] Unsubscribe link added (if marketing emails)

---

## Cost Comparison

| Provider | Free Tier | Paid Plans | Best For |
|----------|-----------|------------|----------|
| **Gmail** | 500/day | N/A | Development/Testing |
| **Mailtrap** | Unlimited | $10/mo | Development only |
| **SendGrid** | 100/day | $20/mo (40k) | Small business |
| **Mailgun** | 5,000/mo | $35/mo (50k) | Production |
| **SES** | 62,000/mo | $0.10/1000 | High volume |

---

## Next Steps

1. Choose email provider
2. Get credentials
3. Update `.env` file
4. Test with test booking
5. Monitor `storage/logs/laravel.log`
6. Set up automated reminders (Phase 2)

---

**Need Help?**  
Check Laravel documentation: https://laravel.com/docs/mail
