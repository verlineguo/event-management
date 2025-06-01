const mongoose = require('mongoose');

const sessionAttendanceSchema = new mongoose.Schema({
  session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session', required: true },
  session_registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'SessionRegistration', required: true },
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration', required: true },

  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  attended: { type: Boolean, default: true },
  check_in_time: { type: Date, default: Date.now },
  scanned_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true }, // Panitia yang scan
  qr_code_used: String, 
  attendance_method: { 
    type: String, 
    enum: ['qr_scan', 'manual'], 
    default: 'qr_scan' 
  },
}, { timestamps: true });

sessionAttendanceSchema.index({ session_id: 1, registration_id: 1 }, { unique: true });

module.exports = mongoose.model('SessionAttendance', sessionAttendanceSchema);
