// controllers/certificateController.js
const Certificate = require('../models/Certificate');
const SessionRegistration = require('../models/sessionRegistration');
const Session = require('../models/Session');
const Registration = require('../models/Registration');
const User = require('../models/User');
const mongoose = require('mongoose');

// Generate unique certificate number
const generateCertificateNumber = () => {
  const timestamp = Date.now().toString();
  const random = Math.random().toString(36).substring(2, 8).toUpperCase();
  return `CERT-${timestamp}-${random}`;
};

// Get user's certificates
exports.getMyCertificates = async (req, res) => {
  try {
    const user_id = req.user._id;
    
    const certificates = await Certificate.find({ 
      user_id, 
      status: 'issued' 
    })
    .populate('session_id', 'title date')
    .populate({
      path: 'session_id',
      populate: {
        path: 'event_id',
        select: 'name poster'
      }
    })
    .sort({ issued_date: -1 });

    res.json(certificates);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Download certificate
exports.downloadCertificate = async (req, res) => {
  try {
    const { participantId } = req.params;
    
    const certificate = await Certificate.findOne({ 
      user_id: participantId,
      status: 'issued' 
    });


    if (!certificate) {
      return res.status(404).json({ message: 'Certificate not found' });
    }

    res.json({
      certificate_number: certificate.certificate_number,
      download_url: certificate.file_url,
      issued_date: certificate.issued_date
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Upload single certificate (for committee)
exports.uploadCertificate = async (req, res) => {
  try {
    const { sessionId } = req.params;
    const { participant_id, certificate_path, uploaded_by } = req.body;

    // Validate session exists
    const session = await Session.findById(sessionId);
    if (!session) {
      return res.status(404).json({ message: 'Session not found' });
    }

    // Check if session is completed
    if (session.status !== 'completed') {
      return res.status(400).json({ message: 'Certificates can only be uploaded for completed sessions' });
    }

    // Find the session registration
    const sessionRegistration = await SessionRegistration.findOne({
      session_id: sessionId,
      user_id: participant_id
    });

    if (!sessionRegistration) {
      return res.status(404).json({ message: 'Participant not registered for this session' });
    }

    // Find the main registration
    const registration = await Registration.findById(sessionRegistration.registration_id);
    if (!registration) {
      return res.status(404).json({ message: 'Registration not found' });
    }

    // Check if certificate already exists
    const existingCertificate = await Certificate.findOne({
      session_id: sessionId,
      user_id: participant_id
    });

    if (existingCertificate) {
      // Update existing certificate
      existingCertificate.file_url = certificate_path;
      existingCertificate.uploaded_by = uploaded_by;
      existingCertificate.issued_date = new Date();
      existingCertificate.status = 'issued';
      
      await existingCertificate.save();
      
      return res.json({ 
        message: 'Certificate updated successfully',
        certificate: existingCertificate 
      });
    }

    // Create new certificate
    const certificate = new Certificate({
      session_id: sessionId,
      registration_id: registration._id,
      user_id: participant_id,
      certificate_number: generateCertificateNumber(),
      file_url: certificate_path,
      uploaded_by: uploaded_by,
      issued_date: new Date(),
      status: 'issued'
    });

    await certificate.save();

    res.status(201).json({ 
      message: 'Certificate uploaded successfully',
      certificate 
    });

  } catch (error) {
    console.error('Upload certificate error:', error);
    res.status(500).json({ message: error.message });
  }
};

// Bulk upload certificates (for committee)
exports.bulkUploadCertificates = async (req, res) => {
  try {
    const { sessionId } = req.params;
    const { certificates, uploaded_by } = req.body;

    // Validate input
    if (!certificates || !Array.isArray(certificates) || certificates.length === 0) {
      return res.status(400).json({ message: 'No certificates data provided' });
    }

    // Validate session exists
    const session = await Session.findById(sessionId);
    if (!session) {
      return res.status(404).json({ message: 'Session not found' });
    }

    // Check if session is completed
    if (session.status !== 'completed') {
      return res.status(400).json({ message: 'Certificates can only be uploaded for completed sessions' });
    }

    const results = {
      success: [],
      errors: []
    };

    // Process each certificate dengan better error handling
    for (let i = 0; i < certificates.length; i++) {
      const certData = certificates[i];
      
      try {
        const { participant_id, certificate_path } = certData;

        if (!participant_id || !certificate_path) {
          results.errors.push({
            index: i,
            participant_id: participant_id || 'unknown',
            error: 'Missing participant_id or certificate_path'
          });
          continue;
        }

        // Find the session registration dengan populasi
        const sessionRegistration = await SessionRegistration.findOne({
          session_id: sessionId,
          user_id: participant_id
        }).populate('user_id', 'name email');

        if (!sessionRegistration) {
          results.errors.push({
            index: i,
            participant_id,
            error: 'Participant not registered for this session'
          });
          continue;
        }

        // Find the main registration
        const registration = await Registration.findById(sessionRegistration.registration_id);
        if (!registration) {
          results.errors.push({
            index: i,
            participant_id,
            error: 'Registration not found'
          });
          continue;
        }

        // Check if certificate already exists
        let certificate = await Certificate.findOne({
          session_id: sessionId,
          user_id: participant_id
        });

        if (certificate) {
          // Update existing certificate
          certificate.file_url = certificate_path;
          certificate.uploaded_by = uploaded_by;
          certificate.issued_date = new Date();
          certificate.status = 'issued';
          
          await certificate.save();
          
          results.success.push({
            index: i,
            participant_id,
            participant_name: sessionRegistration.user_id.name,
            certificate_number: certificate.certificate_number,
            action: 'updated'
          });
        } else {
          // Create new certificate
          certificate = new Certificate({
            session_id: sessionId,
            registration_id: registration._id,
            user_id: participant_id,
            certificate_number: generateCertificateNumber(),
            file_url: certificate_path,
            uploaded_by: uploaded_by,
            issued_date: new Date(),
            status: 'issued'
          });

          await certificate.save();

          results.success.push({
            index: i,
            participant_id,
            participant_name: sessionRegistration.user_id.name,
            certificate_number: certificate.certificate_number,
            action: 'created'
          });
        }

      } catch (error) {
        console.error(`Error processing certificate ${i}:`, error);
        results.errors.push({
          index: i,
          participant_id: certData.participant_id || 'unknown',
          error: error.message
        });
      }
    }

    // Return comprehensive response
    res.json({
      message: `Processed ${certificates.length} certificates: ${results.success.length} successful, ${results.errors.length} failed`,
      success_count: results.success.length,
      error_count: results.errors.length,
      results
    });

  } catch (error) {
    console.error('Bulk upload certificates error:', error);
    res.status(500).json({ message: error.message });
  }
};

// Get certificates for a session (for committee)
exports.getSessionCertificates = async (req, res) => {
  try {
    const { sessionId } = req.params;

    const certificates = await Certificate.find({ 
      session_id: sessionId 
    })
    .populate('user_id', 'name email')
    .populate('uploaded_by', 'name')
    .sort({ issued_date: -1 });

    res.json(certificates);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Delete/Revoke certificate (for committee)
exports.revokeCertificate = async (req, res) => {
  try {
    const { participantId } = req.params;
    const { reason, participant_id, revoked_by } = req.body;

    // Validation
    if (!reason || reason.trim().length < 10) {
      return res.status(400).json({ message: 'Reason must be at least 10 characters long' });
    }

    // Use participant_id from body if available, otherwise use from params
    const userId = participant_id || participantId;

    // Find certificate by user_id (participant_id) and status issued
    const certificate = await Certificate.findOne({ 
      user_id: userId,
      status: 'issued' 
    }).populate('user_id', 'name email')
      .populate('session_id', 'title');

    if (!certificate) {
      return res.status(404).json({ message: 'Certificate not found or already revoked' });
    }

    // Create revocation log before updating
    const revocationLog = {
      certificate_id: certificate._id,
      certificate_number: certificate.certificate_number,
      user_id: certificate.user_id._id,
      user_name: certificate.user_id.name,
      session_title: certificate.session_id.title,
      reason: reason.trim(),
      revoked_by: revoked_by,
      revoked_date: new Date()
    };

    // Update certificate status to revoked
    certificate.status = 'revoked';
    certificate.notes = reason.trim();
    certificate.revoked_date = new Date();
    certificate.revoked_by = revoked_by;
    
    await certificate.save();

    // Optional: Save revocation log to separate collection for audit
    // await RevocationLog.create(revocationLog);

    res.json({ 
      message: 'Certificate revoked successfully',
      certificate: {
        _id: certificate._id,
        certificate_number: certificate.certificate_number,
        user_id: certificate.user_id._id,
        user_name: certificate.user_id.name,
        status: certificate.status,
        notes: certificate.notes,
        revoked_date: certificate.revoked_date
      }
    });
  } catch (error) {
    console.error('Revoke certificate error:', error);
    res.status(500).json({ message: error.message });
  }
};


exports.getSessionAttendanceWithCertificates = async (req, res) => {
  try {
    const { sessionId } = req.params;

    // Validate session exists
    const session = await Session.findById(sessionId);
    if (!session) {
      return res.status(404).json({ message: 'Session not found' });
    }

    // Get all attendances with user info
    const attendances = await mongoose.model('Attendance').find({ 
      session_id: sessionId 
    })
    .populate('user_id', 'name email phone')
    .populate('scanned_by', 'name')
    .sort({ check_in_time: -1 });

    // Get certificates for this session (including revoked ones for display)
    const certificates = await Certificate.find({ 
      session_id: sessionId
    });

    // Create a map of user_id to certificate
    const certificateMap = {};
    certificates.forEach(cert => {
      certificateMap[cert.user_id.toString()] = {
        certificate_path: cert.file_url,
        certificate_number: cert.certificate_number,
        certificate_issued_date: cert.issued_date,
        certificate_status: cert.status,
        revoked_date: cert.revoked_date,
        notes: cert.notes
      };
    });

    // Add certificate info to attendance records
    const attendancesWithCertificates = attendances.map(attendance => {
      const attendanceObj = attendance.toObject();
      const userId = attendance.user_id._id.toString();
      
      if (certificateMap[userId]) {
        Object.assign(attendanceObj, certificateMap[userId]);
      }
      
      return attendanceObj;
    });

    res.json({
      session: session,
      total_attendees: attendances.length,
      attendances: attendancesWithCertificates
    });
  } catch (error) {
    console.error('Get attendance with certificates error:', error);
    res.status(500).json({ message: error.message });
  }
};

// TAMBAHAN: Export attendance untuk CSV
exports.exportSessionAttendance = async (req, res) => {
  try {
    const { sessionId } = req.params;

    // Reuse the existing function
    const data = await this.getSessionAttendanceWithCertificates(req, res);
    
    // This will be handled by Laravel for CSV generation
    res.json(data);
  } catch (error) {
    console.error('Export attendance error:', error);
    res.status(500).json({ message: error.message });
  }
};