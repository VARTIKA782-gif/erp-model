const mongoose = require('mongoose');

const StudentSchema = new mongoose.Schema({
  studentName: { type: String, required: true, trim: true },
  rollNumber:  { type: String, required: true, unique: true, trim: true },
  course:      { type: String, required: true, trim: true },
  semester:    { type: Number, required: true, min: 1 },
}, { timestamps: true });

module.exports = mongoose.model('Student', StudentSchema);
