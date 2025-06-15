// controllers/qrController.js
const SessionRegistration = require('../models/sessionRegistration');
const Registration = require('../models/Registration');
const Session = require('../models/Session');
const Event = require('../models/Event');
const Attendance = require('../models/Attendance');
const Certificate = require('../models/Certificate');

// Scan QR Code for attendance
exports.scanQRCode = async (req, res) => {
  try {
    const { qr_token, scanned_by } = req.body;
    
    console.log('=== QR SCAN DEBUG ===');
    console.log('Received qr_token:', qr_token);
    console.log('Received scanned_by:', scanned_by);
    console.log('Token type:', typeof qr_token);
    console.log('Token length:', qr_token ? qr_token.length : 'null');

    if (!qr_token || !scanned_by) {
      return res.status(400).json({ 
        message: 'QR token dan ID scanner diperlukan' 
      });
    }

    // First, let's see if ANY SessionRegistration exists with this exact token
    console.log('Searching for token in database...');
    const directSearch = await SessionRegistration.findOne({ qr_token: qr_token });
    console.log('Direct search result:', directSearch ? 'FOUND' : 'NOT FOUND');
    
    if (!directSearch) {
      // Let's see what tokens actually exist
      const allTokens = await SessionRegistration.find({})
        .select('qr_token user_id session_id')
        .limit(10);
      
      console.log('Sample existing tokens:', allTokens.map(t => ({
        id: t._id,
        token: t.qr_token,
        user_id: t.user_id,
        session_id: t.session_id
      })));
      
      return res.status(404).json({ 
        message: 'QR Code tidak valid atau tidak ditemukan',
        debug_info: {
          searched_token: qr_token,
          sample_existing_tokens: allTokens.map(t => t.qr_token).filter(t => t)
        }
      });
    }

    // Continue with normal flow if token is found
    const sessionRegistration = await SessionRegistration.findOne({
      qr_token: qr_token
    })
    .populate({
      path: 'registration_id',
      select: 'registration_status payment_status',
      populate: {
        path: 'event_id',
        select: 'name status'
      }
    })
    .populate({
      path: 'session_id',
      select: 'title date start_time end_time location speaker status'
    })
    .populate({
      path: 'user_id',
      select: 'name email phone'
    });

    console.log('Populated session registration found:', !!sessionRegistration);

    // Rest of your validation logic...
    if (!sessionRegistration.registration_id) {
      return res.status(400).json({ 
        message: 'Data registrasi tidak lengkap' 
      });
    }

    // Check if registration is confirmed
    if (sessionRegistration.registration_id.registration_status !== 'confirmed') {
      return res.status(400).json({ 
        message: 'Registrasi belum dikonfirmasi' 
      });
    }

    // Check if payment is approved
    if (sessionRegistration.registration_id.payment_status !== 'approved') {
      return res.status(400).json({ 
        message: 'Pembayaran belum disetujui' 
      });
    }

    // Check if event is still active
    if (sessionRegistration.registration_id.event_id.status === 'cancelled') {
      return res.status(400).json({ 
        message: 'Event telah dibatalkan' 
      });
    }

    // Check if session is available for check-in
    if (sessionRegistration.session_id.status === 'cancelled') {
      return res.status(400).json({ 
        message: 'Sesi telah dibatalkan' 
      });
    }

    if (sessionRegistration.session_id.status === 'completed') {
      return res.status(400).json({ 
        message: 'Sesi telah selesai' 
      });
    }

    // Check if QR code has already been used
    const existingAttendance = await Attendance.findOne({
      session_id: sessionRegistration.session_id._id,
      session_registration_id: sessionRegistration._id,
      user_id: sessionRegistration.user_id._id
    });

    if (existingAttendance) {
      return res.status(400).json({ 
        message: 'QR Code sudah digunakan untuk check-in',
        used_at: existingAttendance.check_in_time,
        scanned_by: existingAttendance.scanned_by
      });
    }

    // Create attendance record
    const attendance = new Attendance({
      session_id: sessionRegistration.session_id._id,
      session_registration_id: sessionRegistration._id,
      user_id: sessionRegistration.user_id._id,
      attended: true,
      check_in_time: new Date(),
      scanned_by: scanned_by,
      qr_code_used: qr_token,
      attendance_method: 'qr_scan'
    });

    await attendance.save();

    // Update session registration status
    sessionRegistration.qr_used = true;
    sessionRegistration.status = 'completed';
    await sessionRegistration.save();

    console.log('Check-in successful for user:', sessionRegistration.user_id.name);

    // Return success response
    res.json({
      message: 'Check-in berhasil',
      participant: {
        name: sessionRegistration.user_id.name,
        email: sessionRegistration.user_id.email,
        phone: sessionRegistration.user_id.phone,
        registration_id: sessionRegistration.registration_id._id,
        session: sessionRegistration.session_id,
        event: sessionRegistration.registration_id.event_id,
        checked_in_at: attendance.check_in_time,
        attendance_id: attendance._id
      }
    });

  } catch (err) {
    console.error('Scan QR Error:', err);
    res.status(500).json({ message: err.message });
  }
};

