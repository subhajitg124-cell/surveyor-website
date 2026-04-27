# 🔧 Automatic Booking Notification Setup Guide

## Overview
The SG Survey website now has **automatic booking notifications** that send to:
- **WhatsApp**: +91-9749332827
- **Email**: abhijitghosh9749332827@gmail.com

These notifications are sent **instantly** and **automatically** when someone books a survey, without any hesitation or manual intervention.

---

## ✅ What's Implemented

### 1. **Automatic Email Notifications**
- ✅ Already enabled and working
- Sends beautiful formatted emails via Replit Mail
- Contains all booking details, customer message, and action buttons
- Emails are delivered to your configured email address

### 2. **Automatic WhatsApp Notifications**
- Sends formatted WhatsApp messages to your phone
- **Currently in Fallback Mode** (provides wa.me link)
- Can be upgraded to direct API delivery (see below)

---

## 🚀 Upgrade WhatsApp to Direct Delivery (Optional but Recommended)

### Option 1: CallMeBot (Recommended - Free)

#### Setup Steps:

1. **Go to your phone and open WhatsApp**

2. **Send this message to CallMeBot:**
   - Phone Number: **+34 644 51 95 23**
   - Message Text: `I allow callmebot to send me messages`

3. **Wait for CallMeBot's Response:**
   - You'll receive an automatic reply with your **personal API key**
   - Save this key safely (format: `XXXXX-XXXXX-XXXXX-XXXXX`)

4. **Configure Replit Environment Variable:**
   - Go to your Replit project settings
   - Add new environment variable:
     - **Key**: `CALLMEBOT_API_KEY`
     - **Value**: Your API key from step 3
   - Save and restart the server

5. **Verify It's Working:**
   - Try submitting a test booking form
   - You should receive a WhatsApp message within 5 seconds
   - Check the notifications log: `notifications.log`

---

### Option 2: Twilio (Professional - Paid)

If you want a more robust service, you can use Twilio:

1. Sign up at https://www.twilio.com
2. Get WhatsApp API credentials
3. Add these environment variables to Replit:
   - `TWILIO_ACCOUNT_SID`
   - `TWILIO_AUTH_TOKEN`
   - `TWILIO_WHATSAPP_FROM`
4. Update `WHATSAPP_SERVICE` in `config.php` to `'twilio'`

---

## 📋 Current Configuration

Your system is configured as follows (see `config.php`):

```php
ADMIN_PHONE = '9749332827'           // WhatsApp destination
ADMIN_EMAIL = 'abhijitghosh9749332827@gmail.com'  // Email destination
EMAIL_SERVICE = 'replit'              // Email via Replit
WHATSAPP_SERVICE = 'callmebot'        // WhatsApp (requires API key)
```

---

## 📊 Monitoring & Troubleshooting

### Check Notification Logs
Notification events are logged in: `notifications.log`

View recent logs:
```bash
tail -f notifications.log
```

### Sample Log Entry
```
[2024-04-27 14:30:45] EMAIL | SENT | {"booking_id":5,"http":200}
[2024-04-27 14:30:46] WHATSAPP | SENT_CALLMEBOT | {"booking_id":5,"http":200}
```

### Common Issues

**❌ WhatsApp not arriving?**
- Verify CallMeBot API key is correct
- Check that you sent the setup message to CallMeBot from the destination phone
- Wait 24 hours after setup (CallMeBot activation can take time)
- Check `notifications.log` for errors

**❌ Email not arriving?**
- Emails go to the Replit account email first (pixelsubhajit@gmail.com)
- Set up Gmail filter to forward to abhijitghosh9749332827@gmail.com
- Or update email service to use your own SMTP server

**❌ Booking doesn't complete?**
- Check that db.php is working correctly
- Verify form field names match: name, phone, location, type, date, message
- Check browser console for JavaScript errors

---

## 🛡️ Security Notes

✅ **What's Protected:**
- All input is sanitized
- Database uses prepared statements (SQL injection safe)
- Phone numbers are validated (10 digits only)
- Messages are truncated safely for WhatsApp

✅ **Best Practices:**
- Never share your CallMeBot API key
- Keep environment variables secure
- Regularly check notification logs
- Monitor booking database for anomalies

---

## 📞 Support & Debugging

### Enable Debug Logging
In `config.php`, ensure:
```php
define('LOG_NOTIFICATIONS', true);
```

### View All Notifications
```bash
cat notifications.log | tail -50
```

### Test Email Only
You can temporarily disable WhatsApp in `config.php`:
```php
define('ENABLE_WHATSAPP_NOTIFICATIONS', false);
```

### Test WhatsApp Only
Temporarily disable email:
```php
define('ENABLE_EMAIL_NOTIFICATIONS', false);
```

---

## 📈 Features Included

✅ **Automatic** - No manual action needed
✅ **Non-blocking** - One service failure doesn't block the booking
✅ **Logged** - All attempts are logged for monitoring
✅ **Formatted** - Beautiful emails and WhatsApp messages
✅ **Fallback** - If API is down, provides manual wa.me link
✅ **Configurable** - Easy to enable/disable services
✅ **Multi-service** - Email + WhatsApp simultaneously

---

## 🎯 Next Steps

1. **Immediate**: The system is ready. Bookings will get an email automatically.

2. **Recommended**: Set up CallMeBot API key (5 min) for WhatsApp delivery

3. **Optional**: Monitor the logs and adjust settings as needed

4. **Advanced**: Integrate Twilio or SendGrid for even better reliability

---

## 📝 File Structure

```
├── config.php              ← Admin contact & notification settings
├── mailer.php              ← All notification logic
├── book.php                ← Booking form handler (uses mailer)
├── notifications.log       ← Auto-generated log file
└── README-NOTIFICATIONS.md ← This file
```

---

**Questions?** Check the notifications.log file for detailed error messages!
