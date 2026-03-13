

<?php $__env->startSection('title', 'Invoice Ready - Doyen Auto Services'); ?>

<?php $__env->startSection('content'); ?>
    <div class="greeting">
        Hello <?php echo e($customer->full_name); ?>,
    </div>

    <div class="alert alert-success">
        <strong>✓ Your Invoice is Ready!</strong><br>
        Your vehicle service has been completed and your invoice is now available.
    </div>

    <p>
        Thank you for choosing Doyen Auto Services for your vehicle maintenance. We appreciate your business!
    </p>

    <div class="info-box">
        <h3>📄 Invoice Details</h3>
        <div class="info-row">
            <span class="info-label">Invoice Number:</span>
            <span class="info-value"><strong><?php echo e($invoice->invoice_number); ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Invoice Date:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('F j, Y')); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Due Date:</span>
            <span class="info-value"><?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('F j, Y')); ?></span>
        </div>
    </div>

    <?php if($jobCard): ?>
    <div class="info-box">
        <h3>🔧 Service Information</h3>
        <div class="info-row">
            <span class="info-label">Job Card:</span>
            <span class="info-value"><?php echo e($jobCard->job_number); ?></span>
        </div>
        <?php if($jobCard->vehicle): ?>
        <div class="info-row">
            <span class="info-label">Vehicle:</span>
            <span class="info-value"><?php echo e($jobCard->vehicle->make); ?> <?php echo e($jobCard->vehicle->model); ?> - <?php echo e(strtoupper($jobCard->vehicle->registration_number)); ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="info-box" style="background: #f0f9ff; border-left-color: #0ea5e9;">
        <h3 style="color: #0369a1;">💰 Payment Summary</h3>
        <div class="info-row">
            <span class="info-label">Subtotal:</span>
            <span class="info-value">£<?php echo e(number_format($invoice->subtotal, 2)); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">VAT (20%):</span>
            <span class="info-value">£<?php echo e(number_format($invoice->tax_amount, 2)); ?></span>
        </div>
        <?php if($invoice->discount_amount > 0): ?>
        <div class="info-row" style="color: #16a34a;">
            <span class="info-label">Discount:</span>
            <span class="info-value">-£<?php echo e(number_format($invoice->discount_amount, 2)); ?></span>
        </div>
        <?php endif; ?>
        <div class="divider"></div>
        <div class="info-row" style="font-size: 18px;">
            <span class="info-label"><strong>Total Amount:</strong></span>
            <span class="info-value" style="color: #0369a1;"><strong>£<?php echo e(number_format($invoice->total_amount, 2)); ?></strong></span>
        </div>
        <?php if($invoice->amount_paid > 0): ?>
        <div class="info-row" style="color: #16a34a;">
            <span class="info-label">Amount Paid:</span>
            <span class="info-value">£<?php echo e(number_format($invoice->amount_paid, 2)); ?></span>
        </div>
        <div class="info-row" style="font-weight: 600;">
            <span class="info-label">Balance Due:</span>
            <span class="info-value" style="color: #dc2626;">£<?php echo e(number_format($invoice->total_amount - $invoice->amount_paid, 2)); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <?php if($invoice->payment_status !== 'paid'): ?>
    <div class="alert alert-warning">
        <strong>💳 Payment Information:</strong><br>
        Please arrange payment by <?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('F j, Y')); ?>.<br>
        We accept: Cash, Card, Bank Transfer
    </div>

    <div style="text-align: center;">
        <a href="<?php echo e(route('invoices.show', $invoice->id)); ?>" class="button">View & Pay Invoice</a>
    </div>
    <?php else: ?>
    <div class="alert alert-success">
        <strong>✓ Payment Received!</strong><br>
        Thank you for your payment. This invoice has been marked as paid.
    </div>
    <?php endif; ?>

    <p style="margin-top: 30px; color: #64748b; font-size: 14px;">
        If you have any questions about this invoice, please don't hesitate to contact us at 
        <a href="tel:+447760926245" style="color: #3b82f6;">07760 926 245</a> or 
        <a href="mailto:info@doyenautos.co.uk" style="color: #3b82f6;">info@doyenautos.co.uk</a>
    </p>

    <div class="divider"></div>

    <p style="text-align: center; color: #64748b; font-size: 12px;">
        <strong>Payment Terms:</strong> Payment due within <?php echo e($invoice->payment_terms ?? 7); ?> days<br>
        <strong>Bank Details:</strong> Sort: 00-00-00 | Account: 12345678 | Ref: <?php echo e($invoice->invoice_number); ?>

    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\invoice-created.blade.php ENDPATH**/ ?>