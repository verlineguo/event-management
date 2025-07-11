const Event = require('../models/Event');
const Session = require('../models/Session');
const Registration = require('../models/Registration');
const SessionRegistration = require('../models/sessionRegistration');
const QRCode = require('qrcode');
const { v4: uuidv4 } = require('uuid');
const mongoose = require('mongoose');
const Attendance = require('../models/Attendance');
const Certificate = require('../models/Certificate');

// Check if user already registered for an event
exports.checkRegistration = async (req, res) => {
  try {
    const { id } = req.params; // event_id
    const userId = req.user._id;

    const existingRegistration = await Registration.findOne({
      event_id: id,
      user_id: userId,
      registration_status: { $in: ['registered', 'confirmed', 'pending'] }
    });

    res.json(!!existingRegistration);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Calculate total payment based on selected sessions
const calculateTotalPayment = async (selectedSessions) => {
  const sessions = await Session.find({ _id: { $in: selectedSessions } });
  return sessions.reduce((total, session) => total + (session.session_fee || 0), 0);
};

exports.createRegistration = async (req, res) => {
  try {
    const { event_id, selected_sessions, payment_proof_url, payment_amount } = req.body;
    const userId = req.user._id;

    // Check existing registration
    const existingRegistration = await Registration.findOne({
      event_id,
      user_id: userId,
      registration_status: { $in: ['confirmed'] }
    });

    if (existingRegistration) {
      return res.status(400).json({ message: 'Anda sudah terdaftar untuk event ini' });
    }

    // Find draft or pending registration
    const existingDraftOrPending = await Registration.findOne({
      event_id,
      user_id: userId,
      registration_status: { $in: ['draft', 'registered'] }
    });

    // Get event details
    const event = await Event.findById(event_id);
    if (!event) {
      return res.status(404).json({ message: 'Event tidak ditemukan' });
    }

    // Validate selected sessions
    const sessions = await Session.find({ 
      _id: { $in: selected_sessions }, 
      event_id 
    });

    if (sessions.length !== selected_sessions.length) {
      return res.status(400).json({ message: 'Beberapa sesi tidak valid' });
    }

    // Calculate total payment based on selected sessions
    const calculatedTotal = await calculateTotalPayment(selected_sessions);

    // Check session capacities
    for (const session of sessions) {
      const sessionRegistrationCount = await SessionRegistration.countDocuments({
        session_id: session._id,
        status: { $in: ['registered', 'completed'] }
      });

      const sessionMaxParticipants = session.max_participants || event.max_participants;
      if (sessionRegistrationCount >= sessionMaxParticipants) {
        return res.status(400).json({ 
          message: `Sesi "${session.title}" sudah penuh` 
        });
      }
    }

    let registration;
    
    // Handle existing draft or pending registration
    if (existingDraftOrPending) {
      console.log('Updating existing registration:', existingDraftOrPending._id);
      
      // Update existing draft/pending
      existingDraftOrPending.registration_status = calculatedTotal > 0 ? 'registered' : 'confirmed';
      existingDraftOrPending.payment_status = calculatedTotal > 0 ? 'pending' : 'approved'; 
      existingDraftOrPending.payment_proof_url = payment_proof_url || null;
      existingDraftOrPending.payment_amount = calculatedTotal;
      existingDraftOrPending.updatedAt = new Date();
      
      registration = await existingDraftOrPending.save();
      
      // Delete old session registrations
      await SessionRegistration.deleteMany({
        registration_id: registration._id
      });
      
    } else {
      // Create new registration
      registration = await Registration.create({
        event_id,
        user_id: userId,
        payment_amount: calculatedTotal,
        payment_proof_url: payment_proof_url || null,
        registration_status: calculatedTotal > 0 ? 'registered' : 'confirmed',
        payment_status: calculatedTotal > 0 ? 'pending' : 'approved'
      });
    }

    console.log('Registration created/updated:', registration._id, 'Status:', registration.registration_status);

    // Create session registrations
    const sessionRegistrations = [];
    for (const sessionId of selected_sessions) {
      try {
        const sessionRegData = {
          registration_id: registration._id,
          session_id: sessionId,
          user_id: userId,
          status: 'registered',
          registered_at: new Date(),
          qr_used: false
        };

        const sessionReg = new SessionRegistration(sessionRegData);
        const savedSessionReg = await sessionReg.save();
        sessionRegistrations.push(savedSessionReg);
        
        console.log('Session registration created:', savedSessionReg._id);
      } catch (sessionError) {
        console.error('Session registration error:', sessionError);
        await Registration.findByIdAndDelete(registration._id);
        throw new Error(`Failed to create session registration: ${sessionError.message}`);
      }
    }

    console.log('Session registrations created:', sessionRegistrations.length);
    
    // Populate registration for response
    const populatedRegistration = await Registration.findById(registration._id)
      .populate('event_id', 'name description max_participants')
      .populate('user_id', 'name email');

    console.log('Sending success response for registration:', registration._id);

    res.status(201).json({
      message: 'Registrasi berhasil dibuat',
      registration: populatedRegistration,
      session_registrations: sessionRegistrations
    });

  } catch (err) {
    console.error('createRegistration error:', err);
    
    if (err.name === 'ValidationError') {
      const validationErrors = Object.values(err.errors).map(e => e.message);
      return res.status(400).json({ 
        message: 'Validation error', 
        errors: validationErrors 
      });
    }
    
    if (err.code === 121) {
      return res.status(400).json({ 
        message: 'Document validation failed', 
        error: 'Please check the data format and required fields' 
      });
    }
    
    res.status(500).json({ message: err.message });
  }
};

exports.getMyRegistrations = async (req, res) => {
  try {
    const userId = req.user._id;

    const registrations = await Registration.find({ user_id: userId })
      .populate('event_id', 'name description max_participants status')
      .sort({ createdAt: -1 });

    // Get session registration details for each registration
    const registrationsWithDetails = await Promise.all(
      registrations.map(async (registration) => {
        const sessionRegistrations = await SessionRegistration.find({
          registration_id: registration._id
        }).populate('session_id', 'title date start_time end_time location speaker session_fee');

        return {
          ...registration.toObject(),
          session_registrations: sessionRegistrations
        };
      })
    );

    res.json(registrationsWithDetails);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get single registration
// Get single registration - UPDATED VERSION
exports.getRegistration = async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user._id;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId
    })
      .populate('event_id', 'name description max_participants status')
      .populate('user_id', 'name email');

    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    // Get session registrations with populated session data
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title description date start_time end_time location speaker status session_fee');

    // For each session registration, get attendance and certificate data
    const sessionRegistrationsWithDetails = await Promise.all(
      sessionRegistrations.map(async (sessionReg) => {
        const sessionRegObj = sessionReg.toObject();

        // Get attendance data
        const attendance = await Attendance.findOne({
          session_registration_id: sessionReg._id,
          user_id: userId
        }).populate('scanned_by', 'name');

        // Get certificate data
        const certificate = await Certificate.findOne({
          session_id: sessionReg.session_id._id,
          user_id: userId,
          registration_id: registration._id
        }).populate('uploaded_by', 'name');

        // Add attendance and certificate data to session registration
        sessionRegObj.attendance = attendance;
        sessionRegObj.certificate = certificate;

        return sessionRegObj;
      })
    );

    res.json({
      ...registration.toObject(),
      session_registrations: sessionRegistrationsWithDetails
    });
  } catch (err) {
    console.error('Error in getRegistration:', err);
    res.status(500).json({ message: err.message });
  }
};

