<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/MLPredictor.php';

Auth::requireLogin();

$database = new Database();
$db = $database->getConnection();
$predictor = new MLPredictor($db);

$modelInfo = $predictor->getModelInfo();
$modelsTrained = $predictor->areModelsTrained();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Info - Student Performance Prediction</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>ML Model Information</h1>
                <a href="train.php" class="btn btn-primary">
                    <i class="fas fa-cogs"></i> Train Models
                </a>
            </div>
            
            <!-- Model Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Total Models</h4>
                        <p>3</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon <?php echo $modelsTrained ? 'success' : 'danger'; ?>">
                        <i class="fas <?php echo $modelsTrained ? 'fa-check' : 'fa-times'; ?>"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Status</h4>
                        <p><?php echo $modelsTrained ? 'Ready' : 'Not Trained'; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Algorithm Explanations -->
            <div class="card">
                <div class="card-header">
                    <h3>Algorithms Used</h3>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h4 style="color: #2563eb; margin-bottom: 8px;">
                        <i class="fas fa-check-circle"></i> Pass/Fail Prediction - Naive Bayes
                    </h4>
                    <p style="color: #64748b; margin-bottom: 8px;">
                        Naive Bayes is a probabilistic classifier based on Bayes' theorem. 
                        It assumes features are independent and calculates the probability of a student passing or failing 
                        based on their attendance, study hours, and grades.
                    </p>
                    <p><strong>Formula:</strong> P(Pass|features) = P(features|Pass) × P(Pass) / P(features)</p>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h4 style="color: #f59e0b; margin-bottom: 8px;">
                        <i class="fas fa-exclamation-triangle"></i> Dropout Risk - Decision Tree
                    </h4>
                    <p style="color: #64748b; margin-bottom: 8px;">
                        Decision Trees create a flowchart-like structure where each node represents a feature test, 
                        branches represent outcomes, and leaves represent classifications. 
                        The tree learns patterns like "If attendance < 60% AND study_hours < 2, then High dropout risk."
                    </p>
                    <p><strong>Method:</strong> Uses information gain to split data at each node.</p>
                </div>
                
                <div>
                    <h4 style="color: #22c55e; margin-bottom: 8px;">
                        <i class="fas fa-chalkboard-teacher"></i> Tutoring Need - Naive Bayes
                    </h4>
                    <p style="color: #64748b; margin-bottom: 8px;">
                        Also uses Naive Bayes to determine if a student needs extra tutoring support. 
                        Considers factors like grades, study hours, and extracurricular involvement 
                        to predict if intervention would be beneficial.
                    </p>
                    <p><strong>Advantage:</strong> Works well with small datasets and is fast to train.</p>
                </div>
            </div>
            
            <!-- Model History -->
            <div class="card">
                <div class="card-header">
                    <h3>Training History</h3>
                </div>
                
                <?php if (!empty($modelInfo)): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Model</th>
                                <th>Accuracy</th>
                                <th>Trained At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modelInfo as $model): ?>
                            <tr>
                                <td>
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $model['model_name'])); ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?php echo $model['accuracy'] >= 70 ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo $model['accuracy']; ?>%
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($model['trained_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="color: #64748b; text-align: center; padding: 40px;">
                    No models trained yet. <a href="train.php">Train models now</a>
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Features Used -->
            <div class="card">
                <div class="card-header">
                    <h3>Input Features</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th>Type</th>
                                <th>Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Attendance</td>
                                <td>Numeric (0-100%)</td>
                                <td><span class="badge badge-danger">High</span></td>
                            </tr>
                            <tr>
                                <td>Study Hours</td>
                                <td>Numeric (0-12 hrs)</td>
                                <td><span class="badge badge-danger">High</span></td>
                            </tr>
                            <tr>
                                <td>Previous Grade</td>
                                <td>Numeric (0-100%)</td>
                                <td><span class="badge badge-danger">High</span></td>
                            </tr>
                            <tr>
                                <td>Extracurricular</td>
                                <td>Boolean (Yes/No)</td>
                                <td><span class="badge badge-warning">Medium</span></td>
                            </tr>
                            <tr>
                                <td>Parent Education</td>
                                <td>Categorical (4 levels)</td>
                                <td><span class="badge badge-info">Low</span></td>
                            </tr>
                            <tr>
                                <td>Family Income</td>
                                <td>Categorical (3 levels)</td>
                                <td><span class="badge badge-info">Low</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
