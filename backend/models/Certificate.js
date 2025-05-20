const mongoose = require('mongoose');

const certificateSchema = new mongoose.Schema({
  registration_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Registration' },
  event_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Event' },
  file_url: String,
  uploaded_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  uploaded_at: Date
}, { timestamps: true });

module.exports = mongoose.model('Certificate', certificateSchema);
