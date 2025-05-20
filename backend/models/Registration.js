const mongoose = require('mongoose');

const registrationSchema = new mongoose.Schema({
  event_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Event' },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  payment_proof_url: String,
  payment_status: { type: String, enum: ['pending', 'approved', 'rejected'], default: 'pending' },
  qr_code: String
}, { timestamps: true });

module.exports = mongoose.model('Registration', registrationSchema);
