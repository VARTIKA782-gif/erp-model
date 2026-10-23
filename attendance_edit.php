<?php
// attendance_edit.php – Edit an attendance record
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: attendance_list.php"); exit();
}
$att_id = (int)$_GET['id'];
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $att_date   = trim($_POST['attendance_date'] ?? '');
    $status     = $_POST['status'] ?? 'Present';

    if ($student_id === 0 || $subject_id === 0 || empty($att_date)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE attendance SET student_id=?, subject_id=?, attendance_date=?, status=? WHERE attendance_id=?");
        mysqli_stmt_bind_param($stmt, 'iissi', $student_id, $subject_id, $att_date, $status, $att_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => '✓ Attendance record updated!'];
            header("Location: attendance_list.php");
            exit();
        } else {
            $error = 'Error updating record: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Load current record
$stmt = mysqli_prepare($conn, "SELECT * FROM attendance WHERE attendance_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $att_id);
mysqli_stmt_execute($stmt);
$att = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$att) { header("Location: attendance_list.php"); exit(); }

// Use POST values if editing failed, else use DB values
$val_student  = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['student_id']      ?? 0)         : $att['student_id'];
$val_subject  = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['subject_id']      ?? 0)         : $att['subject_id'];
$val_date     = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['attendance_date'] ?? '')        : $att['attendance_date'];
$val_status   = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['status']          ?? 'Present') : $att['status'];

$students = mysqli_query($conn, "SELECT student_id, student_name, roll_number FROM students ORDER BY student_name");
$subjects = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");

$page_title = "Edit Attendance";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h2>Edit Attendance Record</h2>
        <a href="attendance_list.php" class="btn btn-secondary btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="attendance_edit.php?id=<?= $att_id ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="student_id">Student *</label>
                    <select id="student_id" name="student_id" class="form-control" required>
                        <?php while ($s = mysqli_fetch_assoc($students)): ?>
                            <option value="<?= $s['student_id'] ?>"
                                <?= ($val_student == $s['student_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['student_name']) ?> (<?= htmlspecialchars($s['roll_number']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject_id">Subject *</label>
                    <select id="subject_id" name="subject_id" class="form-control" required>
                        <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                            <option value="<?= $sub['subject_id'] ?>"
                                <?= ($val_subject == $sub['subject_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['subject_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="attendance_date">Date *</label>
                    <input type="date" id="attendance_date" name="attendance_date" class="form-control"
                           required value="<?= htmlspecialchars($val_date) ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Present" <?= ($val_status === 'Present') ? 'selected' : '' ?>>Present</option>
                        <option value="Absent"  <?= ($val_status === 'Absent')  ? 'selected' : '' ?>>Absent</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Record</button>
                <a href="attendance_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
