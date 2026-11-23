{{-- =====================================================================
     CSS halaman Pendaftaran Konseling Kelompok (daftar / konfirmasi / berhasil)

     Dipakai ulang dari CSS global + halaman Konseling Kelompok:
       .container .section-pad .btn .btn-primary
       .page-hero-badge  .slot-badge .slot-open .slot-almost
     Kelas baru di file ini semuanya berawalan .reg-
     ===================================================================== --}}
<style>
    .reg-container { max-width: 800px; }

    /* ---------- Link kembali ---------- */
    .reg-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: var(--primary, #7DA381);
        text-decoration: none;
        margin-bottom: 28px;
        transition: color .15s;
    }
    .reg-back:hover { color: #5F8A66; }

    /* ---------- Judul halaman ---------- */
    .reg-head {
        text-align: center;
        margin-bottom: 32px;
    }
    .reg-head h1 {
        font-size: clamp(24px, 3.4vw, 34px);
        font-weight: 700;
        line-height: 1.25;
        margin: 14px 0 12px;
    }
    .reg-head p {
        color: #6B7280;
        font-size: 15.5px;
        line-height: 1.6;
        margin: 0;
    }

    /* ---------- Kartu ringkasan program ---------- */
    .reg-program {
        background: #F3F8F3;
        border: 1px solid #CFE2D2;
        border-radius: 16px;
        padding: 22px 24px;
        margin-bottom: 26px;
    }
    .reg-program-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }
    .reg-program-top h2 {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 6px;
    }
    .reg-program-top p {
        font-size: 13.5px;
        color: #6B7280;
        margin: 0;
    }
    .reg-program-meta {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px 24px;
    }
    .reg-program-meta li {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #4B5563;
    }
    .reg-program-meta svg { flex-shrink: 0; color: #9CA3AF; }

    /* ---------- Kartu putih (form / konfirmasi / detail) ---------- */
    .reg-card {
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 16px;
        padding: 28px 32px 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }
    .reg-card > h2 {
        font-size: 19px;
        font-weight: 700;
        margin: 0 0 8px;
    }
    .reg-card-sub {
        font-size: 14px;
        color: #6B7280;
        margin: 0 0 24px;
    }

    /* ---------- Form ---------- */
    .reg-field { margin-bottom: 18px; }
    .reg-field label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #1F2937;
    }
    .reg-field .req { color: #EF4444; }

    .reg-field input,
    .reg-field select {
        width: 100%;
        background: #fff;
        border: 1px solid #D9DED4;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        color: #1F2937;
        transition: border-color .15s, box-shadow .15s;
    }
    .reg-field input::placeholder { color: #9CA3AF; }
    .reg-field input:focus,
    .reg-field select:focus {
        outline: none;
        border-color: var(--primary, #7DA381);
        box-shadow: 0 0 0 3px rgba(125, 163, 129, .18);
    }
    .reg-field select { cursor: pointer; }

    .reg-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0 20px;
    }

    .reg-alert {
        background: #FEE2E2;
        border: 1px solid #FCA5A5;
        color: #B91C1C;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 13.5px;
        margin-bottom: 20px;
    }
    .reg-alert ul { margin: 0; padding-left: 18px; }

    /* ---------- Tombol aksi ---------- */
    .reg-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 26px;
    }
    .reg-actions-end { justify-content: flex-end; }
    .reg-btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 14px;
        border: none;
        cursor: pointer;
    }
    .reg-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: var(--primary, #7DA381);
        border: 1px solid var(--primary, #7DA381);
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s;
    }
    .reg-btn-ghost:hover { background: #F3F8F3; }
    .reg-btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* ---------- Konfirmasi: label seksi + baris data ---------- */
    .reg-section-label {
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--primary, #7DA381);
        margin: 26px 0 10px;
    }
    .reg-section-label:first-of-type { margin-top: 0; }

    .reg-table {
        border: 1px solid #EDEFEA;
        border-radius: 10px;
        overflow: hidden;
    }
    .reg-row {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 12px;
        align-items: center;
        background: #F8F9F7;
        padding: 14px 18px;
        border-bottom: 1px solid #fff;
    }
    .reg-row:last-child { border-bottom: none; }
    .reg-row dt {
        font-size: 13.5px;
        color: #6B7280;
        margin: 0;
    }
    .reg-row dd {
        font-size: 14px;
        color: #1F2937;
        font-weight: 500;
        margin: 0;
        word-break: break-word;
    }

    /* ---------- Checkbox persetujuan ---------- */
    .reg-agree {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #F8F9F7;
        border: 1px solid #EDEFEA;
        border-radius: 10px;
        padding: 16px 18px;
        margin-top: 22px;
        font-size: 14px;
        color: #1F2937;
    }
    .reg-agree input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: var(--primary, #7DA381);
        cursor: pointer;
        flex-shrink: 0;
    }
    .reg-agree label { cursor: pointer; line-height: 1.5; }
    .reg-agree .req { color: #EF4444; }

    /* ---------- Halaman berhasil ---------- */
    /* halaman berhasil dirapatkan ke atas */
    .reg-success-pad { padding-top: 40px; }

    .reg-success { text-align: center; }
    .reg-success-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #DCFCE7;
        color: #2F6B3F;
    }
    .reg-success h1 {
        font-size: clamp(23px, 3.2vw, 31px);
        font-weight: 700;
        line-height: 1.25;
        margin: 10px 0 10px;
    }
    .reg-success > p {
        max-width: 480px;
        margin: 0 auto 24px;
        color: #6B7280;
        font-size: 15px;
        line-height: 1.6;
    }

    /* ---------- Kartu grup WhatsApp ---------- */
    .reg-wa {
        position: relative;
        display: flex;
        align-items: center;
        gap: 18px;
        max-width: 520px;
        margin: 0 auto 22px;
        padding: 20px 22px;
        text-align: left;
        background: #fff;
        border: 1px solid #CFE2D2;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        overflow: hidden;
        flex-wrap: wrap;
        transition: box-shadow .2s, border-color .2s;
    }
    .reg-wa::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: #25D366;
    }
    .reg-wa:hover {
        border-color: #A8D7B4;
        box-shadow: 0 10px 24px rgba(60, 90, 65, .10);
    }
    .reg-wa-icon {
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: #E7F9EE;
        color: #1DA851;
    }
    .reg-wa-text { flex: 1 1 220px; min-width: 0; }
    .reg-wa-text h2 {
        font-size: 15.5px;
        font-weight: 700;
        margin: 0 0 5px;
    }
    .reg-wa-text p {
        font-size: 13px;
        line-height: 1.6;
        color: #6B7280;
        margin: 0;
    }
    .reg-wa-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 14px;
        background: #25D366;
        border-color: #25D366;
        color: #fff;
    }
    .reg-wa-btn:hover { background: #1DA851; border-color: #1DA851; }

    .reg-code {
        background: #F3F8F3;
        border: 1px solid #CFE2D2;
        border-radius: 12px;
        padding: 16px 20px;
        max-width: 520px;
        margin: 0 auto 18px;
    }
    .reg-code-label {
        font-size: 12px;
        color: #6B7280;
        margin: 0 0 4px;
    }
    .reg-code strong {
        display: block;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: .04em;
        color: #2F6B3F;
        margin-bottom: 5px;
    }
    .reg-code small {
        font-size: 11.5px;
        color: #9CA3AF;
    }

    .reg-detail {
        max-width: 520px;
        margin: 0 auto 26px;
        background: #fff;
        border: 1px solid #E5E9E1;
        border-radius: 14px;
        padding: 8px 26px;
        text-align: left;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
    }
    .reg-detail-row {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 12px;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #EDEFEA;
    }
    .reg-detail-row:last-child { border-bottom: none; }
    .reg-detail-row dt { font-size: 13.5px; color: #6B7280; margin: 0; }
    .reg-detail-row dd { font-size: 14px; color: #1F2937; margin: 0; }

    .reg-success-actions {
        max-width: 520px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .reg-success-actions .btn-primary,
    .reg-success-actions .reg-btn-ghost {
        width: 100%;
        justify-content: center;
        text-align: center;
        padding: 14px 20px;
        border-radius: 10px;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 767px) {
        .reg-grid-2,
        .reg-program-meta { grid-template-columns: 1fr; }
        .reg-card { padding: 24px 20px 28px; }
        .reg-row,
        .reg-detail-row { grid-template-columns: 1fr; gap: 4px; }
        .reg-program-top { flex-direction: column; }
        .reg-actions { flex-direction: column-reverse; }
        .reg-actions .btn, .reg-actions .reg-btn-ghost { width: 100%; justify-content: center; }
    }

    @media (max-width: 420px) {
        .reg-container { width: 100%; padding-left: 12px; padding-right: 12px; box-sizing: border-box; }
        .reg-card { padding: 20px 15px 24px; border-radius: 14px; }
        .reg-card h1 { font-size: 21px; }
        .reg-program-card { padding: 15px; }
        .reg-detail { padding-left: 16px; padding-right: 16px; }
        .reg-wa { padding: 14px; }
        .reg-wa-icon { flex-basis: 40px; width: 40px; height: 40px; }
    }
</style>