// Generate QR codes for confirmed registration
exports.getQRCodes = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const userId = req.user._id;
    
    const registration = await Registration.findOne({
      _id: id,
      user_id: userId,
      registration_status: 'confirmed'
    }).populate('event_id', 'name date location')
    .populate('user_id', 'name email');
    
    if (!registration) {
      return res.status(404).json({
        message: 'Registrasi tidak ditemukan atau belum dikonfirmasi'
      });
    }
    
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title date start_time end_time location speaker');
    
    const qrCodes = [];
    
    for (const sessionReg of sessionRegistrations) {
      // Check if QR token already exists
      if (!sessionReg.qr_token) {
        // Generate unique token
        const uniqueToken = crypto.randomUUID();
        
        console.log('Generating QR token for session registration:', sessionReg._id);
        console.log('Generated token:', uniqueToken);
        
        // Save the token
        sessionReg.qr_token = uniqueToken;
        await sessionReg.save();
        
        console.log('Token saved successfully');
      }
      
      // Generate QR code image if not exists
      if (!sessionReg.qr_code) {
        try {
          // QR code contains only the token
          const qrCodeDataURL = await QRCode.toDataURL(sessionReg.qr_token, {
            errorCorrectionLevel: 'H',
            type: 'image/png',
            quality: 0.92,
            margin: 1,
            width: 256
          });
          
          sessionReg.qr_code = qrCodeDataURL;
          await sessionReg.save();
          
          console.log('QR code image generated and saved');
        } catch (qrError) {
          console.error('Error generating QR code:', qrError);
          // Continue without QR code image if generation fails
        }
      }
      
      // Verify the token was saved
      const verifyReg = await SessionRegistration.findById(sessionReg._id);
      console.log('Verification - Token exists:', !!verifyReg.qr_token);
      console.log('Verification - Token value:', verifyReg.qr_token);
      
      qrCodes.push({
        session: sessionReg.session_id,
        session_registration_id: sessionReg._id,
        qr_code: sessionReg.qr_code,
        qr_token: sessionReg.qr_token, // Include token in response for debugging
        qr_used: sessionReg.qr_used,
        used_at: sessionReg.used_at
      });
    }
    
    res.json({
      registration: registration,
      qr_codes: qrCodes
    });
    
  } catch (err) {
    console.error('Get QR Codes Error:', err);
    res.status(500).json({ message: err.message });
  }
};

