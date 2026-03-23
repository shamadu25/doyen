

<?php $__env->startSection('title', 'Customer Replied to Ticket'); ?>

<?php $__env->startSection('content'); ?>
<p class="greeting">Customer Reply Received</p>
<p>A customer has replied to support ticket <strong><?php echo e($ticket->ticket_number); ?></strong>.</p>

<div class="info-box" style="margin:16px 0 20px;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px; width:140px;">Ticket Ref:</td>
            <td style="padding:5px 0; font-weight:600; color:#1e40af;"><?php echo e($ticket->ticket_number); ?></td>
        </tr>
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px;">Customer:</td>
            <td style="padding:5px 0; font-weight:500;"><?php echo e($ticket->customer->name); ?> (<?php echo e($ticket->customer->email); ?>)</td>
        </tr>
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px;">Subject:</td>
            <td style="padding:5px 0;"><?php echo e($ticket->subject); ?></td>
        </tr>
        <tr>
            <td style="padding:5px 0; color:#64748b; font-size:14px;">Status:</td>
            <td style="padding:5px 0; text-transform:capitalize;"><?php echo e(str_replace('_', ' ', $ticket->status)); ?></td>
        </tr>
    </table>
</div>

<p style="font-weight:600; margin-bottom:6px;">Customer's reply:</p>
<div style="background:#f8fafc; border-left:4px solid #f59e0b; padding:15px; border-radius:0 6px 6px 0; font-size:14px; color:#475569; white-space:pre-wrap;"><?php echo e($reply->message); ?></div>

<div style="text-align:center; margin:25px 0;">
    <a href="<?php echo e(config('app.url')); ?>/tickets/<?php echo e($ticket->id); ?>" style="background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%); color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">View Ticket & Reply</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\ticket-replied-admin.blade.php ENDPATH**/ ?>