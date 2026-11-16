<?php
declare(strict_types=1);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}

$groups = [
    'Dokumen' => [
        'doc_number' => ['No. dokumen', '001/LK-KLBI/2024'],
        'work_unit' => ['Unit kerja', 'Fisik Investasi dan Kalibrasi'],
        'doc_date' => ['Tanggal dokumen', '', 'date'],
        'doc_revision' => ['Revisi', '2'],
    ],
    'Identitas Alat yang Dikalibrasi' => [
        'certificate_number' => ['No. sertifikat'],
        'equipment_name' => ['Nama alat'],
        'measuring_range' => ['Rentang ukur'],
        'resolution' => ['Resolusi'],
        'type_model' => ['Tipe / model'],
        'brand' => ['Merk / Buatan'],
        'serial_number' => ['Nomor Seri (SN)'],
        'calibration_date' => ['Tanggal kalibrasi', '', 'date'],
        'owner_section' => ['Pemilik Alat'],
        'calibration_method' => ['Metode Kalibrasi'],
    ],
    'Data Alat Standar & Kondisi Lingkungan' => [
        'std_name' => ['Nama alat standar'],
        'std_certificate_number' => ['No. sertifikat standar'],
        'std_certificate_date' => ['Tanggal sertifikat standar', '', 'date'],
        'std_traceability' => ['Ketertelusuran'],
        'calibration_location' => ['Lokasi kalibrasi', 'Ruang Kalibrasi'],
        'env_temperature' => ['Suhu'],
        'env_humidity' => ['Kelembapan'],
    ],
    'Hasil pengukuran' => [
        'measurement_unit' => ['Satuan'],
        'uncertainty_u95' => ['U 95%'],
        'uncertainty_u95_rounded' => ['Nilai pembulatan'],
    ],
    'Pemeriksaan dan kalibrasi' => [
        'checked_by_name' => ['Nama pemeriksa'],
        'checked_date' => ['Tanggal pemeriksaan', '', 'date'],
        'calibrated_by_name' => ['Nama kalibrator'],
        'calibrated_by_date' => ['Tanggal kalibrasi (tanda tangan)', '', 'date'],
    ],
];

$standardEquipment = array_column(
    json_decode(file_get_contents(dirname(__DIR__) . '/templates/standard-equipment.json'), true, 512, JSON_THROW_ON_ERROR),
    null,
    'id'
);

$columns = ['scale' => 'Skala Pengukuran', 'm1' => 'Pengukuran 1', 'm2' => 'Pengukuran 2', 'm3' => 'Pengukuran 3', 'average' => 'Rata-Rata'];

