const mongoose = require('mongoose');

const TeacherSchema = new mongoose.Schema({
  teacherName:   { type: String, required: true, trim: true },
  qualification: { type: String, required: true, trim: true },
  experience:    { type: String, default: '5 Years', trim: true },
  subjectTaught: { type: String, required: true, trim: true },
}, { timestamps: true });

module.exports = mongoose.model('Teacher', TeacherSchema);
