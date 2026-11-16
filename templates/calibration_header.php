<style>
    .calibration-header {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin: 0 0 10px;
        border: 1px solid #000;
    }
    .calibration-header td {
        border: 1px solid #000;
        padding: 3px 5px;
        height: 22px;
        vertical-align: middle;
        font-size: 12px;
        line-height: 1.25;
        font-weight: bold;
    }
    .calibration-header .header-logo {
        padding: 6px;
        text-align: center;
        line-height: 0;
    }
    .calibration-header .header-logo img {
        display: inline-block;
        vertical-align: middle;
        width: 110px;
        max-width: 100%;
        height: auto;
        margin: 0;
    }
    .calibration-header .header-title {
        text-align: center;
        padding: 5px 8px;
        line-height: 1.4;
    }
    .calibration-header .header-label,
    .calibration-header .header-colon,
    .calibration-header .header-value {
        font-size: 9px !important;
        line-height: 1.15;
        height: 20px;
        padding-top: 2px;
        padding-bottom: 2px;
    }
    .calibration-header .header-label {
        white-space: nowrap;
        border-right: none;
        padding-left: 3px;
        padding-right: 1px;
    }
    .calibration-header .header-colon {
        text-align: center;
        padding-left: 0;
        padding-right: 0;
        border-left: none;
        border-right: none;
    }
    .calibration-header .header-value {
        border-left: none;
        padding-left: 3px;
        padding-right: 3px;
    }
    .calibration-header .header-nowrap { white-space: nowrap; }
</style>
<table class="calibration-header">
    <colgroup>
        <col style="width:18%" />
        <col style="width:50%" />
        <col style="width:15%" />
        <col style="width:1%" />
        <col style="width:16%" />
    </colgroup>
    <tr>
        <td width="18%" rowspan="4" class="header-logo">
            <?php if ($logoBase64): ?>
                <img src="<?= escape($logoBase64) ?>" alt="PERURI" />
            <?php else: ?>
                <span style="font-size:18px;line-height:1.2;color:#1e3a8a;">PERURI</span>
            <?php endif; ?>
        </td>
        <td width="50%" rowspan="2" class="header-title">DIVISI TEKNIK DAN JAMINAN KEANDALAN</td>
        <td width="15%" class="header-label">No Dokumen</td>
        <td width="1%" class="header-colon">:</td>
        <td width="16%" class="header-value header-nowrap"><?= escape($documentNumber) ?></td>
    </tr>
    <tr>
        <td class="header-label">Unit Kerja</td>
        <td class="header-colon">:</td>
        <td class="header-value"><?= escape($document['work_unit'] ?? 'Fisik Investasi dan Kalibrasi') ?></td>
    </tr>
    <tr>
        <td rowspan="2" class="header-title">FISIK INVESTASI MESIN DAN KALIBRASI</td>
        <td class="header-label">Tanggal Dokumen</td>
        <td class="header-colon">:</td>
        <td class="header-value header-nowrap"><?= escape($document['doc_date_formatted'] ?? '-') ?></td>
    </tr>
    <tr>
        <td class="header-label">Revisi</td>
        <td class="header-colon">:</td>
        <td class="header-value"><?= escape($document['doc_revision'] ?? '2') ?></td>
    </tr>
</table>
