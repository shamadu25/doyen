

<?php $__env->startSection('title', 'Quote Declined — ' . $quote->quote_number); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Quote Declined — <?php echo e($quote->quote_number); ?>

    </div>

    <div class="alert" style="background:#fee2e2;border-left:4px solid #dc2626;color:#991b1b;padding:12px 15px;border-radius:6px;margin:15px 0;">
        <strong>❌ Customer Declined Quote <?php echo e($quote->quote_number); ?></strong><br>
        <?php echo e($quote->customer->full_name); ?> has declined the quote. You may wish to follow up to understand their requirements.
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
            <span class="info-label">Declined At:</span>
            <span class="info-value"><?php echo e($quote->declined_at ? $quote->declined_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Quote Total:</span>
            <span class="info-value">£<?php echo e(number_format($quote->total_amount, 2)); ?></span>
        </div>
    </div>

    <p style="margin-top:20px;">
        <a href="<?php echo e(config('app.url')); ?>/quotes/<?php echo e($quote->id); ?>" class="button" style="background:#dc2626;">
            View Quote in Dashboard
        </a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\admin\quote-declined.blade.php ENDPATH**/ ?>