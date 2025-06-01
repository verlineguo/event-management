const mongoose = require('mongoose');

const sessionSchema = new mongoose.Schema({
  event_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Event', required: true },
  title: { type: String, required: true },
  description: { type: String },
  date: { type: Date, required: true }, 
  start_time: { type: String, required: true }, 
  end_time: { type: String, required: true }, 
  location: { type: String, required: true },
  speaker: { type: String, required: true },
  max_participants: { type: Number }, 
  session_order: { type: Number, default: 1 },
  status: { 
    type: String, 
    enum: ['scheduled', 'ongoing', 'completed', 'cancelled'], 
    default: 'scheduled' 
  }
}, { timestamps: true });

module.exports = mongoose.model('Session', sessionSchema);