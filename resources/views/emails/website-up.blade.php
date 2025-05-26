<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Website Recovered</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .success-title {
            color: #28a745;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .website-info {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .info-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .downtime-summary {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1 class="success-title">Website Recovered</h1>
        </div>

        <p>Great news! Your website is back online and responding normally to our monitoring checks.</p>

        <div class="website-info">
            <div class="info-row">
                <span class="label">Website:</span>
                <span class="value">{{ $website->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">URL:</span>
                <span class="value">{{ $websiteUrl->url }}</span>
            </div>
            <div class="info-row">
                <span class="label">Recovered at:</span>
                <span class="value">{{ $incident->ended_at->format('M j, Y H:i:s T') }}</span>
            </div>
        </div>

        <div class="downtime-summary">
            <h3 style="margin-top: 0; color: #856404;">Downtime Summary</h3>
            <div class="info-row">
                <span class="label">Started:</span>
                <span class="value">{{ $incident->started_at->format('M j, Y H:i:s T') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Duration:</span>
                <span class="value">{{ $incident->formatted_duration }}</span>
            </div>
        </div>

        <p>We will continue monitoring your website to ensure it remains online.</p>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/website-monitoring" class="btn">View Dashboard</a>
        </div>

        <div class="footer">
            <p>This recovery notification was sent by RedAlerts Website Monitoring</p>
            <p>Thank you for using our monitoring service!</p>
        </div>
    </div>
</body>
</html>
