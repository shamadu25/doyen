# UK Garage Management System - Environment Configuration

## Quick Setup Guide

This file contains all environment variables needed for the garage management system.

## 1. Application Settings

```env
APP_NAME="UK Garage Manager"           # Your garage business name
APP_ENV=local                           # Environment: local, production
APP_KEY=                                # Run: php artisan key:generate
APP_DEBUG=true                          # Set to false in production
APP_TIMEZONE=Europe/London              # UK timezone
APP_URL=http://localhost:8000           # Your application URL
```

## 2. Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=garage_management           # Database name
DB_USERNAME=root                        # Your MySQL username
DB_PASSWORD=                            # Your MySQL password
```

### Create Database:
```sql
CREATE DATABASE garage_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 3. DVLA API (Vehicle Lookup)

Get your API key from: https://www.gov.uk/guidance/use-the-dvla-vehicle-enquiry-api

```env
DVLA_API_KEY=your_dvla_api_key_here
DVLA_API_URL=https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1
```

**Features:**
- Automatic vehicle details retrieval
- MOT due date
- Tax due date
- Make, model, color, fuel type

## 4. DVSA API (MOT History)

Get your API key from: https://documentation.history.mot.api.gov.uk/

```env
DVSA_API_KEY=your_dvsa_api_key_here
DVSA_API_URL=https://beta.check-mot.service.gov.uk/trade/vehicles/mot-tests
```

**Features:**
- Complete MOT test history
- Test results (pass/fail)
- Advisories and failures
- Mileage records

## 5. TecDoc API (Parts Catalog)

Contact TecDoc provider for API access: https://www.tecdoc.net/

```env
TECDOC_API_KEY=your_tecdoc_api_key_here
TECDOC_API_URL=https://webservice.tecdoc.com
TECDOC_PROVIDER_ID=your_provider_id
```

**Features:**
- Parts catalog lookup
- Vehicle-specific parts
- Part numbers and pricing
- Service schedules

## 6. Email Configuration

### For Development (Mailtrap):
Register at https://mailtrap.io

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@garage.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### For Production (Gmail):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### For Production (Office365):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_email@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 7. Garage Business Details

Update these with your actual business information:

```env
GARAGE_NAME="Your Garage Name"
GARAGE_ADDRESS="123 High Street"
GARAGE_CITY="London"
GARAGE_POSTCODE="SW1A 1AA"
GARAGE_PHONE="020 1234 5678"
GARAGE_EMAIL="info@yourgarage.co.uk"
GARAGE_VAT_NUMBER="GB123456789"
GARAGE_COMPANY_NUMBER="12345678"
```

These appear on:
- Invoices
- Job cards
- Email footers
- Reports

## 8. Reminder Settings

```env
MOT_REMINDER_DAYS=30        # Send MOT reminder X days before due
TAX_REMINDER_DAYS=14        # Send tax reminder X days before due
```

## 9. Invoice Configuration

```env
INVOICE_PREFIX="INV"        # Invoice number prefix (INV-202601-0001)
INVOICE_TERMS_DAYS=30       # Default payment terms in days
DEFAULT_VAT_RATE=20.00      # UK standard VAT rate (20%)
```

## 10. SMS Notifications (Optional)

For appointment reminders via SMS using Twilio:
Register at https://www.twilio.com

```env
SMS_ENABLED=true
SMS_PROVIDER=twilio
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+447123456789
```

## 🚀 Quick Start Commands

### 1. Copy environment file:
```bash
cp .env.example .env
```

### 2. Generate application key:
```bash
php artisan key:generate
```

### 3. Create database:
```bash
# In MySQL:
CREATE DATABASE garage_management;
```

### 4. Run migrations:
```bash
php artisan migrate
```

### 5. Seed sample data:
```bash
php artisan db:seed --class=ServiceSeeder
```

### 6. Build assets:
```bash
npm install
npm run build
```

### 7. Start server:
```bash
php artisan serve
```

Visit: http://localhost:8000

## 📝 Environment Checklist

Before going live, ensure you have:

- [ ] Database created and credentials configured
- [ ] Application key generated (`php artisan key:generate`)
- [ ] DVLA API key added (optional but recommended)
- [ ] DVSA API key added (optional but recommended)
- [ ] Email configuration tested
- [ ] Garage business details updated
- [ ] `APP_ENV=production` in production
- [ ] `APP_DEBUG=false` in production
- [ ] SSL certificate installed (HTTPS)
- [ ] Regular database backups configured

## 🔒 Security Notes

### In Production:
1. **Never commit `.env` to version control**
2. Set `APP_ENV=production`
3. Set `APP_DEBUG=false`
4. Use strong `DB_PASSWORD`
5. Use HTTPS only (`APP_URL=https://...`)
6. Restrict file permissions: `chmod 600 .env`
7. Use environment encryption if available

### API Keys:
- Keep API keys secure and private
- Rotate keys periodically
- Monitor API usage limits
- Use separate keys for dev/production

## 🆘 Troubleshooting

### Issue: "No application encryption key"
```bash
php artisan key:generate
```

### Issue: Database connection failed
- Check MySQL is running: `mysql -u root -p`
- Verify database exists: `SHOW DATABASES;`
- Check credentials in `.env`

### Issue: DVLA API not working
- Verify API key is correct
- Check API rate limits
- Ensure registration format is correct (no spaces)

### Issue: Emails not sending
- Test with Mailtrap.io first
- Check SMTP credentials
- Verify firewall allows outbound port 587

## 📚 Additional Resources

- Laravel Configuration: https://laravel.com/docs/configuration
- DVLA API Docs: https://www.gov.uk/guidance/use-the-dvla-vehicle-enquiry-api
- DVSA MOT API: https://documentation.history.mot.api.gov.uk/
- Mailtrap Testing: https://mailtrap.io
- Twilio SMS: https://www.twilio.com/docs

---

**Last Updated:** January 27, 2026
