

<?php $__env->startSection('title', 'Your Quote ' . $quote->quote_number . ' — Review & Approve'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($quote->customer->full_name); ?>,
    </div>

    <p>
        Thank you for choosing <strong><?php echo e(config('app.garage_name', 'Doyen Auto Services')); ?></strong>!
        We have prepared a quote for the work requested on your vehicle.
        Please review the details below and click the button to approve or decline.
    </p>

    <?php if($quote->appointment): ?>
    <div class="info-box">
        <h3>📅 Booking Reference</h3>
        <div class="info-row">
            <span class="info-label">Reference:</span>
            <span class="info-value"><strong><?php echo e($quote->appointment->reference_number); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Requested Date:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($quote->appointment->scheduled_date)->format('l, F j, Y \a\t g:i A')); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="info-box">
        <h3>🔧 Quote Summary — <?php echo e($quote->quote_number); ?></h3>
        <div class="info-row">
            <span class="info-label">Quote Date:</span>
            <span class="info-value"><?php echo e($quote->quote_date->format('F j, Y')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Valid Until:</span>
            <span class="info-value"><?php echo e($quote->valid_until->format('F j, Y')); ?></span>
        </div>
        <?php if($quote->vehicle): ?>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($quote->vehicle->registration_number); ?> — <?php echo e($quote->vehicle->make); ?> <?php echo e($quote->vehicle->model); ?></span>
        </div>
        <?php endif; ?>
        <?php if($quote->description): ?>
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value"><?php echo e($quote->description); ?></span>
        </div>
        <?php endif; ?>
    </div>

    
    <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px;">
        <thead>
            <tr style="background:#1e40af;color:#fff;">
                <th style="padding:10px;text-align:left;">Description</th>
                <th style="padding:10px;text-align:center;">Qty</th>
                <th style="padding:10px;text-align:right;">Unit Price</th>
                <th style="padding:10px;text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $quote->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr style="background:<?php echo e($i % 2 === 0 ? '#f8fafc' : '#ffffff'); ?>;border-bottom:1px solid #e5e7eb;">
                <td style="padding:9px 10px;">
                    <span style="font-size:11px;color:#64748b;text-transform:uppercase;"><?php echo e($item->item_type); ?></span><br>
                    <?php echo e($item->description); ?>

                </td>
                <td style="padding:9px 10px;text-align:center;"><?php echo e($item->quantity); ?></td>
                <td style="padding:9px 10px;text-align:right;">£<?php echo e(number_format($item->unit_price, 2)); ?></td>
                <td style="padding:9px 10px;text-align:right;">£<?php echo e(number_format($item->total_price, 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <?php if($quote->discount_amount > 0): ?>
            <tr>
                <td colspan="3" style="padding:8px 10px;text-align:right;color:#64748b;">Subtotal:</td>
                <td style="padding:8px 10px;text-align:right;">£<?php echo e(number_format($quote->subtotal, 2)); ?></td>
            </tr>
            <tr>
                <td colspan="3" style="padding:8px 10px;text-align:right;color:#dc2626;">Discount (<?php echo e($quote->discount_percentage); ?>%):</td>
                <td style="padding:8px 10px;text-align:right;color:#dc2626;">−£<?php echo e(number_format($quote->discount_amount, 2)); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="3" style="padding:8px 10px;text-align:right;color:#64748b;">VAT (<?php echo e($quote->vat_rate); ?>%):</td>
                <td style="padding:8px 10px;text-align:right;">£<?php echo e(number_format($quote->vat_amount, 2)); ?></td>
            </tr>
            <tr style="background:#1e40af;color:#fff;font-weight:700;font-size:16px;">
                <td colspan="3" style="padding:12px 10px;text-align:right;">Total:</td>
                <td style="padding:12px 10px;text-align:right;">£<?php echo e(number_format($quote->total_amount, 2)); ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if($quote->notes): ?>
    <div class="info-box">
        <h3>📝 Notes</h3>
        <p style="margin:0;"><?php echo e($quote->notes); ?></p>
    </div>
    <?php endif; ?>

    <div style="text-align:center;margin:30px 0;">
        <p style="color:#374151;margin-bottom:15px;">To review and respond to this quote, click the button below:</p>
        <a href="<?php echo e($reviewUrl); ?>" class="button" style="background:#16a34a;padding:14px 40px;font-size:16px;">
            ✅ Review &amp; Approve Quote
        </a>
        <p style="font-size:13px;color:#64748b;margin-top:12px;">
            This link is unique to you. Do not share it.<br>
            Quote valid until <strong><?php echo e($quote->valid_until->format('F j, Y')); ?></strong>.
        </p>
    </div>

    <div class="divider"></div>
    <p style="font-size:13px;color:#64748b;">
        If the button above does not work, copy and paste this link into your browser:<br>
        <a href="<?php echo e($reviewUrl); ?>" style="color:#3b82f6;word-break:break-all;"><?php echo e($reviewUrl); ?></a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\quote-review-request.blade.php ENDPATH**/ ?>