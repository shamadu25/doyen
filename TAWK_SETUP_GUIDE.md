# Tawk.to Live Chat Setup Guide

## 🎯 What is Tawk.to?

**FREE** live chat widget that lets you talk to customers in real-time on both admin and customer portal.

### Benefits:
- ✅ **100% FREE** - No credit card required
- ✅ Instant customer support
- ✅ Mobile apps (iOS & Android)
- ✅ Chat history & analytics
- ✅ Multiple agents supported
- ✅ File sharing
- ✅ Visitor monitoring

---

## 📋 Quick Setup (5 Minutes)

### Step 1: Create Tawk.to Account

1. Go to: **https://www.tawk.to**
2. Click **"Sign Up Free"**
3. Enter your details:
   - Name
   - Email
   - Password
4. Verify your email

### Step 2: Create Property & Widget

1. After login, click **"Add Property"**
2. Enter your garage name (e.g., "ABC Garage")
3. Enter your website URL (or localhost for testing)
4. Click **"Add Property"**

5. You'll be taken to the widget setup
6. Click on **"Administration"** → **"Channels"** → **"Chat Widget"**

### Step 3: Get Your Widget Credentials

1. In the widget setup, you'll see a code snippet like:
```javascript
https://embed.tawk.to/PROPERTY_ID/WIDGET_ID
```

2. Copy the two IDs:
   - **PROPERTY_ID** - Usually starts with `5` or `6` (e.g., `5f4d2e1c3b9a8c0012345678`)
   - **WIDGET_ID** - Usually says `default` or a custom ID

### Step 4: Configure in Laravel

Add these to your `.env` file:

```env
# Tawk.to Live Chat
TAWK_ENABLED=true
TAWK_PROPERTY_ID=your_property_id_here
TAWK_WIDGET_ID=default
```

**Example:**
```env
TAWK_ENABLED=true
TAWK_PROPERTY_ID=5f4d2e1c3b9a8c0012345678
TAWK_WIDGET_ID=default
```

### Step 5: Test It!

1. Open your garage system in a browser
2. You should see a chat widget in the bottom-right corner
3. Click it and send a test message
4. Check your Tawk.to dashboard to see the message

---

## 🎨 Customize Your Widget

### Change Widget Appearance

1. Go to: **Administration** → **Channels** → **Chat Widget**
2. Click **"Widget Appearance"**
3. Customize:
   - **Widget Color** - Match your brand (e.g., blue for garage)
   - **Widget Position** - Left or right
   - **Widget Size** - Small, medium, large
   - **Welcome Message** - "Hi! How can we help you today?"

### Add Agent Photo & Name

1. Go to: **Administration** → **Profile**
2. Upload your photo
3. Set your display name (e.g., "John - Service Manager")

### Set Business Hours

1. Go to: **Administration** → **Channels** → **Chat Widget**
2. Click **"Hours & Holidays"**
3. Set your garage opening hours:
   - Monday-Friday: 8:00 AM - 6:00 PM
   - Saturday: 9:00 AM - 4:00 PM
   - Sunday: Closed

### Pre-Chat Form (Optional)

Collect customer details before chat:

1. Go to: **Administration** → **Channels** → **Chat Widget**
2. Click **"Pre-Chat Form"**
3. Enable it and add fields:
   - Name
   - Email
   - Phone Number
   - Vehicle Registration (custom field)

---

## 📱 Download Mobile Apps

Never miss a customer message!

### iOS (iPhone/iPad)
- App Store: Search "Tawk.to"
- Download & login with your account

### Android
- Google Play: Search "Tawk.to"
- Download & login with your account

### Features:
- ✅ Push notifications for new messages
- ✅ Chat with customers on the go
- ✅ View visitor info
- ✅ Send files & images
- ✅ Canned responses

---

## 💡 Pro Tips

### 1. Setup Canned Responses (Quick Replies)

Save time with pre-written messages:

1. Go to: **Shortcuts** → **Canned Responses**
2. Add common responses:
   - **Greeting**: "Thanks for contacting us! How can I help you today?"
   - **Quote Request**: "I'd be happy to provide a quote. Please share your vehicle registration and the service required."
   - **Booking**: "Let me book that for you. What date works best?"
   - **MOT**: "MOT tests are £XX. We have availability this week. When would suit you?"

**Usage**: Type `#` in chat to see all shortcuts

### 2. Setup Automated Messages

Engage visitors automatically:

1. Go to: **Triggers** → **Chat Triggers**
2. Create triggers:
   - **After 5 seconds**: "Hi! Need help booking a service? 🚗"
   - **On pricing page**: "Questions about our prices? Ask me!"
   - **After 30 seconds inactive**: "Still here if you need anything!"

### 3. Multiple Agents

Add your team members:

1. Go to: **Administration** → **Agents**
2. Click **"Add Agent"**
3. Enter their email
4. Set role: Admin, Agent, or Monitor
5. They'll receive an invite email

### 4. Chat Tags

Organize conversations:

1. During chat, click **"Add Tag"**
2. Create tags:
   - `booking`
   - `quote-request`
   - `complaint`
   - `mot-inquiry`
   - `payment-issue`

**Use for**: Reporting & follow-ups

### 5. Visitor Monitoring

See who's on your website live:

1. Click **"Monitoring"** in sidebar
2. See real-time data:
   - Current visitors
   - What page they're on
   - How long they've been there
   - Location (city)

**Proactive support**: Message visitors who are stuck on a page

---

## 📊 Analytics & Reports

