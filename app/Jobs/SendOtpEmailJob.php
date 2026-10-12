<?php

namespace App\Jobs;

use App\Mail\OtpMail;
use App\Services\AuditLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOtpEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika terjadi kendala jaringan / provider email
     */
    public int $tries = 3;

    /**
     * Waktu tunggu sebelum retry (exponential backoff): 5 detik, 15 detik, 30 detik
     */
    public array $backoff = [5, 15, 30];

    /**
     * Timeout eksekusi per worker job (detik)
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $nim,
        public string $email,
        public string $plainOtp
    ) {}

    /**
     * Execute the job asynchronously.
     */
    public function handle(): void
    {
        $mailHost = env('MAIL_HOST', config('mail.mailers.smtp.host', ''));
        $mailPassword = env('MAIL_PASSWORD', config('mail.mailers.smtp.password', ''));

        // 1. Kirim via Resend HTTPS REST API jika kredensial aktif
        if ((str_contains($mailHost, 'resend.com') || str_starts_with((string) $mailPassword, 're_')) && !empty($mailPassword) && !str_contains($mailPassword, 'TEMPEL_KODE')) {
            $fromAddress = env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev');
            $fromName = env('MAIL_FROM_NAME', 'KPR PEMIRA POLITEKNIK NEGERI BENGKALIS');

            $emailHtml = $this->renderHtmlEmail($this->nim, $this->plainOtp);

            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withToken($mailPassword)
                ->post('https://api.resend.com/emails', [
                    'from'    => "{$fromName} <{$fromAddress}>",
                    'to'      => [$this->email],
                    'subject' => '[PEMIRA POLITEKNIK NEGERI BENGKALIS] Kode OTP Pendaftaran Pemilih',
                    'html'    => $emailHtml,
                ]);

            if (!$response->successful()) {
                $errorMsg = $response->json()['message'] ?? $response->body();
                Log::error("[RESEND QUEUE ERROR] Ke: {$this->email} | Response: {$errorMsg}");
                throw new \RuntimeException("Resend API gagal: {$errorMsg}");
            }

            Log::info("[OTP EMAIL QUEUE SUCCESS] Email OTP terkirim via Resend ke {$this->email} untuk NIM {$this->nim}");
            return;
        }

        // 2. Fallback SMTP standar (kirim langsung tanpa double-queue)
        try {
            Mail::to($this->email)->sendNow(new OtpMail($this->plainOtp, $this->nim));
            Log::info("[OTP EMAIL QUEUE SUCCESS] Email OTP terkirim via SMTP ke {$this->email} untuk NIM {$this->nim}");
        } catch (Throwable $e) {
            Log::error("[OTP SMTP QUEUE ERROR] Ke: {$this->email} | Error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Tangani kegagalan permanen setelah seluruh retry habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::critical("[OTP QUEUE DEAD LETTER] Gagal mengirimkan email OTP ke {$this->email} untuk NIM {$this->nim} setelah {$this->tries}x percobaan. Error: " . ($exception?->getMessage() ?? 'Unknown error'));
        AuditLogger::log('OTP_QUEUE_PERMANENT_FAIL', "Gagal kirim OTP ke {$this->email} setelah {$this->tries}x retry: " . ($exception?->getMessage() ?? ''), $this->nim, 'system');
    }

    /**
     * Render template HTML email OTP
     */
    protected function renderHtmlEmail(string $nim, string $plainOtp): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #334155;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; border: 1px solid #e2e8f0; text-align: center;">
        <h2 style="color: #0f172a; margin-bottom: 8px;">KPR PEMIRA POLITEKNIK NEGERI BENGKALIS</h2>
        <p style="color: #64748b; font-size: 14px; margin-top: 0;">Otentikasi Akun Pemilih Mahasiswa</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="text-align: left; font-size: 15px;">Halo Mahasiswa dengan NIM <strong>{$nim}</strong>,</p>
        <p style="text-align: left; font-size: 14px; color: #475569;">Berikut adalah Kode OTP rahasia Anda untuk menyelesaikan pendaftaran akun pemilih:</p>
        <div style="margin: 25px 0; padding: 18px; background: #f1f5f9; border-radius: 8px; border: 2px dashed #cbd5e1;">
            <span style="font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0284c7; font-family: monospace;">{$plainOtp}</span>
        </div>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
            Kode OTP ini berlaku selama <strong>5 menit</strong> dan hanya dapat digunakan 1 kali.<br>
            Jangan berikan kode ini kepada siapa pun demi kerahasiaan hak suara Anda.
        </p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 11px; color: #94a3b8;">Komisi Pemilihan Raya (KPR) &bull; Politeknik Negeri Bengkalis</p>
    </div>
</body>
</html>
HTML;
    }
}
