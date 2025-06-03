const Event = require('../models/Event');
const Session = require('../models/Session');
const Registration = require('../models/Registration');
const SessionRegistration = require('../models/sessionRegistration');
const QRCode = require('qrcode');
const { v4: uuidv4 } = require('uuid');
const mongoose = require('mongoose');


// Check if user already registered for an event
exports.checkRegistration = async (req, res) => {
  try {
    const { id } = req.params; // event_id

    const userId = req.user.id;

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

exports.createRegistration = async (req, res) => {
  try {
    const { event_id, selected_sessions, payment_proof_url, payment_amount } = req.body;
    const userId = req.user._id;

    // Check if already registered
    const existingRegistration = await Registration.findOne({
      event_id,
      user_id: userId,
      registration_status: { $in: ['registered', 'confirmed'] }
    });

    if (existingRegistration) {
      return res.status(400).json({ message: 'Anda sudah terdaftar untuk event ini' });
    }

    const existingDraft = await Registration.findOne({
      event_id,
      user_id: userId,
      registration_status: 'draft'
    });

    // Get event details
    const event = await Event.findById(event_id);
    if (!event) {
      return res.status(404).json({ message: 'Event tidak ditemukan' });
    }

    // Check event capacity
    const eventRegistrationCount = await Registration.countDocuments({
      event_id,
      registration_status: { $in: ['registered', 'confirmed'] }
    });

    if (eventRegistrationCount >= event.max_participants) {
      return res.status(400).json({ message: 'Event sudah penuh' });
    }

    // Validate selected sessions
    const sessions = await Session.find({ 
      _id: { $in: selected_sessions }, 
      event_id 
    });

    if (sessions.length !== selected_sessions.length) {
      return res.status(400).json({ message: 'Beberapa sesi tidak valid' });
    }

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
    if (existingDraft) {
      // Update draft menjadi registrasi
      existingDraft.registration_status = payment_amount > 0 ? 'registered' : 'confirmed';
      existingDraft.payment_status = payment_amount > 0 ? 'pending' : 'completed';
      existingDraft.payment_proof_url = payment_proof_url;
      existingDraft.registered_at = new Date();
      registration = await existingDraft.save();
    } else {
      // Buat registrasi baru
      const registrationId = `REG-${event_id.slice(-6).toUpperCase()}-${Date.now()}`;
      registration = await Registration.create({
        registration_id: registrationId,
        event_id,
        user_id: userId,
        selected_sessions,
        payment_amount: payment_amount || event.registration_fee || 0,
        payment_proof_url,
        registration_status: payment_amount > 0 ? 'registered' : 'confirmed',
        payment_status: payment_amount > 0 ? 'pending' : 'completed',
        registered_at: new Date()
      });
    }

    // Create/Update session registrations (masih tanpa QR code)
    await SessionRegistration.deleteMany({ registration_id: registration._id });
    
    const sessionRegistrations = [];
    for (const sessionId of selected_sessions) {
      const sessionReg = new SessionRegistration({
        registration_id: registration._id,
        session_id: sessionId,
        user_id: userId,
        status: 'registered',
        qr_code: null, // ✅ BENAR: QR code belum di-generate
        qr_used: false
      });
      await sessionReg.save();
      sessionRegistrations.push(sessionReg);
    }

    // Populate registration for response
    const populatedRegistration = await Registration.findById(registration._id)
      .populate('event_id', 'name description registration_fee location date')
      .populate('user_id', 'name email')
      .populate('selected_sessions', 'title date start_time end_time location');

    res.status(201).json({
      message: 'Registrasi berhasil dibuat',
      registration: populatedRegistration,
      session_registrations: sessionRegistrations
    });

  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get user's registrations
exports.getMyRegistrations = async (req, res) => {
  try {
    const userId = req.user.id;

    const registrations = await Registration.find({ user_id: userId })
      .populate('event_id', 'name description price location date status')
      .populate('selected_sessions', 'title date start_time end_time location')
      .sort({ registered_at: -1 });

    // Get session registration details for each registration
    const registrationsWithDetails = await Promise.all(
      registrations.map(async (registration) => {
        const sessionRegistrations = await SessionRegistration.find({
          registration_id: registration._id
        }).populate('session_id', 'title date start_time end_time location');

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
exports.getRegistration = async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user._id;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId
    })
      .populate('event_id', 'name description registration_fee max_participants status')
      .populate('user_id', 'name email');

    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title description date start_time end_time location speaker status');

    res.json({
      ...registration.toObject(),
      session_registrations: sessionRegistrations
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Generate QR codes for confirmed registration
exports.getQRCodes = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const userId = req.user.id;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId,
      registration_status: 'confirmed'
    }).populate('event_id', 'name date location');

    if (!registration) {
      return res.status(404).json({ 
        message: 'Registrasi tidak ditemukan atau belum dikonfirmasi' 
      });
    }

    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title date start_time end_time location');

    // Generate QR codes if not exists
    const qrCodes = [];
    for (const sessionReg of sessionRegistrations) {
      if (!sessionReg.qr_code) {
        // Generate unique QR data
        const qrData = {
          registration_id: registration._id,
          session_id: sessionReg.session_id._id,
          user_id: userId,
          event_id: registration.event_id._id,
          timestamp: new Date().toISOString(),
          uuid: uuidv4()
        };

        const qrString = JSON.stringify(qrData);
        const qrCodeDataURL = await QRCode.toDataURL(qrString, {
          errorCorrectionLevel: 'H',
          type: 'image/png',
          quality: 0.92,
          margin: 1,
          width: 256
        });

        // Update session registration with QR code
        sessionReg.qr_code = qrCodeDataURL;
        await sessionReg.save();
      }

      qrCodes.push({
        session: sessionReg.session_id,
        session_registration_id: sessionReg._id,
        qr_code: sessionReg.qr_code,
        qr_used: sessionReg.qr_used,
        used_at: sessionReg.used_at
      });
    }

    res.json({
      registration: registration,
      qr_codes: qrCodes
    });

  } catch (err) {
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
      registration.payment_amount = payment_amount;
      registration.registration_status = 'draft';
      registration.payment_status = 'pending';
      registration.updatedAt = new Date(); 
      await registration.save();
    } else {
      const registrationData = {
        event_id: eventObjectId,
        user_id: userObjectId,
        registration_number: `REG-${event_id.slice(-6).toUpperCase()}-${Date.now()}`,
        payment_proof_url: null,
        payment_amount: payment_amount || 0,
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
    for (const sessionId of sessionObjectIds) {
      try {
        const sessionRegData = {
          registration_id: registration._id,
          session_id: sessionId,
          user_id: userObjectId,
          qr_code: null, // ✅ BENAR: Tidak generate QR code untuk draft
          status: 'registered',
          registered_at: new Date()
        };
        
        const sessionReg = new SessionRegistration(sessionRegData);
        await sessionReg.save();
      } catch (sessionError) {
        console.error('Failed to create session registration:', sessionError.message);
      }
    }

    res.json({
      message: 'Draft berhasil disimpan',
      registration_id: registration._id
    });

  } catch (err) {
    res.status(500).json({ 
      message: 'Gagal menyimpan draft registrasi',
      error: err.message 
    });
  }
};


exports.getDraft = async (req, res) => {
  try {
    const { id } = req.params; // event_id
    const userId = req.user._id; // Handle both cases

    console.log('Getting draft for event:', id, 'user:', userId);

    const draft = await Registration.findOne({
      event_id: id,
      user_id: userId,
      registration_status: 'draft'
    }).populate('event_id', 'name registration_fee');

    if (!draft) {
      console.log('No draft found');
      return res.json(null);
    }

    console.log('Draft found:', draft._id);

    const sessionRegistrations = await SessionRegistration.find({
      registration_id: draft._id
    }).populate('session_id', '_id title date start_time end_time');

    console.log('Session registrations found:', sessionRegistrations.length);

    const draftWithSessions = {
      ...draft.toObject(),
      session_registrations: sessionRegistrations,
      selected_sessions: sessionRegistrations.map(sr => sr.session_id._id.toString()) // Convert to string array
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
    const userId = req.user.id;

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

// Cancel registration (before confirmation)
exports.cancelRegistration = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const userId = req.user.id;

    const registration = await Registration.findOne({
      _id: id,
      user_id: userId
    });

    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    if (registration.registration_status === 'confirmed') {
      return res.status(400).json({ 
        message: 'Registrasi yang sudah dikonfirmasi tidak dapat dibatalkan' 
      });
    }

    // Update registration status
    registration.registration_status = 'cancelled';
    registration.cancelled_at = new Date();
    await registration.save();

    // Update session registrations
    await SessionRegistration.updateMany(
      { registration_id: registration._id },
      { 
        status: 'cancelled',
        cancelled_at: new Date()
      }
    );

    res.json({ message: 'Registrasi berhasil dibatalkan' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Admin: Confirm payment and registration
exports.confirmPayment = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const { notes } = req.body;

    const registration = await Registration.findById(id);
    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    if (registration.registration_status === 'confirmed') {
      return res.status(400).json({ message: 'Registrasi sudah dikonfirmasi' });
    }

    // Update registration
    registration.registration_status = 'confirmed';
    registration.payment_status = 'completed';
    registration.confirmed_at = new Date();
    registration.confirmation_notes = notes;
    await registration.save();

    // Update session registrations dan generate QR codes
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    });

    for (const sessionReg of sessionRegistrations) {
      // Generate QR code setelah konfirmasi pembayaran
      const qrData = {
        registration_id: registration._id,
        session_id: sessionReg.session_id,
        user_id: sessionReg.user_id,
        event_id: registration.event_id,
        timestamp: new Date().toISOString(),
        uuid: uuidv4()
      };

      const qrString = JSON.stringify(qrData);
      const qrCodeDataURL = await QRCode.toDataURL(qrString, {
        errorCorrectionLevel: 'H',
        type: 'image/png',
        quality: 0.92,
        margin: 1,
        width: 256
      });

      // ✅ BENAR: QR code baru di-generate setelah konfirmasi pembayaran
      sessionReg.status = 'confirmed';
      sessionReg.qr_code = qrCodeDataURL;
      await sessionReg.save();
    }

    res.json({ 
      message: 'Pembayaran dan registrasi berhasil dikonfirmasi',
      registration 
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Admin: Reject payment
exports.rejectPayment = async (req, res) => {
  try {
    const { id } = req.params; // registration_id
    const { reason } = req.body;

    const registration = await Registration.findById(id);
    if (!registration) {
      return res.status(404).json({ message: 'Registrasi tidak ditemukan' });
    }

    // Update registration
    registration.registration_status = 'rejected';
    registration.payment_status = 'rejected';
    registration.rejected_at = new Date();
    registration.rejection_reason = reason;
    await registration.save();

    // Update session registrations
    await SessionRegistration.updateMany(
      { registration_id: registration._id },
      { 
        status: 'rejected',
        rejected_at: new Date()
      }
    );

    res.json({ 
      message: 'Pembayaran ditolak',
      registration 
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
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