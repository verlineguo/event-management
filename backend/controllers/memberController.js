const Event = require('../models/Event');
const Session = require('../models/Session');
const Registration = require('../models/Registration');
const SessionRegistration = require('../models/sessionRegistration');


exports.getFeaturedEvents = async (req, res) => {
  try {
    const events = await Event.find({ status: 'open' })
      .populate('created_by', 'name email')
      .populate('category_id', 'name')
      .sort({ createdAt: -1 })
      .limit(6);

    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessions = await Session.find({ event_id: event._id })
          .sort({ session_order: 1, date: 1 });

        // Process sessions with availability info
        const sessionsWithAvailability = await Promise.all(
          sessions.map(async (session) => {
            const registeredCount = await SessionRegistration.countDocuments({
              session_id: session._id,
              status: { $in: ['registered', 'completed'] }
            });

            const availableSlots = session.max_participants || 0;
            const isAvailable = availableSlots > 0;

            return {
              _id: session._id,
              title: session.title,
              date: session.date,
              start_time: session.start_time,
              end_time: session.end_time,
              location: session.location,
              speaker: session.speaker,
              session_fee: session.session_fee,
              available_slots: availableSlots,
              is_available: isAvailable,
              session_order: session.session_order
            };
          })
        );

        // Check if event has any available sessions
        const hasAvailableSessions = sessionsWithAvailability.some(session => session.is_available);
        
        // Get earliest and latest session dates for display
        const sessionDates = sessionsWithAvailability
          .map(s => s.date)
          .filter(date => date)
          .sort();
        
        const firstDate = sessionDates.length > 0 ? sessionDates[0] : null;
        const lastDate = sessionDates.length > 0 ? sessionDates[sessionDates.length - 1] : null;

        return {
          _id: event._id,
          name: event.name,
          description: event.description,
          poster: event.poster,
          category: event.category_id?.name || 'General',
          status: event.status,
          sessions: sessionsWithAvailability,
          sessions_count: sessionsWithAvailability.length,
          has_available_sessions: hasAvailableSessions,
          first_session_date: firstDate,
          last_session_date: lastDate,
          min_fee: Math.min(...sessionsWithAvailability.map(s => s.session_fee)),
          created_at: event.createdAt
        };
      })
    );

    res.json(eventsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};


// Search events with filters
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
    } else {
      query.status = { $ne: 'cancelled' }; // Exclude cancelled events by default
    }

    const events = await Event.find(query)
      .populate('created_by', 'name email')
      .populate('category_id', 'name')
      .sort({ createdAt: -1 });

    const eventsWithSessions = await Promise.all(
      events.map(async (event) => {
        const sessions = await Session.find({ event_id: event._id })
          .sort({ session_order: 1, date: 1 });
        
        const registrationCount = await Registration.countDocuments({ 
          event_id: event._id, 
          registration_status: { $in: ['registered', 'confirmed'] } 
        });
        
        return {
          ...event.toObject(),
          sessions: sessions,
          registered_count: registrationCount,
          available_slots: Math.max(0, event.max_participants - registrationCount)
        };
      })
    );

    res.json(eventsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get single event by ID
exports.getEventById = async (req, res) => {
  try {
    const event = await Event.findById(req.params.id)
      .populate('created_by', 'name email')
      .populate('category_id', 'name');
    
    if (!event) return res.status(404).json({ message: 'Event not found' });
    
    const sessions = await Session.find({ event_id: req.params.id })
      .sort({ session_order: 1, date: 1 });
    
    const registrationCount = await Registration.countDocuments({ 
      event_id: req.params.id, 
      registration_status: { $in: ['registered', 'confirmed'] } 
    });
    
    res.json({
      ...event.toObject(),
      sessions: sessions,
      registered_count: registrationCount,
      available_slots: Math.max(0, event.max_participants - registrationCount)
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};


exports.getEventDetail = async (req, res) => {
  try {
    const eventId = req.params.id;
    
    // Get event details
    const event = await Event.findById(eventId)
      .populate('category_id', 'name')
      .populate('created_by', 'name');
    
    if (!event) {
      return res.status(404).json({ message: 'Event tidak ditemukan' });
    }
    
    // Get all sessions for this event
    const sessions = await Session.find({ event_id: eventId })
      .sort({ session_order: 1, date: 1 });
    
    // Count total registrations for this event (event level)
    const eventRegistrationCount = await Registration.countDocuments({
      event_id: eventId,
      registration_status: { $in: ['registered', 'confirmed'] }
    });
    
    // Calculate available slots for each session
    const sessionsWithAvailability = await Promise.all(
      sessions.map(async (session) => {
        // Count registrations for this specific session
        const sessionRegistrationCount = await Registration.countDocuments({
          event_id: eventId,
          selected_sessions: session._id,
          registration_status: { $in: ['registered', 'confirmed'] }
        });
        
        // Calculate available slots for this session
        const sessionAvailableSlots = Math.max(0, session.max_participants - sessionRegistrationCount);
        
        return {
          ...session.toObject(),
          registered_count: sessionRegistrationCount,
          available_slots: sessionAvailableSlots,
          is_full: sessionAvailableSlots <= 0
        };
      })
    );
    
    // Calculate event-level available slots
    const eventAvailableSlots = Math.max(0, event.max_participants - eventRegistrationCount);
    
    res.json({
      ...event.toObject(),
      sessions: sessionsWithAvailability,
      registered_count: eventRegistrationCount,
      available_slots: eventAvailableSlots,
      is_full: eventAvailableSlots <= 0
    });
    
  } catch (err) {
    res.status(500).json({ message: 'Terjadi kesalahan: ' + err.message });
  }
};



exports.getEventRegistration = async (req, res) => {
  try {
    const { id } = req.params; // event_id
    const userId = req.user._id;

    const registration = await Registration.findOne({
      event_id: id,
      user_id: userId,
      registration_status: { $in: ['registered', 'confirmed', 'pending', 'draft'] }
    })
    .populate('event_id', 'name description max_participants')
    .populate('user_id', 'name email');

    if (!registration) {
      return res.json(null);
    }

    // Get session registration details
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title date start_time end_time location speaker session_fee');

    res.json({
      ...registration.toObject(),
      session_registrations: sessionRegistrations
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};
