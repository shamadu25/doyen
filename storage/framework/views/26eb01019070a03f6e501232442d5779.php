<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointment Reschedule Request</title>
<style>
  body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; color:#333; }
  .container { max-width:600px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
  .header { background:#1e3a5f; padding:30px; text-align:center; color:#fff; }
  .header h1 { margin:0; font-size:22px; }
  .header p { margin:6px 0 0; font-size:14px; color:#a0c4e8; }
  .body { padding:30px; }
  .info-box { background:#f0f7ff; border-left:4px solid #2563eb; padding:16px 20px; border-radius:4px; margin:20px 0; }
  .info-box p { margin:4px 0; font-size:14px; }
  .info-box strong { color:#1e3a5f; }
  .btn { display:inline-block; padding:14px 32px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:15px; margin:8px 4px; }
  .btn-accept { background:#16a34a; color:#fff; }
  .btn-decline { background:#dc2626; color:#fff; }
  .footer { background:#f4f4f4; padding:20px 30px; text-align:center; font-size:12px; color:#777; border-top:1px solid #e5e7eb; }
  .original-box { background:#fff7ed; border-left:4px solid #f97316; padding:12px 16px; border-radius:4px; margin:16px 0; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Doyen Auto Services</h1>
    <p>Appointment Reschedule Request</p>
  </div>
  <div class="body">
    <p>Dear <strong><?php echo e($appointment->customer->first_name ?? 'Customer'); ?></strong>,</p>

    <p>We would like to propose a new date and time for your upcoming appointment. Please review the details below and <strong>accept or decline</strong> the new time.</p>

    <div class="info-box">
      <p><strong>Vehicle:</strong>
        <?php echo e($appointment->vehicle->make ?? ''); ?> <?php echo e($appointment->vehicle->model ?? ''); ?>

        (<?php echo e(strtoupper($appointment->vehicle->registration_number ?? '')); ?>)
      </p>
      <p><strong>Service:</strong> <?php echo e(ucwords(str_replace('-', ' ', $appointment->appointment_type ?? 'Service'))); ?></p>
      <p><strong>Proposed New Date:</strong> <?php echo e(\Carbon\Carbon::parse($appointment->proposed_date)->format('l, d F Y')); ?></p>
      <p><strong>Proposed New Time:</strong> <?php echo e(\Carbon\Carbon::parse('2000-01-01 ' . $appointment->proposed_time)->format('g:i A')); ?></p>
    </div>

    <div class="original-box">
      <p style="margin:0;font-size:13px;color:#92400e;">
        <strong>Original booking:</strong>
        <?php echo e($appointment->scheduled_date ? $appointment->scheduled_date->format('D, d M Y \a\t g:i A') : 'N/A'); ?>

      </p>
    </div>

    <p style="text-align:center;margin:28px 0 12px;">
      <a href="<?php echo e($acceptUrl); ?>" class="btn btn-accept">&#10003; Accept New Time</a>
      <a href="<?php echo e($declineUrl); ?>" class="btn btn-decline">&#10007; Decline / Keep Original</a>
    </p>

    <p style="font-size:13px;color:#666;">If the buttons above do not work, copy and paste these links into your browser:</p>
    <p style="font-size:12px;word-break:break-all;color:#2563eb;">Accept: <?php echo e($acceptUrl); ?></p>
    <p style="font-size:12px;word-break:break-all;color:#dc2626;">Decline: <?php echo e($declineUrl); ?></p>

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">
    <p style="font-size:13px;color:#555;">If you have any questions, please contact us:</p>
    <p style="font-size:13px;color:#555;">
      📞 <strong>+44 141 482 0726</strong><br>
      📍 Rutherglen, Glasgow
    </p>
  </div>
  <div class="footer">
    &copy; <?php echo e(date('Y')); ?> Doyen Auto Services &mdash; Precision Vehicle Diagnostics &amp; Technical Solutions Centre
  </div>
</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\doyen\doyen\resources\views\emails\appointment-rescheduled.blade.php ENDPATH**/ ?>