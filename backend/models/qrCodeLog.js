const qrCodeLogSchema = new mongoose.Schema({
  qr_code: { type: String, required: true },
  session_registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'SessionRegistration' },
  session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session' },
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration' },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  
  scanned_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  scan_time: { type: Date, default: Date.now },
  scan_result: { 
    type: String, 
    enum: ['success', 'duplicate', 'invalid', 'expired', 'wrong_session', 'not_registered'], 
    required: true 
  },
}, { timestamps: true });

module.exports = {
  QRCodeLog: mongoose.model('QRCodeLog', qrCodeLogSchema)
};