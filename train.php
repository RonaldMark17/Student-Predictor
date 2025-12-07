<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/MLPredictor.php';

Auth::requireLogin();

$database = new Database();
$db = $database->getConnection();
$predictor = new MLPredictor($db);

$message = '';
$error = '';
$results = null;

// Handle file upload and training
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['dataset']) && $_FILES['dataset']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['dataset'];
        $allowedTypes = ['text/csv', 'application/vnd.ms-excel'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($extension !== 'csv') {
            $error = 'Please upload a CSV file.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'File size must be less than 5MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadPath = $uploadDir . 'training_data.csv';
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                try {
                    $results = $predictor->trainFromCSV($uploadPath);
                    $message = 'Models trained successfully!';
                } catch (Exception $e) {
                    $error = 'Training failed: ' . $e->getMessage();
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    } else {
        $error = 'Please select a CSV file to upload.';
    }
}

$modelInfo = $predictor->getModelInfo();
$modelsTrained = $predictor->areModelsTrained();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Models - Student Performance Prediction</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Train ML Models</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Training Results -->
            <?php if ($results): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Pass/Fail Model</h4>
                        <p><?php echo $results['pass_fail']; ?>%</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Dropout Model</h4>
                        <p><?php echo $results['dropout']; ?>%</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Tutoring Model</h4>
                        <p><?php echo $results['tutoring']; ?>%</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Model Status -->
            <div class="card">
                <div class="card-header">
                    <h3>Model Status</h3>
                    <span class="badge <?php echo $modelsTrained ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $modelsTrained ? 'Trained' : 'Not Trained'; ?>
                    </span>
                </div>
                <?php if ($modelsTrained): ?>
                <p style="color: #22c55e;">
                    <i class="fas fa-check-circle"></i> All models are trained and ready for predictions.
                </p>
                <?php else: ?>
                <p style="color: #ef4444;">
                    <i class="fas fa-times-circle"></i> Models need to be trained before making predictions.
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Upload Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Upload Training Dataset</h3>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="file-upload" onclick="document.getElementById('dataset').click();">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Click to upload</strong> or drag and drop</p>
                        <p style="font-size: 0.8rem;">CSV file (max 5MB)</p>
                        <input type="file" id="dataset" name="dataset" accept=".csv">
                    </div>
                    
                    <p id="file-name" style="margin-top: 10px; color: #64748b;"></p>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-cogs"></i> Train Models
                    </button>
                </form>
            </div>
            
            <!-- CSV Format Guide -->
            <div class="card">
                <div class="card-header">
                    <h3>CSV Format Guide</h3>
                </div>
                <p style="margin-bottom: 16px;">Your CSV file should have the following columns:</p>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>attendance</td><td>Float (0-100)</td><td>Attendance percentage</td></tr>
                            <tr><td>study_hours</td><td>Float (0-12)</td><td>Daily study hours</td></tr>
                            <tr><td>previous_grade</td><td>Float (0-100)</td><td>Previous exam grade</td></tr>
                            <tr><td>extracurricular</td><td>Int (0 or 1)</td><td>Participates in activities</td></tr>
                            <tr><td>parent_education</td><td>Int (0-3)</td><td>0=None, 1=Primary, 2=Secondary, 3=Higher</td></tr>
                            <tr><td>family_income</td><td>Int (0-2)</td><td>0=Low, 1=Medium, 2=High</td></tr>
                            <tr><td>pass_fail</td><td>String</td><td>"Pass" or "Fail"</td></tr>
                            <tr><td>dropout_risk</td><td>String</td><td>"High" or "Low"</td></tr>
                            <tr><td>needs_tutoring</td><td>String</td><td>"Yes" or "No"</td></tr>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top: 16px;">
                    <a href="sample_dataset.csv" class="btn btn-secondary" download>
                        <i class="fas fa-download"></i> Download Sample Dataset
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('dataset').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            document.getElementById('file-name').textContent = fileName ? 'Selected: ' + fileName : '';
        });
    </script>
</body>
</html>