// Manual check-in (for backup)
exports.manualCheckIn = async (req, res) => {
  try {
    const { session_id, user_id, scanned_by } = req.body;

    // Find session registration
    const sessionRegistration = await SessionRegistration.findOne({
      session_id: session_id,
      user_id: user_id
    })
    .populate('registration_id', 'registration_status payment_status')
    .populate('session_id', 'title date start_time end_time')
    .populate('user_id', 'name email');

    if (!sessionRegistration) {
      return res.status(404).json({ 
        message: 'Registrasi sesi tidak ditemukan' 
      });
    }

    // Similar validation as QR scan
    if (sessionRegistration.registration_id.registration_status !== 'confirmed') {
      return res.status(400).json({ 
        message: 'Registrasi belum dikonfirmasi' 
      });
    }

    if (sessionRegistration.registration_id.payment_status !== 'approved') {
      return res.status(400).json({ 
        message: 'Pembayaran belum disetujui' 
      });
    }

    // Check existing attendance
    const existingAttendance = await Attendance.findOne({
      session_id: session_id,
      user_id: user_id
    });

    if (existingAttendance) {
      return res.status(400).json({ 
        message: 'Peserta sudah check-in',
        checked_in_at: existingAttendance.check_in_time
      });
    }

    // Create attendance record
    const attendance = new Attendance({
      session_id: session_id,
      session_registration_id: sessionRegistration._id,
      user_id: user_id,
      attended: true,
      check_in_time: new Date(),
      scanned_by: scanned_by,
      attendance_method: 'manual'
    });

    await attendance.save();

    // Update session registration
    sessionRegistration.status = 'completed';
    await sessionRegistration.save();

    res.json({
      message: 'Manual check-in berhasil',
      participant: {
        name: sessionRegistration.user_id.name,
        email: sessionRegistration.user_id.email,
        session: sessionRegistration.session_id,
        checked_in_at: attendance.check_in_time,
        attendance_id: attendance._id
      }
    });

  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Manual check-in (for backup)
exports.manualCheckIn = async (req, res) => {
  try {
    const { session_id, user_id, scanned_by } = req.body;

    // Find session registration
    const sessionRegistration = await SessionRegistration.findOne({
      session_id: session_id,
      user_id: user_id
    })
    .populate('registration_id', 'registration_status payment_status')
    .populate('session_id', 'title date start_time end_time')
    .populate('user_id', 'name email');

    if (!sessionRegistration) {
      return res.status(404).json({ 
        message: 'Registrasi sesi tidak ditemukan' 
      });
    }

    // Similar validation as QR scan
    if (sessionRegistration.registration_id.registration_status !== 'confirmed') {
      return res.status(400).json({ 
        message: 'Registrasi belum dikonfirmasi' 
      });
    }

    if (sessionRegistration.registration_id.payment_status !== 'approved') {
      return res.status(400).json({ 
        message: 'Pembayaran belum disetujui' 
      });
    }

    // Check existing attendance
    const existingAttendance = await Attendance.findOne({
      session_id: session_id,
      user_id: user_id
    });

    if (existingAttendance) {
      return res.status(400).json({ 
        message: 'Peserta sudah check-in',
        checked_in_at: existingAttendance.check_in_time
      });
    }

    // Create attendance record
    const attendance = new Attendance({
      session_id: session_id,
      session_registration_id: sessionRegistration._id,
      user_id: user_id,
      attended: true,
      check_in_time: new Date(),
      scanned_by: scanned_by,
      attendance_method: 'manual'
    });

    await attendance.save();

    // Update session registration
    sessionRegistration.status = 'completed';
    await sessionRegistration.save();

    res.json({
      message: 'Manual check-in berhasil',
      participant: {
        name: sessionRegistration.user_id.name,
        email: sessionRegistration.user_id.email,
        session: sessionRegistration.session_id,
        checked_in_at: attendance.check_in_time,
        attendance_id: attendance._id
      }
    });

  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get attendance list for a session
exports.getSessionAttendance = async (req, res) => {
  try {
    const { sessionId } = req.params;

    // Ambil data attendance dengan populate lengkap
    const attendances = await Attendance.find({ session_id: sessionId })
      .populate('user_id', 'name email phone')
      .populate('scanned_by', 'name')
      .populate({
        path: 'session_registration_id',
        populate: {
          path: 'registration_id',
          select: 'registration_status payment_status event_id user_id'
        }
      })
      .sort({ check_in_time: -1 });

    // Ambil data session
    const session = await Session.findById(sessionId)
      .populate('event_id', 'name');

    // Ambil data certificate untuk setiap attendance
    const attendancesWithCertificates = await Promise.all(
      attendances.map(async (attendance) => {
        // Cari certificate berdasarkan session_id dan user_id
        const certificate = await Certificate.findOne({
          session_id: sessionId,
          user_id: attendance.user_id._id,
          status: 'issued'
        }).select('file_url certificate_number issued_date');

        // Convert ke object biasa dan tambahkan data certificate
        const attendanceObj = attendance.toObject();
        
        if (certificate) {
          attendanceObj.certificate_path = certificate.file_url;
          attendanceObj.certificate_number = certificate.certificate_number;
          attendanceObj.certificate_issued_date = certificate.issued_date;
        } else {
          attendanceObj.certificate_path = null;
          attendanceObj.certificate_number = null;
          attendanceObj.certificate_issued_date = null;
        }

        return attendanceObj;
      })
    );

    res.json({
      session: session,
      attendances: attendancesWithCertificates,
      total_attendees: attendances.length
    });

  } catch (err) {
    console.error('Error getting session attendance:', err);
    res.status(500).json({ message: err.message });
  }
};



// Get all participants for an event
exports.getEventParticipants = async (req, res) => {
  try {
    const { eventId } = req.params;
    
    // Basic validation
    if (!eventId) {
      return res.status(400).json({ 
        message: 'Event ID is required',
        event: null,
        participants: [],
        total_participants: 0
      });
    }

    // Find event with populated fields
    const event = await Event.findById(eventId)
      .populate('category_id', 'name')
      .populate('created_by', 'name');

    if (!event) {
      return res.status(404).json({ 
        message: 'Event not found',
        event: null,
        participants: [],
        total_participants: 0
      });
    }

    // Get event sessions
    const eventSessions = await Session.find({
      event_id: event._id
    }).sort({ session_order: 1, date: 1 });

    // Get registrations with populated user data
    const registrations = await Registration.find({
      event_id: event._id
    })
    .populate('user_id', 'name email phone profile')
    .populate('payment_verified_by', 'name')
    .sort({ createdAt: -1 });

    // Get participants with session data
    const participantsWithSessions = [];
    
    for (const registration of registrations) {
      try {
        const sessionRegistrations = await SessionRegistration.find({
          registration_id: registration._id
        })
        .populate('session_id', 'title date start_time end_time location')
        .sort({ 'session_id.date': 1 });

        const sessionsWithAttendance = [];
        
        for (const sessionReg of sessionRegistrations) {
          try {
            const attendance = await Attendance.findOne({
              session_registration_id: sessionReg._id
            });

            sessionsWithAttendance.push({
              ...sessionReg.toObject(),
              attendance: attendance ? {
                attended: attendance.attended,
                check_in_time: attendance.check_in_time,
                attendance_method: attendance.attendance_method
              } : null
            });
          } catch (attendanceError) {
            console.log('Attendance error for session:', sessionReg._id, attendanceError.message);
            sessionsWithAttendance.push({
              ...sessionReg.toObject(),
              attendance: null
            });
          }
        }

        // Transform the data
        const participantData = {
          _id: registration._id,
          user_id: registration.user_id,
          event_id: registration.event_id,
          payment_proof_url: registration.payment_proof_url,
          payment_amount: registration.payment_amount,
          payment_status: registration.payment_status,
          payment_verified_by: registration.payment_verified_by,
          payment_verified_at: registration.payment_verified_at,
          rejection_reason: registration.rejection_reason,
          registration_status: registration.registration_status,
          createdAt: registration.createdAt,
          updatedAt: registration.updatedAt,
          session_registrations: sessionsWithAttendance
        };

        participantsWithSessions.push(participantData);
      } catch (sessionError) {
        console.log('Session error for registration:', registration._id, sessionError.message);
        
        // Still add the participant even if session data fails
        const participantData = {
          _id: registration._id,
          user_id: registration.user_id,
          event_id: registration.event_id,
          payment_proof_url: registration.payment_proof_url,
          payment_amount: registration.payment_amount,
          payment_status: registration.payment_status,
          payment_verified_by: registration.payment_verified_by,
          payment_verified_at: registration.payment_verified_at,
          rejection_reason: registration.rejection_reason,
          registration_status: registration.registration_status,
          createdAt: registration.createdAt,
          updatedAt: registration.updatedAt,
          session_registrations: []
        };

        participantsWithSessions.push(participantData);
      }
    }

    // Add sessions to event object
    const eventWithSessions = {
      ...event.toObject(),
      sessions: eventSessions
    };

    // Calculate stats
    const stats = {
      total_participants: participantsWithSessions.length,
      pending_payments: participantsWithSessions.filter(p => p.payment_status === 'pending').length,
      confirmed_registrations: participantsWithSessions.filter(p => p.registration_status === 'confirmed').length,
      total_attendances: participantsWithSessions.reduce((total, participant) => {
        const attendedSessions = participant.session_registrations.filter(session => 
          session.attendance && session.attendance.attended
        );
        return total + attendedSessions.length;
      }, 0)
    };

    res.json({
      event: eventWithSessions,
      participants: participantsWithSessions,
      stats: stats,
      total_participants: participantsWithSessions.length
    });

  } catch (err) {
    console.error('Error:', err.message);
    
    res.status(500).json({ 
      message: err.message,
      event: null,
      participants: [],
      total_participants: 0
    });
  }
};



