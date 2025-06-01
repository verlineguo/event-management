 const mongoose = require('mongoose');
 
 const sessionAttendanceSchema = new mongoose.Schema({
  session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session', required: true },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration', required: true },
  attended: { type: Boolean, default: false },
  check_in_time: Date,
  check_out_time: Date,
  feedback_rating: { type: Number, min: 1, max: 5 },
  feedback_comment: String
}, { timestamps: true });

module.exports = mongoose.model('SessionAttendance', sessionAttendanceSchema);