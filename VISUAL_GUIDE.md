# 📸 Visual Guide - What You'll See

This guide shows you what the UK Garage Management System looks like and what each section does.

---

## 🎨 Dashboard Overview

### Main Dashboard (`/dashboard`)

When you first log in, you'll see:

```
┌─────────────────────────────────────────────────────────────────┐
│  🚗 UK Garage Manager                          👤 User Menu     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  📊 STATISTICS CARDS (4 across)                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ Total    │ │ Today's  │ │ Active   │ │ Revenue  │          │
│  │Customers │ │Appts     │ │Job Cards │ │This Month│          │
│  │   142    │ │    8     │ │    12    │ │ £24,350  │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│                                                                  │
│  TODAY'S APPOINTMENTS                    ACTIVE JOB CARDS       │
│  ┌──────────────────────┐               ┌────────────────────┐ │
│  │ 09:00 - John Smith   │               │ JOB-2026-00123     │ │
│  │ VW Golf - Service    │               │ Ford Focus         │ │
│  │ ✓ Confirmed          │               │ In Progress        │ │
│  ├──────────────────────┤               ├────────────────────┤ │
│  │ 10:30 - Sarah Jones  │               │ JOB-2026-00124     │ │
│  │ BMW 3 Series - MOT   │               │ BMW X5             │ │
│  └──────────────────────┘               └────────────────────┘ │
│                                                                  │
│  🔔 ALERTS & REMINDERS                                          │
│  ⚠️ 5 vehicles have MOT due within 30 days                     │
│  💰 3 invoices are overdue                                      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

**Quick Actions:**
- New Appointment
- New Job Card  
- New Customer
- New Vehicle

---

## 👥 Customer Management

### Customer List (`/customers`)

```
┌─────────────────────────────────────────────────────────────────┐
│  Customers                                    [+ New Customer]  │
├─────────────────────────────────────────────────────────────────┤
│  [🔍 Search customers...]                                       │
│                                                                  │
│  [All] [Individual] [Business]  ← Filter tabs                  │
│                                                                  │
│  CUSTOMER CARDS (Grid Layout - 3 per row)                       │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐  │
│  │ 👤 JS           │ │ 👤 MB           │ │ 🏢 AC          │  │
│  │ John Smith      │ │ Mary Brown      │ │ ABC Motors     │  │
│  │ Individual      │ │ Individual      │ │ Business       │  │
│  │                 │ │                 │ │                │  │
│  │ 📧 john@...     │ │ 📧 mary@...     │ │ 📧 info@...    │  │
│  │ 📞 07700...     │ │ 📞 07800...     │ │ 📞 020...      │  │
│  │ 📍 SW1A 1AA     │ │ 📍 E1 6AN       │ │ 📍 W1D 3QU     │  │
│  │                 │ │                 │ │                │  │
│  │ 🚗 3 vehicles   │ │ 🚗 1 vehicle    │ │ 🚗 8 vehicles  │  │
│  │ [View Details→] │ │ [View Details→] │ │ [View Details→]│  │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Customer Profile (`/customers/{id}`)

