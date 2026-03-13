# Garage Management System (GarageMS)

A world-class, comprehensive garage management system built with Laravel, designed specifically for UK garages with integrations to DVLA, DVSA, and TecDoc.

## 🚀 Features

### Core Functionality
- **Customer Management**: Complete customer database with individual and business customer support
- **Vehicle Management**: Comprehensive vehicle records with automatic DVLA data lookup
- **Appointment Scheduling**: Advanced booking system with availability checking and calendar view
- **Job Card System**: Full workshop management with parts and labor tracking
- **Invoice Generation**: Professional invoicing with VAT support and payment tracking
- **MOT Integration**: DVSA MOT history lookup and tracking
- **Parts Management**: Inventory management with TecDoc integration

### UK-Specific Integrations
- **DVLA API**: Automatic vehicle data retrieval by registration number
- **DVSA MOT History**: Complete MOT test history and status checking
- **TecDoc**: Parts catalog integration for accurate parts identification

### Premium UI/UX
- Modern, responsive design using Tailwind CSS
- Intuitive navigation with role-based access
- Real-time status updates
- Mobile-friendly interface
- Professional dashboard with key metrics
- Color-coded status indicators

## 📋 System Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & NPM
- Apache/Nginx web server

## 🛠️ Installation

### 1. Clone and Setup

```bash
cd temp-laravel
composer install
npm install
```

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Update the following in your `.env` file:

```env
APP_NAME="Garage Management System"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=garage_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

# DVLA API Configuration
DVLA_API_KEY=your_dvla_api_key

# DVSA API Configuration
DVSA_API_KEY=your_dvsa_api_key

# TecDoc API Configuration
TECDOC_API_KEY=your_tecdoc_api_key
TECDOC_PROVIDER_ID=your_provider_id
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed Database (Optional)

```bash
php artisan db:seed
```

### 6. Build Assets

```bash
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 🔑 API Keys Setup

### DVLA API
1. Register at [DVLA Developer Portal](https://developer-portal.driver-vehicle-licensing.api.gov.uk/)
2. Subscribe to the Vehicle Enquiry API
3. Copy your API key to `.env` file

### DVSA MOT History API
1. Register at [DVSA MOT History API](https://documentation.history.mot.api.gov.uk/)
2. Request API access
3. Add your API key to `.env` file

### TecDoc
1. Contact TecDoc for API access
2. Obtain your Provider ID and API credentials
3. Configure in `.env` file

## 📚 Database Structure

### Main Tables
- `customers` - Customer information
- `vehicles` - Vehicle records with DVLA integration
- `appointments` - Booking and scheduling
- `job_cards` - Workshop job management
- `services` - Service catalog
- `parts` - Parts inventory
- `invoices` - Billing and payments
- `mot_tests` - MOT history records

### Relationships
- Customer → Vehicles (One to Many)
- Customer → Appointments (One to Many)
- Vehicle → Job Cards (One to Many)
- Job Card → Services & Parts (Many to Many)
- Job Card → Invoice (One to One)

## 🎨 UI Components

### Dashboard
- Real-time statistics
- Today's appointments
- Active job cards
- Pending invoices
- Quick action buttons
- Alert notifications

### Modules
1. **Customers**: Create, view, edit, and manage customer records
2. **Vehicles**: Vehicle database with DVLA lookup
3. **Appointments**: Calendar-based scheduling system
4. **Job Cards**: Complete workshop management
5. **Invoices**: Professional invoicing with payment tracking

## 🔐 User Roles

- **Admin**: Full system access
- **Manager**: Management and reporting
- **Technician**: Job card and service management
- **Receptionist**: Customer and appointment management

## 📱 Features by Module

### Customer Management
- Individual and business customer support
- Complete contact information
- Vehicle history
- Appointment history
- Invoice history
- Notes and comments

### Vehicle Management
- DVLA data auto-population
- MOT due date tracking
- Tax due date tracking
- Service history
- Mileage tracking
- TecDoc integration for specifications

### Appointment System
- Calendar view
- Availability checking
- Customer notifications
- Technician assignment
- Status tracking
- Reminder system

### Job Card System
- Job number generation
- Parts and labor tracking
- Time tracking
- Status workflow
- Customer approval
- Technician notes
- Photo uploads

### Invoice System
- Professional invoice generation
- VAT calculation
- Payment tracking
- PDF export
- Email delivery
- Payment history

## 🔧 Configuration

### Service Categories
- MOT
- Service
- Repair
- Diagnosis
- Bodywork

### Appointment Types
- Service
- MOT
- Repair
- Diagnosis

### Job Card Status
- Open
- In Progress
- Awaiting Parts
- Awaiting Approval
- Completed
- Invoiced

### Invoice Status
- Draft
- Sent
- Paid
- Partially Paid
- Overdue
- Cancelled

## 📊 Reports (Future Enhancement)

- Daily/Weekly/Monthly revenue
- Customer analysis
- Service history
- Parts usage
- Technician performance
- MOT due reminders
- Outstanding invoices

## 🔄 Workflow

1. **Customer Books Appointment**
   - Create/Select customer
   - Select vehicle (DVLA lookup)
   - Choose service type
   - Select date/time
   - Assign technician

2. **Create Job Card**
   - From appointment or direct entry
   - Record customer complaint
   - Add services and parts
   - Track progress
   - Update status

3. **Complete Work**
   - Mark services as complete
   - Record work done
   - Update vehicle mileage
   - Generate invoice

4. **Invoice & Payment**
   - Auto-generate from job card
   - Send to customer
   - Record payment
   - Print/Email receipt

## 🚀 Deployment

### Production Checklist
1. Set `APP_ENV=production`
2. Set `APP_DEBUG=false`
3. Configure proper database
4. Set up SSL certificate
5. Configure email settings
6. Set up backup system
7. Configure cron jobs
8. Optimize autoloader: `composer install --optimize-autoloader --no-dev`
9. Cache configuration: `php artisan config:cache`
10. Cache routes: `php artisan route:cache`
11. Cache views: `php artisan view:cache`

### Server Requirements
- PHP 8.2+ with required extensions
- MySQL 8.0+
- SSL certificate for HTTPS
- Sufficient storage for documents
- Regular backup system

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
```bash
php artisan config:clear
php artisan cache:clear
```

**DVLA API Not Working**
- Check API key is correct
- Verify API subscription is active
- Check rate limits

**Assets Not Loading**
```bash
npm run build
php artisan cache:clear
```

## 📝 License

Proprietary - All rights reserved

## 👥 Support

For support and inquiries, please contact your system administrator.

## 🔄 Version History

### v1.0.0 (Current)
- Initial release
- Core functionality
- DVLA, DVSA, TecDoc integration
- Premium UI/UX
- Complete customer and vehicle management
- Appointment scheduling
- Job card system
- Invoice generation

## 🎯 Roadmap

- [ ] SMS notifications
- [ ] Email reminders
- [ ] Advanced reporting
- [ ] Mobile app
- [ ] Customer portal
- [ ] Online booking
- [ ] Payment gateway integration
- [ ] Multi-location support
- [ ] Advanced inventory management
- [ ] Staff scheduling

---

Built with ❤️ using Laravel & Tailwind CSS
