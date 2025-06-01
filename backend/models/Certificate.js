const mongoose = require('mongoose');

const certificateSchema = new mongoose.Schema({
  session_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Session', required: true },
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration', required: true },
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  certificate_number: { type: String, unique: true }, // Auto generated
  file_url: { type: String, required: true }, // URL file sertifikat
  issued_date: { type: Date, default: Date.now },
  uploaded_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true }, // Panitia yang upload
  status: { 
    type: String, 
    enum: ['issued', 'revoked'], 
    default: 'issued' 
  },
  notes: String
}, { timestamps: true });

module.exports = mongoose.model('Certificate', certificateSchema);