const mongoose = require('mongoose');

const sessionRegistrationSchema = new mongoose.Schema({
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration', required: true },
  session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session', required: true },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  qr_code: { type: String, unique: true }, // QR code unik per sesi
  status: { 
    type: String, 
    enum: ['registered', 'cancelled', 'completed'], 
    default: 'registered' 
  },
  registered_at: { type: Date, default: Date.now },

}, { timestamps: true });

sessionRegistrationSchema.index({ registration_id: 1, session_id: 1 }, { unique: true });

module.exports = mongoose.model('SessionRegistration', sessionRegistrationSchema);