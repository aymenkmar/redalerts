<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Website Down Alert</title>
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
        .alert-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .alert-title {
            color: #dc3545;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .website-info {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
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
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-icon">🔴</div>
            <h1 class="alert-title">Website Down Alert</h1>
        </div>

        <p>We've detected that your website is currently down and not responding to our monitoring checks.</p>

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
                <span class="label">Detected at:</span>
                <span class="value">{{ $incident->started_at->format('M j, Y H:i:s T') }}</span>
            </div>
            @if($incident->error_message)
            <div class="info-row">
                <span class="label">Error:</span>
                <span class="value">{{ $incident->error_message }}</span>
            </div>
            @endif
        </div>

        <p>We will continue monitoring your website and notify you when it's back online.</p>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/website-monitoring" class="btn">View Dashboard</a>
        </div>

        <div class="footer">
            <p>This alert was sent by RedAlerts Website Monitoring</p>
            <p>If you believe this is an error, please check your website manually.</p>
        </div>
    </div>
</body>
</html>