```
┌─────────────────────────────────────────────────────────────────┐
│  ← Back to Customers                                            │
├─────────────────────────────────────────────────────────────────┤
│  CUSTOMER HEADER (Blue Gradient)                                │
│  👤 JS  John Smith                          [Edit Customer]     │
│  Individual | Customer since Jan 2024                           │
│                                                                  │
│  CONTACT INFO              ADDRESS                              │
│  📧 john@example.com       123 High Street                      │
│  📞 07700 900000           London                               │
│  📱 07700 900001           SW1A 1AA                             │
│                                                                  │
│  VEHICLES (3)                              [+ Add Vehicle]      │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ AB12 CDE │ Ford Focus 2020  │ MOT: 15/06/2026 │ 45,230│    │
│  │ CD34 EFG │ VW Golf 2019     │ MOT: 22/08/2026 │ 52,100│    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                  │
│  RECENT APPOINTMENTS          RECENT INVOICES                   │
│  15 Jan - Service ✓           INV-202601-045 - £230.00 Paid    │
│  12 Dec - MOT ✓               INV-202512-123 - £180.50 Paid    │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🚗 Vehicle Management

### Vehicle List (`/vehicles`)

```
┌─────────────────────────────────────────────────────────────────┐
│  Vehicles                                      [+ Add Vehicle]  │
├─────────────────────────────────────────────────────────────────┤
│  [🔍 Search by registration, make, model...]                    │
│                                                                  │
│  VEHICLE TABLE                                                  │
│  ┌────────┬────────────────┬──────────────┬──────────┬────────┐│
│  │  Reg   │    Vehicle     │   Customer   │ MOT Due  │ Action ││
│  ├────────┼────────────────┼──────────────┼──────────┼────────┤│
│  │ UK     │ Ford Focus     │ John Smith   │15/06/26  │[View]  ││
│  │AB12CDE │ 2020 Blue      │07700 900000  │🟢        │[Edit]  ││
│  │        │ Petrol         │              │          │        ││
│  ├────────┼────────────────┼──────────────┼──────────┼────────┤│
│  │ UK     │ BMW X5         │ Sarah Jones  │02/02/26  │[View]  ││
│  │CD34EFG │ 2018 Black     │07800 800000  │🟡        │[Edit]  ││
│  │        │ Diesel         │              │30 days   │        ││
│  └────────┴────────────────┴──────────────┴──────────┴────────┘│
│                                                                  │
│  Color codes: 🟢 Valid | 🟡 Due Soon | 🔴 Overdue              │
└─────────────────────────────────────────────────────────────────┘
```

### Add Vehicle Form (`/vehicles/create`)

```
┌─────────────────────────────────────────────────────────────────┐
│  Add New Vehicle                                                │
├─────────────────────────────────────────────────────────────────┤
│  DVLA LOOKUP (Blue Background)                                  │
│  ┌────────────────────────────────────────┬──────────┐         │
│  │ Registration: [AB12CDE_______________] │ [Lookup] │         │
│  └────────────────────────────────────────┴──────────┘         │
│  Enter UK registration to auto-populate details                │
│                                                                  │
│  Customer: [Select customer ▼                       ]          │
│                                                                  │
│  VEHICLE DETAILS                                                │
│  Registration*: [AB12CDE___]  VIN: [WBA123456789_____]        │
│  Make*: [Ford_________]       Model*: [Focus__________]        │
│  Year: [2020___]              Color: [Blue___________]         │
│  Fuel: [Petrol ▼]             Trans: [Manual ▼]               │
│  Engine: [1600____] cc        Mileage*: [45230_____]          │
│  MOT Due: [2026-06-15]        Tax Due: [2026-04-01]           │
│                                                                  │
│  Notes: [________________________________]                     │
│         [________________________________]                     │
│                                                                  │
│                              [Cancel] [Add Vehicle]             │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📅 Appointment System

### Appointment Calendar (`/appointments`)

```
┌─────────────────────────────────────────────────────────────────┐
│  Appointments                              [+ New Appointment]  │
├─────────────────────────────────────────────────────────────────┤
│  [🔍 Search appointments...]                                    │
│                                                                  │
│  [All] [Pending] [Confirmed] [Completed] [Cancelled]           │
│                                                                  │
│  APPOINTMENTS TABLE                                             │
│  ┌──────────┬────────────┬──────────┬────────────┬──────────┐  │
│  │   Date   │  Customer  │ Vehicle  │  Service   │  Status  │  │
│  ├──────────┼────────────┼──────────┼────────────┼──────────┤  │
│  │ 27 Jan   │ John Smith │ AB12CDE  │ Service    │🟢        │  │
│  │ 09:00    │ 07700...   │Ford Focus│ 90 min     │Confirmed │  │
│  ├──────────┼────────────┼──────────┼────────────┼──────────┤  │
│  │ 27 Jan   │ Sarah Jones│ CD34EFG  │ MOT Test   │🟡        │  │
│  │ 10:30    │ 07800...   │BMW X5    │ 60 min     │Pending   │  │
│  ├──────────┼────────────┼──────────┼────────────┼──────────┤  │
│  │ 27 Jan   │ Mike Brown │ EF56GHI  │ Repair     │🟢        │  │
│  │ 14:00    │ 07900...   │Audi A4   │ 120 min    │Confirmed │  │
│  └──────────┴────────────┴──────────┴────────────┴──────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔧 Job Card System

### Job Card List (`/job-cards`)

```
┌─────────────────────────────────────────────────────────────────┐
│  Job Cards                                   [+ New Job Card]   │
├─────────────────────────────────────────────────────────────────┤
│  [All] [Open] [In Progress] [Awaiting Parts] [Completed]       │
│                                                                  │
│  JOB CARDS (2 per row)                                          │
│  ┌──────────────────────────┐ ┌──────────────────────────┐    │
│  │ JOB-2026-00123  🔵       │ │ JOB-2026-00124  🔴       │    │
│  │ 27 Jan 2026 09:00        │ │ 26 Jan 2026 14:30        │    │
│  │ In Progress  High        │ │ Awaiting Parts  Urgent   │    │
│  │                          │ │                          │    │
│  │ 👤 John Smith            │ │ 👤 Sarah Jones           │    │
│  │ 🚗 AB12CDE Ford Focus    │ │ 🚗 CD34EFG BMW X5        │    │
│  │ 👨‍🔧 Mike (Technician)     │ │ 👨‍🔧 Dave (Technician)     │    │
│  │                          │ │                          │    │
│  │ Complaint:               │ │ Complaint:               │    │
│  │ "Strange noise from..."  │ │ "Engine warning light"   │    │
│  │                          │ │                          │    │
│  │ Services: 2 │ Parts: 3   │ │ Services: 1 │ Parts: 0   │    │
│  │ Total: £485.50           │ │ Total: £125.00           │    │
│  │                          │ │                          │    │
│  │ 🚨 Customer Waiting      │ │ 🔑 Courtesy Car          │    │
│  │ 📅 Due: 27 Jan 17:00     │ │                          │    │
│  │                          │ │                          │    │
│  │     [View Job Card]      │ │     [View Job Card]      │    │
│  └──────────────────────────┘ └──────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

