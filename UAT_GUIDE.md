# 🎯 USER ACCEPTANCE TESTING (UAT) GUIDE
## Doyen Auto Services - Garage Management System

**Date:** February 12, 2026  
**Testing Type:** User Acceptance Testing (UAT)  
**Purpose:** Verify system meets business requirements from end-user perspective

---

## 📋 UAT OVERVIEW

### What is UAT?
User Acceptance Testing validates that the system works as expected in real-world scenarios and meets business requirements before going live.

### Who Should Perform UAT?
- Garage Manager/Owner
- Receptionist/Scheduler
- Technicians
- Admin Staff
- Actual Customers (optional)

### UAT Testing Dashboard
**Access:** `http://localhost/garage/garage/public/uat-testing-dashboard.html`

This interactive dashboard provides:
- ✅ 20 comprehensive test scenarios
- 📊 Real-time progress tracking
- 📝 Notes section for each test
- 🎯 Pass/Fail marking
- 💾 Auto-save state to browser

---

## 🎭 TEST SCENARIOS BY USER ROLE

### 1. PUBLIC USER (No Login) - 3 Scenarios

#### UAT-PUB-001: Premium Landing Page
**Objective:** Verify landing page loads correctly and is professional

**Steps:**
1. Visit: `http://localhost/garage/garage/public`
2. Check top bar shows phone: 07760 926 245
3. Verify all 10 services are listed
4. See 3 testimonials with 5-star ratings
5. Trust badges visible (DVSA, insurance, etc.)

**Expected:** Professional, trustworthy landing page with clear booking CTAs

---

#### UAT-PUB-002: Complete Booking with DVLA Auto-Lookup ⭐
**Objective:** Test complete guest booking workflow with automatic vehicle lookup

**Steps:**
1. Click "Book Online"
2. Fill customer details:
   - Name: John Doe
   - Email: john.test@example.com
   - Phone: 07123456789
3. Click "Next"
4. Enter registration: `AB12CDE`
5. **Wait 1 second - observe loading spinner**
6. **Verify vehicle details auto-fill** (make, model, year, color)
7. See green success message
8. Click "Next"
9. Select: MOT
10. Choose tomorrow's date
11. Select time: 10:00
12. Add notes: "UAT Test Booking"
13. Click "Book Appointment"

**Expected:**
- ✅ Loading spinner appears during lookup
- ✅ Success message: "Vehicle found! Details auto-filled..."
- ✅ Make, model, year, color auto-populated
- ✅ Booking confirmation page with reference number
- ✅ Database has new customer, vehicle, appointment

**Critical:** This is the MAIN feature - ensure auto-lookup works!

---

#### UAT-PUB-003: Manual Vehicle Entry (DVLA Fails)
**Objective:** Verify fallback when DVLA can't find vehicle

**Steps:**
1. Start new booking
2. Fill customer details
3. Enter registration: `ZZZZZZZ` (invalid)
4. Wait for error message
5. Manually enter: Toyota, Corolla, 2020
6. Complete booking

**Expected:** Warning message appears, manual entry allowed, booking succeeds

---

### 2. CUSTOMER MANAGEMENT - 3 Scenarios

#### UAT-CUST-001: Add New Customer
**Objective:** Create customer record

**Steps:**
1. Login to admin
2. Navigate: Customers → Add Customer
3. Fill all fields
4. Save
5. Verify in list

**Expected:** Customer created, appears in list, searchable

---

#### UAT-CUST-002: Edit Customer
**Objective:** Update customer details

**Steps:**
1. Find customer
2. Edit phone number
3. Save
4. Verify update

**Expected:** Changes persist, activity logged

---

#### UAT-CUST-003: Add Vehicle to Customer
**Objective:** Link vehicle to customer

**Steps:**
1. View customer
2. Add vehicle: BX21ABC, BMW, 320d, 2021
3. Save
4. Verify linkage

**Expected:** Vehicle shows under customer

---

### 3. SCHEDULING - 3 Scenarios

#### UAT-SCHED-001: Create Appointment
**Objective:** Schedule new appointment

**Steps:**
1. Bookings → New Appointment
2. Select customer & vehicle
3. Service type: Service
4. Set date & time
5. Save

**Expected:** Appointment created, status "pending"

---

#### UAT-SCHED-002: Calendar View
**Objective:** View appointments on calendar

**Steps:**
1. Click "Calendar View"
2. See all appointments
3. Click appointment for details
4. Navigate months

**Expected:** Calendar displays correctly, navigation smooth

---

#### UAT-SCHED-003: Convert to Job Card
**Objective:** Start work on appointment

**Steps:**
1. Find confirmed appointment
2. "Convert to Job Card"
3. Verify job card created
4. Status updated

**Expected:** Job card created with appointment details

---

### 4. TECHNICIAN WORKFLOW - 2 Scenarios

#### UAT-TECH-001: Complete Job Card
**Objective:** Record work done

**Steps:**
1. Open job card
2. Add labour: "Oil Change - 1hr - £50"
3. Add part: Oil Filter
4. Status → "In Progress"
5. Add completion notes
6. "Complete Job Card"

**Expected:** Labour/parts added, stock deducted, job ready for invoice

---

#### UAT-TECH-002: Record MOT Test ⭐
**Objective:** Test MOT system

**Steps:**
1. MOT Tests → New Test
2. Select vehicle
3. Test date: today
4. Status initially: "booked"
5. Save
6. Edit MOT
7. Result: "passed"
8. Expiry: 1 year from now
9. Mileage: 45000
10. Save

**Expected:**
- ✅ Booking created with status "booked"
- ✅ No error about missing expiry_date
- ✅ Can update to "passed"
- ✅ Vehicle MOT date updated

