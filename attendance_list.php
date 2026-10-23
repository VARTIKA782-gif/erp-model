<?php
// attendance_list.php – View attendance with filter and % summary + individual records.
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

$page_title = "Attendance";

// ---- Filter values from form (default = 0 = "all") ----
$filter_student = isset($_GET['student_id']) && is_numeric($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$filter_subject = isset($_GET['subject_id']) && is_numeric($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// ---- Load filter dropdowns ----
$students_dd = mysqli_query($conn, "SELECT student_id, student_name, roll_number FROM students ORDER BY student_name");
$subjects_dd = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");

// ---- Attendance SUMMARY query (% per student per subject) ----
$summary_sql = "
    SELECT
        s.student_id, s.student_name, s.roll_number,
        sub.subject_id, sub.subject_name,
        COUNT(*) AS total_classes,
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN a.status = 'Absent'  THEN 1 ELSE 0 END) AS absent_count,
        ROUND(100.0 * SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / COUNT(*), 1) AS percentage
    FROM attendance a
    JOIN students s   ON a.student_id = s.student_id
    JOIN subjects sub ON a.subject_id = sub.subject_id
    WHERE (? = 0 OR a.student_id = ?)
      AND (? = 0 OR a.subject_id = ?)
    GROUP BY s.student_id, s.student_name, s.roll_number, sub.subject_id, sub.subject_name
    ORDER BY s.student_name, sub.subject_name";

$stmt = mysqli_prepare($conn, $summary_sql);
mysqli_stmt_bind_param($stmt, 'iiii', $filter_student, $filter_student, $filter_subject, $filter_subject);
mysqli_stmt_execute($stmt);
$summary_result = mysqli_stmt_get_result($stmt);

// ---- Individual RECORDS query ----
$records_sql = "
    SELECT a.attendance_id, s.student_name, s.roll_number,
           sub.subject_name, a.attendance_date, a.status
    FROM attendance a
    JOIN students s   ON a.student_id = s.student_id
    JOIN subjects sub ON a.subject_id = sub.subject_id
    WHERE (? = 0 OR a.student_id = ?)
      AND (? = 0 OR a.subject_id = ?)
    ORDER BY a.attendance_date DESC, s.student_name";

$stmt2 = mysqli_prepare($conn, $records_sql);
mysqli_stmt_bind_param($stmt2, 'iiii', $filter_student, $filter_student, $filter_subject, $filter_subject);
mysqli_stmt_execute($stmt2);
$records_result = mysqli_stmt_get_result($stmt2);

// Helper: return CSS class for % bar color
function getPctClass($pct) {
    if ($pct >= 75) return 'pct-good';
    if ($pct >= 50) return 'pct-warn';
    return 'pct-bad';
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<?php
if (isset($_SESSION['flash'])) {
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    echo '<div class="alert alert-' . $f['type'] . '">' . htmlspecialchars($f['msg']) . '</div>';
} ?>

<!-- ===== FILTER BAR ===== -->
<form method="GET" action="attendance_list.php">
    <div class="filter-bar">
        <div class="form-group">
            <label>Filter by Student</label>
            <select name="student_id" class="form-control">
                <option value="0">-- All Students --</option>
                <?php while ($s = mysqli_fetch_assoc($students_dd)): ?>
                    <option value="<?= $s['student_id'] ?>"
                        <?= ($filter_student == $s['student_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['student_name']) ?> (<?= htmlspecialchars($s['roll_number']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Filter by Subject</label>
            <select name="subject_id" class="form-control">
                <option value="0">-- All Subjects --</option>
                <?php while ($sub = mysqli_fetch_assoc($subjects_dd)): ?>
                    <option value="<?= $sub['subject_id'] ?>"
                        <?= ($filter_subject == $sub['subject_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sub['subject_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group" style="justify-content:flex-end;">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </div>
        <div class="form-group" style="justify-content:flex-end;">
            <label>&nbsp;</label>
            <a href="attendance_list.php" class="btn btn-secondary">Clear</a>
        </div>
    </div>
</form>

<!-- ===== ATTENDANCE SUMMARY WITH % ===== -->
<div class="content-card" style="margin-bottom:24px;">
    <div class="card-header">
        <h2>Attendance Summary (% per Student per Subject)</h2>
    </div>
    <?php if (mysqli_num_rows($summary_result) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Roll No.</th>
                <th>Student</th>
                <th>Subject</th>
                <th>Total</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($summary_result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['roll_number']) ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= $row['total_classes'] ?></td>
                <td><?= $row['present_count'] ?></td>
                <td><?= $row['absent_count'] ?></td>
                <td>
                    <div class="pct-wrap">
                        <div class="pct-bar-bg">
                            <div class="pct-bar-fill <?= getPctClass($row['percentage']) ?>"
                                 style="width:<?= $row['percentage'] ?>%"></div>
                        </div>
                        <span class="pct-text"><?= $row['percentage'] ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align:center;padding:30px;color:#718096;">No attendance records found for the selected filter.</div>
    <?php endif; ?>
</div>

<!-- ===== INDIVIDUAL RECORDS ===== -->
<div class="content-card">
    <div class="card-header">
        <h2>Individual Attendance Records</h2>
        <a href="attendance_add.php" class="btn btn-primary btn-sm">+ Add Record</a>
    </div>
    <?php if (mysqli_num_rows($records_result) > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Roll No.</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($records_result)): ?>
            <tr>
                <td><?= $row['attendance_id'] ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['roll_number']) ?></td>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= date('d M Y', strtotime($row['attendance_date'])) ?></td>
                <td>
                    <?php if ($row['status'] === 'Present'): ?>
                        <span class="badge badge-present">Present</span>
                    <?php else: ?>
                        <span class="badge badge-absent">Absent</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="table-actions">
                        <a href="attendance_edit.php?id=<?= $row['attendance_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="attendance_delete.php?id=<?= $row['attendance_id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this attendance record?');">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align:center;padding:40px;color:#718096;">
        <div style="font-size:40px;margin-bottom:10px;">✅</div>
        <p>No attendance records found.</p>
        <a href="attendance_add.php" class="btn btn-primary" style="margin-top:14px;">Add First Record</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
