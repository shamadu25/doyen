

<?php $__env->startSection('title', 'Services Management'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-header h1 { font-size: 2rem; color: #333; }
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
    }
    .stat-card .num { font-size: 2.2rem; font-weight: 700; }
    .stat-card .label { font-size: 0.85rem; color: #666; margin-top: 4px; }
    .stat-card.total   .num { color: #667eea; }
    .stat-card.active  .num { color: #28a745; }
    .stat-card.approved .num { color: #17a2b8; }
    .stat-card.website .num { color: #fd7e14; }

    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        align-items: flex-end;
    }
    .filter-bar .form-group { display: flex; flex-direction: column; gap: 4px; }
    .filter-bar .form-group label { font-size: 0.8rem; color: #666; font-weight: 600; }
    .filter-bar input, .filter-bar select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
    }
    .filter-bar .btn-sm { padding: 8px 16px; font-size: 0.85rem; }

    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    table { width: 100%; border-collapse: collapse; }
    thead { background: #f8f9fa; }
    th, td { padding: 12px 16px; text-align: left; font-size: 0.88rem; border-bottom: 1px solid #f0f0f0; }
    th { font-weight: 600; color: #555; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #fafafe; }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-success  { background: #d4edda; color: #155724; }
    .badge-danger   { background: #f8d7da; color: #721c24; }
    .badge-info     { background: #d1ecf1; color: #0c5460; }
    .badge-warning  { background: #fff3cd; color: #856404; }
    .badge-secondary{ background: #e2e3e5; color: #383d41; }
    .badge-orange   { background: #ffe6cc; color: #7a3e00; }

    .action-btns { display: flex; gap: 6px; flex-wrap: wrap; }
    .btn-xs {
        padding: 4px 10px;
        font-size: 0.75rem;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-xs:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-edit       { background: #667eea; color: white; }
    .btn-approve    { background: #17a2b8; color: white; }
    .btn-unapprove  { background: #ffc107; color: #333; }
    .btn-web-on     { background: #fd7e14; color: white; }
    .btn-web-off    { background: #6c757d; color: white; }
    .btn-enable     { background: #28a745; color: white; }
    .btn-disable    { background: #dc3545; color: white; }
    .btn-delete     { background: #dc3545; color: white; }
    .btn-view       { background: #6c757d; color: white; }

    .bulk-bar {
        display: none;
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 12px;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .bulk-bar.visible { display: flex; }
    .bulk-bar span { font-weight: 600; font-size: 0.9rem; }
    .bulk-bar select { padding: 6px 10px; border-radius: 5px; border: 1px solid #ccc; }

    .pagination { padding: 16px 20px; display: flex; justify-content: center; }
    .pagination a, .pagination span {
        padding: 6px 12px;
        margin: 0 2px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 0.85rem;
        text-decoration: none;
        color: #667eea;
    }
    .pagination .active span { background: #667eea; color: white; border-color: #667eea; }

    .website-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        background: linear-gradient(135deg, #fd7e14, #e65c00);
        color: white;
    }
    .category-tag {
        background: #e8ecff;
        color: #3d56c9;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1>🔧 Services Management</h1>
    <a href="<?php echo e(route('services.create')); ?>" class="btn btn-primary">+ Add New Service</a>
</div>


<div class="stats-row">
    <div class="stat-card total">
        <div class="num"><?php echo e($stats['total']); ?></div>
        <div class="label">Total Services</div>
    </div>
    <div class="stat-card active">
        <div class="num"><?php echo e($stats['active']); ?></div>
        <div class="label">Active</div>
    </div>
    <div class="stat-card approved">
        <div class="num"><?php echo e($stats['approved']); ?></div>
        <div class="label">Approved</div>
    </div>
    <div class="stat-card website">
        <div class="num"><?php echo e($stats['on_website']); ?></div>
        <div class="label">Live on Website</div>
    </div>
</div>


<form method="GET" action="<?php echo e(route('services.index')); ?>" class="filter-bar">
    <div class="form-group">
        <label>Search</label>
        <input type="text" name="search" placeholder="Name, code, category…" value="<?php echo e(request('search')); ?>">
    </div>
    <div class="form-group">
        <label>Category</label>
        <select name="category">
            <option value="">All Categories</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($cat); ?>" <?php echo e(request('category') == $cat ? 'selected' : ''); ?>><?php echo e(ucwords($cat)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="">All Statuses</option>
            <option value="active"   <?php echo e(request('status') == 'active'   ? 'selected' : ''); ?>>Active</option>
            <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
            <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
            <option value="pending"  <?php echo e(request('status') == 'pending'  ? 'selected' : ''); ?>>Pending Approval</option>
            <option value="website"  <?php echo e(request('status') == 'website'  ? 'selected' : ''); ?>>On Website</option>
        </select>
    </div>
    <div class="form-group">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </div>
    <?php if(request()->hasAny(['search','category','status'])): ?>
    <div class="form-group">
        <label>&nbsp;</label>
        <a href="<?php echo e(route('services.index')); ?>" class="btn btn-sm" style="background:#eee;color:#333;">Clear</a>
    </div>
    <?php endif; ?>
</form>


<div class="bulk-bar" id="bulkBar">
    <span id="bulkCount">0 selected</span>
    <form method="POST" action="<?php echo e(route('services.bulk-action')); ?>" id="bulkForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="service_ids" id="bulkIds" value="">
        <select name="action" required>
            <option value="">Choose action…</option>
            <option value="approve">✅ Approve</option>
            <option value="unapprove">❌ Unapprove & Hide</option>
            <option value="enable">🟢 Enable</option>
            <option value="disable">🔴 Disable & Hide</option>
            <option value="show_website">🌐 Publish to Website</option>
            <option value="hide_website">🚫 Hide from Website</option>
            <option value="delete">🗑 Delete Selected</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulk(event)">Apply</button>
    </form>
</div>


<div class="table-card">
    <div class="table-toolbar">
        <div style="font-size:0.85rem;color:#666;">
            Showing <?php echo e($services->firstItem() ?? 0); ?>–<?php echo e($services->lastItem() ?? 0); ?> of <?php echo e($services->total()); ?> services
        </div>
        <label style="font-size:0.85rem;cursor:pointer;">
            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"> Select all on page
        </label>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:36px;"></th>
                <th>Service</th>
                <th>Category</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Active</th>
                <th>Approved</th>
                <th>Website</th>
                <th style="min-width:240px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>
                    <input type="checkbox" class="svc-check" value="<?php echo e($service->id); ?>" onchange="updateBulk()">
                </td>
                <td>
                    <div style="font-weight:600;"><?php echo e($service->name); ?></div>
                    <?php if($service->code): ?>
                    <div style="font-size:0.75rem;color:#999;"><?php echo e($service->code); ?></div>
                    <?php endif; ?>
                    <?php if($service->show_on_website && $service->is_approved && $service->is_active): ?>
                    <span class="website-pill">🌐 live</span>
                    <?php endif; ?>
                </td>
                <td><span class="category-tag"><?php echo e(ucwords($service->category ?? '—')); ?></span></td>
                <td>
                    <strong>£<?php echo e(number_format($service->default_price, 2)); ?></strong>
                    <?php if($service->vat_rate > 0): ?>
                    <div style="font-size:0.72rem;color:#999;">+<?php echo e($service->vat_rate); ?>% VAT</div>
                    <?php endif; ?>
                </td>
                <td style="color:#666;font-size:0.85rem;">
                    <?php echo e($service->estimated_duration_minutes ? $service->estimated_duration_minutes . ' min' : '—'); ?>

                </td>
                <td>
                    <?php if($service->is_active): ?>
                        <span class="badge badge-success">✓ Active</span>
                    <?php else: ?>
                        <span class="badge badge-danger">✗ Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($service->is_approved): ?>
                        <span class="badge badge-info">✓ Approved</span>
                    <?php else: ?>
                        <span class="badge badge-warning">⏳ Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($service->show_on_website && $service->is_approved && $service->is_active): ?>
                        <span class="badge badge-orange">🌐 Shown</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Hidden</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="<?php echo e(route('services.edit', $service)); ?>" class="btn-xs btn-edit">Edit</a>

                        <form method="POST" action="<?php echo e(route('services.toggle-approve', $service)); ?>" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php if($service->is_approved): ?>
                                <button type="submit" class="btn-xs btn-unapprove">Unapprove</button>
                            <?php else: ?>
                                <button type="submit" class="btn-xs btn-approve">Approve</button>
                            <?php endif; ?>
                        </form>

                        <form method="POST" action="<?php echo e(route('services.toggle-website', $service)); ?>" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php if($service->show_on_website): ?>
                                <button type="submit" class="btn-xs btn-web-off">Hide Web</button>
                            <?php else: ?>
                                <button type="submit" class="btn-xs btn-web-on" title="<?php echo e(!$service->is_approved ? 'Must be approved first' : ''); ?>">Show Web</button>
                            <?php endif; ?>
                        </form>

                        <form method="POST" action="<?php echo e(route('services.toggle-active', $service)); ?>" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php if($service->is_active): ?>
                                <button type="submit" class="btn-xs btn-disable">Disable</button>
                            <?php else: ?>
                                <button type="submit" class="btn-xs btn-enable">Enable</button>
                            <?php endif; ?>
                        </form>

                        <form method="POST" action="<?php echo e(route('services.destroy', $service)); ?>" style="display:inline;"
                              onsubmit="return confirm('Delete \'<?php echo e(addslashes($service->name)); ?>\'?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn-xs btn-delete">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:#999;">
                    No services found. <a href="<?php echo e(route('services.create')); ?>">Add your first service →</a>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if($services->hasPages()): ?>
    <div class="pagination">
        <?php echo e($services->links()); ?>

    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function updateBulk() {
    const checked = document.querySelectorAll('.svc-check:checked');
    const ids = Array.from(checked).map(c => c.value);
    document.getElementById('bulkIds').value = ids.join(',');
    document.getElementById('bulkCount').textContent = checked.length + ' selected';
    document.getElementById('bulkBar').classList.toggle('visible', checked.length > 0);
    document.getElementById('bulkForm').elements['service_ids'].value = '';
    // We'll assign them as separate inputs via hidden field
}

document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const ids = Array.from(document.querySelectorAll('.svc-check:checked')).map(c => c.value);
    // Remove old extras
    document.querySelectorAll('.bulk-id-input').forEach(el => el.remove());
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'service_ids[]';
        inp.value = id;
        inp.className = 'bulk-id-input';
        this.appendChild(inp);
    });
});

function toggleSelectAll(cb) {
    document.querySelectorAll('.svc-check').forEach(c => c.checked = cb.checked);
    updateBulk();
}

function confirmBulk(e) {
    const action = document.querySelector('#bulkForm select[name=action]').value;
    if (action === 'delete') return confirm('Delete the selected services? This cannot be undone.');
    return true;
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\admin\services\index.blade.php ENDPATH**/ ?>