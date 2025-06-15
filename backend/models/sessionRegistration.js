const mongoose = require('mongoose');

const sessionRegistrationSchema = new mongoose.Schema({
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration', required: true },
  session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session', required: true },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  qr_token: { type: String, sparse: true }, // Unique token for QR code scanning
  qr_code: { type: String, sparse: true }, // Base64 QR code image data
  status: { 
    type: String, 
    enum: ['registered', 'cancelled', 'completed'], 
    default: 'registered' 
  },
  registered_at: { type: Date, default: Date.now },
  qr_used: { type: Boolean },
  used_at: { type: Date },

}, { timestamps: true });

sessionRegistrationSchema.index({ registration_id: 1, session_id: 1 }, { unique: true });
sessionRegistrationSchema.index({ qr_token: 1 }, { unique: true, sparse: true });


module.exports = mongoose.model('SessionRegistration', sessionRegistrationSchema);