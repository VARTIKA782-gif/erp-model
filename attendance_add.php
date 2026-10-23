<?php
// attendance_add.php – Add a new attendance record
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $att_date   = trim($_POST['attendance_date'] ?? '');
    $status     = $_POST['status'] ?? 'Present';

    if ($student_id === 0 || $subject_id === 0 || empty($att_date)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($status, ['Present', 'Absent'])) {
        $error = 'Invalid status value.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiss', $student_id, $subject_id, $att_date, $status);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => '✓ Attendance record added!'];
            header("Location: attendance_list.php");
            exit();
        } else {
            $error = 'Error adding record: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Load dropdowns
$students = mysqli_query($conn, "SELECT student_id, student_name, roll_number FROM students ORDER BY student_name");
$subjects = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");

$page_title = "Add Attendance";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h2>Add Attendance Record</h2>
        <a href="attendance_list.php" class="btn btn-secondary btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="attendance_add.php">
            <div class="form-grid">
                <div class="form-group">
                    <label for="student_id">Student *</label>
                    <select id="student_id" name="student_id" class="form-control" required>
                        <option value="0">-- Select Student --</option>
                        <?php while ($s = mysqli_fetch_assoc($students)): ?>
                            <option value="<?= $s['student_id'] ?>"
                                <?= (($_POST['student_id'] ?? 0) == $s['student_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['student_name']) ?> (<?= htmlspecialchars($s['roll_number']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject_id">Subject *</label>
                    <select id="subject_id" name="subject_id" class="form-control" required>
                        <option value="0">-- Select Subject --</option>
                        <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                            <option value="<?= $sub['subject_id'] ?>"
                                <?= (($_POST['subject_id'] ?? 0) == $sub['subject_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['subject_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="attendance_date">Date *</label>
                    <input type="date" id="attendance_date" name="attendance_date" class="form-control"
                           required value="<?= htmlspecialchars($_POST['attendance_date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Present" <?= (($_POST['status'] ?? 'Present') === 'Present') ? 'selected' : '' ?>>Present</option>
                        <option value="Absent"  <?= (($_POST['status'] ?? '') === 'Absent')  ? 'selected' : '' ?>>Absent</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Record</button>
                <a href="attendance_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
