

<?php $__env->startSection('title', 'Appointment Reminder - Doyen Auto Services'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($customer->full_name); ?>,
    </div>

    <div class="alert alert-info">
        <strong>⏰ Appointment Reminder</strong><br>
        This is a friendly reminder about your upcoming appointment with Doyen Auto Services.
    </div>

    <p>
        We're looking forward to seeing you soon!
    </p>

    <div class="info-box">
        <h3>📋 Appointment Details</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><strong><?php echo e($appointment->reference_number); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Service:</span>
            <span class="info-value"><?php echo e(ucfirst(str_replace('_', ' ', $appointment->appointment_type))); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Time:</span>
            <span class="info-value"><strong><?php echo e(\Carbon\Carbon::parse($appointment->scheduled_date)->format('l, F j, Y \a\t g:i A')); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($vehicle->make); ?> <?php echo e($vehicle->model); ?> - <?php echo e(strtoupper($vehicle->registration_number)); ?></span>
        </div>
    </div>

    <div class="alert alert-warning">
        <strong>📌 Please Remember:</strong><br>
        • Arrive 10 minutes early<br>
        • Bring your vehicle keys and any relevant documents<br>
        • Our address: 59 Southcroft Road, Rutherglen, Glasgow, G73 1SP<br>
        • Parking available on site
    </div>

    <div style="text-align: center;">
        <a href="https://maps.google.com/?q=59+Southcroft+Road+Rutherglen+Glasgow+G73+1SP" class="button">Get Directions</a>
    </div>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        <strong>Need to reschedule?</strong><br>
        Please call us as soon as possible at <a href="tel:+441414820726" style="color: #3b82f6;">+44 141 482 0726</a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\appointment-reminder.blade.php ENDPATH**/ ?>