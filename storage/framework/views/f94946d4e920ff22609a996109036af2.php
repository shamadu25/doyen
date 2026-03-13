

<?php $__env->startSection('title', 'WhatsApp Support'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
    .page-header h1 { font-size:2rem; color:#333; }

    /* Stats */
    .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:16px; margin-bottom:24px; }
    .stat-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.08); text-align:center; }
    .stat-card .num { font-size:2rem; font-weight:700; }
    .stat-card .lbl { font-size:.82rem; color:#666; margin-top:4px; }
    .stat-card.open     .num { color:#dc3545; }
    .stat-card.progress .num { color:#fd7e14; }
    .stat-card.closed   .num { color:#28a745; }
    .stat-card.unread   .num { color:#667eea; }

    /* Filter bar */
    .filter-bar { background:#fff; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,.08); display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:20px; }
    .filter-bar .fg { display:flex; flex-direction:column; gap:4px; }
    .filter-bar .fg label { font-size:.8rem; color:#666; font-weight:600; }
    .filter-bar input, .filter-bar select { padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:.9rem; }
    .filter-bar button { padding:8px 16px; background:#667eea; color:#fff; border:none; border-radius:6px; cursor:pointer; }
    .filter-bar button:hover { background:#5568d3; }

    /* Table */
    .card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 8px rgba(0,0,0,.08); margin-bottom:24px; }
    table { width:100%; border-collapse:collapse; }
    th { background:#f8f9fa; padding:12px 16px; text-align:left; font-size:.85rem; color:#666; border-bottom:2px solid #dee2e6; }
    td { padding:12px 16px; border-bottom:1px solid #f0f0f0; vertical-align:middle; font-size:.9rem; }
    tr:hover td { background:#f8f9ff; }
    tr:last-child td { border-bottom:none; }

    .badge { padding:4px 10px; border-radius:20px; font-size:.75rem; font-weight:600; }
    .badge-open     { background:#fff3cd; color:#856404; }
    .badge-progress { background:#fff0e6; color:#da6c00; }
    .badge-closed   { background:#d4edda; color:#155724; }

    .unread-dot { display:inline-block; width:10px; height:10px; background:#dc3545; border-radius:50%; margin-right:6px; }

    .btn-sm { padding:5px 12px; border:none; border-radius:5px; cursor:pointer; font-size:.82rem; text-decoration:none; display:inline-block; }
    .btn-view     { background:#667eea; color:#fff; }
    .btn-view:hover { background:#5568d3; }
    .btn-danger   { background:#dc3545; color:#fff; }
    .btn-danger:hover { background:#b02a37; }

    /* Compose modal */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; justify-content:center; align-items:center; }
    .modal-overlay.active { display:flex; }
    .modal-box { background:#fff; border-radius:16px; padding:30px; width:100%; max-width:500px; box-shadow:0 10px 40px rgba(0,0,0,.2); }
    .modal-box h3 { margin-bottom:18px; color:#333; }
    .form-group { margin-bottom:14px; }
    .form-group label { display:block; font-size:.85rem; font-weight:600; color:#555; margin-bottom:6px; }
    .form-group input, .form-group textarea { width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:8px; font-size:.9rem; }
    .form-group textarea { resize:vertical; min-height:100px; }
    .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:16px; }
    .btn-send { background:#25d366; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-size:.9rem; font-weight:600; }
    .btn-send:hover { background:#1eba57; }
    .btn-cancel { background:#6c757d; color:#fff; border:none; padding:10px 18px; border-radius:8px; cursor:pointer; font-size:.9rem; }

    .whatsapp-icon { color:#25d366; font-size:1.2rem; margin-right:6px; }
    .pagination { margin-top:20px; display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
    .pagination a, .pagination span { padding:6px 12px; border:1px solid #ddd; border-radius:6px; font-size:.85rem; text-decoration:none; color:#667eea; }
    .pagination .active span { background:#667eea; color:#fff; border-color:#667eea; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1>&#128172; WhatsApp Support</h1>
    <button class="btn btn-primary" onclick="openCompose()" style="background:#25d366;border:none;padding:10px 20px;border-radius:8px;color:#fff;cursor:pointer;font-weight:600;font-size:.9rem;">
        &#10133; New Message
    </button>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success" style="margin-bottom:16px;"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('warning')): ?>
    <div class="alert" style="background:#fff3cd;color:#856404;border:1px solid #ffc107;padding:12px 16px;border-radius:8px;margin-bottom:16px;"><?php echo e(session('warning')); ?></div>
<?php endif; ?>


<div class="stats-row">
    <div class="stat-card open">
        <div class="num"><?php echo e($stats['open']); ?></div>
        <div class="lbl">Open</div>
    </div>
    <div class="stat-card progress">
        <div class="num"><?php echo e($stats['in_progress']); ?></div>
        <div class="lbl">In Progress</div>
    </div>
    <div class="stat-card closed">
        <div class="num"><?php echo e($stats['closed']); ?></div>
        <div class="lbl">Closed</div>
    </div>
    <div class="stat-card unread">
        <div class="num"><?php echo e($stats['unread']); ?></div>
        <div class="lbl">Unread Messages</div>
    </div>
</div>


<form method="GET" class="filter-bar">
    <div class="fg">
        <label>Search</label>
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name or phone...">
    </div>
    <div class="fg">
        <label>Status</label>
        <select name="status">
            <option value="">All</option>
            <option value="open"        <?php echo e(request('status') === 'open'        ? 'selected' : ''); ?>>Open</option>
            <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
            <option value="closed"      <?php echo e(request('status') === 'closed'      ? 'selected' : ''); ?>>Closed</option>
        </select>
    </div>
    <button type="submit">Filter</button>
    <a href="<?php echo e(route('whatsapp.support.index')); ?>" style="padding:8px 14px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none;font-size:.9rem;">Reset</a>
</form>


<div class="card">
    <?php if($conversations->count()): ?>
    <table>
        <thead>
            <tr>
                <th>Contact</th>
                <th>Last Message</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Last Activity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <?php if($conv->unread_count > 0): ?>
                        <span class="unread-dot"></span>
                    <?php endif; ?>
                    <strong><?php echo e($conv->customer_name ?? $conv->customer_phone); ?></strong>
                    <br><small style="color:#888;"><?php echo e($conv->customer_phone); ?></small>
                    <?php if($conv->customer): ?>
                        <br><small style="color:#667eea;">&#128100; <?php echo e($conv->customer->name); ?></small>
                    <?php endif; ?>
                </td>
                <td style="max-width:280px;">
                    <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?php echo e($conv->last_message ?? '—'); ?>

                    </span>
                    <?php if($conv->unread_count > 0): ?>
                        <span style="background:#dc3545;color:#fff;padding:2px 8px;border-radius:12px;font-size:.72rem;margin-top:4px;display:inline-block;">
                            <?php echo e($conv->unread_count); ?> unread
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($conv->status === 'open'): ?>
                        <span class="badge badge-open">Open</span>
                    <?php elseif($conv->status === 'in_progress'): ?>
                        <span class="badge badge-progress">In Progress</span>
                    <?php else: ?>
                        <span class="badge badge-closed">Closed</span>
                    <?php endif; ?>
                </td>
                <td><?php echo e($conv->assignedTo?->name ?? '—'); ?></td>
                <td><?php echo e($conv->last_message_at ? $conv->last_message_at->diffForHumans() : '—'); ?></td>
                <td>
                    <a href="<?php echo e(route('whatsapp.support.show', $conv)); ?>" class="btn-sm btn-view">Open Chat</a>
                    <form method="POST" action="<?php echo e(route('whatsapp.support.destroy', $conv)); ?>" style="display:inline;" onsubmit="return confirm('Delete this conversation?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php echo e($conversations->links()); ?>

    </div>
    <?php else: ?>
        <div style="text-align:center;padding:60px 20px;color:#888;">
            <div style="font-size:3rem;">&#128172;</div>
            <p style="margin-top:12px;">No WhatsApp conversations yet.</p>
        </div>
    <?php endif; ?>
</div>


<div class="modal-overlay" id="composeModal">
    <div class="modal-box">
        <h3>&#128172; Send New WhatsApp Message</h3>
        <form method="POST" action="<?php echo e(route('whatsapp.support.compose')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone" placeholder="+447911123456" required>
            </div>
            <div class="form-group">
                <label>Contact Name (optional)</label>
                <input type="text" name="name" placeholder="e.g. John Smith">
            </div>
            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" placeholder="Type your message..." required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeCompose()">Cancel</button>
                <button type="submit" class="btn-send">&#9654; Send</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openCompose()  { document.getElementById('composeModal').classList.add('active'); }
function closeCompose() { document.getElementById('composeModal').classList.remove('active'); }
document.getElementById('composeModal').addEventListener('click', function(e){
    if (e.target === this) closeCompose();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\admin\whatsapp\index.blade.php ENDPATH**/ ?>