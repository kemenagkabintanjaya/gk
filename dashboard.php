<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();

$u = current_user();
layout_header('Dashboard Statistik', 'dashboard.php');
?>
<div class="sec-head">
  <h2>📊 Dashboard Statistik Gereja Kristen</h2>
  <button class="btn-refresh" onclick="loadData()">↻ Muat Ulang</button>
</div>

<div class="info-banner">
  <span class="ib-meta">
    <span class="ib-meta-item">⛪ Total Gereja: <strong id="ib-total">–</strong></span>
    <span class="ib-meta-item">👥 Total Jemaat: <strong id="ib-umat">–</strong></span>
    <span class="ib-meta-item">✅ Terverifikasi: <strong id="ib-ver">–</strong></span>
    <span class="ib-meta-item">🕒 Pembaruan: <strong id="ib-update">–</strong></span>
  </span>
</div>

<div class="subnav">
  <button class="subtab-btn active" data-tab="ringkasan" onclick="switchTab('ringkasan')">Ringkasan</button>
  <button class="subtab-btn" data-tab="demografi" onclick="switchTab('demografi')">Demografi Jemaat</button>
  <button class="subtab-btn" data-tab="distrik" onclick="switchTab('distrik')">Per Distrik</button>
</div>

<!-- RINGKASAN -->
<div class="tab-content active" id="tab-ringkasan">
  <div class="stats-strip">
    <div class="stat-card"><div class="stat-icon si-green">⛪</div><div><div class="stat-num" id="s-total">–</div><div class="stat-lbl">Total Gereja</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue">👥</div><div><div class="stat-num" id="s-umat">–</div><div class="stat-lbl">Total Jemaat</div></div></div>
    <div class="stat-card"><div class="stat-icon si-slate">🏛️</div><div><div class="stat-num" id="s-gedung">–</div><div class="stat-lbl">Total Gedung</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">🏗️</div><div><div class="stat-num" id="s-permanen">–</div><div class="stat-lbl">Gedung Permanen</div></div></div>
    <div class="stat-card"><div class="stat-icon si-gold">🧑‍💼</div><div><div class="stat-num" id="s-personil">–</div><div class="stat-lbl">Total Personil</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">📖</div><div><div class="stat-num" id="s-pendeta">–</div><div class="stat-lbl">Total Pendeta</div></div></div>
    <div class="stat-card"><div class="stat-icon si-gold">📜</div><div><div class="stat-num" id="s-terdaftar">–</div><div class="stat-lbl">Terdaftar Kemenag</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue">✅</div><div><div class="stat-num" id="s-ver">–</div><div class="stat-lbl">Terverifikasi</div></div></div>
  </div>

  <div class="chart-grid">
    <div class="chart-card">
      <h3>Distribusi Aras Organisasi</h3>
      <div class="donut-wrap"><div class="donut-lg"><canvas id="chart-aras"></canvas></div><div class="donut-legend" id="legend-aras"></div></div>
    </div>
    <div class="chart-card">
      <h3>Kondisi Fisik Gedung</h3>
      <div class="donut-wrap"><div class="donut-lg"><canvas id="chart-gedung"></canvas></div><div class="donut-legend" id="legend-gedung"></div></div>
    </div>
    <div class="chart-card">
      <h3>Jemaat per Distrik</h3>
      <div class="loader" id="loader-distrik"><span class="loader-dot"></span></div>
      <div class="bar-chart" id="bar-distrik"></div>
    </div>
    <div class="chart-card">
      <h3>Personil Pelayanan</h3>
      <div class="chart-canvas-wrap"><canvas id="chart-personil"></canvas></div>
    </div>
  </div>
</div>

<!-- DEMOGRAFI -->
<div class="tab-content" id="tab-demografi">
  <div class="stats-strip">
    <div class="stat-card"><div class="stat-icon si-blue">👨</div><div><div class="stat-num" id="dem-laki">–</div><div class="stat-lbl">Laki-laki</div></div></div>
    <div class="stat-card"><div class="stat-icon si-gold">👩</div><div><div class="stat-num" id="dem-perempuan">–</div><div class="stat-lbl">Perempuan</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">👔</div><div><div class="stat-num" id="dem-bapak">–</div><div class="stat-lbl">Bapak</div></div></div>
    <div class="stat-card"><div class="stat-icon si-gold">👗</div><div><div class="stat-num" id="dem-ibu">–</div><div class="stat-lbl">Ibu</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue">🧑</div><div><div class="stat-num" id="dem-pemuda">–</div><div class="stat-lbl">Pemuda</div></div></div>
    <div class="stat-card"><div class="stat-icon si-slate">🧒</div><div><div class="stat-num" id="dem-remaja">–</div><div class="stat-lbl">Remaja</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">👶</div><div><div class="stat-num" id="dem-anaksm">–</div><div class="stat-lbl">Anak Sek. Minggu</div></div></div>
    <div class="stat-card"><div class="stat-icon si-gold">📖</div><div><div class="stat-num" id="dem-pendeta">–</div><div class="stat-lbl">Pendeta</div></div></div>
  </div>
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Komposisi Jenis Kelamin</h3>
      <div class="donut-wrap"><div class="donut-lg"><canvas id="chart-gender"></canvas></div><div class="donut-legend" id="legend-gender"></div></div>
    </div>
    <div class="chart-card">
      <h3>Unsur Jemaat</h3>
      <div class="chart-canvas-wrap"><canvas id="chart-unsur"></canvas></div>
    </div>
  </div>
</div>

<!-- PER DISTRIK -->
<div class="tab-content" id="tab-distrik">
  <div class="tbl-wrap">
    <div class="tbl-header"><span class="tbl-title">Rekapitulasi per Distrik</span><span class="sec-badge" id="distrik-count">–</span></div>
    <div class="tbl-scroll">
    <table>
      <thead><tr><th>No</th><th>Distrik</th><th>Jml Gereja</th><th>Total Jemaat</th><th>Laki-laki</th><th>Perempuan</th><th>Pemuda</th></tr></thead>
      <tbody id="tbl-distrik-body"><tr><td colspan="7"><div class="empty-state">Memuat…</div></td></tr></tbody>
    </table>
    </div>
  </div>
</div>

<script src="assets/app.js"></script>
<?php layout_footer(); ?>