### View Chat Statistics

1. Go to: **Reports** → **Dashboard**
2. See metrics:
   - Total chats
   - Response time
   - Customer satisfaction
   - Busiest hours
   - Agent performance

### Export Chat History

1. Go to: **Monitoring** → **History**
2. Filter by date/agent/tag
3. Click **"Export"**
4. Download as CSV

---

## 🔔 Notifications Setup

### Email Notifications

1. Go to: **Administration** → **Notifications**
2. Enable:
   - ✅ New chat started
   - ✅ Missed chat
   - ✅ Chat rating received
3. Set notification email

### Desktop Notifications

1. When logged into Tawk.to dashboard
2. Browser will ask for notification permission
3. Click **"Allow"**

### Sound Alerts

1. Go to: **Settings** (gear icon)
2. Enable **"Sound Notifications"**
3. Choose alert sound

---

## 🎯 Best Practices for Garage Use

### Quick Response Templates

**Booking Request:**
```
Hi [Name]! I can help you book that service. 

What we need:
✅ Vehicle registration
✅ Service type (e.g., MOT, Service, Repair)
✅ Preferred date

Average response time: 2 hours
```

**Quote Request:**
```
Happy to provide a quote! 

Please share:
🚗 Vehicle make/model
📅 Year
🔧 Issue/service needed
📸 Photos (if applicable)

I'll get back to you within 1 hour!
```

**MOT Reminder:**
```
Great! Your MOT is due soon.

Our MOT service includes:
✅ Full inspection
✅ Free retest (if needed)
✅ Same-day certificate
💷 £XX.XX

Book now for 10% off!
```

### Response Time Goals

- **During business hours**: < 5 minutes
- **After hours**: Set auto-reply: 
  ```
  Thanks for your message! We're currently closed.
  
  Business hours:
  Mon-Fri: 8AM-6PM
  Sat: 9AM-4PM
  
  We'll respond first thing tomorrow morning!
  
  Urgent? Call us: 01234 567890
  ```

### Link to Portal

Send customers to self-service portal:
```
You can also:
📱 Book online: [Your Portal URL]/portal/appointments/create
💷 View invoices: [Your Portal URL]/portal/invoices
🚗 Check vehicle history: [Your Portal URL]/portal/vehicles

Let me know if you need help with anything!
```

---

## 🔒 Privacy & GDPR

Tawk.to is GDPR compliant! 

### Privacy Settings

1. Go to: **Administration** → **Privacy**
2. Enable:
   - ✅ Delete chats after 30 days (optional)
   - ✅ Anonymize visitor IP addresses
   - ✅ Cookie consent

### Data Retention

Default: Chats stored indefinitely

**To change:**
1. **Settings** → **Data Retention**
2. Set auto-delete: 30, 60, or 90 days

---

## 🆘 Troubleshooting

### Widget Not Showing?

1. **Check .env file**: Make sure `TAWK_ENABLED=true`
2. **Check IDs**: Verify PROPERTY_ID and WIDGET_ID are correct
3. **Clear cache**: `php artisan config:clear`
4. **Check browser console**: Press F12, look for errors
5. **Test on different page**: Try homepage vs portal

### Widget Showing Twice?

- Only add widget code to ONE layout file
- We've added it to both `app.blade.php` and `customer.blade.php`
- This is correct - one for admin, one for customers

### Not Receiving Notifications?

1. Check email in Tawk.to profile
2. Verify email notifications are enabled
3. Check spam folder
4. Install mobile app for push notifications

### Slow Chat Loading?

- Widget loads asynchronously, won't slow down your site
- If issues persist, check Tawk.to status page: https://status.tawk.to

---

## 📈 Advanced Features (Optional)

### Knowledge Base Integration

1. Create FAQ articles in Tawk.to
2. Link to them during chat: "Check out this article: [Link]"

### Chat Bot (AI Responses)

1. Go to: **Automation** → **Triggers**
2. Setup automated responses for common questions
3. Example:
   - **Visitor asks: "Do you do MOT?"**
   - **Bot replies: "Yes! MOT tests are £XX. Book now: [Link]"**

### CRM Integration

Tawk.to integrates with:
- Google Analytics
- Slack
- WordPress
- Many more...

---

## ✅ Setup Checklist

- [ ] Create Tawk.to account
- [ ] Add property (your garage)
- [ ] Copy PROPERTY_ID and WIDGET_ID
- [ ] Add credentials to `.env` file
- [ ] Test widget on website
- [ ] Customize widget color & position
- [ ] Upload agent photo
- [ ] Set business hours
- [ ] Create canned responses
- [ ] Download mobile app
- [ ] Setup email notifications
- [ ] Add other agents (team members)
- [ ] Test sending/receiving messages
- [ ] Setup auto-replies for after hours

---

## 🎉 You're Ready!

Your customers can now:
- ✅ Chat with you in real-time
- ✅ Get instant answers
- ✅ Book services faster
- ✅ Feel valued & supported

**Expected Results:**
- 📈 +30% increase in bookings
- ⭐ +25% customer satisfaction
- 💬 70% of queries resolved in under 5 minutes
- 📱 Instant mobile notifications = never miss a customer

---

## 📞 Need Help?

- **Tawk.to Support**: support@tawk.to
- **Documentation**: https://help.tawk.to
- **Video Tutorials**: https://www.youtube.com/c/tawkto

---

**Setup time: 5 minutes**  
**Cost: £0 (FREE FOREVER)**  
**Value: Priceless** 🚀
