<?php
/**
 * SG Survey — Email & WhatsApp automation helpers
 * --------------------------------------------------
 * Email is sent via Replit Mail (delivered to the verified Replit-account email).
 * WhatsApp is sent via CallMeBot when a CALLMEBOT_API_KEY env var is configured;
 * otherwise the booking confirmation page falls back to the wa.me auto-open flow.
 */

/**
 * Get a Replit identity token for authenticating to the connectors API.
 * Returns null on failure.
 */
function getReplitIdentityToken(): ?string {
    $hostname = getenv('REPLIT_CONNECTORS_HOSTNAME') ?: 'connectors.replit.com';
    $cmd = sprintf(
        'replit identity create --audience %s 2>&1',
        escapeshellarg('https://' . $hostname)
    );
    $token = trim((string) shell_exec($cmd));
    if ($token === '' || strpos($token, 'v2.public.') !== 0) {
        error_log('Replit identity token error: ' . substr($token, 0, 200));
        return null;
    }
    return $token;
}

/**
 * Send an email via the Replit Mail service.
 *
 * IMPORTANT PLATFORM LIMITATION:
 * The free Replit Mail service IGNORES the `to` field and ALWAYS delivers
 * to the verified Replit account email (currently pixelsubhajit@gmail.com).
 * To forward to abhijitghosh9749332827@gmail.com, set up a Gmail filter on
 * the verified inbox: subject contains "New Booking" → forward to abhijit.
 * Alternatively, swap this function to use SendGrid/Resend with an API key
 * for true multi-recipient sending.
 */
function sendBookingEmail(array $booking): array {
    $hostname = getenv('REPLIT_CONNECTORS_HOSTNAME') ?: 'connectors.replit.com';
    $token = getReplitIdentityToken();
    if (!$token) {
        return ['success' => false, 'error' => 'no_identity_token'];
    }

    // Listed for clarity; the platform currently overrides this to the verified account email.
    $recipients = [
        'abhijitghosh9749332827@gmail.com',
        'pixelsubhajit@gmail.com',
    ];

    $subject = sprintf('🆕 New Booking #%d — %s', $booking['id'], $booking['name']);
    $bookedAt = date('d M Y, h:i A', strtotime($booking['created_at'] ?? 'now'));
    $msg = trim((string) ($booking['message'] ?? '')) ?: 'No additional message';

    $text = "NEW BOOKING — SG SURVEY\n"
          . "================================\n\n"
          . "Booking ID:      #{$booking['id']}\n"
          . "Name:            {$booking['name']}\n"
          . "Phone:           {$booking['phone']}\n"
          . "Location:        {$booking['location']}\n"
          . "Survey Type:     {$booking['survey_type']}\n"
          . "Preferred Date:  {$booking['preferred_date']}\n"
          . "Booked At:       {$bookedAt}\n\n"
          . "Customer Message:\n{$msg}\n\n"
          . "================================\n"
          . "Reply: tel:{$booking['phone']} | wa.me/91{$booking['phone']}\n"
          . "Sent automatically from SG Survey website.";

    $html = '
<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;max-width:600px;margin:0 auto;background:#f6f8fb;padding:24px;color:#1a1a2e;">
  <div style="background:linear-gradient(135deg,#0d2040,#1e4a7f);color:#fff;padding:24px 28px;border-radius:14px 14px 0 0;">
    <div style="font-size:12px;letter-spacing:2px;color:#e8c96e;font-weight:600;">SG SURVEY</div>
    <h1 style="margin:6px 0 0;font-size:22px;font-weight:600;">🆕 New Booking Received</h1>
  </div>
  <div style="background:#fff;padding:24px 28px;border-radius:0 0 14px 14px;box-shadow:0 6px 20px rgba(0,0,0,0.06);">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <tr><td style="padding:10px 0;color:#888;width:140px;">Booking ID</td><td style="font-weight:600;">#' . htmlspecialchars((string)$booking['id']) . '</td></tr>
      <tr><td style="padding:10px 0;color:#888;border-top:1px solid #eee;">Name</td><td style="font-weight:600;border-top:1px solid #eee;">' . htmlspecialchars($booking['name']) . '</td></tr>
      <tr><td style="padding:10px 0;color:#888;border-top:1px solid #eee;">Phone</td><td style="border-top:1px solid #eee;"><a href="tel:' . htmlspecialchars($booking['phone']) . '" style="color:#1e4a7f;text-decoration:none;font-weight:600;">' . htmlspecialchars($booking['phone']) . '</a></td></tr>
      <tr><td style="padding:10px 0;color:#888;border-top:1px solid #eee;">Location</td><td style="border-top:1px solid #eee;">' . htmlspecialchars($booking['location']) . '</td></tr>
      <tr><td style="padding:10px 0;color:#888;border-top:1px solid #eee;">Survey Type</td><td style="border-top:1px solid #eee;">' . htmlspecialchars($booking['survey_type']) . '</td></tr>
      <tr><td style="padding:10px 0;color:#888;border-top:1px solid #eee;">Preferred Date</td><td style="border-top:1px solid #eee;">' . htmlspecialchars($booking['preferred_date']) . '</td></tr>
      <tr><td style="padding:10px 0;color:#888;border-top:1px solid #eee;">Booked At</td><td style="border-top:1px solid #eee;">' . htmlspecialchars($bookedAt) . '</td></tr>
    </table>
    <div style="margin-top:18px;padding:14px 16px;background:#f9f6ee;border-left:3px solid #c9a84c;border-radius:6px;">
      <div style="font-size:11px;color:#888;letter-spacing:1px;font-weight:600;margin-bottom:6px;">CUSTOMER MESSAGE</div>
      <div style="font-size:14px;line-height:1.6;">' . nl2br(htmlspecialchars($msg)) . '</div>
    </div>
    <div style="margin-top:24px;text-align:center;">
      <a href="https://wa.me/91' . urlencode($booking['phone']) . '" style="display:inline-block;background:#25d366;color:#fff;padding:12px 22px;border-radius:30px;text-decoration:none;font-weight:600;font-size:14px;margin:4px;">💬 Reply on WhatsApp</a>
      <a href="tel:' . htmlspecialchars($booking['phone']) . '" style="display:inline-block;background:#1e4a7f;color:#fff;padding:12px 22px;border-radius:30px;text-decoration:none;font-weight:600;font-size:14px;margin:4px;">📞 Call Now</a>
    </div>
  </div>
  <p style="text-align:center;color:#999;font-size:12px;margin-top:18px;">Sent automatically from SG Survey website</p>
</div>';

    $payload = json_encode([
        'to'      => $recipients,
        'subject' => $subject,
        'text'    => $text,
        'html'    => $html,
    ]);

    $ch = curl_init('https://' . $hostname . '/api/v2/mailer/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Replit-Authentication: Bearer ' . $token,
        ],
        CURLOPT_TIMEOUT        => 12,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'http' => $httpCode, 'response' => json_decode($response, true)];
    }
    error_log("Email API error HTTP $httpCode: $response | curlErr: $err");
    return ['success' => false, 'http' => $httpCode, 'error' => $response ?: $err];
}