---

## 💰 Invoice System

### Invoice List (`/invoices`)

```
┌─────────────────────────────────────────────────────────────────┐
│  Invoices                                      [+ New Invoice]  │
├─────────────────────────────────────────────────────────────────┤
│  [All] [Draft] [Sent] [Paid] [Overdue]                         │
│                                                                  │
│  INVOICES TABLE                                                 │
│  ┌──────────┬──────────┬────────┬────────┬──────┬────────────┐ │
│  │ Invoice# │ Customer │  Date  │ Amount │ Paid │   Status   │ │
│  ├──────────┼──────────┼────────┼────────┼──────┼────────────┤ │
│  │INV-26-01-│John Smith│27/01/26│£485.50 │£485  │🟢 Paid     │ │
│  │0045      │AB12CDE   │Due:    │        │      │            │ │
│  │          │          │26/02/26│        │      │[View][PDF] │ │
│  ├──────────┼──────────┼────────┼────────┼──────┼────────────┤ │
│  │INV-26-01-│Sarah     │25/01/26│£125.00 │£0.00 │🔴 Overdue  │ │
│  │0044      │Jones     │Due:    │        │      │            │ │
│  │          │CD34EFG   │24/02/26│        │      │[View][PDF] │ │
│  ├──────────┼──────────┼────────┼────────┼──────┼────────────┤ │
│  │INV-26-01-│Mike Brown│27/01/26│£230.00 │£100  │🟡 Partial  │ │
│  │0046      │EF56GHI   │Due:    │        │Bal:  │            │ │
│  │          │          │26/02/26│        │£130  │[View][PDF] │ │
│  └──────────┴──────────┴────────┴────────┴──────┴────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Color Coding System

Throughout the interface, you'll see consistent color coding:

### Status Colors
- **🟢 Green** - Completed, Paid, Confirmed, Valid
- **🔵 Blue** - In Progress, Sent, Active
- **🟡 Yellow** - Pending, Due Soon, Partial Payment, Awaiting
- **🟠 Orange** - High Priority, Awaiting Approval
- **🔴 Red** - Urgent, Overdue, Failed, Cancelled
- **⚪ Gray** - Draft, Inactive

### Priority Levels
- **🔴 Urgent** - Red badge
- **🟠 High** - Orange badge
- **⚪ Normal** - Gray badge
- **🔵 Low** - Blue badge

---

## 📱 Mobile View

On mobile devices, the interface adapts:

```
┌─────────────────┐
│ ≡  UK Garage   │  ← Hamburger menu
├─────────────────┤
│                 │
│  Statistics     │  ← Stack vertically
│  (Full width)   │
│                 │
│  Appointments   │  ← One column
│  (Full width)   │
│                 │
│  Job Cards      │  ← One column
│  (Full width)   │
│                 │
│  [+ Quick Act]  │  ← Floating action
└─────────────────┘
```

---

## 🎯 Key Features Visual Summary

### Dashboard Features
- ✅ Real-time statistics
- ✅ Today's schedule
- ✅ Active jobs overview
- ✅ Alert notifications
- ✅ Quick action buttons

### Form Features
- ✅ Auto-complete fields
- ✅ Dropdown selectors
- ✅ Date/time pickers
- ✅ Real-time validation
- ✅ Success/error messages

### Table Features
- ✅ Sortable columns
- ✅ Search functionality
- ✅ Filter tabs
- ✅ Pagination
- ✅ Row actions (view/edit/delete)

### Design Features
- ✅ Gradient headers
- ✅ Card-based layouts
- ✅ Icon indicators
- ✅ Hover effects
- ✅ Smooth animations
- ✅ Responsive design

---

*This visual guide shows the general layout. Actual design may vary slightly based on screen size and content.*
