const mongoose = require('mongoose');

const eventSchema = new mongoose.Schema({
  name: String,
  date: Date,
  time: String,
  location: String,
  speaker: String,
  poster_url: String,
  registration_fee: Number,
  max_participants: Number,
  created_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  status: { type: String, enum: ['open', 'closed'], default: 'open' }
}, { timestamps: true });

module.exports = mongoose.model('Event', eventSchema);
