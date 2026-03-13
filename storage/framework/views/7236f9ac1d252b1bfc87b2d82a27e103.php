

<?php $__env->startSection('title', 'Add New Service'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        max-width: 900px;
    }
    .form-card-header {
        padding: 20px 28px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .form-card-header h2 { font-size: 1.3rem; color: #333; }
    .form-body { padding: 28px; }
    .form-section { margin-bottom: 28px; }
    .form-section h3 {
        font-size: 1rem;
        color: #667eea;
        border-bottom: 2px solid #e8ecff;
        padding-bottom: 8px;
        margin-bottom: 18px;
        font-weight: 700;
    }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-weight: 600; font-size: 0.88rem; color: #444; }
    .form-group input[type=text],
    .form-group input[type=number],
    .form-group select,
    .form-group textarea {
        padding: 10px 13px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.9rem;
        font-family: inherit;
        transition: border-color 0.2s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
    }
    .form-group textarea { resize: vertical; min-height: 90px; }
    .form-group .hint { font-size: 0.78rem; color: #888; }
    .toggle-row { display: flex; gap: 24px; flex-wrap: wrap; }
    .toggle-group {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px 16px;
        flex: 1;
        min-width: 160px;
    }
    .toggle-group label { margin: 0; font-weight: 600; font-size: 0.88rem; }
    .toggle-group .hint { font-size: 0.76rem; color: #888; }
    .toggle-group input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }
    .form-actions {
        padding: 20px 28px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    .error-msg { color: #dc3545; font-size: 0.8rem; }
    .datalist-input { position: relative; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div style="margin-bottom:16px;">
    <a href="<?php echo e(route('services.index')); ?>" style="color:#667eea;text-decoration:none;">← Back to Services</a>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h2>➕ Add New Service</h2>
        <span style="font-size:0.85rem;color:#888;">All services require approval before showing on website</span>
    </div>

    <?php if($errors->any()): ?>
    <div class="alert alert-danger" style="margin:16px 28px 0;">
        <strong>Please fix the following errors:</strong>
        <ul style="margin-top:8px;padding-left:20px;">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('services.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-body">

            
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="name">Service Name <span style="color:red">*</span></label>
                        <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required placeholder="e.g. Full Service">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error-msg"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label for="code">Service Code</label>
                        <input type="text" name="code" id="code" value="<?php echo e(old('code')); ?>" placeholder="AUTO-GENERATED if blank">
                        <span class="hint">Unique identifier. Leave blank to auto-generate.</span>
                        <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error-msg"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="form-grid-2" style="margin-top:16px;">
                    <div class="form-group">
                        <label for="category">Category <span style="color:red">*</span></label>
                        <input type="text" name="category" id="category" list="category-list"
                               value="<?php echo e(old('category')); ?>" required placeholder="e.g. MOT, Service, Repair">
                        <datalist id="category-list">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <option value="MOT">
                            <option value="Service">
                            <option value="Repair">
                            <option value="Diagnostics">
                            <option value="Bodywork">
                            <option value="Electrical">
                            <option value="Tyres">
                            <option value="Commercial">
                        </datalist>
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error-msg"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon / Emoji</label>
                        <input type="text" name="icon" id="icon" value="<?php echo e(old('icon')); ?>" placeholder="e.g. 🔧 or fa-wrench">
                        <span class="hint">Emoji or icon class for display on website.</span>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label for="description">Internal Description</label>
                    <textarea name="description" id="description" placeholder="Internal notes about this service…"><?php echo e(old('description')); ?></textarea>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label for="website_description">Website Description</label>
                    <textarea name="website_description" id="website_description"
                              placeholder="Description shown to customers on the website…"><?php echo e(old('website_description')); ?></textarea>
                    <span class="hint">This text appears publicly on your website when the service is enabled.</span>
                </div>
            </div>

            
            <div class="form-section">
                <h3>Pricing & Duration</h3>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="default_price">Price (£) <span style="color:red">*</span></label>
                        <input type="number" name="default_price" id="default_price"
                               value="<?php echo e(old('default_price', '0.00')); ?>" min="0" step="0.01" required>
                        <?php $__errorArgs = ['default_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error-msg"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group">
                        <label for="cost_price">Cost Price (£)</label>
                        <input type="number" name="cost_price" id="cost_price"
                               value="<?php echo e(old('cost_price', '0.00')); ?>" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="vat_rate">VAT Rate (%)</label>
                        <input type="number" name="vat_rate" id="vat_rate"
                               value="<?php echo e(old('vat_rate', '20.00')); ?>" min="0" max="100" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="estimated_duration_minutes">Duration (mins)</label>
                        <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes"
                               value="<?php echo e(old('estimated_duration_minutes', '60')); ?>" min="0" step="15">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order"
                               value="<?php echo e(old('sort_order', '0')); ?>" min="0">
                        <span class="hint">Lower number = shown first on website.</span>
                    </div>
                </div>
            </div>

            
            <div class="form-section">
                <h3>Status & Website Visibility</h3>
                <div class="toggle-row">
                    <div class="toggle-group">
                        <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo e(old('is_active', '1') ? 'checked' : ''); ?>>
                        <div>
                            <label for="is_active">Active</label>
                            <div class="hint">Service is available for use in the system</div>
                        </div>
                    </div>
                    <div class="toggle-group">
                        <input type="checkbox" name="requires_booking" id="requires_booking" value="1" <?php echo e(old('requires_booking', '1') ? 'checked' : ''); ?>>
                        <div>
                            <label for="requires_booking">Requires Booking</label>
                            <div class="hint">Customers must book in advance</div>
                        </div>
                    </div>
                    <div class="toggle-group">
                        <input type="checkbox" name="is_approved" id="is_approved" value="1" <?php echo e(old('is_approved') ? 'checked' : ''); ?>>
                        <div>
                            <label for="is_approved">✅ Approved</label>
                            <div class="hint">Mark as approved for publication</div>
                        </div>
                    </div>
                    <div class="toggle-group">
                        <input type="checkbox" name="show_on_website" id="show_on_website" value="1" <?php echo e(old('show_on_website') ? 'checked' : ''); ?>>
                        <div>
                            <label for="show_on_website">🌐 Show on Website</label>
                            <div class="hint">Must be approved & active to appear live</div>
                        </div>
                    </div>
                </div>
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 16px;margin-top:16px;font-size:0.85rem;">
                    ⚠️ A service will only appear on the website when it is <strong>Active</strong>, <strong>Approved</strong>, and <strong>Show on Website</strong> are all enabled.
                </div>
            </div>

        </div>
        <div class="form-actions">
            <a href="<?php echo e(route('services.index')); ?>" class="btn" style="background:#eee;color:#333;">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Service</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\admin\services\create.blade.php ENDPATH**/ ?>