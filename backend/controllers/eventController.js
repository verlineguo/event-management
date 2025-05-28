const Event = require('../models/Event');

// Get all events
exports.getAllEvents = async (req, res) => {
  try {
    const events = await Event.find().populate('created_by', 'name');
    res.json(events);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get one event
exports.getEventById = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id).populate('created_by', 'name');
    if (!event) return res.status(404).json({ message: 'Event not found' });
    res.json(event);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Create event
exports.createEvent = async (req, res) => {
  try {
    const event = new Event({
      name: req.body.name,
      date: req.body.date,
      time: req.body.time,
      location: req.body.location,
      speaker: req.body.speaker,
      poster: req.body.poster,
      registration_fee: req.body.registration_fee,
      max_participants: req.body.max_participants,
      created_by: req.body.created_by,
      status: req.body.status ?? 'open'
    });

    const newEvent = await event.save();
    res.status(201).json(newEvent);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

// Update event
exports.updateEvent = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id);
    if (!event) return res.status(404).json({ message: 'Event not found' });

    if (req.body.name) event.name = req.body.name;
    if (req.body.date) event.date = req.body.date;
    if (req.body.time) event.time = req.body.time;
    if (req.body.location) event.location = req.body.location;
    if (req.body.speaker) event.speaker = req.body.speaker;
    if (req.body.poster) event.poster = req.body.poster;
    if (req.body.registration_fee !== undefined) event.registration_fee = req.body.registration_fee;
    if (req.body.max_participants !== undefined) event.max_participants = req.body.max_participants;
    if (req.body.created_by) event.created_by = req.body.created_by;
    if (req.body.status) event.status = req.body.status;

    const updatedEvent = await event.save();
    res.json(updatedEvent);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Delete event
exports.deleteEvent = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id);
    if (!event) return res.status(404).json({ message: 'Event not found' });

    await Event.deleteOne({ _id: req.params.id });
    res.json({ message: 'Event deleted' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};
