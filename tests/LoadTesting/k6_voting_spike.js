import http from 'k6/http';
import { check, sleep, group } from 'k6';

/**
 * ============================================================================
 * SKENARIO UJI BEBAN PUNCAK (PEAK LOAD TESTING) - 6.000 PEMILIH SERENTAK
 * SISTEM E-VOTING KAMPUS - POLITEKNIK NEGERI BENGKALIS
 * ============================================================================
 * 
 * CARA MENJALANKAN (Pastikan k6 sudah terinstal di server/laptop):
 * 
 * 1. Mode Uji Beban Ringan (Smoke Test - 50 Pemilih):
 *    k6 run --vus 50 --duration 30s tests/LoadTesting/k6_voting_spike.js
 * 
 * 2. Mode Simulasi Beban Puncak Bertahap (Full Ramp-up hingga 6.000 Pemilih):
 *    k6 run tests/LoadTesting/k6_voting_spike.js
 * ============================================================================
 */

export const options = {
    stages: [
        { duration: '30s', target: 200 },    // Ramp-up awal: 200 pemilih online
        { duration: '1m',  target: 1000 },   // Peningkatan cepat: 1.000 pemilih
        { duration: '2m',  target: 3000 },   // Jam sibuk istirahat kuliah: 3.000 pemilih
        { duration: '1m',  target: 6000 },   // Lonjakan puncak (Peak Spike): 6.000 pemilih serentak
        { duration: '1m',  target: 1000 },   // Penurunan beban: 1.000 pemilih
        { duration: '30s', target: 0 },      // Cool-down selesai
    ],
    thresholds: {
        // 95% request harus selesai di bawah 500ms
        http_req_duration: ['p(95)<500'],
        // Rasio error (500, 502, 504) wajib di bawah 1%
        http_req_failed: ['rate<0.01'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
    const rand = Math.random();

    // 1. Skenario A (40% traffic): Pemilih & Publik memantau Live Quick Count
    if (rand < 0.40) {
        group('1. Polling Live Quick Count', function () {
            const res = http.get(`${BASE_URL}/api/quick-count/live`, {
                headers: { 'Accept': 'application/json' },
            });
            check(res, {
                'Quick Count Status 200': (r) => r.status === 200,
                'Quick Count Response < 300ms': (r) => r.timings.duration < 300,
            });
        });
    }
    // 2. Skenario B (30% traffic): Membuka Halaman Login / Portal Bilik Suara
    else if (rand < 0.70) {
        group('2. Akses Portal Bilik Suara', function () {
            const res = http.get(`${BASE_URL}/voter/login`);
            check(res, {
                'Login Page Status 200': (r) => r.status === 200,
                'HTML Contains Bilik Suara': (r) => r.body && r.body.includes('Bilik Suara'),
            });
        });
    }
    // 3. Skenario C (20% traffic): Mengakses Halaman Cek Status DPT
    else if (rand < 0.90) {
        group('3. Akses Form Cek Status NIM', function () {
            const res = http.get(`${BASE_URL}/nim-status`);
            check(res, {
                'Cek Status NIM Page 200': (r) => r.status === 200,
            });
        });
    }
    // 4. Skenario D (10% traffic): Akses Halaman Verifikasi Surat Suara Independen
    else {
        group('4. Akses Halaman Audit Verifikasi Suara', function () {
            const res = http.get(`${BASE_URL}/verify-ballot`);
            check(res, {
                'Verify Ballot Page 200': (r) => r.status === 200,
            });
        });
    }

    // Jeda antar request alami (pacing 0.5s - 1.5s menyerupai interaksi manusia)
    sleep(Math.random() * 1.0 + 0.5);
}
