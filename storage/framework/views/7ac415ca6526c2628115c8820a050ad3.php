

<?php $__env->startSection('title', 'MOT Expiry Reminder - Doyen Auto Services'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($customerName); ?>,
    </div>

    <div class="alert alert-warning">
        <strong>⚠️ MOT Expiry Notice</strong><br>
        Your vehicle's MOT is due to expire soon. Book your test today to avoid driving illegally!
    </div>

    <div class="info-box">
        <h3>🚗 Vehicle Information</h3>
        <div class="info-row">
            <span class="info-label">Registration:</span>
            <span class="info-value"><strong><?php echo e(strtoupper($vehicleReg)); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($vehicleMake); ?> <?php echo e($vehicleModel); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">MOT Expiry Date:</span>
            <span class="info-value" style="color: #dc2626;"><strong><?php echo e($expiryDate); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Days Remaining:</span>
            <span class="info-value" style="color: <?php echo e($isUrgent ? '#dc2626' : '#d97706'); ?>;"><strong><?php echo e($daysRemaining); ?> days</strong></span>
        </div>
    </div>

    <div class="info-box" style="background: #dcfce7; border-left-color: #16a34a;">
        <h3 style="color: #166534;">💰 Special MOT Offer</h3>
        <p style="margin: 0; font-size: 24px; color: #166534;">
            <strong>MOT Test - Only £40</strong>
        </p>
        <p style="margin: 5px 0 0; color: #166534;">
            While you wait service • DVSA Approved • Expert Technicians
        </p>
    </div>

    <div class="alert alert-info">
        <strong>📅 Book Your MOT Today:</strong><br>
        • Test while you wait (approx. 45 minutes)<br>
        • Free retest within 10 working days if needed<br>
        • Competitive repair prices if work is required<br>
        • Courtesy car available on request<br>
        • Reminder service - we'll remind you next year too!
    </div>

    <div style="text-align: center;">
        <a href="<?php echo e(url('/book')); ?>" class="button">Book MOT Test Now</a>
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        <strong>Important:</strong> It's illegal to drive without a valid MOT certificate (unless you're driving to a pre-booked MOT test).
        You could face a fine of up to £1,000 and your insurance may be invalidated.
    </p>

    <div class="divider"></div>

    <p style="text-align: center; color: #64748b; font-size: 13px;">
        Prefer to book over the phone? Call us on <a href="tel:+441414820726" style="color: #3b82f6;">+44 141 482 0726</a><br>
        Monday - Friday: 8AM - 6PM | Saturday: 9AM - 2PM
    </p>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\mot-reminder.blade.php ENDPATH**/ ?>