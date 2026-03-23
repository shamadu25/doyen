

<?php $__env->startSection('title', 'Quote Approved — ' . $quote->quote_number); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        New Quote Approval — Action Required
    </div>

    <div class="alert alert-success" style="background:#dcfce7;border-left:4px solid #16a34a;color:#166534;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>✅ Customer Approved Quote <?php echo e($quote->quote_number); ?></strong><br>
        <?php echo e($quote->customer->full_name); ?> has approved the quote. Please proceed with scheduling the work.
    </div>

    <div class="info-box">
        <h3>📋 Quote Details</h3>
        <div class="info-row">
            <span class="info-label">Quote Number:</span>
            <span class="info-value"><strong><?php echo e($quote->quote_number); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-value"><?php echo e($quote->customer->full_name); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value"><?php echo e($quote->customer->email); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span class="info-value"><?php echo e($quote->customer->phone); ?></span>
        </div>
        <?php if($quote->vehicle): ?>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($quote->vehicle->registration_number); ?> — <?php echo e($quote->vehicle->make); ?> <?php echo e($quote->vehicle->model); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Approved At:</span>
            <span class="info-value"><?php echo e($quote->approved_at ? $quote->approved_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Total:</span>
            <span class="info-value"><strong>£<?php echo e(number_format($quote->total_amount, 2)); ?></strong></span>
        </div>
    </div>

    <?php if($quote->appointment): ?>
    <div class="info-box">
        <h3>📅 Linked Booking</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><?php echo e($quote->appointment->reference_number); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, d F Y \a\t H:i')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <p style="margin-top:20px;">
        <a href="<?php echo e(config('app.url')); ?>/quotes/<?php echo e($quote->id); ?>" class="button" style="background:#16a34a;">
            View Quote in Dashboard
        </a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\admin\quote-approved.blade.php ENDPATH**/ ?>