exports.saveDraft = async (req, res) => {
  try {
    const { event_id, selected_sessions, payment_amount } = req.body;
    const userId = req.user._id;
    
    const eventObjectId = new mongoose.Types.ObjectId(event_id);
    const userObjectId = new mongoose.Types.ObjectId(userId);

    const event = await Event.findById(eventObjectId);
    if (!event) {
      return res.status(404).json({ message: 'Event tidak ditemukan' });
    }

    // Validate sessions
    const sessionObjectIds = selected_sessions.map(id => new mongoose.Types.ObjectId(id));
    const sessions = await Session.find({
      _id: { $in: sessionObjectIds },
      event_id: eventObjectId
    });

    if (sessions.length !== selected_sessions.length) {
      return res.status(400).json({ message: 'Beberapa sesi tidak valid' });
    }

    // Calculate total payment based on selected sessions
    const calculatedTotal = await calculateTotalPayment(sessionObjectIds);

    // Check if registration already exists
    let registration = await Registration.findOne({
      event_id: eventObjectId,
      user_id: userObjectId,
      $or: [
        { registration_status: 'draft' },
        { registration_status: 'registered' }
      ]
    });

    if (registration) {
      // Update existing registration
      registration.payment_amount = calculatedTotal;
      registration.registration_status = 'draft';
      registration.payment_status = 'pending';
      registration.updatedAt = new Date();
      
      await registration.save();
    } else {
      const registrationData = {
        event_id: eventObjectId,
        user_id: userObjectId,
        payment_proof_url: null,
        payment_amount: calculatedTotal,
        payment_status: 'pending',
        payment_verified_by: null,
        payment_verified_at: null,
        rejection_reason: null,
        registration_status: 'draft',
      };
      
      try {
        registration = new Registration(registrationData);
        await registration.save();
      } catch (mongooseError) {
        console.error('Registration save error:', mongooseError);
        try {
          const result = await Registration.collection.insertOne(registrationData);
          registration = await Registration.findById(result.insertedId);
        } catch (directError) {
          throw directError;
        }
      }
    }

    // Delete existing session registrations for this draft
    await SessionRegistration.deleteMany({
      registration_id: registration._id
    });

    // Create session registrations WITHOUT QR code (for draft)
    const sessionRegistrations = [];
    
    for (const sessionId of sessionObjectIds) {
      const sessionRegData = {
        registration_id: registration._id,
        session_id: sessionId,
        user_id: userObjectId,
        status: 'registered',
        registered_at: new Date(),
        qr_used: false
      };
      
      try {
        const sessionReg = new SessionRegistration(sessionRegData);
        const savedSessionReg = await sessionReg.save();
        sessionRegistrations.push(savedSessionReg);
        
        console.log(`Session registration saved: ${savedSessionReg._id}`);
        
      } catch (sessionError) {
        console.error('Session Registration Error:', {
          name: sessionError.name,
          message: sessionError.message,
          errors: sessionError.errors,
          data: sessionRegData
        });
        
        throw new Error(`Gagal menyimpan session registration: ${sessionError.message}`);
      }
    }

    // Verifikasi bahwa session registrations berhasil tersimpan
    const savedCount = await SessionRegistration.countDocuments({
      registration_id: registration._id
    });
    
    console.log(`Total session registrations saved: ${savedCount} out of ${selected_sessions.length}`);

    res.json({
      message: 'Draft berhasil disimpan',
      registration_id: registration._id,
      session_registrations_count: savedCount,
      session_registrations: sessionRegistrations.map(sr => sr._id),
      total_payment: calculatedTotal
    });
    
  } catch (err) {
    console.error('saveDraft error:', err);
    res.status(500).json({
      message: 'Gagal menyimpan draft registrasi',
      error: err.message
    });
  }
};

