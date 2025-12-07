<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Student.php';

Auth::requireLogin();

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

$stats = $student->getStats();
$totalStudents = $student->getCount();
$user = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Performance Prediction</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Total Students</h4>
                        <p><?php echo $totalStudents; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Avg Attendance</h4>
                        <p><?php echo $stats['avg_attendance'] ?? 0; ?>%</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Avg Study Hours</h4>
                        <p><?php echo $stats['avg_study_hours'] ?? 0; ?> hrs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Avg Grade</h4>
                        <p><?php echo $stats['avg_grade'] ?? 0; ?>%</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="btn-group">
                    <a href="students.php?action=add" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Student
                    </a>
                    <a href="predict.php" class="btn btn-success">
                        <i class="fas fa-brain"></i> Make Prediction
                    </a>
                    <a href="train.php" class="btn btn-secondary">
                        <i class="fas fa-cogs"></i> Train Models
                    </a>
                </div>
            </div>
            
            <!-- Recent Students -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Students</h3>
                    <a href="students.php" class="btn btn-sm btn-secondary">View All</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Attendance</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $students = $student->getAll();
                            $recent = array_slice($students, 0, 5);
                            foreach ($recent as $s): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($s['name']); ?></td>
                                <td><?php echo $s['attendance']; ?>%</td>
                                <td><?php echo $s['previous_grade']; ?>%</td>
                                <td>
                                    <a href="predict.php?id=<?php echo $s['id']; ?>" 
                                       class="btn btn-sm btn-success">
                                        <i class="fas fa-brain"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">
                                    No students found. Add your first student!
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
