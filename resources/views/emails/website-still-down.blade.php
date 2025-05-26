<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Still Down Alert</title>
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
        .alert-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .alert-title {
            color: #dc3545;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .alert-subtitle {
            color: #6c757d;
            font-size: 16px;
        }
        .details {
            background-color: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #dc3545;
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
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }
        .duration-highlight {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
        }
        .action-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .notification-count {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-icon">🔴</div>
            <h1 class="alert-title">Website Still Down Alert</h1>
            <p class="alert-subtitle">Your website continues to be offline and not responding to our monitoring checks.</p>
        </div>

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Website:</span>
                <span class="detail-value">{{ $website->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">URL:</span>
                <span class="detail-value">{{ $websiteUrl->url }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">First detected:</span>
                <span class="detail-value">{{ $incident->started_at->format('M j, Y H:i:s') }} UTC</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Current duration:</span>
                <span class="detail-value">{{ $incident->formatted_duration }}</span>
            </div>
        </div>

        <div class="duration-highlight">
            ⏰ Your website has been down for {{ $incident->formatted_duration }}
        </div>

        <div class="notification-count">
            📧 This is notification #{{ $incident->notification_count }} for this downtime incident
        </div>

        @if($incident->error_message)
        <div class="error-message">
            <strong>Error Details:</strong><br>
            {{ $incident->error_message }}
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ $websiteUrl->url }}" class="action-button" target="_blank">
                🔗 Check Website Now
            </a>
        </div>

        <div class="footer">
            <p><strong>What's happening?</strong></p>
            <p>We're sending you this reminder because your website has been down for more than 15 minutes. You'll continue to receive these notifications every 15 minutes until your website is back online.</p>
            
            <p><strong>Next steps:</strong></p>
            <ul style="text-align: left; display: inline-block;">
                <li>Check your server status</li>
                <li>Verify your hosting provider</li>
                <li>Check for any maintenance windows</li>
                <li>Contact your technical team if needed</li>
            </ul>
            
            <hr style="margin: 20px 0;">
            <p>This is an automated message from your Website Monitoring System.</p>
            <p>You'll receive a recovery notification when your website is back online.</p>
        </div>
    </div>
</body>
</html>