exports.getDraft = async (req, res) => {
  try {
    const { id } = req.params; // event_id
    const userId = req.user._id;

    console.log('Getting draft for event:', id, 'user:', userId);

    const draft = await Registration.findOne({
      event_id: id,
      user_id: userId,
      registration_status: 'draft'
    }).populate('event_id', 'name');

    if (!draft) {
      console.log('No draft found');
      return res.json(null);
    }

    console.log('Draft found:', draft._id);

    const sessionRegistrations = await SessionRegistration.find({
      registration_id: draft._id
    }).populate('session_id', '_id title date start_time end_time session_fee');

    console.log('Session registrations found:', sessionRegistrations.length);

    const draftWithSessions = {
      ...draft.toObject(),
      session_registrations: sessionRegistrations,
      selected_sessions: sessionRegistrations.map(sr => sr.session_id._id.toString())
    };

    res.json(draftWithSessions);
  } catch (err) {
    console.error('Error getting draft:', err);
    res.status(500).json({ message: err.message });
  }
};

// Delete registration draft
exports.deleteDraft = async (req, res) => {
  try {
    const { id } = req.params; // event_id
    const userId = req.user._id;

    await Registration.deleteOne({
      event_id: id,
      user_id: userId,
      registration_status: 'draft'
    });

    res.json({ message: 'Draft berhasil dihapus' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};
exports.reuploadPayment = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const userId = req.user._id;
    const { payment_proof_url } = req.body;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId
    });

    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    // Check if payment was actually rejected
    if (registration.payment_status !== 'rejected') {
      return res.status(400).json({ 
        message: 'Pembayaran tidak dalam status ditolak' 
      });
    }

    // Update payment proof and reset status to pending
    registration.payment_proof_url = payment_proof_url;
    registration.payment_status = 'pending';
    registration.rejection_reason = null; // Clear rejection reason
    registration.updatedAt = new Date();

    await registration.save();

    res.json({
      message: 'Bukti pembayaran berhasil diupload ulang',
      registration: registration
    });

  } catch (err) {
    console.error('reuploadPayment error:', err);
    res.status(500).json({ message: err.message });
  }
};

// Cancel registration (updated)
exports.cancelRegistration = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const userId = req.user._id;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId
    });

    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    // Check if registration can be cancelled
    if (registration.registration_status === 'confirmed') {
      return res.status(400).json({ 
        message: 'Registrasi yang sudah dikonfirmasi tidak dapat dibatalkan' 
      });
    }

    if (registration.registration_status === 'cancelled') {
      return res.status(400).json({ 
        message: 'Registrasi sudah dibatalkan sebelumnya' 
      });
    }

    // Update registration status
    registration.registration_status = 'cancelled';
    registration.cancelled_at = new Date();
    registration.updatedAt = new Date();
    
    await registration.save();

    // Update session registrations
    await SessionRegistration.updateMany(
      { registration_id: registration._id },
      { 
        status: 'cancelled',
        cancelled_at: new Date(),
        updatedAt: new Date()
      }
    );

    console.log('Registration cancelled successfully:', registration._id);

    res.json({ 
      message: 'Registrasi berhasil dibatalkan',
      registration: registration
    });
    
  } catch (err) {
    console.error('cancelRegistration error:', err);
    res.status(500).json({ message: err.message });
  }
};


// Get registration with detailed status (helper method)
exports.getRegistrationStatus = async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user._id;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId
    })
    .populate('event_id', 'name description')
    .populate('user_id', 'name email');

    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title date start_time end_time location speaker session_fee');

    // Add status info
    const statusInfo = {
      can_cancel: ['draft', 'registered'].includes(registration.registration_status),
      can_reupload: registration.payment_status === 'rejected',
      can_view_qr: registration.registration_status === 'confirmed',
      is_paid_event: registration.payment_amount > 0,
      refund_policy: 'Uang yang sudah dibayar tidak dapat dikembalikan'
    };

    res.json({
      ...registration.toObject(),
      session_registrations: sessionRegistrations,
      status_info: statusInfo
    });

  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};