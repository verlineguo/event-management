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

        // Count total event registrations
        const eventRegistrationCount = await Registration.countDocuments({
          event_id: event._id,
          registration_status: { $in: ['registered', 'confirmed'] }
        });

        // Get session-specific registration counts
        const sessionsWithQuota = await Promise.all(
          sessions.map(async (session) => {
            const sessionRegistrationCount = await SessionRegistration.countDocuments({
              session_id: session._id,
              status: { $in: ['registered', 'completed'] }
            });

            // Use session max_participants if available, otherwise use event max_participants
            const sessionMaxParticipants = session.max_participants || event.max_participants;
            
            // PERBAIKAN: available_slots = kapasitas - yang sudah daftar
            const sessionAvailableSlots = Math.max(0, sessionMaxParticipants - sessionRegistrationCount);
            
            // PERBAIKAN: quota_percentage = persentase TERISI (bukan tersedia)
            const quotaPercentage = sessionMaxParticipants > 0 
              ? Math.round((sessionRegistrationCount / sessionMaxParticipants) * 100) 
              : 0;

            return {
              ...session.toObject(),
              registered_count: sessionRegistrationCount, // Yang sudah daftar
              max_participants: sessionMaxParticipants,   // Kapasitas maksimum
              available_slots: sessionAvailableSlots,     // Sisa slot tersedia
              quota_percentage: quotaPercentage,          // Persentase terisi
              is_full: sessionAvailableSlots <= 0,        // Penuh jika sisa slot = 0
              is_almost_full: quotaPercentage >= 80       // Hampir penuh jika 80% terisi
            };
          })
        );

        // Calculate overall event availability
        const totalEventSlots = event.max_participants; // Kapasitas total event
        
        // PERBAIKAN: available_slots = kapasitas - yang sudah daftar
        const eventAvailableSlots = Math.max(0, totalEventSlots - eventRegistrationCount);
        
        // PERBAIKAN: quota_percentage = persentase TERISI
        const eventQuotaPercentage = totalEventSlots > 0 
          ? Math.round((eventRegistrationCount / totalEventSlots) * 100) 
          : 0;

        // Check if any session is still available
        const hasAvailableSessions = sessionsWithQuota.some(session => session.available_slots > 0);

        // Calculate average session occupancy for display
        const avgSessionOccupancy = sessionsWithQuota.length > 0 
          ? Math.round(sessionsWithQuota.reduce((sum, session) => sum + session.quota_percentage, 0) / sessionsWithQuota.length) 
          : 0;

        return {
          ...event.toObject(),
          sessions: sessionsWithQuota,
          
          // Event quota information (DIPERBAIKI)
          registered_count: eventRegistrationCount,    // Total yang sudah daftar
          max_participants: totalEventSlots,           // Kapasitas maksimum
          available_slots: eventAvailableSlots,        // Sisa slot tersedia
          quota_percentage: eventQuotaPercentage,      // Persentase terisi
          is_full: eventAvailableSlots <= 0,           // Event penuh
          is_almost_full: eventQuotaPercentage >= 80,  // Event hampir penuh
          
          has_available_sessions: hasAvailableSessions,
          avg_session_occupancy: avgSessionOccupancy,
          sessions_count: sessionsWithQuota.length
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