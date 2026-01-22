<?php
include('header.php');
include('sidebar.php');
include('navbar.php');
include('../connect.php');

$_SESSION['sop_last_activity'] = time();
?>

<link rel="stylesheet" href="modern-table-styles.css">
<style>
  .sop-header-actions{display:flex;gap:.75rem}
  .sop-search{background:#fff;border:1px solid rgba(0,0,0,.08);box-shadow:0 4px 16px rgba(15,23,42,.06);border-radius:14px;padding:.85rem 1rem}
  .sop-search .form-control{border:none;box-shadow:none}
  .sop-grid{margin-top:1rem}
  .sop-card{background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:18px;padding:18px;box-shadow:0 10px 30px rgba(2,6,23,.07);transition:transform .2s ease, box-shadow .2s ease}
  .sop-card:hover{transform:translateY(-3px);box-shadow:0 16px 38px rgba(2,6,23,.12)}
  .sop-icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:linear-gradient(135deg,#f8e7ff,#fff0f6);color:#a23ec0}
  .sop-title{font-weight:700;margin:0}
  .sop-badge{display:inline-block;font-size:.75rem;padding:.25rem .5rem;border-radius:999px;background:#fff2e0;color:#b26b00}
  .sop-desc{color:#6c757d;margin:.35rem 0 0}
  .sop-meta{color:#6c757d;font-size:.9rem;display:flex;gap:18px;flex-wrap:wrap;margin-top:.75rem;align-items:center}
  .status-archived{font-size:.72rem;padding:.2rem .6rem;border-radius:999px;background:#f2f4f7;border:1px solid #e6e9ef;color:#6b7280}
  .sop-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-top:.9rem}
  .btn-soft{background:#f4f7fb;border:1px solid #e7ecf3;color:#2b3f4e;font-weight:600}
  .btn-soft:hover{background:#eef2f8}
  .btn-soft-success{background:#eafaf1;border:1px solid #c9eedc;color:#0f7a45;font-weight:600}
  .btn-soft-success:hover{background:#ddf3e8}
  .btn-soft-dark{background:#f1f3f5;border:1px solid #e7ecf3;color:#1f2937;font-weight:600}
  .btn-soft-dark:hover{background:#eceff2}
  /* Dark mode adjustments */
  body.dark-mode .content h3{color:#e6edf3}
  body.dark-mode .sop-card{background:#111827;border-color:rgba(255,255,255,.10);box-shadow:0 10px 30px rgba(0,0,0,.65)}
  body.dark-mode .sop-title{color:#e6edf3}
  body.dark-mode .sop-desc,body.dark-mode .sop-meta{color:#cbd5e1}
  body.dark-mode .sop-icon{background:linear-gradient(135deg,#271b2d,#1f1631);color:#f0abfc}
  body.dark-mode .sop-badge{background:rgba(245,158,11,.15);color:#fcd34d}
  body.dark-mode .status-archived{background:#1f2937;border-color:rgba(255,255,255,.12);color:#cbd5e1}
  body.dark-mode .btn-soft{background:#1f2937;border-color:rgba(255,255,255,.12);color:#e5e7eb}
  body.dark-mode .btn-soft:hover{background:#233044}
  body.dark-mode .btn-soft-success{background:rgba(34,197,94,.15);border-color:rgba(34,197,94,.35);color:#86efac}
  body.dark-mode .btn-soft-dark{background:#0b1220;border-color:rgba(255,255,255,.12);color:#e6edf3}
  body.dark-mode .btn-soft-dark:hover{background:#182034}
</style>

<div class="content p-4">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h3 class="mb-1">Archived SOPs</h3>
    </div>
  </div>

  <div class="sop-grid row g-3" id="archivedGrid">
    <?php
      $result = $conn->query("SELECT * FROM sop_documents WHERE status='Archived' ORDER BY created_at DESC");
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $title = htmlspecialchars($row['title']);
          $category = htmlspecialchars($row['category']);
          $contentPlain = trim(strip_tags($row['content'] ?? ''));
          $excerpt = mb_strimwidth($contentPlain, 0, 220, '...');
          $created = date('Y-m-d', strtotime($row['created_at']));
          echo "
          <div class='col-12 col-md-6'>
            <div class='sop-card' data-title='".htmlspecialchars($row['title'], ENT_QUOTES)."' data-category='".htmlspecialchars($row['category'], ENT_QUOTES)."'>
              <div class='d-flex align-items-start gap-3'>
                <div class='sop-icon'><i class='fas fa-file-archive'></i></div>
                <div class='flex-grow-1'>
                  <div class='d-flex align-items-center gap-2 mb-1'>
                    <h5 class='sop-title mb-0'>".$title."</h5>
                    <span class='sop-badge'><i class='fas fa-tag me-1'></i>".$category."</span>
                  </div>
                  <p class='sop-desc'>".htmlspecialchars($excerpt)."</p>
                  <div class='sop-meta'>
                    <div><i class='far fa-clock me-1'></i>Archived: ".$created."</div>
                    <span class='status-archived'>Archived</span>
                  </div>
                  <div class='sop-actions'>
                    <button class='btn btn-soft viewBtn'
                      data-id='".$row['sop_id']."'
                      data-title='".$title."'
                      data-category='".$category."'
                      data-status='Archived'
                      data-content='".htmlspecialchars($row['content'], ENT_QUOTES)."'
                      data-file='".$row['file_path']."'>
                      <i class='fas fa-eye me-2'></i>View
                    </button>
                    <button class='btn btn-soft-success unarchiveBtn'
                      data-id='".$row['sop_id']."'
                      data-title='".htmlspecialchars($row['title'], ENT_QUOTES)."'
                      data-bs-toggle='modal' data-bs-target='#unarchiveSopModal'>
                      <i class='fas fa-folder-open me-2'></i>Unarchive
                    </button>
                    <a href='print_sop.php?id=".$row['sop_id']."' target='_blank' class='btn btn-soft-dark'>
                      <i class='fas fa-print me-2'></i>Print
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>";
        }
      } else {
        echo "<div class='col-12'><div class='sop-card text-center'><h5 class='mb-1'>No archived SOPs found</h5><div class='text-muted'>You can unarchive from here when available.</div></div></div>";
      }
    ?>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewSopModal" tabindex="-1" aria-labelledby="viewSopLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="viewSopLabel">SOP Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Title:</strong> <span id="sopTitle"></span></p>
        <p><strong>Category:</strong> <span id="sopCategory"></span></p>
        <p><strong>Status:</strong> <span id="sopStatus"></span></p>
        <p><strong>Content:</strong></p>
        <div class="border rounded p-2 bg-light" id="sopContent"></div>
        <p><strong>Attached File:</strong> <a href="" target="_blank" id="sopFileLink">No file uploaded</a></p>
        <div class="mt-3" id="sopFilePreview" style="display:none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Clean Unarchive Modal -->
<div class="modal fade" id="unarchiveSopModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Unarchive SOP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p>Are you sure you want to unarchive <strong id="unarchiveSopTitle"></strong>?</p>
      </div>

      <div class="modal-footer">
        <form method="POST" action="unarchive_sop.php">
          <input type="hidden" name="sop_id" id="unarchiveSopId">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Unarchive</button>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- Unarchive Success Modal -->
<div class="modal fade" id="unarchivedSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body p-4">
        <div class="mb-2 text-success"><i class="fas fa-folder-open fa-2x"></i></div>
        <h6 class="mb-1">SOP unarchived successfully.</h6>
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Unarchive Error Modal -->
<div class="modal fade" id="unarchivedErrorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body p-4">
        <div class="mb-2 text-danger"><i class="fas fa-times-circle fa-2x"></i></div>
        <h6 class="mb-1">Unable to unarchive</h6>
        <p class="text-muted mb-3" id="unarchivedErrorMsg"></p>
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Search filter
  const search = document.getElementById('archivedSearch');
  if (search) {
    search.addEventListener('input', function(){
      const q = this.value.toLowerCase();
      document.querySelectorAll('#archivedGrid .sop-card').forEach(card=>{
        const text = (card.getAttribute('data-title')||'').toLowerCase() + ' ' + (card.getAttribute('data-category')||'').toLowerCase();
        card.parentElement.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  // View Modal
  document.querySelectorAll('.viewBtn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('sopTitle').innerText = this.dataset.title;
      document.getElementById('sopCategory').innerText = this.dataset.category;
      document.getElementById('sopStatus').innerText = this.dataset.status;
      document.getElementById('sopContent').innerHTML = this.dataset.content.replace(/\n/g, '<br>');

      const filePath = this.dataset.file;
      const fileLink = document.getElementById('sopFileLink');
      const preview = document.getElementById('sopFilePreview');
      if (filePath && filePath.trim() !== '') {
        fileLink.href = '../' + filePath;
        fileLink.innerText = 'Download File';

        const ext = (filePath.split('.').pop() || '').toLowerCase();
        const previewUrl = '../' + filePath;
        if (preview) {
          if (['jpg','jpeg','png','webp'].includes(ext)) {
            preview.style.display = '';
            preview.innerHTML = '<img src="' + previewUrl + '" alt="Attachment" style="max-width:100%;height:auto" class="border rounded">';
          } else if (ext === 'pdf') {
            preview.style.display = '';
            preview.innerHTML = '<iframe src="' + previewUrl + '" style="width:100%;height:420px" class="border rounded"></iframe>';
          } else {
            preview.style.display = 'none';
            preview.innerHTML = '';
          }
        }
      } else {
        fileLink.href = '#';
        fileLink.innerText = 'No file uploaded';
        if (preview) { preview.style.display = 'none'; preview.innerHTML = ''; }
      }

      new bootstrap.Modal(document.getElementById('viewSopModal')).show();
    });
  });

  // Unarchive: fill hidden fields (modal opens via data-bs-target)
  document.querySelectorAll('.unarchiveBtn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('unarchiveSopId').value = this.dataset.id;
      document.getElementById('unarchiveSopTitle').innerText = this.dataset.title;
    });
  });

  // Show success/error from query params
  const params = new URLSearchParams(window.location.search);
  if (params.get('unarchived') === '1') {
    const m = new bootstrap.Modal(document.getElementById('unarchivedSuccessModal'));
    m.show();
  }
  if (params.get('error')) {
    const msg = decodeURIComponent(params.get('error'));
    const el = document.getElementById('unarchivedErrorMsg');
    if (el) el.textContent = msg;
    const m = new bootstrap.Modal(document.getElementById('unarchivedErrorModal'));
    m.show();
  }
  if (params.toString()) {
    const url = window.location.pathname; // clean the URL
    window.history.replaceState({}, '', url);
  }
});
</script>

<?php include('footer.php'); ?>
