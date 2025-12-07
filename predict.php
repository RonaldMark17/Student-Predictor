<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Student.php';
require_once 'classes/MLPredictor.php';

Auth::requireLogin();

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);
$predictor = new MLPredictor($db);

$id = $_GET['id'] ?? null;
$selectedStudent = null;
$predictions = null;
$error = '';

// Get all students for dropdown
$students = $student->getAll();

// If student ID provided, get that student
if ($id) {
    $selectedStudent = $student->getById($id);
    if (!$selectedStudent) {
        $error = 'Student not found.';
    }
}

// Handle prediction request
if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($selectedStudent && isset($_GET['predict']))) {
    $studentId = $_POST['student_id'] ?? $id;
    $selectedStudent = $student->getById($studentId);
    
    if ($selectedStudent) {
        // Check if models are trained
        if (!$predictor->areModelsTrained()) {
            $error = 'Models are not trained yet. Please train the models first.';
        } else {
            // Prepare student data for prediction
            $parentEducationCode = ['None' => 0, 'Primary' => 1, 'Secondary' => 2, 'Higher' => 3];
            $incomeCode = ['Low' => 0, 'Medium' => 1, 'High' => 2];
            
            $studentData = [
                floatval($selectedStudent['attendance']),
                floatval($selectedStudent['study_hours']),
                floatval($selectedStudent['previous_grade']),
                intval($selectedStudent['extracurricular']),
                $parentEducationCode[$selectedStudent['parent_education']] ?? 2,
                $incomeCode[$selectedStudent['family_income']] ?? 1
            ];
            
            $predictions = $predictor->predictAll($studentData);
        }
    } else {
        $error = 'Please select a student.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predictions - Student Performance Prediction</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Student Predictions</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Student Selection -->
            <div class="card">
                <div class="card-header">
                    <h3>Select Student</h3>
                </div>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_id">Choose Student</label>
                            <select id="student_id" name="student_id" class="form-control" required>
                                <option value="">-- Select a Student --</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>" 
                                        <?php echo ($selectedStudent && $selectedStudent['id'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['student_id'] . ' - ' . $s['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary" style="margin-top: 0;">
                                <i class="fas fa-brain"></i> Get Predictions
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if ($selectedStudent): ?>
            <!-- Student Info -->
            <div class="card">
                <div class="card-header">
                    <h3>Student Information</h3>
                </div>
                <div class="form-row">
                    <div>
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($selectedStudent['student_id']); ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($selectedStudent['name']); ?></p>
                        <p><strong>Age:</strong> <?php echo $selectedStudent['age']; ?> years</p>
                    </div>
                    <div>
                        <p><strong>Attendance:</strong> <?php echo $selectedStudent['attendance']; ?>%</p>
                        <p><strong>Study Hours:</strong> <?php echo $selectedStudent['study_hours']; ?> hrs/day</p>
                        <p><strong>Previous Grade:</strong> <?php echo $selectedStudent['previous_grade']; ?>%</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($predictions): ?>
            <!-- Prediction Results -->
            <div class="prediction-grid">
                <!-- Pass/Fail Prediction -->
                <div class="prediction-card">
                    <h4><i class="fas fa-check-circle"></i> Pass/Fail Prediction</h4>
                    <div class="prediction-result <?php echo strtolower($predictions['pass_fail']['result']); ?>">
                        <?php echo htmlspecialchars($predictions['pass_fail']['result']); ?>
                    </div>
                    <div class="prediction-probability">
                        Confidence: <?php echo $predictions['pass_fail']['probability']; ?>%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $predictions['pass_fail']['result'] === 'Pass' ? 'success' : 'danger'; ?>" 
                             style="width: <?php echo $predictions['pass_fail']['probability']; ?>%"></div>
                    </div>
                </div>
                
                <!-- Dropout Risk -->
                <div class="prediction-card">
                    <h4><i class="fas fa-exclamation-triangle"></i> Dropout Risk</h4>
                    <div class="prediction-result <?php echo strtolower($predictions['dropout']['result']); ?>">
                        <?php echo htmlspecialchars($predictions['dropout']['result']); ?>
                    </div>
                    <div class="prediction-probability">
                        Risk Level: <?php echo $predictions['dropout']['probability']; ?>%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $predictions['dropout']['result'] === 'High' ? 'danger' : 'success'; ?>" 
                             style="width: <?php echo $predictions['dropout']['probability']; ?>%"></div>
                    </div>
                </div>
                
                <!-- Tutoring Need -->
                <div class="prediction-card">
                    <h4><i class="fas fa-chalkboard-teacher"></i> Needs Tutoring</h4>
                    <div class="prediction-result <?php echo strtolower($predictions['tutoring']['result']); ?>">
                        <?php echo htmlspecialchars($predictions['tutoring']['result']); ?>
                    </div>
                    <div class="prediction-probability">
                        Probability: <?php echo $predictions['tutoring']['probability']; ?>%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $predictions['tutoring']['result'] === 'Yes' ? 'warning' : 'success'; ?>" 
                             style="width: <?php echo $predictions['tutoring']['probability']; ?>%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Recommendations -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-lightbulb"></i> Recommendations</h3>
                </div>
                <ul style="margin-left: 20px;">
                    <?php if ($predictions['pass_fail']['result'] === 'Fail'): ?>
                    <li style="margin-bottom: 8px;">Student is at risk of failing. Consider additional support.</li>
                    <?php endif; ?>
                    
                    <?php if ($predictions['dropout']['result'] === 'High'): ?>
                    <li style="margin-bottom: 8px;">High dropout risk detected. Schedule counseling session.</li>
                    <?php endif; ?>
                    
                    <?php if ($predictions['tutoring']['result'] === 'Yes'): ?>
                    <li style="margin-bottom: 8px;">Tutoring is recommended for this student.</li>
                    <?php endif; ?>
                    
                    <?php if ($selectedStudent['attendance'] < 75): ?>
                    <li style="margin-bottom: 8px;">Improve attendance (currently <?php echo $selectedStudent['attendance']; ?>%).</li>
                    <?php endif; ?>
                    
                    <?php if ($selectedStudent['study_hours'] < 3): ?>
                    <li style="margin-bottom: 8px;">Increase daily study hours (currently <?php echo $selectedStudent['study_hours']; ?> hrs).</li>
                    <?php endif; ?>
                    
                    <?php if ($predictions['pass_fail']['result'] === 'Pass' && $predictions['dropout']['result'] === 'Low' && $predictions['tutoring']['result'] === 'No'): ?>
                    <li style="color: #22c55e;">Student is performing well! Keep up the good work.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!$predictor->areModelsTrained()): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Models not trained!</strong> 
                Please <a href="train.php">train the ML models</a> before making predictions.
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
