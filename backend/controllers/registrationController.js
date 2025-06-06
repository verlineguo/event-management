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
    
    // Handle existing draft or pending registration
    if (existingDraftOrPending) {
      console.log('Updating existing registration:', existingDraftOrPending._id);
      
      // Update existing draft/pending
      existingDraftOrPending.registration_status = payment_amount > 0 ? 'registered' : 'confirmed';
      existingDraftOrPending.payment_status = payment_amount > 0 ? 'pending' : 'approved'; 
      existingDraftOrPending.payment_proof_url = payment_proof_url;
      existingDraftOrPending.payment_amount = payment_amount || event.registration_fee || 0;
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
        payment_amount: payment_amount || event.registration_fee || 0,
        payment_proof_url,
        registration_status: payment_amount > 0 ? 'registered' : 'confirmed',
        payment_status: payment_amount > 0 ? 'pending' : 'approved' // Changed from 'completed' to 'approved'
      });
    }

    console.log('Registration created/updated:', registration._id, 'Status:', registration.registration_status);

    // Create session registrations - FIX: Ensure all required fields are provided
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
          // Don't set qr_code to null explicitly - let schema handle default/sparse behavior
        };

        const sessionReg = new SessionRegistration(sessionRegData);
        const savedSessionReg = await sessionReg.save();
        sessionRegistrations.push(savedSessionReg);
        
        console.log('Session registration created:', savedSessionReg._id);
      } catch (sessionError) {
        console.error('Session registration error:', sessionError);
        // If one session registration fails, clean up and return error
        await Registration.findByIdAndDelete(registration._id);
        throw new Error(`Failed to create session registration: ${sessionError.message}`);
      }
    }

    console.log('Session registrations created:', sessionRegistrations.length);
    
    // Populate registration for response
    const populatedRegistration = await Registration.findById(registration._id)
      .populate('event_id', 'name description registration_fee max_participants')
      .populate('user_id', 'name email');

    console.log('Sending success response for registration:', registration._id);

    res.status(201).json({
      message: 'Registrasi berhasil dibuat',
      registration: populatedRegistration,
      session_registrations: sessionRegistrations
    });

  } catch (err) {
    console.error('createRegistration error:', err);
    
    // Provide more detailed error information
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
      .populate('event_id', 'name description registration_fee max_participants status')
      .sort({ createdAt: -1 }); // Using createdAt instead of registered_at

    // Get session registration details for each registration
    const registrationsWithDetails = await Promise.all(
      registrations.map(async (registration) => {
        const sessionRegistrations = await SessionRegistration.find({
          registration_id: registration._id
        }).populate('session_id', 'title date start_time end_time location speaker');

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
    const userId = req.user._id;

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
      // PERBAIKAN: Deklarasi sessionRegData di luar try block
      const sessionRegData = {
        registration_id: registration._id,
        session_id: sessionId,
        user_id: userObjectId,
        // PERBAIKAN: Jangan set qr_code sama sekali untuk draft
        // qr_code akan di-generate saat registration dikonfirmasi
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
          data: sessionRegData // Sekarang sessionRegData bisa diakses di sini
        });
        
        // PERBAIKAN: Throw error agar proses dihentikan jika ada masalah
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
      session_registrations: sessionRegistrations.map(sr => sr._id)
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

// Cancel registration (before confirmation)
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

