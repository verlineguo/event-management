const Event = require('../models/Event');
const Session = require('../models/Session');
const { v4: uuidv4 } = require('uuid');
const QRCode = require('qrcode');

// Get all events with sessions
exports.getAllEvents = async (req, res) => {
  try {
    // First get all events
    const events = await Event.find()
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color')
      .sort({ createdAt: -1 });
    
    // Then get sessions for each event
    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessions = await Session.find({ event_id: event._id })
          .sort({ session_order: 1, date: 1 });
        
        return {
          ...event.toObject(),
          sessions: sessions
        };
      })
    );
    
    res.json(eventsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Alternative: Get all sessions with event details (recommended approach)
exports.getAllSessionsWithEvents = async (req, res) => {
  try {
    const sessions = await Session.find()
      .populate({
        path: 'event_id',
        populate: [
          { path: 'created_by', select: 'name email' },
          { path: 'category_id', select: 'name description color' }
        ]
      })
      .sort({ date: -1, session_order: 1 });
    
    res.json(sessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get one event with sessions
exports.getEventById = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id)
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color');
    
    if (!event) return res.status(404).json({ message: 'Event not found' });
    
    // Get sessions for this event
    const sessions = await Session.find({ event_id: req.params.id })
      .sort({ session_order: 1, date: 1 });
    
    res.json({
      ...event.toObject(),
      sessions: sessions
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get sessions by event ID
exports.getEventSessions = async (req, res) => {
  try {
    const sessions = await Session.find({ event_id: req.params.id })
      .populate('event_id', 'name status')
      .sort({ session_order: 1, date: 1 });
    
    if (sessions.length === 0) {
      return res.status(404).json({ message: 'No sessions found for this event' });
    }
    
    res.json(sessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Create event with sessions
exports.createEvent = async (req, res) => {
  try {
    // Generate unique QR code
    const qrCodeData = `EVENT_${uuidv4()}`;
    const qrCodeUrl = await QRCode.toDataURL(qrCodeData);
    
    // Create event first
    const event = new Event({
      name: req.body.name,
      description: req.body.description,
      category_id: req.body.category_id,
      poster: req.body.poster,
      registration_fee: req.body.registration_fee || 0,
      max_participants: req.body.max_participants,
      created_by: req.body.created_by,
      status: req.body.status || 'open',
      qr_code: qrCodeData
    });

    const savedEvent = await event.save();

    // Create sessions if provided
    let savedSessions = [];
    if (req.body.sessions && req.body.sessions.length > 0) {
      const sessionPromises = req.body.sessions.map((sessionData, index) => {
        const session = new Session({
          event_id: savedEvent._id,
          title: sessionData.title,
          description: sessionData.description,
          date: sessionData.date,
          start_time: sessionData.start_time,
          end_time: sessionData.end_time,
          location: sessionData.location,
          speaker: sessionData.speaker,
          max_participants: sessionData.max_participants || savedEvent.max_participants,
          session_order: index + 1,
          status: 'scheduled'
        });
        return session.save();
      });

      savedSessions = await Promise.all(sessionPromises);
    }

    // Return the created event with sessions
    const eventWithDetails = await Event.findById(savedEvent._id)
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color');

    res.status(201).json({
      ...eventWithDetails.toObject(),
      sessions: savedSessions,
      qr_code_url: qrCodeUrl
    });
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

// Search events with sessions
exports.searchEvents = async (req, res) => {
  try {
    const { q, category, status } = req.query;
    let query = {};

    if (q) {
      query.$or = [
        { name: { $regex: q, $options: 'i' } },
        { description: { $regex: q, $options: 'i' } }
      ];
    }

    if (category) {
      query.category_id = category;
    }

    if (status) {
      query.status = status;
    }

    const events = await Event.find(query)
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color')
      .sort({ createdAt: -1 });

    // Add sessions to each event
    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessions = await Session.find({ event_id: event._id })
          .sort({ session_order: 1, date: 1 });
        
        return {
          ...event.toObject(),
          sessions: sessions
        };
      })
    );

    res.json(eventsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Update event and sessions
exports.updateEvent = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id);
    if (!event) return res.status(404).json({ message: 'Event not found' });

    // Update event fields
    if (req.body.name) event.name = req.body.name;
    if (req.body.description !== undefined) event.description = req.body.description;
    if (req.body.category_id) event.category_id = req.body.category_id;
    if (req.body.poster) event.poster = req.body.poster;
    if (req.body.registration_fee !== undefined) event.registration_fee = req.body.registration_fee;
    if (req.body.max_participants !== undefined) event.max_participants = req.body.max_participants;
    if (req.body.status) event.status = req.body.status;

    const updatedEvent = await event.save();

    // Update sessions if provided
    if (req.body.sessions) {
      // Delete existing sessions for this event
      await Session.deleteMany({ event_id: req.params.id });
      
      // Create new sessions
      if (req.body.sessions.length > 0) {
        const sessionPromises = req.body.sessions.map((sessionData, index) => {
          const session = new Session({
            event_id: req.params.id,
            title: sessionData.title,
            description: sessionData.description,
            date: sessionData.date,
            start_time: sessionData.start_time,
            end_time: sessionData.end_time,
            location: sessionData.location,
            speaker: sessionData.speaker,
            max_participants: sessionData.max_participants || updatedEvent.max_participants,
            session_order: index + 1,
            status: sessionData.status || 'scheduled'
          });
          return session.save();
        });

        await Promise.all(sessionPromises);
      }
    }

    // Return updated event with sessions
    const eventWithDetails = await Event.findById(req.params.id)
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color');
    
    const sessions = await Session.find({ event_id: req.params.id })
      .sort({ session_order: 1, date: 1 });

    res.json({
      ...eventWithDetails.toObject(),
      sessions: sessions
    });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Delete event and related data
exports.deleteEvent = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id);
    if (!event) return res.status(404).json({ message: 'Event not found' });

    // Get session IDs for cleanup
    const sessions = await Session.find({ event_id: req.params.id });
    const sessionIds = sessions.map(session => session._id);

    // Delete related sessions first
    await Session.deleteMany({ event_id: req.params.id });
    
    // You might want to delete related data too
    // const Registration = require('../models/Registration');
    // const SessionAttendance = require('../models/SessionAttendance');
    // const Certificate = require('../models/Certificate');
    // const QRCodeLog = require('../models/QRCodeLog');
    
    // await Registration.deleteMany({ event_id: req.params.id });
    // await SessionAttendance.deleteMany({ session_id: { $in: sessionIds } });
    // await Certificate.deleteMany({ session_id: { $in: sessionIds } });
    // await QRCodeLog.deleteMany({ 
    //   $or: [
    //     { event_id: req.params.id },
    //     { session_id: { $in: sessionIds } }
    //   ]
    // });

    // Delete the event
    await Event.deleteOne({ _id: req.params.id });
    
    res.json({ message: 'Event and related data deleted successfully' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get events by category with sessions
exports.getEventsByCategory = async (req, res) => {
  try {
    const events = await Event.find({ category_id: req.params.categoryId })
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color')
      .sort({ createdAt: -1 });
    
    // Add sessions to each event
    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessions = await Session.find({ event_id: event._id })
          .sort({ session_order: 1, date: 1 });
        
        return {
          ...event.toObject(),
          sessions: sessions
        };
      })
    );
    
    res.json(eventsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get events by status with sessions
exports.getEventsByStatus = async (req, res) => {
  try {
    const { status } = req.params;
    const validStatuses = ['open', 'closed', 'cancelled', 'completed'];
    
    if (!validStatuses.includes(status)) {
      return res.status(400).json({ message: 'Invalid status' });
    }

    const events = await Event.find({ status })
      .populate('created_by', 'name email')
      .populate('category_id', 'name description color')
      .sort({ createdAt: -1 });
    
    // Add sessions to each event
    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessions = await Session.find({ event_id: event._id })
          .sort({ session_order: 1, date: 1 });
        
        return {
          ...event.toObject(),
          sessions: sessions
        };
      })
    );
    
    res.json(eventsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Update event status
exports.updateEventStatus = async (req, res) => {
  try {
    const { status } = req.body;
    const validStatuses = ['open', 'closed', 'cancelled', 'completed'];
    
    if (!validStatuses.includes(status)) {
      return res.status(400).json({ message: 'Invalid status' });
    }

    const event = await Event.findByIdAndUpdate(
      req.params.id,
      { status },
      { new: true }
    ).populate('created_by', 'name email')
     .populate('category_id', 'name description color');

    if (!event) return res.status(404).json({ message: 'Event not found' });

    res.json({ success: true, event });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Get event QR code
exports.getEventQRCode = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id);
    if (!event) return res.status(404).json({ message: 'Event not found' });

    const qrCodeUrl = await QRCode.toDataURL(event.qr_code);
    res.json({ qr_code: event.qr_code, qr_code_url: qrCodeUrl });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};