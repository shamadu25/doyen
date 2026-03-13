<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garage Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1 {
            color: #333;
            font-size: 3em;
            margin-bottom: 20px;
            font-weight: 700;
        }
        p {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 40px;
            font-size: 1.1em;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }
        .features {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            text-align: left;
        }
        .feature {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .feature-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .feature-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .feature-desc {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Garage Management</h1>
        <p>Complete solution for managing your garage operations, from appointments to invoicing.</p>
        
        <div class="buttons">
            <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Login</a>
            <a href="<?php echo e(route('register')); ?>" class="btn btn-secondary">Register</a>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">📅</div>
                <div class="feature-title">Appointments</div>
                <div class="feature-desc">Easy booking system</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🔧</div>
                <div class="feature-title">Job Cards</div>
                <div class="feature-desc">Track all services</div>
            </div>
            <div class="feature">
                <div class="feature-icon">📄</div>
                <div class="feature-title">Invoicing</div>
                <div class="feature-desc">Professional invoices</div>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Reports</div>
                <div class="feature-desc">Business insights</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\welcome.blade.php ENDPATH**/ ?>