function field(mixed $value): string
{
    return is_string($value) ? trim($value) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require dirname(__DIR__) . '/vendor/autoload.php';

    $worksheet = [];
    foreach ($groups as $fields) {
        foreach ($fields as $key => $definition) {
            $value = field($_POST[$key] ?? '');
            if ($value !== '') {
                $worksheet[$key] = $value;
            }
        }
    }
    $standardId = field($_POST['standard_equipment_id'] ?? '');
    if ($standardId !== '') {
        $standard = $standardEquipment[$standardId] ?? null;
        if ($standard === null) {
            http_response_code(422);
            exit('Alat standar yang dipilih tidak tersedia.');
        }
        $worksheet['std_name'] = $standard['name'];
        $worksheet['std_certificate_number'] = $standard['certificate_number'] ?? '-';
        $worksheet['std_certificate_date'] = $standard['certificate_date'] ?? '-';
    }

    $worksheet['equipment_category'] = field($_POST['equipment_category'] ?? '');
    $worksheet['additional_notes'] = field($_POST['additional_notes'] ?? '');
    $worksheet['is_draft'] = isset($_POST['is_draft']);
    foreach ((array) ($_POST['measurements'] ?? []) as $input) {
        $row = [];
        foreach ($columns as $key => $label) {
            $row[$key] = field($input[$key] ?? '');
        }
        $worksheet['measurements'][] = $row;
    }

    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $documentDate = $worksheet['doc_date'] ?? $worksheet['calibration_date'] ?? '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $documentDate);
    $worksheet['doc_date_formatted'] = $date
        ? $date->format('d') . ' ' . $months[(int) $date->format('n') - 1] . ' ' . $date->format('Y')
        : ($documentDate ?: '-');

    foreach (['calibration_date', 'std_certificate_date', 'checked_date', 'calibrated_by_date'] as $key) {
        if (isset($worksheet[$key])) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $worksheet[$key]);
            $target = $key === 'calibration_date' ? 'calibration_date_formatted' : $key;
            $worksheet[$target] = $date ? $date->format('d-m-Y') : $worksheet[$key];
        }
    }

    foreach (['checked_signature', 'calibrated_signature'] as $key) {
        $upload = $_FILES[$key] ?? [];
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $path = $upload['tmp_name'] ?? '';
        $info = is_uploaded_file($path) ? getimagesize($path) : false;
        if (($upload['error'] ?? null) !== UPLOAD_ERR_OK || !$info
            || ($upload['size'] ?? 0) > 2 * 1024 * 1024
            || !in_array($info['mime'], ['image/png', 'image/jpeg'], true)
            || $info[0] * $info[1] > 16000000) {
            http_response_code(422);
            exit('Gunakan gambar tanda tangan PNG atau JPEG dengan ukuran maksimal 2 MB dan 16 megapiksel.');
        }
        $worksheet[$key] = 'data:' . $info['mime'] . ';base64,' . base64_encode(file_get_contents($path));
    }

    ob_start();
    require dirname(__DIR__) . '/templates/calibration_worksheet.php';
    $html = ob_get_clean();
    $fontDirectory = getenv('LKK_ARIAL_NARROW_DIR') ?: (
        PHP_OS_FAMILY === 'Windows'
            ? getenv('WINDIR') . '/Fonts'
            : dirname(__DIR__) . '/templates/fonts'
    );
    $options = new Dompdf\Options();
    $regular = $fontDirectory . '/ARIALN.TTF';
    $bold = $fontDirectory . '/ARIALNB.TTF';
    $hasArialNarrow = $fontDirectory !== '' && is_readable($regular) && is_readable($bold);
    if ($hasArialNarrow) {
        $fontCache = sys_get_temp_dir() . '/lkk-dompdf-fonts';
        if (!is_dir($fontCache)) {
            mkdir($fontCache, 0700, true);
        }
        $options->setFontDir($fontCache);
        $options->setFontCache($fontCache);
        $options->setChroot([...$options->getChroot(), $fontDirectory]);
    }
    $pdf = new Dompdf\Dompdf($options);
    if ($hasArialNarrow) {
        $metrics = $pdf->getFontMetrics();
        $metrics->registerFont(['family' => 'Arial Narrow', 'style' => 'normal', 'weight' => 'normal'], $regular);
        $metrics->registerFont(['family' => 'Arial Narrow', 'style' => 'normal', 'weight' => 'bold'], $bold);
    }
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->setPaper('a4', 'portrait');
    $pdf->render();
    $name = preg_replace('/[^A-Za-z0-9_-]/', '_', $worksheet['equipment_name'] ?? 'Alat');
    $pdf->stream('Lembar_Kerja_Kalibrasi_' . $name . '.pdf', ['Attachment' => false]);
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lembar Kerja Kalibrasi</title>
<link rel="stylesheet" href="form.css?v=<?= filemtime(__DIR__ . '/form.css') ?>">
</head>
<body>
<main>
<h1>Lembar Kerja Kalibrasi</h1>
<form method="post" enctype="multipart/form-data" target="_blank">
    <div class="document-header">
        <div class="document-logo"><img src="peruri-logo.jpg" alt="PERURI"></div>
        <div class="document-title">
            <div>DIVISI TEKNIK DAN JAMINAN KEANDALAN</div>
            <div>FISIK INVESTASI MESIN DAN KALIBRASI</div>
        </div>
        <div class="document-fields">
            <?php foreach ($groups['Dokumen'] as $key => $definition): ?>
                <label><span><?= escape($definition[0]) ?></span>
                    <input name="<?= escape($key) ?>" type="<?= escape($definition[2] ?? 'text') ?>"
                        value="<?= escape($definition[1] ?? '') ?>">
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <label class="category">Jenis Kalibrasi
        <select name="equipment_category">
            <option value="internal">Internal</option>
            <option value="external">Eksternal</option>
        </select>
    </label>
    <div class="equipment-panels">
    <?php foreach (array_slice($groups, 1, 2) as $group => $fields): ?>
        <section class="equipment-panel">
            <div class="panel-heading"><h2><?= escape($group) ?></h2><button type="button" class="reset reset-panel">Reset</button></div>
            <div class="equipment-fields">
                <?php foreach ($fields as $key => $definition): ?>
                    <label class="<?= in_array($key, ['certificate_number', 'equipment_name', 'std_name', 'std_certificate_number', 'calibration_location'], true) ? 'wide' : '' ?>">
                        <?= escape($definition[0]) ?>
                        <?php if ($key === 'std_name'): ?>
                            <select name="standard_equipment_id" id="standard-equipment">
                                <option value="">Pilih alat standar</option>
                                <?php foreach ($standardEquipment as $standard): ?>
                                    <option value="<?= escape((string) $standard['id']) ?>"
                                        data-certificate="<?= escape($standard['certificate_number'] ?? '') ?>"
                                        data-date="<?= escape($standard['certificate_date'] ?? '') ?>">
                                        <?= escape($standard['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                        <span class="equipment-input">
                        <?php $unit = ['env_temperature' => '°C', 'env_humidity' => 'RH'][$key] ?? ''; ?>
                        <input<?= $unit ? ' class="with-unit"' : '' ?><?= in_array($key, ['std_certificate_number', 'std_certificate_date'], true) ? ' readonly' : '' ?> name="<?= escape($key) ?>" type="<?= escape($definition[2] ?? 'text') ?>"
                            value="<?= escape($definition[1] ?? '') ?>">
                        <?php if ($unit): ?><span class="unit-suffix"><?= escape($unit) ?></span><?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    </div>
    <section class="results">
        <div class="results-header">
            <h2>Hasil Kalibrasi :</h2>
            <label>Satuan (Unit): <input name="measurement_unit" placeholder="mm"></label>
            <button type="button" class="reset" id="reset-table">Reset Tabel</button>
            <button type="button" id="add-row">+ Tambah Baris</button>
        </div>
            <div class="table"><table>
                <thead><tr><?php foreach ($columns as $label): ?><th scope="col"><?= escape($label) ?><br><span class="measurement-unit">(...)</span></th><?php endforeach; ?><th scope="col" class="row-action"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody id="measurements">
                <?php for ($row = 0; $row < 7; $row++): ?>
                    <tr>
                    <?php foreach ($columns as $key => $label): ?>
                        <td><span class="mobile-cell-label" aria-hidden="true"><?= escape($label) ?></span><input inputmode="decimal" placeholder="<?= $key === 'scale' ? 'e.g. 25' : '25.000' ?>" name="measurements[<?= $row ?>][<?= escape($key) ?>]"
                            aria-label="<?= escape($label . ', baris ' . ($row + 1)) ?>"></td>
                    <?php endforeach; ?>
                    <td class="row-action"><button type="button" class="remove-row" aria-label="Hapus baris"><span class="desktop-remove" aria-hidden="true">&times;</span><span class="mobile-remove" aria-hidden="true">Hapus baris</span></button></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table></div>
            <div class="uncertainty">
                <label><span>U 95% =</span>
                    <input name="uncertainty_u95">
                    <span class="measurement-unit">(...)</span>
                </label>
                <label><span>Nilai Pembulatan =</span>
                    <input name="uncertainty_u95_rounded">
                    <span class="measurement-unit">(...)</span>
                </label>
            </div>
            <label class="notes">Catatan (maksimal 5 baris)
                <textarea name="additional_notes" rows="5"></textarea>
            </label>
    </section>
    <section class="signatures">
        <h2>Pemeriksaan dan Kalibrasi</h2>
        <div class="equipment-panels">
            <?php foreach (['Pemeriksa' => ['checked_by_name', 'checked_date', 'checked_signature'],
                            'Kalibrator' => ['calibrated_by_name', 'calibrated_by_date', 'calibrated_signature']] as $role => [$name, $date, $signature]): ?>
                <section class="signature-panel">
                    <h3><?= escape($role) ?></h3>
                    <div>
                        <label>Nama <?= escape($role) ?> <input name="<?= escape($name) ?>"></label>
                        <label>Tanggal <input type="date" name="<?= escape($date) ?>"></label>
                        <label>Tanda Tangan <?= escape($role) ?>
                            <input type="file" name="<?= escape($signature) ?>" accept="image/png,image/jpeg">
                        </label>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </section>
    <div class="actions">
        <label><input type="checkbox" name="is_draft"> Tandai sebagai draf</label>
        <button type="submit">Pratinjau PDF</button>
    </div>
</form>
</main>
<script>
const measurements = document.querySelector('#measurements');
const initialRows = measurements.innerHTML;
const unitInput = document.querySelector('[name="measurement_unit"]');

unitInput.addEventListener('input', () => {
    for (const label of document.querySelectorAll('.measurement-unit')) {
        label.textContent = '(' + (unitInput.value.trim() || '...') + ')';
    }
});

const standardSelect = document.querySelector('#standard-equipment');

function fillStandardCertificate() {
    const selected = standardSelect.selectedOptions[0];
    document.querySelector('[name="std_certificate_number"]').value = selected.dataset.certificate || '';
    document.querySelector('[name="std_certificate_date"]').value = selected.dataset.date || '';
}

standardSelect.addEventListener('change', fillStandardCertificate);
window.addEventListener('pageshow', fillStandardCertificate);

for (const button of document.querySelectorAll('.reset-panel')) {
    button.addEventListener('click', () => {
        for (const input of button.closest('section').querySelectorAll('input')) {
            input.value = input.defaultValue;
        }
        if (button.closest('section').contains(standardSelect)) {
            standardSelect.selectedIndex = 0;
            fillStandardCertificate();
        }
    });
}

document.querySelector('#reset-table').addEventListener('click', () => {
    measurements.innerHTML = initialRows;
    for (const input of document.querySelectorAll('.uncertainty input')) input.value = '';
});

measurements.addEventListener('click', (event) => {
    const button = event.target.closest('.remove-row');
    if (!button) return;
    const row = button.closest('tr');
    if (measurements.rows.length > 1) row.remove();
    else for (const input of row.querySelectorAll('input')) input.value = '';
});

let nextRow = measurements.rows.length;
document.querySelector('#add-row').addEventListener('click', () => {
    const index = nextRow++;
    const row = measurements.rows[0].cloneNode(true);
    for (const input of row.querySelectorAll('input')) {
        input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
        input.setAttribute('aria-label', input.getAttribute('aria-label').replace(/baris \d+/, 'baris ' + (index + 1)));
        input.value = '';
    }
    measurements.append(row);
    row.querySelector('input').focus();
});
</script>
</body>
</html>

