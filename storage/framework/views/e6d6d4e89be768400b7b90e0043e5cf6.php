

<?php $__env->startSection('title', 'Appointment Confirmed - Doyen Auto Services'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($customer->full_name); ?>,
    </div>

    <div class="alert alert-success">
        <strong>✅ Appointment Confirmed!</strong><br>
        Your booking has been reviewed and confirmed by our team. We look forward to seeing you!
    </div>

    <p>
        Thank you for choosing Doyen Auto Services. Everything is set — please see your confirmed appointment details below.
    </p>

    <div class="info-box">
        <h3>📋 Booking Details</h3>
        <div class="info-row">
            <span class="info-label">Reference Number:</span>
            <span class="info-value"><strong><?php echo e($appointment->reference_number); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Service Type:</span>
            <span class="info-value"><?php echo e(ucfirst(str_replace('_', ' ', $appointment->appointment_type))); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Time:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($appointment->scheduled_date)->format('l, F j, Y \a\t g:i A')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Duration:</span>
            <span class="info-value"><?php echo e($appointment->duration_minutes); ?> minutes</span>
        </div>
        <?php if($appointment->description): ?>
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value"><?php echo e($appointment->description); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-box">
        <h3>🚗 Vehicle Information</h3>
        <div class="info-row">
            <span class="info-label">Registration:</span>
            <span class="info-value"><strong><?php echo e(strtoupper($vehicle->registration_number)); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($vehicle->make); ?> <?php echo e($vehicle->model); ?> (<?php echo e($vehicle->year); ?>)</span>
        </div>
        <?php if($vehicle->color): ?>
        <div class="info-row">
            <span class="info-label">Color:</span>
            <span class="info-value"><?php echo e(ucfirst($vehicle->color)); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="alert alert-info">
        <strong>📌 Important Information:</strong><br>
        • Please arrive 10 minutes before your appointment<br>
        • Bring your vehicle documents and keys<br>
        • If you need to reschedule, please call us at least 24 hours in advance<br>
        • We'll send you a reminder 24 hours before your appointment
    </div>

    <div style="text-align: center;">
        <a href="<?php echo e(url('/')); ?>" class="button">Visit Our Website</a>
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        Need to make changes to your booking? Please contact us at 
        <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', \App\Models\Setting::get('phone', '+44 141 482 0726'))); ?>" style="color: #3b82f6;"><?php echo e(\App\Models\Setting::get('phone', '+44 141 482 0726')); ?></a> or 
        <a href="mailto:<?php echo e(\App\Models\Setting::get('email', 'info@doyenautos.co.uk')); ?>" style="color: #3b82f6;"><?php echo e(\App\Models\Setting::get('email', 'info@doyenautos.co.uk')); ?></a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\appointment-confirmation.blade.php ENDPATH**/ ?>