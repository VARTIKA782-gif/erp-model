const mongoose = require('mongoose');

const SubjectSchema = new mongoose.Schema({
  subjectName: { type: String, required: true, trim: true },
  teacherId:   { type: mongoose.Schema.Types.ObjectId, ref: 'Teacher', default: null },
}, { timestamps: true });

module.exports = mongoose.model('Subject', SubjectSchema);
