

<?php $__env->startSection('title', 'Quote Approved — Thank You!'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($quote->customer->full_name); ?>,
    </div>

    <div class="alert" style="background:#dcfce7;border-left:4px solid #16a34a;color:#166534;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>✅ Great news! Your quote has been approved.</strong><br>
        We have received your approval for quote <?php echo e($quote->quote_number); ?>.
        Our team will be in touch shortly to confirm your booking.
    </div>

    <div class="info-box">
        <h3>📋 Quote Summary</h3>
        <div class="info-row">
            <span class="info-label">Quote Number:</span>
            <span class="info-value"><strong><?php echo e($quote->quote_number); ?></strong></span>
        </div>
        <?php if($quote->vehicle): ?>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($quote->vehicle->registration_number); ?> — <?php echo e($quote->vehicle->make); ?> <?php echo e($quote->vehicle->model); ?></span>
        </div>
        <?php endif; ?>
        <?php if($quote->description): ?>
        <div class="info-row">
            <span class="info-label">Work:</span>
            <span class="info-value"><?php echo e($quote->description); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Total Amount:</span>
            <span class="info-value"><strong style="font-size:16px;">£<?php echo e(number_format($quote->total_amount, 2)); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Approved At:</span>
            <span class="info-value"><?php echo e($quote->approved_at ? $quote->approved_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')); ?></span>
        </div>
    </div>

    <?php if($quote->appointment): ?>
    <div class="info-box">
        <h3>📅 Your Booking</h3>
        <div class="info-row">
            <span class="info-label">Booking Reference:</span>
            <span class="info-value"><strong><?php echo e($quote->appointment->reference_number); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, d F Y \a\t H:i')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value" style="color:#16a34a;font-weight:600;">✅ Confirmed</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="alert" style="background:#eff6ff;border-left:4px solid #3b82f6;color:#1e3a8a;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>📌 What happens next?</strong><br>
        • A member of our team will contact you to confirm the exact date and time<br>
        • You will receive a booking confirmation once everything is arranged<br>
        • Please keep your quote number <strong><?php echo e($quote->quote_number); ?></strong> handy
    </div>

    <p style="color:#64748b;font-size:14px;margin-top:20px;">
        Have questions? Contact us at
        <a href="tel:<?php echo e(preg_replace('/\s+/', '', config('app.garage_phone', '07760926245'))); ?>" style="color:#3b82f6;"><?php echo e(config('app.garage_phone', '07760 926 245')); ?></a> or
        <a href="mailto:<?php echo e(config('mail.from.address')); ?>" style="color:#3b82f6;"><?php echo e(config('mail.from.address')); ?></a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\quote-approved-confirmation.blade.php ENDPATH**/ ?>