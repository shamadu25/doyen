<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo e($invoice->invoice_number); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company-info h1 { font-size: 22px; color: #1e40af; margin-bottom: 4px; }
        .company-info p { font-size: 11px; color: #666; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 28px; color: #1e40af; letter-spacing: 2px; }
        .invoice-title p { font-size: 11px; color: #666; margin-top: 4px; }
        .bill-to { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 24px; }
        .bill-to .label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 1px; margin-bottom: 6px; }
        .bill-to .name { font-weight: 600; font-size: 14px; }
        .vehicle-info { margin-bottom: 24px; font-size: 11px; color: #666; }
        .vehicle-info strong { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead th { background: #1e40af; color: white; padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        thead th:last-child, thead th:nth-child(3), thead th:nth-child(4) { text-align: right; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; }
        tbody td:last-child, tbody td:nth-child(3), tbody td:nth-child(4) { text-align: right; }
        .totals { float: right; width: 260px; }
        .totals table { margin-bottom: 0; }
        .totals td { padding: 6px 12px; border: none; }
        .totals .subtotal td { border-bottom: none; }
        .totals .total td { border-top: 2px solid #1e40af; font-size: 16px; font-weight: 700; color: #1e40af; }
        .totals .label { text-align: left; color: #666; }
        .totals .amount { text-align: right; font-weight: 600; }
        .notes { clear: both; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        .notes .label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 1px; margin-bottom: 4px; }
        .notes p { font-size: 11px; color: #666; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <table style="margin-bottom: 40px; border: none;">
        <tr>
            <td style="border: none; padding: 0; width: 50%; vertical-align: top;">
                <div class="company-info">
                    <h1>🔧 Doyen Auto Services</h1>
                    <p>59 Southcroft Road</p>
                    <p>Rutherglen, Glasgow G73 1SP</p>
                    <p>Tel: +44 141 482 0726</p>
                    <p>Email: info@doyenautos.co.uk</p>
                    <?php if(config('garage.vat_number')): ?><p>VAT No: <?php echo e(config('garage.vat_number')); ?></p><?php endif; ?>
                </div>
            </td>
            <td style="border: none; padding: 0; width: 50%; vertical-align: top; text-align: right;">
                <div class="invoice-title">
                    <h2>INVOICE</h2>
                    <p><strong><?php echo e($invoice->invoice_number); ?></strong></p>
                    <p>Date: <?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y')); ?></p>
                    <?php if($invoice->due_date): ?>
                    <p>Due: <?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y')); ?></p>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bill To -->
    <div class="bill-to">
        <div class="label">Bill To</div>
        <div class="name"><?php echo e($invoice->customer->first_name ?? ''); ?> <?php echo e($invoice->customer->last_name ?? ''); ?></div>
        <?php if($invoice->customer->address ?? false): ?><p><?php echo e($invoice->customer->address); ?></p><?php endif; ?>
        <?php if($invoice->customer->city ?? false): ?><p><?php echo e($invoice->customer->city); ?> <?php echo e($invoice->customer->postcode ?? ''); ?></p><?php endif; ?>
        <?php if($invoice->customer->email ?? false): ?><p><?php echo e($invoice->customer->email); ?></p><?php endif; ?>
        <?php if($invoice->customer->phone ?? false): ?><p><?php echo e($invoice->customer->phone); ?></p><?php endif; ?>
    </div>

    <!-- Vehicle -->
    <?php if($invoice->vehicle): ?>
    <div class="vehicle-info">
        <strong>Vehicle:</strong> <?php echo e($invoice->vehicle->registration_number); ?> &mdash; <?php echo e($invoice->vehicle->make); ?> <?php echo e($invoice->vehicle->model); ?> <?php echo e($invoice->vehicle->year); ?>

    </div>
    <?php endif; ?>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = ($invoice->items ?? $invoice->invoiceItems ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($item->description); ?></td>
                <td style="text-transform: capitalize;"><?php echo e($item->type ?? '-'); ?></td>
                <td style="text-align: right;"><?php echo e($item->quantity); ?></td>
                <td style="text-align: right;">&pound;<?php echo e(number_format($item->unit_price, 2)); ?></td>
                <td style="text-align: right;">&pound;<?php echo e(number_format($item->quantity * $item->unit_price, 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals clearfix">
        <table>
            <tr class="subtotal">
                <td class="label">Subtotal:</td>
                <td class="amount">&pound;<?php echo e(number_format($invoice->subtotal, 2)); ?></td>
            </tr>
            <tr>
                <td class="label">VAT (20%):</td>
                <td class="amount">&pound;<?php echo e(number_format($invoice->vat_amount ?? $invoice->tax_amount ?? 0, 2)); ?></td>
            </tr>
            <tr class="total">
                <td class="label">Total:</td>
                <td class="amount">&pound;<?php echo e(number_format($invoice->total, 2)); ?></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <!-- Notes -->
    <?php if($invoice->notes): ?>
    <div class="notes">
        <div class="label">Notes</div>
        <p><?php echo e($invoice->notes); ?></p>
    </div>
    <?php endif; ?>

    <!-- Payment Terms -->
    <div class="notes" style="margin-top: 20px;">
        <div class="label">Payment Terms</div>
        <p>Payment due within 30 days of invoice date. Bank transfers accepted.</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Doyen Auto Services &bull; 59 Southcroft Road, Rutherglen, Glasgow G73 1SP &bull; Tel: +44 141 482 0726</p>
        <p>Thank you for your business</p>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\pdf\invoice.blade.php ENDPATH**/ ?>