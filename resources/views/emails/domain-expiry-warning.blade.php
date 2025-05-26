<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Expiry Warning</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .warning-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .warning-title {
            color: #fd7e14;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .warning-subtitle {
            color: #6c757d;
            font-size: 16px;
        }
        .details {
            background-color: #fff3cd;
            padding: 20px;
            border-left: 4px solid #fd7e14;
            margin: 20px 0;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
        }
        .urgency-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
            text-align: center;
            font-weight: bold;
        }
        .days-highlight {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
            font-size: 18px;
        }
        .action-button {
            display: inline-block;
            background-color: #fd7e14;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .notification-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="warning-icon">⚠️</div>
            <h1 class="warning-title">Domain Expiry Warning</h1>
            <p class="warning-subtitle">Your domain registration is approaching its expiration date.</p>
        </div>

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Website:</span>
                <span class="detail-value">{{ $website->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Domain:</span>
                <span class="detail-value">{{ parse_url($websiteUrl->url, PHP_URL_HOST) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Full URL:</span>
                <span class="detail-value">{{ $websiteUrl->url }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Checked on:</span>
                <span class="detail-value">{{ now()->format('M j, Y H:i:s') }} UTC</span>
            </div>
        </div>

        <div class="days-highlight">
            🗓️ {{ $daysUntilExpiry }} days until domain expiration
        </div>

        @if($daysUntilExpiry <= 7)
        <div class="urgency-box">
            🚨 URGENT: Domain expires in {{ $daysUntilExpiry }} days!
        </div>
        @elseif($daysUntilExpiry <= 15)
        <div class="urgency-box" style="background-color: #fff3cd; color: #856404; border-color: #ffeaa7;">
            ⚠️ HIGH PRIORITY: Domain expires in {{ $daysUntilExpiry }} days
        </div>
        @endif

        <div class="notification-info">
            📧 Daily reminder: You'll receive this notification every day until the domain is renewed or expires
        </div>

        <div style="text-align: center;">
            <a href="{{ $websiteUrl->url }}" class="action-button" target="_blank">
                🔗 Visit Website
            </a>
        </div>

        <div class="footer">
            <p><strong>What you need to do:</strong></p>
            <ul style="text-align: left; display: inline-block;">
                <li>Contact your domain registrar immediately</li>
                <li>Renew your domain registration</li>
                <li>Update payment information if needed</li>
                <li>Consider enabling auto-renewal</li>
            </ul>
            
            <p><strong>What happens if the domain expires?</strong></p>
            <ul style="text-align: left; display: inline-block;">
                <li>Your website will become inaccessible</li>
                <li>Email services may stop working</li>
                <li>Domain may enter redemption period</li>
                <li>Recovery costs may be significantly higher</li>
            </ul>
            
            <hr style="margin: 20px 0;">
            <p>This is an automated daily reminder from your Website Monitoring System.</p>
            <p>You'll stop receiving these notifications once the domain is renewed and has more than 30 days until expiration.</p>
        </div>
    </div>
</body>
</html>
