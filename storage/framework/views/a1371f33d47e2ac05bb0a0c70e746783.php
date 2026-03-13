

<?php $__env->startSection('title', 'Appointment Cancelled - Doyen Auto Services'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($customer->full_name); ?>,
    </div>

    <div class="alert alert-warning">
        <strong>❌ Appointment Cancelled</strong><br>
        Your appointment has been cancelled as requested.
    </div>

    <div class="info-box">
        <h3>📋 Cancelled Appointment Details</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><?php echo e($appointment->reference_number); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value"><?php echo e(ucfirst(str_replace('_', ' ', $appointment->appointment_type))); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Was Scheduled For:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($appointment->scheduled_date)->format('l, F j, Y \a\t g:i A')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($vehicle->make); ?> <?php echo e($vehicle->model); ?> - <?php echo e(strtoupper($vehicle->registration_number)); ?></span>
        </div>
    </div>

    <p>
        We're sorry we won't be able to serve you on this occasion.
    </p>

    <div class="alert alert-info">
        <strong>💡 Need to Rebook?</strong><br>
        We'd be happy to help you find another time that works better for you.
    </div>

    <div style="text-align: center;">
        <a href="<?php echo e(url('/booking')); ?>" class="button">Book New Appointment</a>
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        If you cancelled by mistake or have any questions, please contact us at 
        <a href="tel:+447760926245" style="color: #3b82f6;">07760 926 245</a> or 
        <a href="mailto:info@doyenautos.co.uk" style="color: #3b82f6;">info@doyenautos.co.uk</a>
    </p>

    <div class="divider"></div>

    <p style="text-align: center; color: #64748b; font-size: 13px;">
        Thank you for considering Doyen Auto Services. We hope to serve you in the future!
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\appointment-cancelled.blade.php ENDPATH**/ ?>