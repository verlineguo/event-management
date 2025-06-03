// controllers/certificateController.js
const Certificate = require('../models/Certificate');
const SessionRegistration = require('../models/sessionRegistration');

// Get user's certificates
exports.getMyCertificates = async (req, res) => {
  try {
    const user_id = req.user.id;

    const certificates = await Certificate.find({ user_id, status: 'issued' })
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
    const { id } = req.params;
    const user_id = req.user.id;

    const certificate = await Certificate.findOne({ 
      _id: id, 
      user_id, 
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