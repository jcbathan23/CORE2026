<?php 
include('header.php'); 
include('sidebar.php'); 
include('navbar.php'); 
include('../connect.php'); 
?>
<style>
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
  }
</style>

<div class="content p-4">
  <h3 class="mb-4">CREATE NEW SOP</h3>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <form action="save_sop.php" method="POST" enctype="multipart/form-data">

        <!-- SOP Title -->
        <div class="mb-3">
          <label class="form-label fw-bold">SOP Title</label>
          <input type="text" name="title" class="form-control" placeholder="e.g., Hazardous Cargo Handling SOP" required>
        </div>

        <!-- Category -->
        <div class="mb-3">
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

        <!-- SOP Content -->
        <div class="mb-3">
          <label class="form-label fw-bold">Procedure Details</label>
          <textarea name="content" class="form-control" rows="6" placeholder="Write step-by-step procedure here..." required></textarea>
        </div>

        <!-- File Upload -->
        <div class="mb-3">
          <label class="form-label fw-bold">Upload SOP File (optional)</label>
          <input type="file" name="sop_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
          <small class="text-muted">Allowed formats: PDF, Word, JPG, PNG</small>
        </div>

        <!-- Status -->
        <div class="mb-3">
          <label class="form-label fw-bold">Status</label>
          <select name="status" class="form-select" required>
            <option value="Active">Active</option>
            <option value="Draft">Draft</option>
          </select>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end">
          <button type="submit" name="save_sop" class="btn btn-success me-2">
            <i class="fas fa-save"></i> Save SOP
          </button>
          <a href="sop_manager.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Cancel
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include('footer.php'); ?>
