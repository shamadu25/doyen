<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Doyen Auto Services'); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .email-header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            color: #1e40af;
            margin-bottom: 15px;
        }
        .info-box {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px;
            color: #1e40af;
            font-size: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #64748b;
        }
        .info-value {
            color: #1e293b;
            text-align: right;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background: #2563eb;
        }
        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e5e7eb;
        }
        .footer-contact {
            margin: 10px 0;
        }
        .footer-contact a {
            color: #3b82f6;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 20px 0;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a;
        }
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .info-row {
                flex-direction: column;
            }
            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🔧 DOYEN AUTO SERVICES</h1>
            <p>Premium Garage Services in the UK</p>
        </div>
        
        <div class="email-body">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
        
        <div class="footer">
            <div class="footer-contact">
                <strong>Contact Us:</strong><br>
                📞 <a href="tel:+447760926245">07760 926 245</a><br>
                📧 <a href="mailto:info@doyenautos.co.uk">info@doyenautos.co.uk</a><br>
                📍 Unit 5, Auto Park, London, UK
            </div>
            <div class="divider"></div>
            <p style="margin: 10px 0 0;">
                © <?php echo e(date('Y')); ?> Doyen Auto Services. All rights reserved.<br>
                This email was sent to you because you made a booking or have an account with us.
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views/emails/layout.blade.php ENDPATH**/ ?>