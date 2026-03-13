

<?php $__env->startSection('title', 'Register'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .register-container {
        max-width: 450px;
        margin: 80px auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .register-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .register-header h2 {
        color: #333;
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: 600;
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #e1e8ed;
        border-radius: 8px;
        font-size: 1rem;
        transition: border 0.3s;
    }
    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }
    .btn-register {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s;
    }
    .btn-register:hover {
        transform: translateY(-2px);
    }
    .login-link {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    .login-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    .error-text {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
</style>

<div class="register-container">
    <div class="register-header">
        <h2>🚗 Register</h2>
        <p style="color: #666;">Create your account</p>
    </div>

    <form method="POST" action="<?php echo e(route('register')); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required autofocus>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="error-text"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="error-text"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="error-text"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn-register">Register</button>

        <div class="login-link">
            Already have an account? <a href="<?php echo e(route('login')); ?>">Login here</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\auth\register.blade.php ENDPATH**/ ?>