 const mongoose = require('mongoose');
 
 const attendanceSchema = new mongoose.Schema({
    session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session', required: true },
    session_registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'SessionRegistration', required: true },  
    user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    attended: { type: Boolean, default: true },
    check_in_time: { type: Date, default: Date.now },
    scanned_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true }, 
    qr_code_used: String, 
    attendance_method: { 
      type: String, 
      enum: ['qr_scan', 'manual'], 
      default: 'qr_scan' 
    },
  }, { timestamps: true });
  
attendanceSchema.index({ session_id: 1, session_registration_id: 1 }, { unique: true });

module.exports = mongoose.model('Attendance', attendanceSchema);