**Critical:** Tests recent database fixes!

---

### 5. ADMIN & BILLING - 4 Scenarios

#### UAT-ADMIN-001: Generate Invoice
**Objective:** Create invoice from job card

**Steps:**
1. Open completed job
2. "Generate Invoice"
3. Verify items included
4. Check total
5. Save

**Expected:** Invoice created with unique number

---

#### UAT-ADMIN-002: Edit & Send Invoice
**Objective:** Modify and send invoice

**Steps:**
1. Find invoice
2. Edit → Add line item
3. Total recalculates
4. Save
5. "Send Invoice"
6. Status → "sent"

**Expected:** Changes save, email queued

---

#### UAT-ADMIN-003: Record Payment
**Objective:** Mark invoice as paid

**Steps:**
1. Open invoice
2. "Record Payment"
3. Amount: full total
4. Method: Cash
5. Save
6. Status → "paid"

**Expected:** Payment recorded, invoice paid

---

#### UAT-ADMIN-004: Manage Inventory
**Objective:** Adjust stock levels

**Steps:**
1. Inventory → Find part used
2. Verify stock decreased
3. "Adjust Stock"
4. Add 10 units
5. Save

**Expected:** Stock adjusts, transaction logged

---

### 6. REPORTING - 2 Scenarios

#### UAT-REP-001: Dashboard
**Objective:** View overview

**Steps:**
1. Dashboard
2. Check stats
3. Upcoming appointments
4. Activity feed
5. Quick actions

**Expected:** Accurate data, all sections load

---

#### UAT-REP-002: Reports & Export ⭐
**Objective:** Generate business reports

**Steps:**
1. Reports
2. Period: This Month
3. View revenue chart
4. Top services table
5. Technician productivity
6. "Export CSV"
7. Download

**Expected:**
- ✅ Chart renders
- ✅ Tables populate
- ✅ CSV downloads with data

**Critical:** Tests recent reports fix!

---

## 📊 UAT ACCEPTANCE CRITERIA

### Pass Criteria
- ✅ 90% or more scenarios pass
- ✅ All critical scenarios marked ⭐ pass
- ✅ No data loss or corruption
- ✅ Performance acceptable (<3s page load)
- ✅ UI/UX is intuitive

### Critical Scenarios (Must Pass)
1. **UAT-PUB-002** - DVLA Auto-Lookup (core feature)
2. **UAT-TECH-002** - MOT Test Recording (recent fix)
3. **UAT-REP-002** - Reports Display (recent fix)

### If Scenarios Fail
- Document exact steps to reproduce
- Note error messages
- Save screenshots if possible
- Continue testing other scenarios
- Report all failures at end

---

## 📝 HOW TO USE UAT DASHBOARD

### Step 1: Open Dashboard
```
http://localhost/garage/garage/public/uat-testing-dashboard.html
```

### Step 2: Select Role Tab
- Public User
- Customer Management
- Scheduler
- Technician
- Admin
- Reports

### Step 3: For Each Scenario
1. Read scenario title and objective
2. Click status badge to mark "Testing"
3. Follow test steps exactly
4. Click test links to open relevant pages
5. Verify expected results
6. Mark "Pass" or "Fail"
7. Add notes about issues or observations

### Step 4: Track Progress
- Dashboard shows: Total, Passed, Failed, Pending
- Progress bar fills as you complete tests
- Notes auto-save to browser

---

## 🎯 UAT CHECKLIST

Before starting:
- [ ] System fully deployed (local or staging)
- [ ] Database has sample data
- [ ] Test user accounts created
- [ ] Browser console open (F12) to catch errors

During testing:
- [ ] Test in Chrome, Firefox, Edge
- [ ] Test on mobile/tablet (responsive)
- [ ] Note any slow pages
- [ ] Check for broken links
- [ ] Verify all buttons work

After testing:
- [ ] Export test notes
- [ ] Create issues list
- [ ] Prioritize fixes (critical/minor)
- [ ] Sign-off or request fixes

---

## 🚨 KNOWN ISSUES (Non-Critical)

These TypeScript warnings exist but don't affect functionality:
- AuthenticatedLayout.vue - Type inference (cosmetic)
- Pagination.vue - Null check (handled safely)
- Invoices/Edit.vue - Index type (works correctly)

---

## ✅ UAT SIGN-OFF

Once testing complete:

**UAT Performed By:** ________________________  
**Date:** _____________  
**Total Scenarios:** 20  
**Passed:** _____  
**Failed:** _____  
**Pass Rate:** ____%  

**Critical Scenarios Status:**
- [ ] UAT-PUB-002 (DVLA Auto-Lookup) - PASS / FAIL
- [ ] UAT-TECH-002 (MOT Recording) - PASS / FAIL
- [ ] UAT-REP-002 (Reports) - PASS / FAIL

**Overall Assessment:**
- [ ] ✅ APPROVED - Ready for production
- [ ] ⚠️ APPROVED WITH MINOR ISSUES - Deploy with known issues
- [ ] ❌ REJECTED - Critical fixes required

**Comments:**
_____________________________________________
_____________________________________________
_____________________________________________

**Signature:** ____________________  
**Date:** ____________________

---

## 📞 SUPPORT

**Technical Issues?**
- Check browser console (F12)
- Review: `storage/logs/laravel.log`
- Run: `php comprehensive-test.php`

**Questions?**
Contact system administrator or refer to documentation in project root.

---

**Ready to Start?**
Open: `http://localhost/garage/garage/public/uat-testing-dashboard.html`

**Estimated Time:** 2-3 hours for complete UAT
