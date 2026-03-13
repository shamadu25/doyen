

<?php $__env->startSection('title', 'Chat – ' . ($conversation->customer_name ?? $conversation->customer_phone)); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .chat-layout { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
    @media(max-width:900px) { .chat-layout { grid-template-columns:1fr; } }

    /* Breadcrumb */
    .breadcrumb { font-size:.85rem; color:#888; margin-bottom:16px; }
    .breadcrumb a { color:#667eea; text-decoration:none; }

    /* Chat card */
    .chat-card { background:#fff; border-radius:16px; box-shadow:0 4px 16px rgba(0,0,0,.1); overflow:hidden; display:flex; flex-direction:column; height:80vh; min-height:560px; }
    .chat-header { background:linear-gradient(135deg,#25d366 0%,#128c7e 100%); color:#fff; padding:16px 20px; display:flex; align-items:center; gap:14px; }
    .chat-header .avatar { width:44px; height:44px; background:rgba(255,255,255,.25); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.3rem; font-weight:700; }
    .chat-header .info h3 { font-size:1.1rem; margin:0; }
    .chat-header .info small { opacity:.85; font-size:.82rem; }
    .chat-header .status-badge { margin-left:auto; padding:5px 14px; border-radius:20px; font-size:.8rem; font-weight:600; background:rgba(255,255,255,.2); }

    /* Messages area */
    .messages-area { flex:1; overflow-y:auto; padding:20px; background:#ece5dd; display:flex; flex-direction:column; gap:12px; }
    .msg { max-width:72%; display:flex; flex-direction:column; }
    .msg.inbound  { align-self:flex-start; }
    .msg.outbound { align-self:flex-end; }
    .msg-bubble { padding:10px 14px; border-radius:12px; font-size:.9rem; line-height:1.5; word-break:break-word; box-shadow:0 1px 2px rgba(0,0,0,.12); }
    .msg.inbound  .msg-bubble { background:#fff; border-bottom-left-radius:3px; color:#333; }
    .msg.outbound .msg-bubble { background:#dcf8c6; border-bottom-right-radius:3px; color:#333; }
    .msg-meta { font-size:.72rem; color:#888; margin-top:3px; }
    .msg.outbound .msg-meta { text-align:right; }
    .msg-media img { max-width:220px; border-radius:8px; margin-top:6px; display:block; }

    /* Day separator */
    .day-sep { text-align:center; margin:8px 0; }
    .day-sep span { background:rgba(255,255,255,.7); padding:4px 14px; border-radius:12px; font-size:.75rem; color:#555; }

    /* Reply bar */
    .reply-bar { border-top:1px solid #e0e0e0; padding:14px 16px; background:#f0f0f0; display:flex; gap:10px; align-items:flex-end; }
    .reply-bar textarea { flex:1; padding:10px 14px; border:none; border-radius:22px; background:#fff; resize:none; font-size:.9rem; min-height:42px; max-height:120px; outline:none; box-shadow:0 1px 3px rgba(0,0,0,.1); }
    .reply-bar button { background:#25d366; color:#fff; border:none; width:46px; height:46px; border-radius:50%; cursor:pointer; font-size:1.3rem; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(37,211,102,.4); flex-shrink:0; }
    .reply-bar button:hover { background:#1eba57; }

    /* Sidebar */
    .sidebar-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:16px; }
    .sidebar-card h4 { font-size:1rem; color:#333; margin-bottom:14px; border-bottom:1px solid #eee; padding-bottom:8px; }
    .info-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-size:.88rem; }
    .info-row .label { color:#888; }
    .info-row .value { font-weight:600; color:#333; }
    .form-group { margin-bottom:12px; }
    .form-group label { display:block; font-size:.82rem; font-weight:600; color:#555; margin-bottom:5px; }
    .form-group select, .form-group textarea { width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:.88rem; }
    .form-group textarea { resize:vertical; min-height:80px; }
    .btn-save { width:100%; padding:10px; background:#667eea; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.9rem; font-weight:600; }
    .btn-save:hover { background:#5568d3; }

    .status-open     { color:#dc3545; font-weight:700; }
    .status-progress { color:#fd7e14; font-weight:700; }
    .status-closed   { color:#28a745; font-weight:700; }

    .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:12px 16px; border-radius:8px; margin-bottom:16px; }
    .alert-warning { background:#fff3cd; color:#856404; border:1px solid #ffc107; padding:12px 16px; border-radius:8px; margin-bottom:16px; }

    /* Whatsapp config warning */
    .config-warn { background:#fff3cd; border:1px solid #ffc107; border-radius:10px; padding:12px 16px; font-size:.85rem; color:#856404; margin-bottom:14px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="breadcrumb">
    <a href="<?php echo e(route('whatsapp.support.index')); ?>">&#128172; WhatsApp Support</a>
    &rsaquo; <?php echo e($conversation->customer_name ?? $conversation->customer_phone); ?>

</div>

<?php if(session('success')): ?>
    <div class="alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('warning')): ?>
    <div class="alert-warning"><?php echo e(session('warning')); ?></div>
<?php endif; ?>

<?php if(!config('services.twilio.whatsapp_enabled')): ?>
<div class="config-warn">
    &#9888; WhatsApp is currently <strong>disabled</strong>. Set <code>WHATSAPP_ENABLED=true</code> in your <code>.env</code> to send messages.
</div>
<?php endif; ?>

<div class="chat-layout">

    
    <div class="chat-card">
        
        <div class="chat-header">
            <div class="avatar">
                <?php echo e(strtoupper(substr($conversation->customer_name ?? $conversation->customer_phone, 0, 1))); ?>

            </div>
            <div class="info">
                <h3><?php echo e($conversation->customer_name ?? $conversation->customer_phone); ?></h3>
                <small><?php echo e($conversation->customer_phone); ?></small>
            </div>
            <span class="status-badge">
                <?php echo e(ucfirst(str_replace('_', ' ', $conversation->status))); ?>

            </span>
        </div>

        
        <div class="messages-area" id="messagesArea">
            <?php $lastDate = null; ?>

            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $msgDate = $msg->created_at->toDateString(); ?>
                <?php if($lastDate !== $msgDate): ?>
                    <div class="day-sep">
                        <span><?php echo e($msg->created_at->isToday() ? 'Today' : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('d M Y'))); ?></span>
                    </div>
                    <?php $lastDate = $msgDate; ?>
                <?php endif; ?>

                <div class="msg <?php echo e($msg->direction); ?>">
                    <div class="msg-bubble">
                        <?php echo e($msg->body); ?>

                        <?php if($msg->hasMedia()): ?>
                            <div class="msg-media">
                                <?php if(str_starts_with($msg->media_content_type ?? '', 'image')): ?>
                                    <img src="<?php echo e($msg->media_url); ?>" alt="Media">
                                <?php else: ?>
                                    <a href="<?php echo e($msg->media_url); ?>" target="_blank" style="color:#667eea;">&#128206; View Attachment</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="msg-meta">
                        <?php echo e($msg->created_at->format('H:i')); ?>

                        <?php if($msg->isOutbound()): ?>
                            &middot; <?php echo e($msg->sentBy?->name ?? 'System'); ?>

                            <?php if($msg->status === 'delivered'): ?> &middot; &#10004;&#10004;
                            <?php elseif($msg->status === 'sent'): ?>    &middot; &#10004;
                            <?php elseif($msg->status === 'read'): ?>    &middot; <span style="color:#4fc3f7;">&#10004;&#10004;</span>
                            <?php elseif($msg->status === 'failed'): ?>  &middot; <span style="color:#dc3545;">Failed</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div style="text-align:center;color:#aaa;margin:auto;">
                    <div style="font-size:3rem;">&#128172;</div>
                    <p style="margin-top:8px;">No messages yet</p>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="reply-bar">
            <form method="POST" action="<?php echo e(route('whatsapp.support.reply', $conversation)); ?>" style="display:contents;" id="replyForm">
                <?php echo csrf_field(); ?>
                <textarea name="message" id="replyInput" placeholder="Type a message..." rows="1"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.closest('form').submit();}"></textarea>
                <button type="submit" title="Send">&#10148;</button>
            </form>
        </div>
    </div>

    
    <div>
        
        <div class="sidebar-card">
            <h4>&#128100; Contact Info</h4>
            <div class="info-row">
                <span class="label">Phone</span>
                <span class="value"><?php echo e($conversation->customer_phone); ?></span>
            </div>
            <?php if($conversation->customer): ?>
            <div class="info-row">
                <span class="label">Customer</span>
                <span><a href="<?php echo e(route('customers.show', $conversation->customer)); ?>" style="color:#667eea;"><?php echo e($conversation->customer->name); ?></a></span>
            </div>
            <div class="info-row">
                <span class="label">Email</span>
                <span class="value" style="font-size:.8rem;"><?php echo e($conversation->customer->email ?? '—'); ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="label">Messages</span>
                <span class="value"><?php echo e($messages->count()); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Started</span>
                <span class="value"><?php echo e($conversation->created_at->format('d M Y')); ?></span>
            </div>
        </div>

        
        <div class="sidebar-card">
            <h4>&#9881; Manage</h4>
            <form method="POST" action="<?php echo e(route('whatsapp.support.update', $conversation)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="open"        <?php echo e($conversation->status === 'open'        ? 'selected' : ''); ?>>Open</option>
                        <option value="in_progress" <?php echo e($conversation->status === 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                        <option value="closed"      <?php echo e($conversation->status === 'closed'      ? 'selected' : ''); ?>>Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Assign To</label>
                    <select name="assigned_to">
                        <option value="">— Unassigned —</option>
                        <?php $__currentLoopData = $staffList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($staff->id); ?>" <?php echo e($conversation->assigned_to == $staff->id ? 'selected' : ''); ?>>
                                <?php echo e($staff->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Internal Notes</label>
                    <textarea name="notes" placeholder="Private notes..."><?php echo e($conversation->notes); ?></textarea>
                </div>
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>

        
        <div class="sidebar-card">
            <h4>&#9889; Quick Replies</h4>
            <?php
                $quick = [
                    'Thank you for contacting Doyen Auto Services! How can we help you today?',
                    'Your appointment has been confirmed. We look forward to seeing you!',
                    'We have received your request and will get back to you shortly.',
                    'Your vehicle is ready for collection. Please call us to arrange pickup.',
                    'For urgent enquiries please call us on ' . config('app.garage_phone', '+44 141 482 0726'),
                ];
            ?>
            <?php $__currentLoopData = $quick; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button onclick="fillReply(this)" data-msg="<?php echo e($qr); ?>"
                style="display:block;width:100%;text-align:left;background:#f8f9ff;border:1px solid #dee2e6;border-radius:8px;padding:9px 12px;margin-bottom:8px;cursor:pointer;font-size:.82rem;color:#444;">
                <?php echo e(Str::limit($qr, 60)); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Auto scroll to bottom of messages
const area = document.getElementById('messagesArea');
if (area) area.scrollTop = area.scrollHeight;

// Auto resize textarea
const ta = document.getElementById('replyInput');
if (ta) {
    ta.addEventListener('input', function(){
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

// Fill quick reply
function fillReply(btn) {
    const ta = document.getElementById('replyInput');
    if (ta) {
        ta.value = btn.dataset.msg;
        ta.focus();
        ta.style.height = 'auto';
        ta.style.height = Math.min(ta.scrollHeight, 120) + 'px';
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\admin\whatsapp\show.blade.php ENDPATH**/ ?>