<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lembar Kerja Kalibrasi - <?= escape($worksheet['certificate_number'] ?? '-') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: 'Arial Narrow', Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        table, th, td {
            font-family: 'Arial Narrow', Arial, Helvetica, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        /* Main Title */
        .worksheet-heading {
            text-align: center;
            margin: 5px 0 10px 0;
        }
        .worksheet-heading h2 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .worksheet-heading h3 {
            margin: 2px 0 0 0;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Info 2-Col Section */
        .info-wrapper {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 8px;
        }
        .info-wrapper > tbody > tr > td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .info-list {
            width: 100%;
            table-layout: fixed;
        }
        .info-list td {
            padding: 1px 3px;
            font-size: 12px;
            line-height: 1.2;
            vertical-align: top;
            word-wrap: break-word;
        }
        .info-label {
            font-weight: bold;
            white-space: nowrap;
        }
        .info-label-indent {
            font-weight: bold;
            padding-left: 16px !important;
            white-space: nowrap;
        }

        /* Section Bar */
        .section-bar {
            background-color: #cbd5e1;
            font-weight: bold;
            font-size: 12px;
            padding: 3.5px 8px;
            margin-bottom: 5px;
            border: 1px solid #64748b;
        }

        /* Measurement Table */
        .measure-table {
            width: 100%;
            table-layout: fixed;
            border: 1px solid #000000;
            margin-bottom: 8px;
        }
        .measure-table th {
            border: 1px solid #000000;
            padding: 4px 4px;
            text-align: center;
            font-size: 12px;
            line-height: 1.2;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .measure-table td {
            border: 1px solid #000000;
            padding: 2.5px 6px;
            text-align: center;
            font-size: 12px;
            line-height: 1.2;
            height: 15px;
        }
        .u95-cell {
            text-align: left !important;
            font-weight: bold;
            padding-left: 6px !important;
        }

        /* Notes Lined Box */
        .notes-table {
            width: 100%;
            table-layout: fixed;
            border: 1px solid #000000;
            margin-bottom: 8px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .notes-table td {
            border: 1px solid #000000;
            height: 15px;
            padding: 2px 6px;
            font-size: 12px;
            line-height: 1.2;
            word-wrap: break-word;
        }

        /* Signature Table */
        .sig-table {
            width: 100%;
            table-layout: fixed;
            border: 1px solid #000000;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .sig-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .sig-table th, .sig-table td {
            border: 1px solid #000000;
            padding: 3.5px 8px;
            font-size: 12px;
            line-height: 1.2;
        }
        .sig-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .sig-table .sig-label {
            width: 18%;
            border-right: none;
            padding-right: 2px;
            white-space: nowrap;
        }
        .sig-table .sig-colon {
            width: 2%;
            border-left: none;
            padding-left: 0;
            padding-right: 6px;
            text-align: right;
        }
        .sig-box {
            height: 64px;
            text-align: center;
            vertical-align: middle;
        }
        .sig-img {
            max-height: 58px;
            max-width: 180px;
        }
    </style>
</head>
<body>
<?php if (!empty($worksheet['is_draft'])): ?>
    <div style="position:fixed;top:40%;left:12%;font-size:65px;color:#dddddd;transform:rotate(-30deg);z-index:-1;">DRAFT — BELUM DISAHKAN</div>
    <div style="position:fixed;bottom:-22px;left:0;font-size:10px;color:#b45309;">DRAFT — Belum disahkan oleh pemeriksa. Bukan dokumen final.</div>
<?php endif; ?>
    <?php
        $logoPath = __DIR__ . '/peruri-logo.jpg';
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    ?>

    <?php
    $document = $worksheet;
    $documentNumber = $worksheet['doc_number'] ?? '001/LK-KLBI/2024';
    require __DIR__ . '/calibration_header.php';
    ?>

    <!-- JUDUL LEMBAR KERJA -->
    <div class="worksheet-heading">
        <h2><?= escape(($worksheet['equipment_category'] ?? 'internal') === 'external' ? 'KALIBRASI EKSTERNAL' : 'KALIBRASI INTERNAL') ?></h2>
        <h3>LEMBAR KERJA KALIBRASI</h3>
    </div>

    <!-- IDENTITAS ALAT & ALAT STANDAR (2 KOLOM SIMETRIS 50% - 50%) -->
    <table class="info-wrapper">
        <tr>
            <!-- Kolom Kiri -->
            <td width="50%">
                <table class="info-list">
                    <tr>
                        <td class="info-label" width="38%">No. Sertifikat</td>
                        <td width="4%">:</td>
                        <td><?= escape($worksheet['certificate_number'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Nama Alat</td>
                        <td>:</td>
                        <td><?= escape($worksheet['equipment_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Rentang ukur</td>
                        <td>:</td>
                        <td><?= escape($worksheet['measuring_range'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Resolusi</td>
                        <td>:</td>
                        <td><?= escape($worksheet['resolution'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Tipe/Model</td>
                        <td>:</td>
                        <td><?= escape($worksheet['type_model'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Nomor Seri</td>
                        <td>:</td>
                        <td><?= escape($worksheet['serial_number'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Merk/Buatan</td>
                        <td>:</td>
                        <td><?= escape($worksheet['brand'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Kalibrasi</td>
                        <td>:</td>
                        <td><?= escape($worksheet['calibration_date_formatted'] ?? ($worksheet['calibration_date'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Metode kalibrasi</td>
                        <td>:</td>
                        <td><?= escape($worksheet['calibration_method'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Pemilik Alat</td>
                        <td>:</td>
                        <td><?= escape($worksheet['owner_section'] ?? '-') ?></td>
                    </tr>
                </table>
            </td>

            <!-- Kolom Kanan -->
            <td width="50%">
                <table class="info-list">
                    <tr>
                        <td class="info-label" width="42%">Data Alat Standar</td>
                        <td width="4%">:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Nama</td>
                        <td>:</td>
                        <td><?= escape($worksheet['std_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">No. Sertifikat</td>
                        <td>:</td>
                        <td><?= escape($worksheet['std_certificate_number'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Tanggal Sertifikat</td>
                        <td>:</td>
                        <td><?= escape($worksheet['std_certificate_date'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label-indent">Ketertelusuran</td>
                        <td>:</td>
                        <td><?= escape($worksheet['std_traceability'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="height: 8px;"></td>
                    </tr>
                    <tr>
                        <td class="info-label">Lokasi Kalibrasi</td>
                        <td>:</td>
                        <td><?= escape($worksheet['calibration_location'] ?? 'Ruang Kalibrasi') ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Kondisi Lingkungan</td>
                        <td>:</td>
                        <td>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 0;"><?= escape($worksheet['env_temperature'] ?? '-') ?></td>
                                    <td style="padding: 0; text-align: right; font-weight: bold;">&deg;C</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0;"><?= escape($worksheet['env_humidity'] ?? '-') ?></td>
                                    <td style="padding: 0; text-align: right; font-weight: bold;">RH</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- BAR HASIL KALIBRASI -->
    <div class="section-bar">Hasil Kalibrasi :</div>

    <?php
        $unit = !empty($worksheet['measurement_unit']) ? $worksheet['measurement_unit'] : '....';
        $rows = $worksheet['measurements'] ?? [];
        $minRows = max(count($rows), 7);
    ?>

    <!-- TABEL PENGUKURAN -->
    <table class="measure-table">
        <thead>
            <tr>
                <th width="20%">
                    Skala Pengukuran<br>
                    (<?= escape($unit) ?>)
                </th>
                <th width="20%">
                    Pengukuran 1<br>
                    (<?= escape($unit) ?>)
                </th>
                <th width="20%">
                    Pengukuran 2<br>
                    (<?= escape($unit) ?>)
                </th>
                <th width="20%">
                    Pengukuran 3<br>
                    (<?= escape($unit) ?>)
                </th>
                <th width="20%">
                    Rata-Rata<br>
                    (<?= escape($unit) ?>)
                </th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < $minRows; $i++): ?>
                <?php
                    $r = $rows[$i] ?? null;
                ?>
                <tr>
                    <td><?= ($r['scale'] ?? '') !== '' ? escape((string) $r['scale']) : '&nbsp;' ?></td>
                    <td><?= ($r['m1'] ?? '') !== '' ? escape((string) $r['m1']) : '&nbsp;' ?></td>
                    <td><?= ($r['m2'] ?? '') !== '' ? escape((string) $r['m2']) : '&nbsp;' ?></td>
                    <td><?= ($r['m3'] ?? '') !== '' ? escape((string) $r['m3']) : '&nbsp;' ?></td>
                    <td><strong><?= ($r['average'] ?? '') !== '' ? escape((string) $r['average']) : '&nbsp;' ?></strong></td>
                </tr>
            <?php endfor; ?>
            <tr>
                <td colspan="5" class="u95-cell">
                    U 95% = <?= escape($worksheet['uncertainty_u95'] ?? '') ?>
                    <?php if (!empty($worksheet['uncertainty_u95_rounded'])): ?>
                        <br><span style="font-size: 11px;">Nilai Pembulatan = <?= escape($worksheet['uncertainty_u95_rounded']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 5 BARIS CATATAN BERGARIS SESUAI FORM FISIK -->
    <?php
        $noteLines = !empty($worksheet['additional_notes'])
            ? preg_split('/\r\n|\r|\n/', $worksheet['additional_notes'])
            : [];
    ?>
    <table class="notes-table">
        <?php for ($lineIdx = 0; $lineIdx < 5; $lineIdx++): ?>
            <tr>
                <td><?= isset($noteLines[$lineIdx]) && $noteLines[$lineIdx] !== '' ? escape($noteLines[$lineIdx]) : '&nbsp;' ?></td>
            </tr>
        <?php endfor; ?>
    </table>

    <!-- TABEL PENGESAHAN & TANDA TANGAN (DIPERIKSA / DIKALIBRASI) -->
    <table class="sig-table">
        <thead>
            <tr>
                <th colspan="2" width="20%"></th>
                <th width="40%">Diperiksa</th>
                <th width="40%">Dikalibrasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="sig-label">Tanggal</td>
                <td class="sig-colon">:</td>
                <td style="text-align: center;"><?= escape($worksheet['checked_date'] ?? ($worksheet['calibration_date_formatted'] ?? '')) ?></td>
                <td style="text-align: center;"><?= escape($worksheet['calibrated_by_date'] ?? ($worksheet['calibration_date_formatted'] ?? '')) ?></td>
            </tr>
            <tr>
                <td class="sig-label">Nama</td>
                <td class="sig-colon">:</td>
                <td style="text-align: center; font-weight: bold;"><?= escape($worksheet['checked_by_name'] ?? '') ?></td>
                <td style="text-align: center; font-weight: bold;"><?= escape($worksheet['calibrated_by_name'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="sig-label" style="vertical-align: middle;">Tanda tangan</td>
                <td class="sig-colon" style="vertical-align: middle;">:</td>
                <td class="sig-box">
                    <?php if (!empty($worksheet['checked_signature'])): ?>
                        <img src="<?= escape($worksheet['checked_signature']) ?>" class="sig-img" alt="TTD Pemeriksa" />
                    <?php endif; ?>
                </td>
                <td class="sig-box">
                    <?php if (!empty($worksheet['calibrated_signature'])): ?>
                        <img src="<?= escape($worksheet['calibrated_signature']) ?>" class="sig-img" alt="TTD Kalibrator" />
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
