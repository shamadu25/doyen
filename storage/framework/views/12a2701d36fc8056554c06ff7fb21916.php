

<?php $__env->startSection('title', 'Your Customer Portal Access – Doyen Auto Services'); ?>

<?php $__env->startSection('content'); ?>
<h2 style="margin: 0 0 16px; font-size: 22px; color: #1e40af;">Welcome to Your Customer Portal</h2>

<p style="margin: 0 0 12px;">Hi <?php echo e($customer->first_name); ?>,</p>

<p style="margin: 0 0 12px;">
    Your account at <strong>Doyen Auto Services</strong> is ready. Use the Customer Portal to:
</p>

<ul style="margin: 0 0 20px; padding-left: 20px; color: #555;">
    <li style="margin-bottom: 6px;">View and manage your upcoming bookings</li>
    <li style="margin-bottom: 6px;">Access your invoices and payment history</li>
    <li style="margin-bottom: 6px;">Review your full service history</li>
    <li style="margin-bottom: 6px;">See the status of your vehicles</li>
</ul>

<p style="margin: 0 0 24px;">Click the button below to set your password and access your account:</p>

<div style="text-align: center; margin: 28px 0;">
    <a href="<?php echo e($link); ?>"
        style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; letter-spacing: 0.3px;">
        Set Password &amp; Access Portal
    </a>
</div>

<p style="margin: 0 0 8px; font-size: 13px; color: #888;">
    This link will expire in 48 hours. If you did not expect this email, you can safely ignore it — no account changes will be made.
</p>

<p style="margin: 24px 0 0; font-size: 13px; color: #888;">
    If the button doesn't work, copy and paste this link into your browser:<br>
    <a href="<?php echo e($link); ?>" style="color: #2563eb; word-break: break-all;"><?php echo e($link); ?></a>
</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\customer-portal-invite.blade.php ENDPATH**/ ?>