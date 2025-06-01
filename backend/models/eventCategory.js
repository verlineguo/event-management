const mongoose = require('mongoose');


const eventCategorySchema = new mongoose.Schema({
  name: { type: String, required: true }, // e.g., "Seminar", "Workshop", "Webinar", "Conference"
  status: { type: Boolean, required: true }

}, { timestamps: true });

module.exports = mongoose.model('EventCategory', eventCategorySchema);
