

<?php $__env->startSection('title', 'Support Ticket Received'); ?>

<?php $__env->startSection('content'); ?>
<p class="greeting">Hi <?php echo e($ticket->customer->name); ?>,</p>
<p>Thank you for contacting us. We have received your support ticket and our team will respond shortly.</p>

<div class="info-box" style="margin: 20px 0;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px; width:140px;">Ticket Reference:</td>
            <td style="padding:6px 0; font-weight:600; color:#1e40af;"><?php echo e($ticket->ticket_number); ?></td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Subject:</td>
            <td style="padding:6px 0; font-weight:500;"><?php echo e($ticket->subject); ?></td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Category:</td>
            <td style="padding:6px 0; text-transform:capitalize;"><?php echo e(str_replace('_', ' ', $ticket->category)); ?></td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Priority:</td>
            <td style="padding:6px 0; text-transform:capitalize;"><?php echo e($ticket->priority); ?></td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Status:</td>
            <td style="padding:6px 0;"><span style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:50px;font-size:13px;font-weight:600;">Open</span></td>
        </tr>
        <tr>
            <td style="padding:6px 0; color:#64748b; font-size:14px;">Submitted:</td>
            <td style="padding:6px 0;"><?php echo e($ticket->created_at->format('d M Y, H:i')); ?></td>
        </tr>
    </table>
</div>

<p style="font-weight:600; margin-top:20px; margin-bottom:6px;">Your message:</p>
<div style="background:#f8fafc; border-left:4px solid #3b82f6; padding:15px; border-radius:0 6px 6px 0; font-size:14px; color:#475569; white-space:pre-wrap;"><?php echo e($ticket->message); ?></div>

<p style="margin-top:20px;">You can view your ticket and any replies by logging into the customer portal:</p>

<div style="text-align:center; margin:25px 0;">
    <a href="<?php echo e(config('app.url')); ?>/customer/tickets/<?php echo e($ticket->id); ?>" style="background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%); color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">View My Ticket</a>
</div>

<p style="color:#64748b; font-size:13px;">Please keep your ticket reference number <strong><?php echo e($ticket->ticket_number); ?></strong> for your records.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\ticket-created-customer.blade.php ENDPATH**/ ?>