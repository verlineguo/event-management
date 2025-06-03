const mongoose = require('mongoose');

const registrationSchema = new mongoose.Schema({
  event_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Event', required: true },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  payment_proof_url: String,
  payment_amount: Number,
  payment_status: { 
    type: String, 
    enum: ['pending', 'approved', 'rejected'], 
    default: 'pending',
    required: true 
  },
  payment_verified_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User', default: null },
  payment_verified_at: { type: Date, default: null },
  rejection_reason: { type: String, default: null }, // Jika pembayaran ditolak
  registration_status: {
    type: String,
    enum: ['draft', 'registered', 'confirmed', 'cancelled'],
    default: 'draft',
    required: true
  }
}, { timestamps: true });

registrationSchema.index({ event_id: 1, user_id: 1 }, { unique: true });

module.exports = mongoose.model('Registration', registrationSchema);