<?php
include('../connect.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Archive/Restore Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-4">
    <h2>Archive/Restore Functionality Test</h2>
    
    <?php
    // Get active providers
    $active_query = "SELECT * FROM active_service_provider WHERE status = 'Active' LIMIT 3";
    $active_result = $conn->query($active_query);
    
    // Get archived providers
    $archived_query = "SELECT * FROM active_service_provider WHERE status = 'Archived' LIMIT 3";
    $archived_result = $conn->query($archived_query);
    ?>
    
    <div class="row">
        <div class="col-md-6">
            <h4>Active Providers (Test Archive)</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($active_result && $active_result->num_rows > 0): ?>
                            <?php while ($row = $active_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['provider_id'] ?></td>
                                    <td><?= htmlspecialchars($row['company_name']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" 
                                                onclick="testArchive(<?= $row['provider_id'] ?>, '<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-archive"></i> Archive
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No active providers found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-6">
            <h4>Archived Providers (Test Restore)</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($archived_result && $archived_result->num_rows > 0): ?>
                            <?php while ($row = $archived_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['provider_id'] ?></td>
                                    <td><?= htmlspecialchars($row['company_name']) ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="testRestore(<?= $row['provider_id'] ?>, '<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No archived providers found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="active_providers.php" class="btn btn-primary">Go to Active Providers</a>
        <a href="archived_providers.php" class="btn btn-secondary">Go to Archived Providers</a>
        <button onclick="location.reload()" class="btn btn-info">Refresh</button>
    </div>

    <script>
    function testArchive(providerId, companyName) {
        Swal.fire({
            title: 'Test Archive',
            text: `Archive ${companyName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Archive'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'archive_provider.php';
                
                const providerIdInput = document.createElement('input');
                providerIdInput.type = 'hidden';
                providerIdInput.name = 'provider_id';
                providerIdInput.value = providerId;
                
                const archiveInput = document.createElement('input');
                archiveInput.type = 'hidden';
                archiveInput.name = 'archive_provider';
                archiveInput.value = '1';
                
                form.appendChild(providerIdInput);
                form.appendChild(archiveInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    function testRestore(providerId, companyName) {
        Swal.fire({
            title: 'Test Restore',
            text: `Restore ${companyName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Restore'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'unarchive_provider.php';
                
                const providerIdInput = document.createElement('input');
                providerIdInput.type = 'hidden';
                providerIdInput.name = 'provider_id';
                providerIdInput.value = providerId;
                
                const unarchiveInput = document.createElement('input');
                unarchiveInput.type = 'hidden';
                unarchiveInput.name = 'unarchive_provider';
                unarchiveInput.value = '1';
                
                form.appendChild(providerIdInput);
                form.appendChild(unarchiveInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>
</body>
</html>
