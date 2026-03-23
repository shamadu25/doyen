<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e(!empty($garage['vat_number']) ? 'VAT Receipt' : 'Invoice'); ?> <?php echo e($invoice->invoice_number); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11.5px; color: #1a1a1a; line-height: 1.55; }
        .page { padding: 36px 44px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        .company-name { font-size: 20px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px; }
        .company-meta { font-size: 10.5px; color: #555; line-height: 1.7; }
        .company-meta span { display: block; }
        .invoice-badge { text-align: right; }
        .invoice-badge .type { font-size: 26px; font-weight: 800; color: #1e3a8a; letter-spacing: 3px; line-height: 1; }
        .invoice-badge .number { font-size: 13px; font-weight: 600; color: #333; margin-top: 6px; }
        .invoice-badge .meta { font-size: 10.5px; color: #555; margin-top: 3px; }
        .address-row { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .address-row td { border: none; padding: 0; width: 50%; vertical-align: top; }
        .address-box { background: #f1f5f9; border-left: 3px solid #1e3a8a; padding: 12px 14px; }
        .address-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #94a3b8; margin-bottom: 5px; }
        .address-name { font-size: 13px; font-weight: 600; color: #111; margin-bottom: 2px; }
        .address-detail { font-size: 10.5px; color: #555; line-height: 1.65; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead th { background: #1e3a8a; color: #fff; padding: 9px 11px; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600; }
        .items-table thead th.r { text-align: right; }
        .items-table tbody td { padding: 9px 11px; border-bottom: 1px solid #e8edf3; font-size: 11px; vertical-align: top; }
        .items-table tbody td.r { text-align: right; }
        .items-table tbody tr:nth-child(even) td { background: #fafbfc; }
        .totals-wrap { float: right; width: 270px; margin-top: 10px; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 5px 10px; border: none; font-size: 11.5px; }
        .totals-table .t-label { color: #555; text-align: left; }
        .totals-table .t-val { text-align: right; font-weight: 600; }
        .totals-table .t-vat td { background: #f1f5f9; }
        .totals-table .t-paid td { color: #16a34a; }
        .totals-table .t-due td { border-top: 2px solid #1e3a8a; font-size: 14px; font-weight: 700; color: #1e3a8a; padding-top: 8px; }
        .discount-label { color: #dc2626; }
        .vat-summary { clear: both; margin-top: 28px; }
        .vat-summary-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        .vat-summary-table th { background: #f1f5f9; color: #555; padding: 6px 10px; font-weight: 600; text-align: left; border: 1px solid #e2e8f0; }
        .vat-summary-table th.r { text-align: right; }
        .vat-summary-table td { padding: 6px 10px; border: 1px solid #e2e8f0; }
        .vat-summary-table td.r { text-align: right; }
        .section-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px; }
        .info-row { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .info-row td { border: none; padding: 0; vertical-align: top; }
        .info-row p { font-size: 11px; color: #444; line-height: 1.7; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 22px 0; }
        .footer { margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 12px; text-align: center; font-size: 9.5px; color: #94a3b8; line-height: 1.7; }
        .footer strong { color: #666; }
        .status-badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; vertical-align: middle; margin-left: 8px; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-overdue { background: #fee2e2; color: #b91c1c; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>
<?php
    // Invoice header — use dedicated invoice_header_* fields; fall back to general garage settings
    $hdrName     = !empty($garage['invoice_header_name'])     ? $garage['invoice_header_name']     : ($garage['garage_name'] ?? 'Doyen Auto Services');
    $hdrAddress  = !empty($garage['invoice_header_address'])  ? $garage['invoice_header_address']  : ($garage['address']     ?? '');
    $hdrCity     = !empty($garage['invoice_header_city'])     ? $garage['invoice_header_city']     : ($garage['city']        ?? '');
    $hdrPostcode = !empty($garage['invoice_header_postcode']) ? $garage['invoice_header_postcode'] : ($garage['postcode']    ?? '');
    $hdrPhone    = !empty($garage['invoice_header_phone'])    ? $garage['invoice_header_phone']    : ($garage['phone']       ?? '');
    $hdrEmail    = !empty($garage['invoice_header_email'])    ? $garage['invoice_header_email']    : ($garage['email']       ?? '');
    $hdrWebsite  = !empty($garage['invoice_header_website'])  ? $garage['invoice_header_website']  : ($garage['website']     ?? '');
    $vatNumber     = $garage['vat_number']              ?? '';
    $vatRate       = $garage['vat_rate']                ?? '20';
    $isVatDoc      = !empty($vatNumber);
    $docTitle      = $isVatDoc ? 'VAT RECEIPT' : 'INVOICE';
    $companyNumber = $garage['invoice_company_number']  ?? '';
    $bankName      = $garage['invoice_bank_name']       ?? '';
    $sortCode      = $garage['invoice_sort_code']       ?? '';
    $accountNum    = $garage['invoice_account_number']  ?? '';
    $accountName   = $garage['invoice_account_name']    ?? '';
    $footerNote    = $garage['invoice_footer_note']     ?? 'Thank you for your custom.';
    $latePayment   = $garage['invoice_late_payment']    ?? '';
    $terms         = !empty($invoice->terms) ? $invoice->terms : ($garage['invoice_terms'] ?? 'Payment due within 30 days of invoice date.');
    $dueDays       = $garage['invoice_due_days']        ?? '30';
    $subtotal      = (float)($invoice->subtotal         ?? 0);
    $vatAmount     = (float)($invoice->vat_amount       ?? $invoice->tax_amount ?? 0);
    $totalAmount   = (float)($invoice->total_amount     ?? $invoice->total ?? ($subtotal + $vatAmount));
    $discount      = (float)($invoice->discount_amount  ?? 0);
    $paidAmount    = (float)($invoice->paid_amount      ?? 0);
    $balance       = $totalAmount - $paidAmount;
    $isPaid        = $invoice->status === 'paid' || $balance <= 0;
    $isOverdue     = $invoice->status === 'overdue';
?>
<div class="page">

    
    <table class="header-table">
        <tr>
            <td style="width:55%">
                <div class="company-name"><?php echo e($hdrName); ?></div>
                <div class="company-meta">
                    <?php if(!empty($hdrAddress)): ?>
                        <span><?php echo e($hdrAddress); ?></span>
                    <?php endif; ?>
                    <?php if(!empty($hdrCity) || !empty($hdrPostcode)): ?>
                        <span><?php echo e(trim($hdrCity.' '.$hdrPostcode)); ?></span>
                    <?php endif; ?>
                    <?php if(!empty($hdrPhone)): ?>
                        <span>Tel: <?php echo e($hdrPhone); ?></span>
                    <?php endif; ?>
                    <?php if(!empty($hdrEmail)): ?>
                        <span><?php echo e($hdrEmail); ?></span>
                    <?php endif; ?>
                    <?php if(!empty($hdrWebsite)): ?>
                        <span><?php echo e($hdrWebsite); ?></span>
                    <?php endif; ?>
                    <?php if($isVatDoc): ?>
                        <span style="margin-top:4px;font-weight:600;color:#1e3a8a;">VAT Reg No: <?php echo e($vatNumber); ?></span>
                    <?php endif; ?>
                    <?php if(!empty($companyNumber)): ?>
                        <span>Company No: <?php echo e($companyNumber); ?></span>
                    <?php endif; ?>
                </div>
            </td>
            <td style="width:45%;text-align:right;">
                <div class="invoice-badge">
                    <div class="type"><?php echo e($docTitle); ?></div>
                    <div class="number">
                        <?php echo e($invoice->invoice_number); ?>

                        <?php if($isPaid): ?><span class="status-badge status-paid">PAID</span>
                        <?php elseif($isOverdue): ?><span class="status-badge status-overdue">OVERDUE</span>
                        <?php else: ?><span class="status-badge status-sent"><?php echo e(strtoupper($invoice->status ?? 'ISSUED')); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="meta">
                        Invoice Date: <?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')); ?><br>
                        Tax Point: <?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y')); ?><br>
                        <?php if($invoice->due_date): ?>
                        Payment Due: <?php echo e(\Carbon\Carbon::parse($invoice->due_date)->format('d M Y')); ?>

                        <?php else: ?>
                        Payment Due: <?php echo e(\Carbon\Carbon::parse($invoice->invoice_date)->addDays((int)$dueDays)->format('d M Y')); ?>

                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    
    <table class="address-row">
        <tr>
            <td style="padding-right:14px;">
                <div class="address-box">
                    <div class="address-label">Invoice To</div>
                    <div class="address-name"><?php echo e(($invoice->customer->first_name ?? '').' '.($invoice->customer->last_name ?? '')); ?></div>
                    <div class="address-detail">
                        <?php if(!empty($invoice->customer->address)): ?>
                            <?php echo e($invoice->customer->address); ?><br>
                        <?php endif; ?>
                        <?php
                            $custCity = $invoice->customer->city ?? '';
                            $custPost = $invoice->customer->postcode ?? '';
                            $custCityLine = trim($custCity . ' ' . $custPost);
                        ?>
                        <?php if(!empty($custCityLine)): ?>
                            <?php echo e($custCityLine); ?><br>
                        <?php endif; ?>
                        <?php if(!empty($invoice->customer->email)): ?>
                            <?php echo e($invoice->customer->email); ?><br>
                        <?php endif; ?>
                        <?php if(!empty($invoice->customer->phone)): ?>
                            <?php echo e($invoice->customer->phone); ?>

                        <?php endif; ?>
                    </div>
                </div>
            </td>
            <td style="padding-left:8px;">
                <?php if($invoice->vehicle): ?>
                <div class="address-box" style="border-left-color:#2563eb;">
                    <div class="address-label">Vehicle</div>
                    <div class="address-name" style="font-size:14px;letter-spacing:1px;"><?php echo e($invoice->vehicle->registration_number); ?></div>
                    <div class="address-detail">
                        <?php echo e($invoice->vehicle->make); ?> <?php echo e($invoice->vehicle->model); ?>

                        <?php if(!empty($invoice->vehicle->year)): ?>
                            &bull; <?php echo e($invoice->vehicle->year); ?>

                        <?php endif; ?>
                        <?php if(!empty($invoice->vehicle->fuel_type)): ?>
                            &bull; <?php echo e(ucfirst($invoice->vehicle->fuel_type)); ?>

                        <?php endif; ?>
                        <?php if(!empty($invoice->vehicle->mileage)): ?>
                            <br>Mileage: <?php echo e(number_format($invoice->vehicle->mileage)); ?> miles
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th>Type</th>
                <th class="r" style="width:7%">Qty</th>
                <th class="r" style="width:12%">Unit Price</th>
                <?php if($isVatDoc): ?>
                    <th class="r" style="width:8%">VAT %</th>
                <?php endif; ?>
                <th class="r" style="width:12%">Net</th>
                <?php if($isVatDoc): ?>
                    <th class="r" style="width:10%">VAT</th>
                <?php endif; ?>
                <th class="r" style="width:12%">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = ($invoice->items ?? $invoice->invoiceItems ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $qty      = (float)($item->quantity  ?? 1);
                $unit     = (float)($item->unit_price ?? 0);
                $iDisc    = (float)($item->discount   ?? 0);
                $net      = ($qty * $unit) - $iDisc;
                $vr       = (float)($item->vat_rate   ?? $vatRate ?? 20);
                $iVat     = $isVatDoc ? round($net * $vr / 100, 2) : 0;
                $iTotal   = $isVatDoc ? $net + $iVat : $net;
            ?>
            <tr>
                <td>
                    <?php echo e($item->description); ?>

                    <?php if(!empty($item->notes)): ?>
                        <br><span style="font-size:9.5px;color:#888;"><?php echo e($item->notes); ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-transform:capitalize;color:#555;"><?php echo e($item->item_type ?? 'service'); ?></td>
                <td class="r"><?php echo e($qty == intval($qty) ? intval($qty) : number_format($qty,2)); ?></td>
                <td class="r">&pound;<?php echo e(number_format($unit, 2)); ?></td>
                <?php if($isVatDoc): ?>
                    <td class="r"><?php echo e(number_format($vr, 0)); ?>%</td>
                <?php endif; ?>
                <td class="r">&pound;<?php echo e(number_format($net, 2)); ?></td>
                <?php if($isVatDoc): ?>
                    <td class="r">&pound;<?php echo e(number_format($iVat, 2)); ?></td>
                <?php endif; ?>
                <td class="r">&pound;<?php echo e(number_format($iTotal, 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="<?php echo e($isVatDoc ? 8 : 6); ?>" style="text-align:center;color:#aaa;padding:16px;">No line items recorded.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td class="t-label">Subtotal (ex. VAT):</td>
                <td class="t-val">&pound;<?php echo e(number_format($subtotal, 2)); ?></td>
            </tr>
            <?php if($discount > 0): ?>
            <tr>
                <td class="t-label discount-label">Discount:</td>
                <td class="t-val discount-label">&minus;&pound;<?php echo e(number_format($discount, 2)); ?></td>
            </tr>
            <?php endif; ?>
            <?php if($isVatDoc): ?>
            <tr class="t-vat">
                <td class="t-label">VAT (<?php echo e($vatRate); ?>%):</td>
                <td class="t-val">&pound;<?php echo e(number_format($vatAmount, 2)); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="t-due">
                <td class="t-label">Total:</td>
                <td class="t-val">&pound;<?php echo e(number_format($totalAmount, 2)); ?></td>
            </tr>
            <?php if($paidAmount > 0): ?>
            <tr class="t-paid">
                <td class="t-label">Amount Paid:</td>
                <td class="t-val">&minus;&pound;<?php echo e(number_format($paidAmount, 2)); ?></td>
            </tr>
            <tr class="t-due">
                <td class="t-label">Balance Due:</td>
                <td class="t-val">&pound;<?php echo e(number_format(max(0, $balance), 2)); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <div style="clear:both;"></div>

    
    <?php if($isVatDoc): ?>
    <div class="vat-summary">
        <div class="section-label">VAT Summary</div>
        <table class="vat-summary-table">
            <thead>
                <tr>
                    <th>VAT Code</th>
                    <th>Description</th>
                    <th>VAT Rate</th>
                    <th class="r">Net Amount</th>
                    <th class="r">VAT Amount</th>
                    <th class="r">Gross Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>S</td>
                    <td>Standard Rate</td>
                    <td><?php echo e($vatRate); ?>%</td>
                    <td class="r">&pound;<?php echo e(number_format($subtotal - $discount, 2)); ?></td>
                    <td class="r">&pound;<?php echo e(number_format($vatAmount, 2)); ?></td>
                    <td class="r">&pound;<?php echo e(number_format($totalAmount, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    
    <hr class="divider">
    <table class="info-row">
        <tr>
            <?php if(!empty($bankName) || !empty($accountNum)): ?>
            <td style="width:46%;padding-right:20px;vertical-align:top;">
                <div class="section-label">Bank Transfer / BACS Details</div>
                <?php if(!empty($bankName)): ?>
                    <p><strong>Bank:</strong> <?php echo e($bankName); ?></p>
                <?php endif; ?>
                <?php if(!empty($accountName)): ?>
                    <p><strong>Account Name:</strong> <?php echo e($accountName); ?></p>
                <?php endif; ?>
                <?php if(!empty($sortCode)): ?>
                    <p><strong>Sort Code:</strong> <?php echo e($sortCode); ?></p>
                <?php endif; ?>
                <?php if(!empty($accountNum)): ?>
                    <p><strong>Account Number:</strong> <?php echo e($accountNum); ?></p>
                <?php endif; ?>
                <p style="margin-top:4px;"><strong>Payment Reference:</strong> <?php echo e($invoice->invoice_number); ?></p>
            </td>
            <?php endif; ?>
            <td style="vertical-align:top;">
                <div class="section-label">Payment Terms</div>
                <p><?php echo e($terms); ?></p>
                <?php if($invoice->notes): ?>
                <div style="margin-top:10px;">
                    <div class="section-label">Notes</div>
                    <p><?php echo e($invoice->notes); ?></p>
                </div>
                <?php endif; ?>
                <?php if(!empty($latePayment)): ?>
                <div style="margin-top:10px;">
                    <p style="font-size:9.5px;color:#999;"><?php echo e($latePayment); ?></p>
                </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    
    <div class="footer">
        <strong><?php echo e($hdrName); ?></strong><br>
        <?php
            $footerCityLine = trim($hdrCity . ' ' . $hdrPostcode);
            $footerAddrFull = $hdrAddress . (!empty($footerCityLine) ? ', ' . $footerCityLine : '');
        ?>
        <?php if(!empty($hdrAddress)): ?>
            <?php echo e($footerAddrFull); ?><br>
        <?php endif; ?>
        <?php if(!empty($hdrPhone)): ?>
            Tel: <?php echo e($hdrPhone); ?>

        <?php endif; ?>
        <?php if(!empty($hdrEmail)): ?>
            &bull; <?php echo e($hdrEmail); ?>

        <?php endif; ?>
        <?php if(!empty($hdrWebsite)): ?>
            &bull; <?php echo e($hdrWebsite); ?>

        <?php endif; ?>
        <?php if($isVatDoc): ?>
            <br>VAT Registration No: <?php echo e($vatNumber); ?>

        <?php endif; ?>
        <?php if(!empty($companyNumber)): ?>
            &bull; Registered in England &amp; Wales No: <?php echo e($companyNumber); ?>

        <?php endif; ?>
        <br><br><?php echo e($footerNote); ?>

    </div>

</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\pdf\invoice.blade.php ENDPATH**/ ?>