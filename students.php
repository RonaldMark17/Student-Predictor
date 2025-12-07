<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Student.php';

Auth::requireLogin();

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'student_id' => trim($_POST['student_id'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'age' => intval($_POST['age'] ?? 0),
        'gender' => $_POST['gender'] ?? 'Male',
        'attendance' => floatval($_POST['attendance'] ?? 0),
        'study_hours' => floatval($_POST['study_hours'] ?? 0),
        'previous_grade' => floatval($_POST['previous_grade'] ?? 0),
        'extracurricular' => isset($_POST['extracurricular']) ? 1 : 0,
        'parent_education' => $_POST['parent_education'] ?? 'Secondary',
        'family_income' => $_POST['family_income'] ?? 'Medium'
    ];
    
    // Validation
    if (empty($data['student_id']) || empty($data['name'])) {
        $error = 'Student ID and Name are required.';
    } elseif ($data['age'] < 10 || $data['age'] > 50) {
        $error = 'Age must be between 10 and 50.';
    } elseif ($data['attendance'] < 0 || $data['attendance'] > 100) {
        $error = 'Attendance must be between 0 and 100.';
    } elseif ($data['previous_grade'] < 0 || $data['previous_grade'] > 100) {
        $error = 'Grade must be between 0 and 100.';
    } else {
        if ($action === 'add') {
            if ($student->create($data)) {
                $message = 'Student added successfully!';
                $action = 'list';
            } else {
                $error = 'Failed to add student. Student ID may already exist.';
            }
        } elseif ($action === 'edit' && $id) {
            if ($student->update($id, $data)) {
                $message = 'Student updated successfully!';
                $action = 'list';
            } else {
                $error = 'Failed to update student.';
            }
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    if ($student->delete($id)) {
        $message = 'Student deleted successfully!';
    } else {
        $error = 'Failed to delete student.';
    }
    $action = 'list';
}

// Get student for editing
$editStudent = null;
if ($action === 'edit' && $id) {
    $editStudent = $student->getById($id);
    if (!$editStudent) {
        $error = 'Student not found.';
        $action = 'list';
    }
}

$students = $student->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Performance Prediction</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1><?php echo $action === 'add' ? 'Add Student' : ($action === 'edit' ? 'Edit Student' : 'Students'); ?></h1>
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
                <?php else: ?>
                <a href="students.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <?php endif; ?>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="card">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_id">Student ID *</label>
                            <input type="text" id="student_id" name="student_id" class="form-control" 
                                   required maxlength="20"
                                   value="<?php echo htmlspecialchars($editStudent['student_id'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   required maxlength="100"
                                   value="<?php echo htmlspecialchars($editStudent['name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">Age *</label>
                            <input type="number" id="age" name="age" class="form-control" 
                                   required min="10" max="50"
                                   value="<?php echo $editStudent['age'] ?? 18; ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="Male" <?php echo ($editStudent['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($editStudent['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="attendance">Attendance (%) *</label>
                            <input type="number" id="attendance" name="attendance" class="form-control" 
                                   required min="0" max="100" step="0.1"
                                   value="<?php echo $editStudent['attendance'] ?? 80; ?>">
                        </div>
                        <div class="form-group">
                            <label for="study_hours">Daily Study Hours *</label>
                            <input type="number" id="study_hours" name="study_hours" class="form-control" 
                                   required min="0" max="12" step="0.5"
                                   value="<?php echo $editStudent['study_hours'] ?? 3; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="previous_grade">Previous Grade (%) *</label>
                            <input type="number" id="previous_grade" name="previous_grade" class="form-control" 
                                   required min="0" max="100" step="0.1"
                                   value="<?php echo $editStudent['previous_grade'] ?? 70; ?>">
                        </div>
                        <div class="form-group">
                            <label for="parent_education">Parent Education</label>
                            <select id="parent_education" name="parent_education" class="form-control">
                                <option value="None" <?php echo ($editStudent['parent_education'] ?? '') === 'None' ? 'selected' : ''; ?>>None</option>
                                <option value="Primary" <?php echo ($editStudent['parent_education'] ?? '') === 'Primary' ? 'selected' : ''; ?>>Primary</option>
                                <option value="Secondary" <?php echo ($editStudent['parent_education'] ?? 'Secondary') === 'Secondary' ? 'selected' : ''; ?>>Secondary</option>
                                <option value="Higher" <?php echo ($editStudent['parent_education'] ?? '') === 'Higher' ? 'selected' : ''; ?>>Higher</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="family_income">Family Income</label>
                            <select id="family_income" name="family_income" class="form-control">
                                <option value="Low" <?php echo ($editStudent['family_income'] ?? '') === 'Low' ? 'selected' : ''; ?>>Low</option>
                                <option value="Medium" <?php echo ($editStudent['family_income'] ?? 'Medium') === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="High" <?php echo ($editStudent['family_income'] ?? '') === 'High' ? 'selected' : ''; ?>>High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div style="padding-top: 8px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" name="extracurricular" value="1" 
                                           <?php echo ($editStudent['extracurricular'] ?? 0) ? 'checked' : ''; ?>
                                           style="margin-right: 8px; width: auto;">
                                    Participates in Extracurricular Activities
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Add Student' : 'Update Student'; ?>
                        </button>
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            
            <?php else: ?>
            <!-- Students List -->
            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Attendance</th>
                                <th>Study Hrs</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($s['name']); ?></td>
                                <td><?php echo $s['age']; ?></td>
                                <td><?php echo $s['gender']; ?></td>
                                <td>
                                    <span class="badge <?php echo $s['attendance'] >= 75 ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $s['attendance']; ?>%
                                    </span>
                                </td>
                                <td><?php echo $s['study_hours']; ?></td>
                                <td>
                                    <span class="badge <?php echo $s['previous_grade'] >= 60 ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $s['previous_grade']; ?>%
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="predict.php?id=<?php echo $s['id']; ?>" 
                                           class="btn btn-sm btn-success" title="Predict">
                                            <i class="fas fa-brain"></i>
                                        </a>
                                        <a href="?action=edit&id=<?php echo $s['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $s['id']; ?>" 
                                           class="btn btn-sm btn-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this student?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #64748b; padding: 40px;">
                                    No students found. <a href="?action=add">Add your first student</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
