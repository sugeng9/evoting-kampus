@component('mail::message')
# Kode OTP Pendaftaran PEMIRA Polbeng

Halo Mahasiswa dengan NIM **{{ $nim }}**,

Berikut adalah kode OTP Anda untuk menyelesaikan pendaftaran:

@component('mail::panel')
<div style="text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px;">
{{ $otp }}
</div>
@endcomponent

Kode ini berlaku selama **10 menit** dan hanya dapat digunakan **satu kali**.
Maksimal **5 percobaan** salah.

> Jika Anda tidak merasa mendaftar, abaikan email ini.

Salam,<br>
Panitia KPU-M PEMIRA Polbeng
@endcomponent
