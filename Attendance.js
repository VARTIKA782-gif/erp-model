const mongoose = require('mongoose');

const AttendanceSchema = new mongoose.Schema({
  studentId:      { type: mongoose.Schema.Types.ObjectId, ref: 'Student', required: true },
  subjectId:      { type: mongoose.Schema.Types.ObjectId, ref: 'Subject', required: true },
  attendanceDate: { type: Date, required: true },
  status:         { type: String, enum: ['Present', 'Absent'], default: 'Present' },
}, { timestamps: true });

module.exports = mongoose.model('Attendance', AttendanceSchema);
