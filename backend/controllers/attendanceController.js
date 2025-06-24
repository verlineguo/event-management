// controllers/qrController.js
const SessionRegistration = require('../models/sessionRegistration');
const Registration = require('../models/Registration');
const Session = require('../models/Session');
const Event = require('../models/Event');
const Attendance = require('../models/Attendance');
const Certificate = require('../models/Certificate');
const mongoose = require('mongoose');

// Scan QR Code for attendance
// Scan QR Code for attendance
exports.scanQRCode = async (req, res) => {
  try {
    const { qr_token, scanned_by } = req.body;
    

    if (!qr_token || !scanned_by) {
      return res.status(400).json({ 
        message: 'QR token dan ID scanner diperlukan' 
      });
    }

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

    // Debug logging untuk memahami struktur data
    console.log('sessionRegistration structure:', {
      id: sessionRegistration?._id,
      registration_id: sessionRegistration?.registration_id ? {
        id: sessionRegistration.registration_id._id,
        registration_status: sessionRegistration.registration_id.registration_status,
        payment_status: sessionRegistration.registration_id.payment_status,
        event_id: sessionRegistration.registration_id.event_id
      } : null,
      session_id: sessionRegistration?.session_id ? {
        id: sessionRegistration.session_id._id,
        title: sessionRegistration.session_id.title,
        status: sessionRegistration.session_id.status
      } : null,
      user_id: sessionRegistration?.user_id ? {
        id: sessionRegistration.user_id._id,
        name: sessionRegistration.user_id.name
      } : null
    });

    // Validate registration_id exists and is populated
    if (!sessionRegistration.registration_id) {
      return res.status(400).json({ 
        message: 'Data registrasi tidak lengkap - registration_id tidak ditemukan' 
      });
    }

    // Validate event_id exists and is populated
    if (!sessionRegistration.registration_id.event_id) {
      return res.status(400).json({ 
        message: 'Event tidak ditemukan - kemungkinan event sudah dihapus atau referensi tidak valid',
        debug_info: {
          registration_id: sessionRegistration.registration_id._id,
          has_event_id: !!sessionRegistration.registration_id.event_id
        }
      });
    }

    // Check if event is cancelled
    if (sessionRegistration.registration_id.event_id.status === 'cancelled') {
      return res.status(400).json({ 
        message: 'Event telah dibatalkan' 
      });
    }

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

      console.log(attendances);
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

exports.getScannedParticipants = async (req, res) => {
  try {
    const { eventId } = req.params;
    if (!eventId) {
      return res.status(400).json({ 
        message: 'Event ID is required',
        scanned_participants: []
      });
    }

    // Cari semua session untuk event ini
    const sessions = await Session.find({ event_id: eventId }).select('_id');
const sessionIds = sessions.map(s => new mongoose.Types.ObjectId(s._id));

    const attendances = await Attendance.find({ session_id: { $in: sessionIds } })
      .populate('session_id', 'title date start_time end_time event_id')
      .populate('user_id', 'name email')
      .populate('session_registration_id')
      .sort({ createdAt: -1 });
    // Transform data untuk response
    const scannedParticipants = attendances
      .filter(att => att.session_id && att.user_id)
      .map(attendance => ({
        name: attendance.user_id.name,
        email: attendance.user_id.email,
        session: attendance.session_id.title,
        session_id: attendance.session_id._id,
        scanned_at: attendance.createdAt,
        user_id: attendance.user_id._id,
        check_in_time: attendance.check_in_time,
        attendance_method: attendance.attendance_method
      }));

    res.json({
      attendance: attendances,
      scanned_participants: scannedParticipants,
      total_scanned: scannedParticipants.length
    });

  } catch (err) {
    console.error('Error:', err.message);
    res.status(500).json({ 
      message: err.message,
      scanned_participants: []
    });
  }
};


exports.getParticipantDetails = async (req, res) => {
  try {
    const { participantId } = req.params;

    // Validate participant ID
    if (!participantId) {
      return res.status(400).json({
        success: false,
        message: 'Participant ID is required'
      });
    }

    // Get participant (registration) data
    const participant = await Registration.findById(participantId)
      .populate('user_id', 'name email phone profile')
      .populate('event_id', 'name description')
      .populate('payment_verified_by', 'name email');

    if (!participant) {
      return res.status(404).json({
        success: false,
        message: 'Participant not found'
      });
    }

    // Get session registrations
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: participant._id
    })
    .populate('session_id', 'title description date start_time end_time location speaker')
    .sort({ 'session_id.date': 1 });

    // Get attendance history
    const attendanceHistory = await Attendance.find({
      user_id: participant.user_id._id,
      session_id: { $in: sessionRegistrations.map(sr => sr.session_id._id) }
    })
    .populate('session_id', 'title date start_time end_time')
    .populate('scanned_by', 'name')
    .sort({ check_in_time: -1 });

    // Add attendance info to session registrations
    const sessionsWithAttendance = sessionRegistrations.map(sessionReg => {
      const attendance = attendanceHistory.find(att => 
        att.session_id._id.toString() === sessionReg.session_id._id.toString()
      );
      
      return {
        ...sessionReg.toObject(),
        attendance: attendance ? {
          attended: attendance.attended,
          check_in_time: attendance.check_in_time,
          attendance_method: attendance.attendance_method,
          scanned_by: attendance.scanned_by
        } : null
      };
    });

    // Get certificates (if any)
    const certificates = await Certificate.find({
      user_id: participant.user_id._id,
      session_id: { $in: sessionRegistrations.map(sr => sr.session_id._id) },
      status: 'issued'
    })
    .populate('session_id', 'title')
    .sort({ issued_date: -1 });

    // Prepare response data
    const responseData = {
      participant: participant.toObject(),
      sessions: sessionsWithAttendance,
      attendance_history: attendanceHistory,
      certificates: certificates,
      stats: {
        total_sessions: sessionRegistrations.length,
        attended_sessions: attendanceHistory.filter(a => a.attended).length,
        total_certificates: certificates.length,
        attendance_rate: sessionRegistrations.length > 0 
          ? Math.round((attendanceHistory.filter(a => a.attended).length / sessionRegistrations.length) * 100)
          : 0
      }
    };

    res.json({
      success: true,
      data: responseData
    });

  } catch (err) {
    console.error('Error getting participant details:', err);
    res.status(500).json({
      success: false,
      message: err.message
    });
  }
};



