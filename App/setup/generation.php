<?php
// generation.php
// Usage: ./setup/generation.php?table=authors

require_once("../config/SYD_Class.php"); // <-- adjust path if needed
$obj = new sydClass();
$db  = $obj->db_name; // e.g., "education_liberary"

// ---------------- helpers ----------------
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function labelize($name){
  $name = preg_replace('/_in$/', '', $name);
  $name = str_replace('_', ' ', $name);
  return ucwords($name);
}
function smartInputType($paramName, $dataType){
  $n = strtolower($paramName);
  if (str_ends_with($n, '_in')) $n = substr($n, 0, -3);
  if (str_contains($n, 'email')) return 'email';
  if (str_contains($n, 'password')) return 'password';
  if (str_contains($n, 'phone') || str_contains($n, 'mobile') || str_contains($n, 'contact')) return 'tel';
  if (str_contains($n, 'date') || str_contains($n, 'created') || str_contains($n, 'updated')) return 'date';
  if (str_contains($n, 'year')) return 'number';
  if (str_contains($n, 'price') || str_contains($n, 'amount') || str_contains($n, 'salary') || str_contains($n, 'qty')) return 'number';
  $t = strtolower($dataType);
  if (in_array($t, ['int','bigint','smallint','decimal','double','float'])) return 'number';
  return 'text';
}

