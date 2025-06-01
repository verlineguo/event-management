const mongoose = require('mongoose');

const registrationSchema = new mongoose.Schema({
  event_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Event', required: true },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  registration_number: { type: String, unique: true }, // Auto generated
  payment_proof_url: String,
  payment_amount: Number,
  payment_status: { 
    type: String, 
    enum: ['pending', 'approved', 'rejected'], 
    default: 'pending' 
  },
  payment_verified_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  payment_verified_at: Date,
  rejection_reason: String, // Jika pembayaran ditolak
  registration_status: {
    type: String,
    enum: ['registered', 'confirmed', 'cancelled'],
    default: 'registered'
  }
}, { timestamps: true });

registrationSchema.index({ event_id: 1, user_id: 1 }, { unique: true });

module.exports = mongoose.model('Registration', registrationSchema);
