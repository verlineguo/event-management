const Event = require('../models/Event');
const Session = require('../models/Session');


// Get all events with sessions
exports.getAllEvents = async (req, res) => {
  try {
    const { q, category, status } = req.query;
    let query = {};

    // Jika ada query search, gunakan logika search
    if (q || category || status) {
      // Build search query
      if (q && q.trim() !== '') {
        query.$or = [
          { name: { $regex: q.trim(), $options: 'i' } },
          { description: { $regex: q.trim(), $options: 'i' } }
        ];
      }

      // Filter by category
      if (category && category.trim() !== '') {
        const mongoose = require('mongoose');
        if (mongoose.Types.ObjectId.isValid(category)) {
          query.category_id = new mongoose.Types.ObjectId(category);
        }
      }

      // Filter by status
      if (status && status.trim() !== '') {
        const validStatuses = ['open', 'closed', 'cancelled', 'completed'];
        if (validStatuses.includes(status)) {
          query.status = status;
        }
      }
    }

    console.log('Query:', JSON.stringify(query, null, 2));

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
    console.error('Error:', err);
    res.status(500).json({ message: err.message });
  }
};

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



// Create event with sessions
exports.createEvent = async (req, res) => {
  try {
   
    // Create event first
    const event = new Event({
      name: req.body.name,
      description: req.body.description,
      category_id: req.body.category_id,
      poster: req.body.poster,
      max_participants: req.body.max_participants,
      created_by: req.body.created_by,
      status: req.body.status || 'open',
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
          status: 'scheduled',
          session_fee: sessionData.session_fee || 0,
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
    });
  } catch (error) {
    res.status(400).json({ message: error.message });
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
            session_fee: sessionData.session_fee || 0,
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



// Scan QR Code (for event check-in)
exports.scanQRCode = async (req, res) => {
  try {
    const { qr_data } = req.body;

    let qrInfo;
    try {
      qrInfo = JSON.parse(qr_data);
    } catch (e) {
      return res.status(400).json({ message: 'QR Code tidak valid' });
    }

    const sessionRegistration = await SessionRegistration.findOne({
      registration_id: qrInfo.registration_id,
      session_id: qrInfo.session_id,
      user_id: qrInfo.user_id
    })
      .populate('registration_id', 'registration_id registration_status')
      .populate('session_id', 'title date start_time end_time')
      .populate('user_id', 'name email');

    if (!sessionRegistration) {
      return res.status(404).json({ message: 'QR Code tidak ditemukan' });
    }

    if (sessionRegistration.registration_id.registration_status !== 'confirmed') {
      return res.status(400).json({ message: 'Registrasi belum dikonfirmasi' });
    }

    if (sessionRegistration.qr_used) {
      return res.status(400).json({ 
        message: 'QR Code sudah digunakan',
        used_at: sessionRegistration.used_at
      });
    }

    // Mark QR as used
    sessionRegistration.qr_used = true;
    sessionRegistration.used_at = new Date();
    sessionRegistration.status = 'completed';
    await sessionRegistration.save();

    res.json({
      message: 'Check-in berhasil',
      participant: {
        name: sessionRegistration.user_id.name,
        email: sessionRegistration.user_id.email,
        registration_id: sessionRegistration.registration_id.registration_id,
        session: sessionRegistration.session_id,
        checked_in_at: sessionRegistration.used_at
      }
    });

  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};