// ---------------- resolve table/proc ----------------
$table = isset($_GET['table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['table']) : '';
if (!$table){ echo "<div class='alert alert-danger m-3'>Missing ?table=...</div>"; exit; }

$proc = "{$table}_proc"; // FINAL rule (e.g., authors_proc)
$prettyName = ucwords(str_replace('_',' ',$table));

// connect mysqli
$obj->connection();
$mysqli = $obj->db;

// ---------------- read procedure params (used everywhere) ----------------
function get_proc_params($mysqli, $db, $proc){
  $ps = $mysqli->prepare("
    SELECT PARAMETER_NAME, DATA_TYPE, ORDINAL_POSITION
    FROM INFORMATION_SCHEMA.PARAMETERS
    WHERE SPECIFIC_SCHEMA = ? AND SPECIFIC_NAME = ?
    ORDER BY ORDINAL_POSITION
  ");
  $ps->bind_param('ss', $db, $proc);
  $ps->execute();
  $res = $ps->get_result();
  $params = [];
  while ($row = $res->fetch_assoc()) $params[] = $row;
  $ps->close();
  return $params;
}
function flush_results($mysqli){
  while ($mysqli->more_results() && $mysqli->next_result()) { /* flush */ }
}

// Figure out PK param (first *_id_in before last 'opr')
$params_all = get_proc_params($mysqli, $db, $proc);
if (!$params_all){
  echo "<div class='alert alert-danger m-3'>Could not read parameters of <b>".h($proc)."</b> in schema <b>".h($db)."</b>.</div>";
  exit;
}
$lastIdx = count($params_all) - 1;
$pkIndex = null;
$pkParam = null;
foreach ($params_all as $i => $p) {
  if ($i === $lastIdx) break;
  if (preg_match('/_id_in$/i', $p['PARAMETER_NAME'])) { $pkIndex = $i; $pkParam = $p['PARAMETER_NAME']; break; }
}
// If no *_id_in found, we just won't have a pkParam and updates will rely on provided hidden value if present.

// ---------------- POST: insert/update/delete via procedure ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['op'])) {
  header('Content-Type: application/json');

  $op = $_POST['op']; // insert | update | delete
  $params = $params_all; // already fetched

  $values = [];
  foreach ($params as $i => $p){
    $pname = $p['PARAMETER_NAME']; // e.g., author_id_in ... last is 'opr'
    if ($i === $lastIdx){
      // last param = opr
      $values[] = "'" . $mysqli->real_escape_string($op) . "'";
    } else {
      // PK behavior:
      // - insert: we do not send PK (NULL), DB auto-increments
      // - update/delete: PK is read from hidden field
      if ($op === 'insert' && $pkParam && $pname === $pkParam) {
        $values[] = "NULL";
        continue;
      }
      $v = $_POST[$pname] ?? null;
      $values[] = ($v === null || $v === '') ? "NULL" : "'" . $mysqli->real_escape_string($v) . "'";
    }
  }

  $sql = "CALL {$proc}(" . implode(',', $values) . ")";
  ob_start();
  $msg = $obj->operationReturn($sql);
  ob_end_clean();
  flush_results($mysqli);

  echo json_encode(['success'=>true, 'message'=>$msg ?: strtoupper($op).' OK']);
  exit;
}

// ---------------- GET (partial): return only the table html for AJAX reload ----------------
if (isset($_GET['load']) && $_GET['load'] == '1') {
  $params = $params_all;
  $argParts = [];
  foreach ($params as $i => $p){
    $argParts[] = ($i === $lastIdx) ? "'select'" : "NULL";
  }
  $selectSql = "CALL {$proc}(" . implode(',', $argParts) . ")";
  $dtId = 'dt_' . $table;

  ob_start();
  $obj->Table($selectSql, $dtId, 'n'); // 'n' -> viewDataTable
  $html = ob_get_clean();
  flush_results($mysqli);

  echo $html;
  exit;
}

// ---------------- GET (full page render) ----------------
// SELECT call for initial render
$argParts = [];
foreach ($params_all as $i => $p){
  $argParts[] = ($i === $lastIdx) ? "'select'" : "NULL";
}
$selectSql = "CALL {$proc}(" . implode(',', $argParts) . ")";
$dtId = 'dt_' . $table;
?>

<!-- CARD + TABLE -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="fw-bold"><?= h($prettyName) ?> — Records</span>
    <div>
      <button class="btn btn-primary btn-sm" id="btnAdd">Add New <?= h($prettyName) ?></button>
    </div>
  </div>
  <div class="card-body">
    <div id="datatable-wrapper">
      <?php
        ob_start();
        $obj->Table($selectSql, $dtId, 'n');
        $html = ob_get_clean();
        echo $html;
        flush_results($mysqli);
      ?>
    </div>
  </div>
</div>

<!-- ADD/EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalTitle">Add New <?= h($prettyName) ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="entityForm" class="row g-3">
          <?php
            // Hidden PK input (NOT shown on UI). We include it only if detected.
            if ($pkParam) {
              echo '<input type="hidden" name="'.h($pkParam).'" id="__pk__">';
            }
            // Render the rest of fields except last 'opr' and the PK (hidden)
            for ($i = 0; $i < count($params_all) - 1; $i++):
              $p = $params_all[$i];
              $name  = $p['PARAMETER_NAME'];   // e.g., author_id_in
              if ($pkParam && $name === $pkParam) continue; // skip visible PK input
              $label = labelize($name);
              $type  = smartInputType($name, $p['DATA_TYPE']);
          ?>
          <div class="col-md-6">
            <label class="form-label"><?= h($label) ?></label>
            <input type="<?= h($type) ?>" class="form-control" name="<?= h($name) ?>" autocomplete="off">
          </div>
          <?php endfor; ?>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="btnSave">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
// ===== DataTables: init (with search box) =====
function initDataTable() {
  const tableId = <?= json_encode('dt_'.$table) ?>;
  const $tbl = $('#'+tableId);
  if ($.fn.DataTable.isDataTable($tbl)) {
    $tbl.DataTable().destroy();
  }
  $tbl.DataTable({
    responsive: true,
    autoWidth: false,
    // keep default DOM so built-in search box appears (lfrtip)
  });

  // Replace action buttons' inner icons to Bootstrap Icons (if your sydClass printed others)
  replaceActionIcons();
}

// Replace edit/remove icons to Bootstrap Icons
function replaceActionIcons() {
  // turn any edit buttons into icon-only buttons
  $("[class*='btn_edit_']").each(function(){
    $(this).addClass('btn btn-outline-primary btn-sm').attr('title', 'Edit');
    $(this).html("<i class='bi bi-pencil-square'></i>");
  });
  $("[class*='btn_remove_']").each(function(){
    $(this).addClass('btn btn-outline-danger btn-sm').attr('title', 'Delete');
    $(this).html("<i class='bi bi-trash'></i>");
  });
}

// Reload table via partial refresh, then re-init DataTables + icons
function reloadTable() {
  const url = new URL(window.location.href);
  url.searchParams.set('load','1');
  fetch(url.toString(), { method: 'GET' })
    .then(r => r.text())
    .then(html => {
      document.getElementById('datatable-wrapper').innerHTML = html;
      initDataTable();
    })
    .catch(() => alert('Failed to reload table'));
}

(function(){
  const pretty = <?= json_encode($prettyName) ?>;
  // init on first paint
  $(document).ready(function(){
    initDataTable();
  });

  // Open modal for INSERT
  $(document).on('click', '#btnAdd', function(){
    $('#modalTitle').text('Add New ' + pretty);
    $('#entityForm')[0].reset();
    $('#entityForm').data('mode', 'insert');
    // ensure hidden pk is cleared
    const pk = document.getElementById('__pk__');
    if (pk) pk.value = '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
  });

  // Hook EDIT buttons generated by sydClass (value contains PK)
  $(document).on('click', "[class*='btn_edit_']", function(){
    const idVal = $(this).val(); // assumes value holds PK
    $('#entityForm')[0].reset();
    $('#entityForm').data('mode', 'update');
    $('#modalTitle').text('Update ' + pretty + ' (ID: ' + idVal + ')');

    // fill hidden PK
    const pk = document.getElementById('__pk__');
    if (pk) pk.value = idVal;

    new bootstrap.Modal(document.getElementById('editModal')).show();
  });

  // Hook DELETE buttons
  $(document).on('click', "[class*='btn_remove_']", function(){
    const idVal = $(this).val();
    if (!confirm('Delete this record?')) return;

    const fd = new FormData();
    fd.append('op', 'delete');

    // send hidden pk param name with value
    const pk = document.getElementById('__pk__');
    if (pk && pk.getAttribute('name')) {
      fd.append(pk.getAttribute('name'), idVal);
    }

    fetch(window.location.href, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(resp => {
        alert(resp.message || 'Deleted');
        reloadTable(); // partial refresh only
      })
      .catch(() => alert('Delete failed'));
  });

  // Save (Insert/Update)
  $(document).on('click', '#btnSave', function(){
    const mode = $('#entityForm').data('mode') || 'insert';
    const fd = new FormData(document.getElementById('entityForm'));
    fd.append('op', mode);

    fetch(window.location.href, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(resp => {
        if (resp.success) {
          alert(resp.message || 'Saved');
          // close modal & reload table
          const m = bootstrap.Modal.getInstance(document.getElementById('editModal'));
          if (m) m.hide();
          reloadTable();
        } else {
          alert(resp.message || 'Error');
        }
      })
      .catch(() => alert('Request failed'));
  });

})();
</script>
