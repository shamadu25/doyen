

<?php $__env->startSection('title', 'Reply to Your Support Ticket'); ?>

<?php $__env->startSection('content'); ?>
<p class="greeting">Hi <?php echo e($ticket->customer->name); ?>,</p>
<p>Our support team has responded to your ticket <strong><?php echo e($ticket->ticket_number); ?></strong>.</p>

<div class="info-box" style="margin:16px 0 20px;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px; width:120px;">Ticket Ref:</td>
            <td style="padding:5px 0; font-weight:600; color:#1e40af;"><?php echo e($ticket->ticket_number); ?></td>
        </tr>
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px;">Subject:</td>
            <td style="padding:5px 0;"><?php echo e($ticket->subject); ?></td>
        </tr>
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px;">Status:</td>
            <td style="padding:5px 0;">
                <?php if($ticket->status === 'resolved'): ?>
                    <span style="background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">Resolved</span>
                <?php elseif($ticket->status === 'closed'): ?>
                    <span style="background:#f1f5f9;color:#64748b;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">Closed</span>
                <?php else: ?>
                    <span style="background:#fef9c3;color:#a16207;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">In Progress</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<p style="font-weight:600; margin-bottom:6px;">Response from Doyen Auto Services:</p>
<div style="background:#f0f9ff; border-left:4px solid #0ea5e9; padding:15px; border-radius:0 6px 6px 0; font-size:14px; color:#0c4a6e; white-space:pre-wrap;"><?php echo e($reply->message); ?></div>

<?php if($ticket->status !== 'closed'): ?>
<p style="margin-top:20px;">If you have further questions, you can reply directly from the customer portal:</p>
<div style="text-align:center; margin:20px 0;">
    <a href="<?php echo e(config('app.url')); ?>/customer/tickets/<?php echo e($ticket->id); ?>" style="background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%); color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">View & Reply to Ticket</a>
</div>
<?php else: ?>
<p style="margin-top:20px; color:#64748b;">This ticket has been closed. If you need further assistance, please open a new support ticket.</p>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\ticket-replied-customer.blade.php ENDPATH**/ ?>