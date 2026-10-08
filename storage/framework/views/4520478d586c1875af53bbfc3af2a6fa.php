<?php $__env->startComponent('mail::message'); ?>
# Kode OTP Pendaftaran PEMIRA Polbeng

Halo Mahasiswa dengan NIM **<?php echo new \Illuminate\Support\EncodedHtmlString($nim); ?>**,

Berikut adalah kode OTP Anda untuk menyelesaikan pendaftaran:

<?php $__env->startComponent('mail::panel'); ?>
<div style="text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px;">
<?php echo new \Illuminate\Support\EncodedHtmlString($otp); ?>

</div>
<?php echo $__env->renderComponent(); ?>

Kode ini berlaku selama **10 menit** dan hanya dapat digunakan **satu kali**.
Maksimal **5 percobaan** salah.

> Jika Anda tidak merasa mendaftar, abaikan email ini.

Salam,<br>
Panitia KPU-M PEMIRA Polbeng
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/emails/otp.blade.php ENDPATH**/ ?>