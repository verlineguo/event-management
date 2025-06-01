const mongoose = require('mongoose');


const eventSchema = new mongoose.Schema({
  name: { type: String, required: true },
  description: { type: String },
  category_id: { type: mongoose.Schema.Types.ObjectId, ref: 'EventCategory', required: true },
  poster: { type: String, required: true },
  registration_fee: { type: Number, required: true },
  max_participants: { type: Number, required: true },
  created_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  status: { type: String, enum: ['open', 'closed', 'cancelled', 'completed'], default: 'open' },

}, { timestamps: true });

module.exports = mongoose.model('Event', eventSchema);