/**
 * Send a WhatsApp message via CallMeBot.
 * Requires CALLMEBOT_API_KEY env var. Owner must have activated CallMeBot on their phone first.
 * One-time setup: owner sends "I allow callmebot to send me messages" to +34 644 51 95 23 from
 * the destination phone (9749332827). CallMeBot replies with their personal API key.
 */
function sendBookingWhatsApp(array $booking, string $ownerPhone = '919749332827'): array {
    $apiKey = getenv('CALLMEBOT_API_KEY');
    if (!$apiKey) {
        return ['success' => false, 'error' => 'no_api_key', 'note' => 'CallMeBot not configured'];
    }

    $bookedAt = date('d M Y, h:i A', strtotime($booking['created_at'] ?? 'now'));
    $msg = "🆕 NEW BOOKING — SG SURVEY\n"
         . "━━━━━━━━━━━━━━━━━━\n\n"
         . "Booking ID: #{$booking['id']}\n"
         . "Name: {$booking['name']}\n"
         . "Phone: {$booking['phone']}\n"
         . "Location: {$booking['location']}\n"
         . "Survey Type: {$booking['survey_type']}\n"
         . "Preferred Date: {$booking['preferred_date']}\n"
         . "Booked At: {$bookedAt}\n\n"
         . (trim((string)($booking['message'] ?? '')) !== '' ? "Message: {$booking['message']}\n\n" : '')
         . "Sent automatically from sgsurvey.com";

    $url = 'https://api.callmebot.com/whatsapp.php?'
         . http_build_query([
             'phone'  => $ownerPhone,
             'text'   => $msg,
             'apikey' => $apiKey,
         ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300 && stripos((string)$response, 'queued') !== false) {
        return ['success' => true, 'http' => $httpCode];
    }
    error_log("CallMeBot WA error HTTP $httpCode: " . substr((string)$response, 0, 200));
    return ['success' => false, 'http' => $httpCode, 'error' => substr((string)$response, 0, 200)];
}
