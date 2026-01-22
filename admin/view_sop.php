  <?php
  include('header.php');
  include('sidebar.php');
  include('navbar.php');
  include('../connect.php');

  $_SESSION['sop_last_activity'] = time();

  // ✅ Handle archiving inline (client redirects with query for modal feedback)
  if (isset($_GET['archive_id'])) {
      $sop_id = intval($_GET['archive_id']);

      $sql = "UPDATE sop_documents SET status='Archived' WHERE sop_id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $sop_id);

      if ($stmt->execute()) {
          echo "<script>window.location='view_sop.php?archived=1';</script>";
      } else {
          $msg = urlencode('Error archiving SOP.');
          echo "<script>window.location='view_sop.php?error={$msg}';</script>";
      }

      $stmt->close();
  }
  ?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
  }
  .sop-header-actions{display:flex;gap:.75rem}
  .sop-search{background:#fff;border:1px solid rgba(0,0,0,.08);box-shadow:0 4px 16px rgba(15,23,42,.06);border-radius:14px;padding:.85rem 1rem;transition:box-shadow .2s ease,border-color .2s ease}
  .sop-search:focus-within{box-shadow:0 6px 22px rgba(15,23,42,.1);border-color:#dbe4ff}
  .sop-search .form-control{border:none;box-shadow:none}
  .sop-grid{margin-top:1rem}
  .sop-card{background:var(--bs-body-bg,#fff);border:1px solid rgba(0,0,0,.06);border-radius:18px;padding:18px;box-shadow:0 10px 30px rgba(2,6,23,.07);transition:transform .2s ease, box-shadow .2s ease}
  .sop-card:hover{transform:translateY(-3px);box-shadow:0 16px 38px rgba(2,6,23,.12)}
  .sop-icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:linear-gradient(135deg,#e9f0ff,#eef6ff);color:#4a6fff}
  .sop-title{font-weight:700;margin:0}
  .sop-badge{display:inline-block;font-size:.75rem;padding:.25rem .5rem;border-radius:999px;background:#eaf5ff;color:#1976d2}
  .sop-desc{color:#6c757d;margin:.35rem 0 0}
  .sop-meta{color:#6c757d;font-size:.9rem;display:flex;gap:18px;flex-wrap:wrap;margin-top:.75rem;align-items:center}
  .status-badge{font-size:.72rem;padding:.2rem .6rem;border-radius:999px}
  .status-active{background:#eafaf1;color:#0f7a45;border:1px solid #c9eedc}
  .status-draft{background:#fff7e6;color:#9a6700;border:1px solid #ffe8b0}
  .sop-actions{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.9rem}
  .btn-soft{background:#f4f7fb;border:1px solid #e7ecf3;color:#2b3f4e;font-weight:600}
  .btn-soft:hover{background:#eef2f8}
  .btn-soft-danger{background:#fff5f5;border:1px solid #ffd6d6;color:#c03221;font-weight:600}
  .btn-soft-danger:hover{background:#ffeaea}
  @media (max-width: 576px){.sop-actions{flex-direction:column}}
  /* Dark mode adjustments */
  body.dark-mode .sop-search{background:#0b1220;border-color:rgba(255,255,255,.10);box-shadow:0 4px 16px rgba(0,0,0,.6)}
  body.dark-mode .sop-search .form-control{background:transparent;color:#e6edf3}
  body.dark-mode .sop-search .form-control::placeholder{color:#94a3b8}
  body.dark-mode .sop-card{background:#111827;border-color:rgba(255,255,255,.10);box-shadow:0 10px 30px rgba(0,0,0,.65)}
  body.dark-mode .sop-title{color:#e6edf3}
  body.dark-mode .sop-desc,body.dark-mode .sop-meta{color:#cbd5e1}
  body.dark-mode .sop-icon{background:linear-gradient(135deg,#1f2a44,#23324a);color:#93c5fd}
  body.dark-mode .sop-badge{background:rgba(59,130,246,.15);color:#bfdbfe}
  body.dark-mode .status-active{background:rgba(34,197,94,.15);border-color:rgba(34,197,94,.35);color:#86efac}
  body.dark-mode .status-draft{background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.35);color:#fcd34d}
  body.dark-mode .btn-soft{background:#1f2937;border-color:rgba(255,255,255,.12);color:#e5e7eb}
  body.dark-mode .btn-soft:hover{background:#233044}
  body.dark-mode .btn-soft-danger{background:#2b1f20;border-color:rgba(248,113,113,.35);color:#fca5a5}
  body.dark-mode .btn-soft-danger:hover{background:#3b2730}
</style>

<div class="content p-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">SOP Manager</h3>
        </div>

  <!-- Update Success Modal -->
  <div class="modal fade" id="sopUpdatedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body p-4">
          <div class="mb-2 text-success"><i class="fas fa-check-circle fa-2x"></i></div>
          <h6 class="mb-1">SOP updated successfully!</h6>
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- SOP Re-auth Modal -->
  <div class="modal fade" id="sopReauthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white">
          <h6 class="modal-title">Session Locked</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="small text-muted mb-2">For security, please re-enter your password to continue.</div>
          <div class="mb-2">
            <input type="password" id="sopReauthPassword" class="form-control" placeholder="Password">
            <div id="sopReauthError" class="text-danger small mt-2" style="display:none"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-secondary" id="sopReauthSubmit">Unlock</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Archive Success Modal -->
  <div class="modal fade" id="sopArchivedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body p-4">
          <div class="mb-2 text-success"><i class="fas fa-archive fa-2x"></i></div>
          <h6 class="mb-1">SOP archived successfully.</h6>
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
        <div class="sop-header-actions">
            <button type="button" class="btn btn-modern-primary" data-bs-toggle="modal" data-bs-target="#addSopModal">
                <i class="fas fa-plus me-2"></i>Add SOP
            </button>
        </div>
    </div>

    <div class="sop-search mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-search text-muted"></i>
            <input id="sopSearch" type="text" class="form-control" placeholder="Search SOPs...">
        </div>
    </div>

    <div class="sop-grid row g-3" id="sopGrid">
        <?php
        $result = $conn->query("SELECT * FROM sop_documents WHERE status IN ('Active','Draft') ORDER BY created_at DESC");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $title = htmlspecialchars($row['title']);
                $category = htmlspecialchars($row['category']);
                $status = htmlspecialchars($row['status']);
                $contentPlain = trim(strip_tags($row['content'] ?? ''));
                $excerpt = mb_strimwidth($contentPlain, 0, 220, '...');
                $created = date('Y-m-d', strtotime($row['created_at']));
                echo "
                <div class='col-12 col-md-6'>
                    <div class='sop-card' data-title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "' data-category='" . htmlspecialchars($row['category'], ENT_QUOTES) . "'>
                        <div class='d-flex align-items-start gap-3'>
                            <div class='sop-icon'><i class='fas fa-file-alt'></i></div>
                            <div class='flex-grow-1'>
                                <div class='d-flex align-items-center gap-2 mb-1'>
                                    <h5 class='sop-title mb-0'>".$title."</h5>
                                    <span class='sop-badge'><i class='fas fa-tag me-1'></i>".$category."</span>
                                </div>
                                <p class='sop-desc'>".htmlspecialchars($excerpt)."</p>
                                <div class='sop-meta'>
                                    <div><i class='far fa-clock me-1'></i>Last Updated: ".$created."</div>
                                    <span class='status-badge ".($status==='Active'?'status-active':'status-draft')."'>".$status."</span>
                                </div>
                                <div class='sop-actions'>
                                    <button class='btn btn-soft updateBtn'
                                        data-id='".$row['sop_id']."'
                                        data-title='".$title."'
                                        data-category='".$category."'
                                        data-status='".$status."'
                                        data-content='" . htmlspecialchars($row['content'], ENT_QUOTES) . "'
                                        data-file='".$row['file_path']."'
                                        data-bs-toggle='modal' data-bs-target='#updateSopModal'>
                                        <i class='fas fa-edit me-2'></i>Edit
                                    </button>
                                    <button class='btn btn-soft-danger archiveBtn'
                                        data-id='".$row['sop_id']."'
                                        data-title='".$title."'
                                        data-bs-toggle='modal' data-bs-target='#archiveSopModal'>
                                        <i class='fas fa-trash-alt me-2'></i>Delete
                                    </button>
                                    <button class='btn btn-soft viewBtn'
                                        data-id='".$row['sop_id']."'
                                        data-title='".$title."'
                                        data-category='".$category."'
                                        data-status='".$status."'
                                        data-content='" . htmlspecialchars($row['content'], ENT_QUOTES) . "'
                                        data-file='".$row['file_path']."'
                                        data-bs-toggle='modal' data-bs-target='#viewSopModal'>
                                        <i class='fas fa-eye me-2'></i>View
                                    </button>
                                    <a class='btn btn-soft' target='_blank' href='print_sop.php?id=".$row['sop_id']."'>
                                      <i class='fas fa-print me-2'></i>Print
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
            }
        } else {
            echo "<div class='col-12'><div class='sop-card text-center'><h5 class='mb-1'>No SOPs Found</h5><div class='text-muted'>There are no Standard Operating Procedures available at this time.</div></div></div>";
        }
        ?>
    </div>
</div>

  <!-- Add SOP Modal -->
  <div class="modal fade" id="addSopModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <form id="addSopForm" action="save_sop.php" method="POST" class="modal-content" enctype="multipart/form-data">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">Add SOP</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-bold">SOP Title</label>
              <input type="text" name="title" class="form-control" placeholder="e.g., Hazardous Cargo Handling SOP" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Status</label>
              <select name="status" class="form-select" required>
                <option value="Active">Active</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Category</label>
              <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="Safety">Safety</option>
                <option value="Customs">Customs</option>
                <option value="Logistics">Logistics</option>
                <option value="Fleet">Fleet Operations</option>
                <option value="General">General</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Upload SOP File (optional)</label>
              <div class="small text-muted mb-2">
                <div class="fw-semibold">Required documents may include:</div>
                <div>Valid business permit</div>
                <div>Clearances</div>
                <div>DENR permit (if applicable)</div>
              </div>
              <input type="file" name="sop_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Procedure Details</label>
              <textarea name="content" class="form-control" rows="6" placeholder="Write step-by-step procedure here..." required></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_sop" class="btn btn-secondary">
            <i class="fas fa-save me-1"></i> Save SOP
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Success Modal -->
  <div class="modal fade" id="sopSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body p-4">
          <div class="mb-2 text-success"><i class="fas fa-check-circle fa-2x"></i></div>
          <h6 class="mb-1">SOP Created Successfully!</h6>
          <p class="text-muted mb-3">Your new SOP has been saved.</p>
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Modal -->
  <div class="modal fade" id="sopErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body p-4">
          <div class="mb-2 text-danger"><i class="fas fa-times-circle fa-2x"></i></div>
          <h6 class="mb-1">Unable to save SOP</h6>
          <p id="sopErrorText" class="text-muted mb-3"></p>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ Clean View Modal -->
  <div class="modal fade" id="viewSopModal" tabindex="-1" aria-labelledby="viewSopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="viewSopLabel">SOP Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Title</label>
              <input type="text" class="form-control" id="sopTitle" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Category</label>
              <input type="text" class="form-control" id="sopCategory" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Status</label>
              <input type="text" class="form-control" id="sopStatus" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Content</label>
              <textarea class="form-control" id="sopContent" rows="8" readonly></textarea>
            </div>

            <div class="col-12">
              <hr>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold">SOP AI Compliance Check</div>
                <button type="button" class="btn btn-sm btn-secondary" id="runSopAiBtn">
                  <i class="fas fa-robot me-1"></i>Run Check
                </button>
              </div>
              <input type="text" class="form-control" id="sopAiAction" placeholder="Describe the action to check against this SOP (e.g., 'Approve provider registration')">
              <div class="form-text">AI uses only the SOP text above + your action. If SOP is unclear, it returns REVIEW.</div>
              <div id="sopAiResult" class="mt-3"></div>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Attached File</label>
              <div>
                <a href="" target="_blank" id="sopFileLink">No file uploaded</a>
              </div>
              <div class="mt-3" id="sopFilePreview" style="display:none"></div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <a href="#" target="_blank" class="btn btn-secondary" id="sopPrintLink">
            <i class="fas fa-print me-1"></i> Print
          </a>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- ✅ Clean Update Modal -->
  <div class="modal fade" id="updateSopModal" tabindex="-1" aria-labelledby="updateSopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <form action="update_sop.php" method="POST" class="modal-content" enctype="multipart/form-data">
        
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="updateSopLabel">Update SOP</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="sop_id" id="updateSopId">

          <!-- Row 1: Title + Category -->
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Title</label>
              <input type="text" name="title" class="form-control" id="updateSopTitle" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Category</label>
              <input type="text" name="category" class="form-control" id="updateSopCategory" required>
            </div>
          </div>

          <!-- Row 2: Status + File -->
          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <label class="form-label fw-bold">Status</label>
              <select name="status" class="form-select" id="updateSopStatus" required>
                <option value="Active">Active</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Upload File (optional)</label>
              <div class="small text-muted mb-2">
                <div class="fw-semibold">Required documents may include:</div>
                <div>Valid business permit</div>
                <div>Clearances</div>
                <div>DENR permit (if applicable)</div>
              </div>
              <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
            </div>
          </div>

          <!-- Row 3: Content -->
          <div class="mt-3">
            <label class="form-label fw-bold">Content</label>
            <textarea name="content" class="form-control" id="updateSopContent" rows="6" required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_sop" class="btn btn-secondary">
            <i class="fas fa-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>


  <!-- Archive Modal -->
  <div class="modal fade" id="archiveSopModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">Archive SOP</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to archive <strong id="archiveSopTitle"></strong>?</p>
        </div>
        <div class="modal-footer">
          <form method="GET" action="view_sop.php">
            <input type="hidden" name="archive_id" id="archiveSopId">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Archive</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const SOP_IDLE_LOCK_MS = 2 * 60 * 1000;
    let sopLocked = false;
    let pendingDownloadUrl = null;
    let pendingPreview = null;
    let idleTimer = null;
    let currentSopId = null;

    function renderSopPreview(previewEl, ext, previewUrl) {
      if (!previewEl) return;
      if (['jpg','jpeg','png','webp'].includes(ext)) {
        previewEl.style.display = '';
        previewEl.innerHTML = '<img src="' + previewUrl + '" alt="Attachment" style="max-width:100%;height:auto" class="border rounded">';
        return;
      }
      if (ext === 'pdf') {
        previewEl.style.display = '';
        previewEl.innerHTML = '<iframe src="' + previewUrl + '" style="width:100%;height:420px" class="border rounded"></iframe>';
        return;
      }
      previewEl.style.display = 'none';
      previewEl.innerHTML = '';
    }

    function resetIdleTimer() {
      if (idleTimer) clearTimeout(idleTimer);
      idleTimer = setTimeout(() => {
        sopLocked = true;
      }, SOP_IDLE_LOCK_MS);
    }

    ['click','mousemove','keydown','scroll','touchstart'].forEach(evt => {
      document.addEventListener(evt, resetIdleTimer, { passive: true });
    });
    resetIdleTimer();

    function showReauthModal() {
      const pass = document.getElementById('sopReauthPassword');
      const err = document.getElementById('sopReauthError');
      if (pass) pass.value = '';
      if (err) { err.style.display = 'none'; err.textContent = ''; }
      const m = new bootstrap.Modal(document.getElementById('sopReauthModal'));
      m.show();
      setTimeout(() => { if (pass) pass.focus(); }, 200);
    }

    async function doReauth() {
      const pass = document.getElementById('sopReauthPassword');
      const err = document.getElementById('sopReauthError');
      const password = pass ? pass.value : '';
      if (!password) {
        if (err) { err.textContent = 'Password is required.'; err.style.display = ''; }
        return;
      }
      try {
        const fd = new FormData();
        fd.append('password', password);
        const resp = await fetch('sop_reauth.php', { method: 'POST', body: fd });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok || !data.ok) {
          if (err) { err.textContent = data.error || 'Re-auth failed.'; err.style.display = ''; }
          return;
        }
        sopLocked = false;
        resetIdleTimer();
        const modalEl = document.getElementById('sopReauthModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        if (pendingDownloadUrl) {
          const url = pendingDownloadUrl;
          pendingDownloadUrl = null;
          window.open(url, '_blank');
        }

        if (pendingPreview && pendingPreview.el) {
          renderSopPreview(pendingPreview.el, pendingPreview.ext, pendingPreview.url);
          pendingPreview = null;
        }
      } catch (e) {
        if (err) { err.textContent = 'Network error. Please try again.'; err.style.display = ''; }
      }
    }

    const reauthBtn = document.getElementById('sopReauthSubmit');
    if (reauthBtn) reauthBtn.addEventListener('click', doReauth);
    const passInput = document.getElementById('sopReauthPassword');
    if (passInput) passInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') doReauth(); });

    const addSopModalEl = document.getElementById('addSopModal');
    if (addSopModalEl) {
      addSopModalEl.addEventListener('show.bs.modal', function(){
        const form = document.getElementById('addSopForm');
        if (form) form.reset();
      });
    }
    // Show success/error modals based on URL params
    const params = new URLSearchParams(window.location.search);
    if (params.get('success') === '1') {
      const m = new bootstrap.Modal(document.getElementById('sopSuccessModal'));
      m.show();
    }
    if (params.get('updated') === '1') {
      const m = new bootstrap.Modal(document.getElementById('sopUpdatedModal'));
      m.show();
    }
    if (params.get('archived') === '1') {
      const m = new bootstrap.Modal(document.getElementById('sopArchivedModal'));
      m.show();
    }
    if (params.get('error')) {
      const msg = params.get('error');
      const el = document.getElementById('sopErrorText');
      if (el) el.textContent = decodeURIComponent(msg);
      const m = new bootstrap.Modal(document.getElementById('sopErrorModal'));
      m.show();
    }
    if (params.toString()) {
      const url = window.location.pathname; // clean URL
      window.history.replaceState({}, '', url);
    }
    const search = document.getElementById('sopSearch');
    if (search) {
      search.addEventListener('input', function(){
        const q = this.value.toLowerCase();
        document.querySelectorAll('#sopGrid .sop-card').forEach(card=>{
          const text = card.getAttribute('data-title').toLowerCase() + ' ' + (card.getAttribute('data-category')||'').toLowerCase();
          card.parentElement.style.display = text.includes(q) ? '' : 'none';
        });
      });
    }
    // View Button
    document.querySelectorAll(".viewBtn").forEach(btn => {
      btn.addEventListener("click", function () {
        currentSopId = this.dataset.id || null;
        document.getElementById("sopTitle").value = this.dataset.title;
        document.getElementById("sopCategory").value = this.dataset.category;
        document.getElementById("sopStatus").value = this.dataset.status;
        document.getElementById("sopContent").value = this.dataset.content;

        const aiActionEl = document.getElementById('sopAiAction');
        const aiResultEl = document.getElementById('sopAiResult');
        if (aiActionEl) aiActionEl.value = '';
        if (aiResultEl) aiResultEl.innerHTML = '';

        let filePath = this.dataset.file;
        let sopId = this.dataset.id;
        let fileLink = document.getElementById("sopFileLink");
        let printLink = document.getElementById("sopPrintLink");
        let preview = document.getElementById("sopFilePreview");
        if (printLink) {
          printLink.href = "print_sop.php?id=" + encodeURIComponent(sopId);
        }
        if (filePath && filePath.trim() !== "") {
          fileLink.href = "download_sop_file.php?sop_id=" + encodeURIComponent(sopId);
          fileLink.innerText = "Download File";
          fileLink.onclick = function (e) {
            if (sopLocked) {
              e.preventDefault();
              pendingDownloadUrl = this.href;
              showReauthModal();
              return false;
            }
            resetIdleTimer();
            return true;
          };

          const ext = (filePath.split('.').pop() || '').toLowerCase();
          const previewUrl = "download_sop_file.php?sop_id=" + encodeURIComponent(sopId) + "&inline=1";
          if (preview) {
            if (sopLocked) {
              pendingPreview = { el: preview, ext: ext, url: previewUrl };
              preview.style.display = '';
              preview.innerHTML = '<div class="alert alert-warning mb-0">Session locked. Click <strong>Download File</strong> to unlock and preview.</div>';
            } else {
              renderSopPreview(preview, ext, previewUrl);
              pendingPreview = null;
            }
          }
        } else {
          fileLink.href = "#";
          fileLink.innerText = "No file uploaded";
          fileLink.onclick = function(e){ e.preventDefault(); return false; };
          if (preview) { preview.style.display = 'none'; preview.innerHTML = ''; }
          pendingPreview = null;
        }
      });
    });

    const runSopAiBtn = document.getElementById('runSopAiBtn');
    if (runSopAiBtn) {
      runSopAiBtn.addEventListener('click', async function () {
        const aiActionEl = document.getElementById('sopAiAction');
        const aiResultEl = document.getElementById('sopAiResult');
        const action = aiActionEl ? (aiActionEl.value || '').toString().trim() : '';

        if (!currentSopId) {
          if (aiResultEl) aiResultEl.innerHTML = '<div class="alert alert-danger mb-0">Missing SOP ID.</div>';
          return;
        }
        if (!action) {
          if (aiResultEl) aiResultEl.innerHTML = '<div class="alert alert-warning mb-0">Please enter an action to check.</div>';
          return;
        }

        if (aiResultEl) {
          aiResultEl.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div><div class="mt-2">Running SOP compliance check...</div></div>';
        }

        try {
          const resp = await fetch('../api/ai/sop_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              sop_id: parseInt(currentSopId, 10),
              action: action,
              context: { source: 'admin/view_sop.php' }
            })
          });

          const data = await resp.json().catch(() => null);
          if (!resp.ok || !data || !data.success) {
            const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'AI check failed.';
            if (aiResultEl) aiResultEl.innerHTML = `<div class="alert alert-danger mb-0">${msg}</div>`;
            return;
          }

          if (aiResultEl) {
            aiResultEl.innerHTML = `
              <div class="mb-2"><strong>Status:</strong> ${data.sop_status}</div>
              <div class="mb-2"><strong>Reference:</strong> ${(data.reference || '').toString()}</div>
              <div class="mb-0"><strong>Explanation:</strong><div class="mt-1">${(data.explanation || '').toString()}</div></div>
            `;
          }
        } catch (e) {
          if (aiResultEl) aiResultEl.innerHTML = '<div class="alert alert-danger mb-0">Network error while calling SOP AI.</div>';
        }
      });
    }

    const viewSopModalEl = document.getElementById('viewSopModal');
    if (viewSopModalEl) {
      viewSopModalEl.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
      });
    }

    // Update Button
    document.querySelectorAll(".updateBtn").forEach(btn => {
      btn.addEventListener("click", function () {
        document.getElementById("updateSopId").value = this.dataset.id;
        document.getElementById("updateSopTitle").value = this.dataset.title;
        document.getElementById("updateSopCategory").value = this.dataset.category;
        document.getElementById("updateSopStatus").value = this.dataset.status;
        document.getElementById("updateSopContent").value = this.dataset.content;
      });
    });

    // Archive Button
    document.querySelectorAll(".archiveBtn").forEach(btn => {
      btn.addEventListener("click", function () {
        document.getElementById("archiveSopId").value = this.dataset.id;
        document.getElementById("archiveSopTitle").textContent = this.dataset.title;
      });
    });
  });
  </script>

  <?php include('footer.php'); ?>
