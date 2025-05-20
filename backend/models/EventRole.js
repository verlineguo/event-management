const mongoose = require('mongoose');

const eventRoleSchema = new mongoose.Schema({
  user_id: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  event_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Event' },
  role: { type: String, enum: ['committee', 'finance'] }
}, { timestamps: true });


module.exports = mongoose.model('EventRole', eventRoleSchema);
